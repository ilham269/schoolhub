<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\{AutoSaveAnswerRequest, LogActivityRequest, SubmitExamRequest};
use App\Models\{CalonSiswa, PpdbExam, PpdbQuestion, PpdbOption, User, PpdbExamSession, PpdbExamAttempt, PpdbExamAnswer, PpdbActivityLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PpdbController extends Controller
{
    private function staff(Request $r): void { abort_unless(in_array(strtolower($r->user()->role), ['admin','guru']), 403, 'Hanya admin atau guru.'); }
    private function candidate(Request $r): CalonSiswa { 
        $role = strtolower($r->user()->role);
        abort_unless(in_array($role, ['murid', 'calon_siswa']), 403, 'Hanya calon siswa yang bisa mengakses ujian PPDB.');
        return CalonSiswa::where('user_id', $r->user()->id)->firstOrFail(); 
    }

    public function register(Request $r) {
        $data=$r->validate(['nama'=>'required|string|max:255','email'=>'required|email|unique:calon_siswas,email','nisn'=>'required|digits:10|unique:calon_siswas,nisn','no_hp'=>'required|string|max:20','asal_sekolah'=>'required|string|max:255','jurusan'=>'required|string|max:100','documents.*'=>'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120']);
        $documents=[]; foreach($r->file('documents',[]) as $file) $documents[]=$file->store('ppdb/documents','public');
        $candidate=CalonSiswa::create([...collect($data)->except('documents')->all(),'documents'=>$documents]);
        return response()->json(['success'=>true,'message'=>'Pendaftaran diterima. Tunggu verifikasi dan akun dari admin.','data'=>$candidate],201);
    }
    public function candidates(Request $r) { $this->staff($r); return response()->json(['success'=>true,'data'=>CalonSiswa::with('user')->latest()->get()]); }
    public function updateCandidate(Request $r, CalonSiswa $calon) { abort_unless(strtolower($r->user()->role)==='admin',403); $data=$r->validate(['status'=>'required|in:Menunggu,Terverifikasi,Ditolak']); $calon->update($data); return ['success'=>true,'data'=>$calon]; }
    public function createAccount(Request $r, CalonSiswa $calon) {
        abort_unless(strtolower($r->user()->role)==='admin',403); abort_if($calon->user_id,422,'Akun sudah dibuat.');
        $data=$r->validate(['password'=>'required|string|min:8','email'=>'nullable|email|unique:users,email']);
        $user=User::create(['name'=>$calon->nama,'email'=>$data['email']??$calon->email,'password'=>Hash::make($data['password']),'role'=>'Calon_Siswa','is_active'=>true]);
        $calon->update(['user_id'=>$user->id,'status'=>'Terverifikasi']); return response()->json(['success'=>true,'message'=>'Akun ujian berhasil dibuat.','data'=>$calon->fresh('user')],201);
    }
    public function profile(Request $r) { return ['success'=>true,'data'=>$this->candidate($r)]; }
    public function exams(Request $r) {
        $candidate=$this->candidate($r); $now=now();
        $exams=PpdbExam::where('is_published',true)->where(fn($q)=>$q->whereNull('start_at')->orWhere('start_at','<=',$now))->where(fn($q)=>$q->whereNull('end_at')->orWhere('end_at','>=',$now))->withCount('questions')->get();
        $attempts=DB::table('ppdb_exam_attempts')->where('calon_siswa_id',$candidate->id)->get()->keyBy('exam_id');
        return ['success'=>true,'data'=>$exams->map(fn($e)=>[...$e->toArray(),'attempt'=>$attempts[$e->id]??null])];
    }
    
    public function start(Request $r, PpdbExam $exam) {
        $c = $this->candidate($r);
        
        abort_unless($exam->is_published, 404, 'Ujian tidak tersedia.');
        
        if ($exam->start_at && now()->lt($exam->start_at)) {
            abort(422, 'Ujian belum dimulai.');
        }
        if ($exam->end_at && now()->gt($exam->end_at)) {
            abort(422, 'Ujian sudah berakhir.');
        }
        
        $session = PpdbExamSession::where([
            'exam_id' => $exam->id,
            'calon_siswa_id' => $c->id,
        ])->first();
        
        if ($session && $session->status === 'submitted') {
            abort(422, 'Ujian sudah dikerjakan.');
        }
        
        if ($session && $session->isExpired() && $session->status === 'active') {
            $session->status = 'expired';
            $session->save();
            
            if ($session->attempt_id) {
                $this->autoSubmitExpired($session);
            }
            
            abort(422, 'Sesi ujian telah berakhir.');
        }
        
        if ($session && $session->isActive()) {
            PpdbActivityLog::logEvent(
                $session->id,
                'network_reconnect',
                'Student reconnected to exam',
                ['ip' => $r->ip(), 'user_agent' => $r->userAgent()]
            );
            
            $session->last_activity_at = now();
            $session->disconnect_count++;
            $session->updateSuspicionScore();
            $session->save();
            
            return $this->sessionResponse($session, $exam);
        }
        
        $attempt = PpdbExamAttempt::create([
            'exam_id' => $exam->id,
            'calon_siswa_id' => $c->id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);
        
        $questionIds = $exam->questions()->pluck('id')->toArray();
        shuffle($questionIds);
        
        $session = PpdbExamSession::create([
            'exam_id' => $exam->id,
            'calon_siswa_id' => $c->id,
            'attempt_id' => $attempt->id,
            'started_at' => now(),
            'expires_at' => now()->addMinutes($exam->duration_minutes),
            'last_activity_at' => now(),
            'status' => 'active',
            'ip_address' => $r->ip(),
            'user_agent' => $r->userAgent(),
            'device_fingerprint' => $r->header('X-Device-Fingerprint'),
            'question_order' => $questionIds,
        ]);
        
        PpdbActivityLog::logEvent(
            $session->id,
            'session_started',
            'Exam session started',
            [
                'ip' => $r->ip(),
                'user_agent' => $r->userAgent(),
                'device_fingerprint' => $r->header('X-Device-Fingerprint'),
            ]
        );
        
        return $this->sessionResponse($session, $exam);
    }
    
    private function sessionResponse(PpdbExamSession $session, PpdbExam $exam): array
    {
        $questions = $exam->questions()
            ->with('options')
            ->whereIn('id', $session->question_order)
            ->get()
            ->sortBy(function ($question) use ($session) {
                return array_search($question->id, $session->question_order);
            })
            ->values();
        
        $answers = [];
        if ($session->attempt_id) {
            $existingAnswers = PpdbExamAnswer::where('attempt_id', $session->attempt_id)->get();
            foreach ($existingAnswers as $answer) {
                $answers[$answer->question_id] = [
                    'option_id' => $answer->option_id,
                    'answer_text' => $answer->answer_text,
                ];
            }
        }
        
        return [
            'success' => true,
            'data' => [
                'session' => [
                    'id' => $session->id,
                    'expires_at' => $session->expires_at->toISOString(),
                    'remaining_seconds' => $session->getRemainingSeconds(),
                    'status' => $session->status,
                ],
                'exam' => [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'description' => $exam->description,
                    'duration_minutes' => $exam->duration_minutes,
                    'show_result' => $exam->show_result,
                ],
                'questions' => $questions->map(function ($q) {
                    return [
                        'id' => $q->id,
                        'question' => $q->question,
                        'type' => $q->type,
                        'score' => $q->score,
                        'options' => $q->options->map(function ($opt) {
                            return [
                                'id' => $opt->id,
                                'option_text' => $opt->option_text,
                            ];
                        }),
                    ];
                }),
                'saved_answers' => $answers,
            ]
        ];
    }

    public function autoSave(AutoSaveAnswerRequest $r)
    {
        $data = $r->validated();
        
        $session = PpdbExamSession::findOrFail($data['session_id']);
        
        $c = $this->candidate($r);
        abort_unless($session->calon_siswa_id === $c->id, 403);
        
        if ($session->isExpired()) {
            $session->status = 'expired';
            $session->save();
            $this->autoSubmitExpired($session);
            return ['success' => false, 'message' => 'Sesi ujian telah berakhir.', 'expired' => true];
        }
        
        abort_unless($session->status === 'active', 422, 'Sesi tidak aktif.');
        
        $session->last_activity_at = now();
        $session->save();
        
        PpdbExamAnswer::updateOrCreate(
            [
                'attempt_id' => $session->attempt_id,
                'question_id' => $data['question_id'],
            ],
            [
                'option_id' => $data['option_id'] ?? null,
                'answer_text' => $data['answer_text'] ?? null,
                'updated_at' => now(),
            ]
        );
        
        PpdbActivityLog::logEvent(
            $session->id,
            'auto_save',
            'Answer auto-saved',
            ['question_id' => $data['question_id']]
        );
        
        return [
            'success' => true,
            'remaining_seconds' => $session->getRemainingSeconds(),
        ];
    }
    
    public function logActivity(LogActivityRequest $r)
    {
        $data = $r->validated();
        
        $session = PpdbExamSession::findOrFail($data['session_id']);
        
        $c = $this->candidate($r);
        abort_unless($session->calon_siswa_id === $c->id, 403);
        
        abort_unless($session->status === 'active', 422, 'Sesi tidak aktif.');
        
        switch ($data['event']) {
            case 'tab_switch':
                $session->tab_switches++;
                break;
            case 'fullscreen_exit':
                $session->fullscreen_exits++;
                break;
            case 'copy_attempt':
                $session->copy_attempts++;
                break;
            case 'paste_attempt':
                $session->paste_attempts++;
                break;
            case 'right_click':
                $session->right_click_attempts++;
                break;
            case 'network_disconnect':
                $session->last_disconnect_at = now();
                break;
        }
        
        $session->updateSuspicionScore();
        $session->last_activity_at = now();
        $session->save();
        
        PpdbActivityLog::logEvent(
            $session->id,
            $data['event'],
            'Suspicious activity detected',
            $data['metadata'] ?? []
        );
        
        return ['success' => true, 'suspicion_score' => $session->suspicion_score];
    }
    
    public function sessionStatus(Request $r, int $sessionId)
    {
        $session = PpdbExamSession::findOrFail($sessionId);
        
        $c = $this->candidate($r);
        abort_unless($session->calon_siswa_id === $c->id, 403);
        
        if ($session->isExpired() && $session->status === 'active') {
            $session->status = 'expired';
            $session->save();
            $this->autoSubmitExpired($session);
        }
        
        return [
            'success' => true,
            'data' => [
                'session_id' => $session->id,
                'status' => $session->status,
                'remaining_seconds' => $session->getRemainingSeconds(),
                'expires_at' => $session->expires_at->toISOString(),
                'is_expired' => $session->isExpired(),
            ]
        ];
    }
    
    public function submit(SubmitExamRequest $r, PpdbExam $exam) {
        $c = $this->candidate($r);
        
        $data = $r->validated();
        
        $session = PpdbExamSession::findOrFail($data['session_id']);
        
        abort_unless($session->calon_siswa_id === $c->id, 403);
        abort_unless($session->exam_id === $exam->id, 422, 'Session tidak cocok dengan ujian.');
        abort_if($session->status === 'submitted', 422, 'Ujian sudah dikirim.');
        
        if ($session->isExpired()) {
            $session->status = 'expired';
            $session->save();
            $this->autoSubmitExpired($session);
            abort(422, 'Waktu ujian telah habis.');
        }
        
        $attempt = PpdbExamAttempt::findOrFail($session->attempt_id);
        
        $questions = $exam->questions()->with('options')->get()->keyBy('id');
        
        $earned = 0;
        $total = $questions->sum('score');
        
        DB::transaction(function() use ($data, $questions, $attempt, $session, &$earned, $total) {
            foreach ($data['answers'] ?? [] as $answer) {
                $q = $questions->get($answer['question_id']);
                if (!$q) continue;
                
                $option = $q->options->firstWhere('id', $answer['option_id'] ?? null);
                $correct = $q->type === 'multiple_choice' ? (bool)($option?->is_correct) : null;
                $score = $correct ? $q->score : 0;
                $earned += $score;
                
                PpdbExamAnswer::updateOrCreate(
                    [
                        'attempt_id' => $attempt->id,
                        'question_id' => $q->id,
                    ],
                    [
                        'option_id' => $option?->id,
                        'answer_text' => $answer['answer_text'] ?? null,
                        'is_correct' => $correct,
                        'score' => $score,
                        'updated_at' => now(),
                    ]
                );
            }
            
            $score = $total > 0 ? round($earned / $total * 100, 2) : 0;
            $attempt->score = $score;
            $attempt->status = 'submitted';
            $attempt->submitted_at = now();
            $attempt->save();
            
            $session->status = 'submitted';
            $session->submitted_at = now();
            $session->save();
            
            PpdbActivityLog::logEvent(
                $session->id,
                'session_ended',
                'Exam submitted successfully',
                ['score' => $score, 'suspicion_score' => $session->suspicion_score]
            );
        });
        
        return [
            'success' => true,
            'message' => 'Jawaban berhasil dikirim.',
            'data' => [
                'score' => $exam->show_result ? $attempt->score : null,
                'show_result' => $exam->show_result,
                'suspicion_score' => $session->suspicion_score,
            ]
        ];
    }
    
    private function autoSubmitExpired(PpdbExamSession $session): void
    {
        if (!$session->attempt_id) return;
        
        $attempt = PpdbExamAttempt::find($session->attempt_id);
        if (!$attempt || $attempt->status === 'submitted') return;
        
        $exam = $session->exam;
        $questions = $exam->questions()->with('options')->get()->keyBy('id');
        
        $existingAnswers = PpdbExamAnswer::where('attempt_id', $attempt->id)->get();
        
        $earned = 0;
        $total = $questions->sum('score');
        
        foreach ($existingAnswers as $answer) {
            $q = $questions->get($answer->question_id);
            if (!$q) continue;
            
            if ($q->type === 'multiple_choice' && $answer->option_id) {
                $option = $q->options->firstWhere('id', $answer->option_id);
                $correct = (bool)($option?->is_correct);
                $score = $correct ? $q->score : 0;
                
                $answer->is_correct = $correct;
                $answer->score = $score;
                $answer->save();
                
                $earned += $score;
            }
        }
        
        $score = $total > 0 ? round($earned / $total * 100, 2) : 0;
        $attempt->score = $score;
        $attempt->status = 'submitted';
        $attempt->submitted_at = now();
        $attempt->save();
        
        PpdbActivityLog::logEvent(
            $session->id,
            'session_ended',
            'Exam auto-submitted due to timeout',
            ['score' => $score, 'reason' => 'timeout']
        );
    }
    
    public function manageExams(Request $r) {$this->staff($r);return ['success'=>true,'data'=>PpdbExam::with('questions.options')->latest()->get()];}
    public function storeExam(Request $r) {$this->staff($r);$data=$r->validate(['title'=>'required|string|max:255','description'=>'nullable|string','start_at'=>'nullable|date','end_at'=>'nullable|date|after:start_at','duration_minutes'=>'required|integer|min:1','passing_score'=>'nullable|numeric|min:0|max:100','is_published'=>'boolean','show_result'=>'boolean']);$data['created_by']=$r->user()->id;return response()->json(['success'=>true,'data'=>PpdbExam::create($data)],201);}
    
    public function updateExam(Request $r, PpdbExam $exam) {
        $this->staff($r);
        $data=$r->validate([
            'title'=>'sometimes|string|max:255',
            'description'=>'nullable|string',
            'start_at'=>'nullable|date',
            'end_at'=>'nullable|date|after:start_at',
            'duration_minutes'=>'sometimes|integer|min:1',
            'passing_score'=>'nullable|numeric|min:0|max:100',
            'is_published'=>'boolean',
            'show_result'=>'boolean'
        ]);
        $exam->update($data);
        return response()->json(['success'=>true,'data'=>$exam]);
    }
    
    public function securityDashboard(Request $r)
    {
        $this->staff($r);
        
        $examId = $r->input('exam_id');
        
        $query = PpdbExamSession::with(['candidate', 'exam'])
            ->orderBy('suspicion_score', 'desc');
        
        if ($examId) {
            $query->where('exam_id', $examId);
        }
        
        $sessions = $query->get()->map(function ($session) {
            return [
                'session_id' => $session->id,
                'student_name' => $session->candidate->nama,
                'exam_title' => $session->exam->title,
                'status' => $session->status,
                'started_at' => $session->started_at,
                'submitted_at' => $session->submitted_at,
                'suspicion_score' => $session->suspicion_score,
                'tab_switches' => $session->tab_switches,
                'fullscreen_exits' => $session->fullscreen_exits,
                'copy_attempts' => $session->copy_attempts,
                'paste_attempts' => $session->paste_attempts,
                'right_click_attempts' => $session->right_click_attempts,
                'disconnect_count' => $session->disconnect_count,
                'ip_address' => $session->ip_address,
                'remaining_seconds' => $session->getRemainingSeconds(),
            ];
        });
        
        return ['success' => true, 'data' => $sessions];
    }
    
    public function sessionActivityLogs(Request $r, int $sessionId)
    {
        $this->staff($r);
        
        $session = PpdbExamSession::with('activityLogs')->findOrFail($sessionId);
        
        return [
            'success' => true,
            'data' => [
                'session' => [
                    'id' => $session->id,
                    'student_name' => $session->candidate->nama,
                    'exam_title' => $session->exam->title,
                    'status' => $session->status,
                    'suspicion_score' => $session->suspicion_score,
                ],
                'logs' => $session->activityLogs->map(function ($log) {
                    return [
                        'event' => $log->event,
                        'description' => $log->description,
                        'metadata' => $log->metadata,
                        'created_at' => $log->created_at,
                    ];
                }),
            ]
        ];
    }
    
    public function storeQuestion(Request $r, PpdbExam $exam) {
        $this->staff($r);
        
        $data=$r->validate([
            'question'=>'required|string',
            'type'=>'required|in:multiple_choice,essay',
            'score'=>'required|numeric|min:0',
            'options'=>'required_if:type,multiple_choice|array|min:2',
            'options.*.option_text'=>'required_with:options|string',
            'options.*.is_correct'=>'boolean'
        ]);
        
        if($data['type']==='multiple_choice'){
            if(!isset($data['options']) || empty($data['options'])) {
                abort(422,'Pilihan jawaban wajib diisi untuk soal pilihan ganda.');
            }
            if(!collect($data['options'])->contains('is_correct',true)) {
                abort(422,'Pilih satu jawaban benar.');
            }
        }
        
        $q=$exam->questions()->create([
            'question'=>$data['question'],
            'type'=>$data['type'],
            'score'=>$data['score'],
            'order'=>$exam->questions()->count()+1
        ]);
        
        if($data['type']==='multiple_choice' && isset($data['options'])) {
            foreach($data['options'] as $i=>$o) {
                $q->options()->create([
                    'option_text'=>$o['option_text'],
                    'is_correct'=>$o['is_correct']??false,
                    'order'=>$i+1
                ]);
            }
        }
        
        return response()->json(['success'=>true,'data'=>$q->load('options')],201);
    }
    
    public function monitorSessions(Request $r, PpdbExam $exam)
    {
        $this->staff($r);
        
        $sessions = PpdbExamSession::with(['candidate', 'attempt'])
            ->where('exam_id', $exam->id)
            ->orderBy('suspicion_score', 'desc')
            ->orderBy('started_at', 'desc')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'candidate' => [
                        'id' => $session->candidate->id,
                        'nama' => $session->candidate->nama,
                        'email' => $session->candidate->email,
                    ],
                    'status' => $session->status,
                    'started_at' => $session->started_at,
                    'expires_at' => $session->expires_at,
                    'submitted_at' => $session->submitted_at,
                    'score' => $session->attempt?->score,
                    'suspicion_score' => $session->suspicion_score,
                    'activities' => [
                        'tab_switches' => $session->tab_switches,
                        'fullscreen_exits' => $session->fullscreen_exits,
                        'copy_attempts' => $session->copy_attempts,
                        'paste_attempts' => $session->paste_attempts,
                        'right_click_attempts' => $session->right_click_attempts,
                        'disconnect_count' => $session->disconnect_count,
                    ],
                    'ip_address' => $session->ip_address,
                    'remaining_seconds' => $session->getRemainingSeconds(),
                ];
            });
        
        return [
            'success' => true,
            'data' => $sessions,
        ];
    }
    
    public function sessionActivityLog(Request $r, int $sessionId)
    {
        $this->staff($r);
        
        $session = PpdbExamSession::with('candidate')->findOrFail($sessionId);
        
        $logs = PpdbActivityLog::where('session_id', $sessionId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'event' => $log->event,
                    'description' => $log->description,
                    'metadata' => $log->metadata,
                    'created_at' => $log->created_at,
                ];
            });
        
        return [
            'success' => true,
            'data' => [
                'session' => [
                    'id' => $session->id,
                    'candidate' => [
                        'nama' => $session->candidate->nama,
                        'email' => $session->candidate->email,
                    ],
                    'status' => $session->status,
                    'suspicion_score' => $session->suspicion_score,
                    'started_at' => $session->started_at,
                    'submitted_at' => $session->submitted_at,
                ],
                'logs' => $logs,
            ],
        ];
    }
    
    public function suspendSession(Request $r, int $sessionId)
    {
        abort_unless(strtolower($r->user()->role) === 'admin', 403, 'Hanya admin yang dapat menangguhkan sesi.');
        
        $data = $r->validate([
            'reason' => 'required|string',
        ]);
        
        $session = PpdbExamSession::findOrFail($sessionId);
        
        abort_if($session->status === 'submitted', 422, 'Ujian sudah selesai.');
        
        $session->status = 'suspended';
        $session->save();
        
        PpdbActivityLog::logEvent(
            $session->id,
            'session_suspended',
            'Session suspended by admin',
            [
                'reason' => $data['reason'],
                'admin_id' => $r->user()->id,
                'admin_name' => $r->user()->name,
            ]
        );
        
        return [
            'success' => true,
            'message' => 'Sesi berhasil ditangguhkan.',
        ];
    }
}