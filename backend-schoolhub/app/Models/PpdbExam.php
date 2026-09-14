<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PpdbExam extends Model {protected $fillable=['created_by','title','description','start_at','end_at','duration_minutes','passing_score','is_published','show_result'];protected function casts():array{return ['start_at'=>'datetime','end_at'=>'datetime','is_published'=>'boolean','show_result'=>'boolean'];}public function questions(){return $this->hasMany(PpdbQuestion::class,'exam_id')->orderBy('order');}}
