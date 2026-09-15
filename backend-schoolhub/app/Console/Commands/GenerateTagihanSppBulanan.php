<?php
// app/Console/Commands/GenerateTagihanSppBulanan.php

namespace App\Console\Commands;

use App\Models\Murid;
use App\Models\Setting;
use App\Models\TagihanSpp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateTagihanSppBulanan extends Command
{
    protected $signature = 'spp:generate-tagihan {--periode= : Format YYYY-MM, default bulan ini}';
    protected $description = 'Generate tagihan SPP bulanan untuk semua murid aktif';

    public function handle(): int
    {
        $periode = $this->option('periode')
            ? \Carbon\Carbon::createFromFormat('Y-m', $this->option('periode'))->startOfMonth()
            : now()->startOfMonth();

        $this->info("Generate tagihan SPP untuk periode: {$periode->format('Y-m')}");

        // Ambil settings, kasih fallback kalau belum ke-seed
        $nominalDefault = (float) Setting::where('key', 'nominal_spp_default')->value('value') ?? 500000;
        $jatuhTempoHari = (int) Setting::where('key', 'jatuh_tempo_spp_hari')->value('value') ?? 10;

        $jatuhTempo = $periode->copy()->addDays($jatuhTempoHari - 1);

        $muridAktif = Murid::whereHas('user', fn ($q) => $q->where('is_active', 1))->get();

        if ($muridAktif->isEmpty()) {
            $this->warn('Tidak ada murid aktif ditemukan.');
            return self::SUCCESS;
        }

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($muridAktif, $periode, $jatuhTempo, $nominalDefault, &$created, &$skipped) {
            foreach ($muridAktif as $murid) {
                // Cegah duplikat: satu murid cuma boleh 1 invoice per periode
                $sudahAda = TagihanSpp::where('murid_id', $murid->id)
                    ->where('periode', $periode->toDateString())
                    ->exists();

                if ($sudahAda) {
                    $skipped++;
                    continue;
                }

                $invoiceNumber = $this->generateInvoiceNumber($periode);

                TagihanSpp::create([
                    'murid_id' => $murid->id,
                    'periode' => $periode->toDateString(),
                    'jumlah' => $nominalDefault,
                    'denda' => 0,
                    'total' => $nominalDefault,
                    'jatuh_tempo' => $jatuhTempo->toDateString(),
                    'status' => 'UNPAID',
                    'invoice_number' => $invoiceNumber,
                    'created_by' => null, // sistem/cron
                ]);

                $created++;
            }
        });

        $this->info("Selesai. Invoice dibuat: {$created}, dilewati (sudah ada): {$skipped}.");

        Log::info('Generate tagihan SPP bulanan', [
            'periode' => $periode->format('Y-m'),
            'created' => $created,
            'skipped' => $skipped,
        ]);

        return self::SUCCESS;
    }

    /**
     * Generate invoice number unik dengan format: INV/SPP/YYYYMM/00001
     * Pakai lockForUpdate biar aman dari race condition kalau command
     * kebetulan jalan bareng dua kali (harusnya gak terjadi, tapi jaga-jaga).
     */
    private function generateInvoiceNumber(\Carbon\Carbon $periode): string
    {
        $prefix = 'INV/SPP/' . $periode->format('Ym') . '/';

        $lastNumber = TagihanSpp::where('invoice_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $nextSequence = 1;

        if ($lastNumber) {
            $lastSequence = (int) substr($lastNumber, -5);
            $nextSequence = $lastSequence + 1;
        }

        return $prefix . str_pad($nextSequence, 5, '0', STR_PAD_LEFT);
    }
}