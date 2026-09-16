<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TagihanSpp;
use App\Models\Pembayaran;
use App\Models\SlipGaji;
use App\Models\Murid;
use App\Models\Karyawan;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KeuanganController extends Controller
{
    /**
     * Dashboard Keuangan - Rekap tagihan & pembayaran
     */
    public function dashboard(): JsonResponse
    {
        try {
            // Stats Tagihan SPP
            $totalTagihan = TagihanSpp::count();
            $tagihanUnpaid = TagihanSpp::where('status', 'UNPAID')->count();
            $tagihanLunas = TagihanSpp::where('status', 'LUNAS')->count();
            $tagihanExpired = TagihanSpp::where('status', 'EXPIRED')->count();
            
            // Total Nominal
            $totalNominalTagihan = TagihanSpp::sum('total');
            $totalNominalLunas = TagihanSpp::where('status', 'LUNAS')->sum('total');
            $totalNominalPending = TagihanSpp::where('status', 'UNPAID')->sum('total');
            
            // Stats Pembayaran
            $totalPembayaran = Pembayaran::count();
            $pembayaranSuccess = Pembayaran::where('status', 'SUCCESS')->count();
            $pembayaranPending = Pembayaran::where('status', 'PENDING')->count();
            $pembayaranFailed = Pembayaran::where('status', 'FAILED')->count();
            
            // Recent transactions (10 terbaru)
            $recentTransactions = Pembayaran::with(['tagihan.murid.user', 'tagihan.murid.kelas'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($pembayaran) {
                    return [
                        'id' => $pembayaran->id,
                        'transaction_id' => $pembayaran->transaction_id,
                        'murid_name' => $pembayaran->tagihan->murid->user->name ?? 'N/A',
                        'kelas' => $pembayaran->tagihan->murid->kelas->name ?? 'N/A',
                        'amount' => $pembayaran->gross_amount,
                        'status' => $pembayaran->status,
                        'payment_type' => $pembayaran->payment_type,
                        'paid_at' => $pembayaran->paid_at,
                        'created_at' => $pembayaran->created_at,
                    ];
                });
            
            // Tagihan jatuh tempo minggu ini
            $jatuhTempoMingguIni = TagihanSpp::where('status', 'UNPAID')
                ->whereBetween('jatuh_tempo', [now()->startOfWeek(), now()->endOfWeek()])
                ->with(['murid.user', 'murid.kelas'])
                ->get()
                ->map(function ($tagihan) {
                    return [
                        'id' => $tagihan->id,
                        'invoice_number' => $tagihan->invoice_number,
                        'murid_name' => $tagihan->murid->user->name ?? 'N/A',
                        'kelas' => $tagihan->murid->kelas->name ?? 'N/A',
                        'total' => $tagihan->total,
                        'jatuh_tempo' => $tagihan->jatuh_tempo,
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => [
                        'tagihan' => [
                            'total' => $totalTagihan,
                            'unpaid' => $tagihanUnpaid,
                            'lunas' => $tagihanLunas,
                            'expired' => $tagihanExpired,
                        ],
                        'nominal' => [
                            'total_tagihan' => $totalNominalTagihan,
                            'total_lunas' => $totalNominalLunas,
                            'total_pending' => $totalNominalPending,
                        ],
                        'pembayaran' => [
                            'total' => $totalPembayaran,
                            'success' => $pembayaranSuccess,
                            'pending' => $pembayaranPending,
                            'failed' => $pembayaranFailed,
                        ],
                    ],
                    'recent_transactions' => $recentTransactions,
                    'jatuh_tempo_minggu_ini' => $jatuhTempoMingguIni,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dashboard',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get all tagihan SPP dengan filter
     */
    public function getTagihanSpp(Request $request): JsonResponse
    {
        try {
            $query = TagihanSpp::with(['murid.user', 'murid.kelas']);
            
            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            // Filter by periode
            if ($request->has('periode')) {
                $query->where('periode', $request->periode);
            }
            
            // Filter by kelas
            if ($request->has('kelas_id')) {
                $query->whereHas('murid', function ($q) use ($request) {
                    $q->where('kelas_id', $request->kelas_id);
                });
            }
            
            // Search by murid name or NIS
            if ($request->has('search')) {
                $search = $request->search;
                $query->whereHas('murid', function ($q) use ($search) {
                    $q->where('nis', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($q2) use ($search) {
                          $q2->where('name', 'like', "%{$search}%");
                      });
                });
            }
            
            $tagihan = $query->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 25);
            
            return response()->json([
                'success' => true,
                'data' => $tagihan,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data tagihan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Create tagihan SPP untuk murid tertentu
     */
    public function createTagihan(Request $request): JsonResponse
    {
        $request->validate([
            'murid_id' => 'required|exists:murids,id',
            'periode' => 'required|date',
            'jumlah' => 'required|numeric|min:0',
            'jatuh_tempo' => 'required|date|after:periode',
            'notes' => 'nullable|string',
        ]);
        
        try {
            // Check if tagihan already exists
            $exists = TagihanSpp::where('murid_id', $request->murid_id)
                ->where('periode', $request->periode)
                ->exists();
            
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tagihan untuk periode ini sudah ada',
                ], 422);
            }
            
            // Generate invoice number
            $periode = Carbon::parse($request->periode);
            $prefix = Setting::where('key', 'invoice_prefix')->value('value') ?? 'INV/SPP';
            $lastInvoice = TagihanSpp::whereYear('periode', $periode->year)
                ->whereMonth('periode', $periode->month)
                ->orderBy('id', 'desc')
                ->first();
            
            $number = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -5) + 1 : 1;
            $invoiceNumber = sprintf('%s/%s/%05d', $prefix, $periode->format('Ym'), $number);
            
            $tagihan = TagihanSpp::create([
                'murid_id' => $request->murid_id,
                'periode' => $request->periode,
                'jumlah' => $request->jumlah,
                'denda' => 0,
                'total' => $request->jumlah,
                'jatuh_tempo' => $request->jatuh_tempo,
                'status' => 'UNPAID',
                'invoice_number' => $invoiceNumber,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Tagihan berhasil dibuat',
                'data' => $tagihan->load(['murid.user', 'murid.kelas']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat tagihan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Update tagihan SPP
     */
    public function updateTagihan(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'jumlah' => 'sometimes|numeric|min:0',
            'denda' => 'sometimes|numeric|min:0',
            'jatuh_tempo' => 'sometimes|date',
            'status' => 'sometimes|in:UNPAID,PENDING,LUNAS,EXPIRED,CANCELLED',
            'notes' => 'nullable|string',
        ]);
        
        try {
            $tagihan = TagihanSpp::findOrFail($id);
            
            // Only allow update if not LUNAS
            if ($tagihan->status === 'LUNAS' && $request->has('jumlah')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengubah tagihan yang sudah LUNAS',
                ], 422);
            }
            
            if ($request->has('jumlah')) {
                $tagihan->jumlah = $request->jumlah;
                $tagihan->total = $request->jumlah + ($tagihan->denda ?? 0);
            }
            
            if ($request->has('denda')) {
                $tagihan->denda = $request->denda;
                $tagihan->total = $tagihan->jumlah + $request->denda;
            }
            
            if ($request->has('jatuh_tempo')) {
                $tagihan->jatuh_tempo = $request->jatuh_tempo;
            }
            
            if ($request->has('status')) {
                $tagihan->status = $request->status;
            }
            
            if ($request->has('notes')) {
                $tagihan->notes = $request->notes;
            }
            
            $tagihan->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Tagihan berhasil diupdate',
                'data' => $tagihan->load(['murid.user', 'murid.kelas']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate tagihan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Delete tagihan SPP
     */
    public function deleteTagihan(int $id): JsonResponse
    {
        try {
            $tagihan = TagihanSpp::findOrFail($id);
            
            // Only allow delete if UNPAID or CANCELLED
            if (!in_array($tagihan->status, ['UNPAID', 'CANCELLED'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya dapat menghapus tagihan UNPAID atau CANCELLED',
                ], 422);
            }
            
            $tagihan->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Tagihan berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tagihan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get all slip gaji
     */
    public function getSlipGaji(Request $request): JsonResponse
    {
        try {
            $query = SlipGaji::with(['karyawan.user', 'creator', 'approver']);
            
            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            // Filter by periode
            if ($request->has('periode')) {
                $query->where('periode', $request->periode);
            }
            
            $slipGaji = $query->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 25);
            
            return response()->json([
                'success' => true,
                'data' => $slipGaji,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data slip gaji',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Create slip gaji
     */
    public function createSlipGaji(Request $request): JsonResponse
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'periode' => 'required|date',
            'gaji_pokok' => 'required|numeric|min:0',
            'tunjangan' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'potongan' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string',
        ]);
        
        try {
            // Check if slip already exists
            $exists = SlipGaji::where('karyawan_id', $request->karyawan_id)
                ->where('periode', $request->periode)
                ->exists();
            
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Slip gaji untuk periode ini sudah ada',
                ], 422);
            }
            
            // Generate slip number
            $periode = Carbon::parse($request->periode);
            $prefix = Setting::where('key', 'slip_gaji_prefix')->value('value') ?? 'SLIP/GAJ';
            $lastSlip = SlipGaji::whereYear('periode', $periode->year)
                ->whereMonth('periode', $periode->month)
                ->orderBy('id', 'desc')
                ->first();
            
            $number = $lastSlip ? (int) substr($lastSlip->slip_number, -5) + 1 : 1;
            $slipNumber = sprintf('%s/%s/%05d', $prefix, $periode->format('Ym'), $number);
            
            $tunjangan = $request->tunjangan ?? 0;
            $bonus = $request->bonus ?? 0;
            $potongan = $request->potongan ?? 0;
            $totalGaji = $request->gaji_pokok + $tunjangan + $bonus - $potongan;
            
            $slipGaji = SlipGaji::create([
                'karyawan_id' => $request->karyawan_id,
                'periode' => $request->periode,
                'gaji_pokok' => $request->gaji_pokok,
                'tunjangan' => $tunjangan,
                'bonus' => $bonus,
                'potongan' => $potongan,
                'total_gaji' => $totalGaji,
                'status' => 'DRAFT',
                'slip_number' => $slipNumber,
                'catatan' => $request->catatan,
                'dibuat_oleh' => auth()->id(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Slip gaji berhasil dibuat',
                'data' => $slipGaji->load(['karyawan.user', 'creator']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat slip gaji',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Update slip gaji
     */
    public function updateSlipGaji(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'gaji_pokok' => 'sometimes|numeric|min:0',
            'tunjangan' => 'sometimes|numeric|min:0',
            'bonus' => 'sometimes|numeric|min:0',
            'potongan' => 'sometimes|numeric|min:0',
            'catatan' => 'nullable|string',
        ]);
        
        try {
            $slipGaji = SlipGaji::findOrFail($id);
            
            // Only allow update if DRAFT
            if ($slipGaji->status !== 'DRAFT') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya dapat mengubah slip gaji dengan status DRAFT',
                ], 422);
            }
            
            if ($request->has('gaji_pokok')) {
                $slipGaji->gaji_pokok = $request->gaji_pokok;
            }
            if ($request->has('tunjangan')) {
                $slipGaji->tunjangan = $request->tunjangan;
            }
            if ($request->has('bonus')) {
                $slipGaji->bonus = $request->bonus;
            }
            if ($request->has('potongan')) {
                $slipGaji->potongan = $request->potongan;
            }
            if ($request->has('catatan')) {
                $slipGaji->catatan = $request->catatan;
            }
            
            // Recalculate total
            $slipGaji->total_gaji = $slipGaji->calculateTotal();
            $slipGaji->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Slip gaji berhasil diupdate',
                'data' => $slipGaji->load(['karyawan.user', 'creator']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate slip gaji',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Approve slip gaji (Admin only)
     */
    public function approveSlipGaji(int $id): JsonResponse
    {
        try {
            $slipGaji = SlipGaji::findOrFail($id);
            
            if ($slipGaji->status !== 'DRAFT') {
                return response()->json([
                    'success' => false,
                    'message' => 'Slip gaji sudah di-approve atau paid',
                ], 422);
            }
            
            $slipGaji->status = 'APPROVED';
            $slipGaji->approved_oleh = auth()->id();
            $slipGaji->approved_at = now();
            $slipGaji->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Slip gaji berhasil di-approve',
                'data' => $slipGaji->load(['karyawan.user', 'creator', 'approver']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal approve slip gaji',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Mark slip gaji as PAID
     */
    public function markAsPaid(int $id): JsonResponse
    {
        try {
            $slipGaji = SlipGaji::findOrFail($id);
            
            if ($slipGaji->status !== 'APPROVED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Slip gaji harus di-approve dulu sebelum mark as paid',
                ], 422);
            }
            
            $slipGaji->status = 'PAID';
            $slipGaji->paid_at = now();
            $slipGaji->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Slip gaji berhasil ditandai PAID',
                'data' => $slipGaji->load(['karyawan.user', 'creator', 'approver']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mark as paid',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Delete slip gaji
     */
    public function deleteSlipGaji(int $id): JsonResponse
    {
        try {
            $slipGaji = SlipGaji::findOrFail($id);
            
            // Only allow delete if DRAFT
            if ($slipGaji->status !== 'DRAFT') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya dapat menghapus slip gaji dengan status DRAFT',
                ], 422);
            }
            
            $slipGaji->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Slip gaji berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus slip gaji',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
