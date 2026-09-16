<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Setting;
use App\Models\TagihanSpp;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateKuitansiPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $tagihanId) {}

    public function handle(): void
    {
        try {
            $tagihan = TagihanSpp::with(['murid.user', 'murid.kelas', 'pembayarans'])->findOrFail($this->tagihanId);
            if ($tagihan->status !== 'LUNAS') return;
            $payment = $tagihan->pembayarans->firstWhere('status', 'SUCCESS');
            $school = Setting::whereIn('key', ['school.name', 'school.address'])->pluck('value', 'key');
            $path = 'kuitansi/' . str_replace('/', '-', $tagihan->invoice_number) . '.pdf';
            $html = $this->receiptHtml($tagihan, $payment, $school->all());
            Storage::disk('public')->put($path, Pdf::loadHtml($html)->setPaper('a4')->output());
            $tagihan->update(['kuitansi_path' => $path]);
        } catch (\Throwable $exception) {
            Log::error('Gagal generate kuitansi PDF', ['tagihan_id' => $this->tagihanId, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }

    private function receiptHtml(TagihanSpp $tagihan, $payment, array $school): string
    {
        $schoolName = e($school['school.name'] ?? config('app.name'));
        $schoolAddress = e($school['school.address'] ?? '');
        $student = e($tagihan->murid->user->name);
        $class = e($tagihan->murid->kelas?->name ?? '-');
        $transaction = e($payment?->transaction_id ?? 'Manual');
        $method = e($payment?->payment_type ?? 'cash');
        return "<html><body style='font-family:DejaVu Sans'><h2>{$schoolName}</h2><p>{$schoolAddress}</p><hr><h2>KUITANSI SPP — <span style='color:green'>LUNAS</span></h2><table border='1' cellpadding='7' width='100%'><tr><td>Invoice</td><td>{$tagihan->invoice_number}</td></tr><tr><td>Murid / Kelas</td><td>{$student} / {$class}</td></tr><tr><td>Periode</td><td>{$tagihan->periode->format('F Y')}</td></tr><tr><td>Jumlah</td><td>Rp ".number_format((float) $tagihan->jumlah, 0, ',', '.')."</td></tr><tr><td>Denda</td><td>Rp ".number_format((float) $tagihan->denda, 0, ',', '.')."</td></tr><tr><th>Total</th><th>Rp ".number_format((float) $tagihan->total, 0, ',', '.')."</th></tr><tr><td>Tanggal bayar</td><td>{$tagihan->paid_at?->format('d-m-Y H:i')}</td></tr><tr><td>Metode / Transaksi</td><td>{$method} / {$transaction}</td></tr></table></body></html>";
    }
}
