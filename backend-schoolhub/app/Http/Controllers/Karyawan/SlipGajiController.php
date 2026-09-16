<?php

declare(strict_types=1);

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\SlipGaji;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SlipGajiController extends Controller
{
    public function index() { return response()->json(SlipGaji::with('karyawan.user')->latest('periode')->paginate(20)); }
    public function create() { return response()->json(['karyawan' => Karyawan::with('user')->get()]); }
    public function store(Request $request) { $slip = SlipGaji::create($this->data($request) + ['status' => 'DRAFT', 'slip_number' => 'SLIP/GAJ/'.now()->format('Ym').'/'.str_pad((string) (SlipGaji::count() + 1), 5, '0', STR_PAD_LEFT), 'dibuat_oleh' => $request->user()->id]); return response()->json(['data' => $slip], 201); }
    public function show(SlipGaji $slipGaji) { return response()->json(['data' => $slipGaji->load('karyawan.user')]); }
    public function edit(SlipGaji $slipGaji) { abort_unless($slipGaji->isDraft(), 422); return response()->json(['data' => $slipGaji, 'karyawan' => Karyawan::with('user')->get()]); }
    public function update(Request $request, SlipGaji $slipGaji) { abort_unless($slipGaji->isDraft(), 422); $slipGaji->update($this->data($request)); return response()->json(['data' => $slipGaji->fresh()]); }
    public function destroy(SlipGaji $slipGaji) { abort_unless($slipGaji->isDraft(), 422); $slipGaji->delete(); return response()->noContent(); }
    public function approve(Request $request, SlipGaji $slipGaji) { abort_unless($slipGaji->isDraft(), 422); $slipGaji->update(['status' => 'APPROVED', 'approved_oleh' => $request->user()->id, 'approved_at' => now()]); return response()->json(['data' => $slipGaji->fresh()]); }
    public function markPaid(SlipGaji $slipGaji) { abort_unless($slipGaji->isApproved(), 422); $slipGaji->update(['status' => 'PAID', 'paid_at' => now()]); return response()->json(['data' => $slipGaji->fresh()]); }
    private function data(Request $request): array { $d=$request->validate(['karyawan_id'=>'required|exists:karyawans,id','periode'=>'required|date','gaji_pokok'=>'required|numeric|min:0','tunjangan'=>'nullable|numeric|min:0','bonus'=>'nullable|numeric|min:0','potongan'=>'nullable|numeric|min:0','catatan'=>'nullable|string']); $d['tunjangan']=$d['tunjangan']??0; $d['bonus']=$d['bonus']??0; $d['potongan']=$d['potongan']??0; $d['total_gaji']=$d['gaji_pokok']+$d['tunjangan']+$d['bonus']-$d['potongan']; return $d; }
}
