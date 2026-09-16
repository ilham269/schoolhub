<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TagihanSpp;
use App\Models\Pembayaran;
use App\Models\SlipGaji;
use App\Models\Murid;
use App\Models\Karyawan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class KeuanganTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding Keuangan Test Data...');
        
        $admin = User::where('role', 'Admin')->first();
        
        // Seed Tagihan SPP
        $this->seedTagihanSpp($admin);
        
        // Seed Pembayaran
        $this->seedPembayaran();
        
        // Seed Slip Gaji
        $this->seedSlipGaji($admin);
        
        $this->command->info('✅ Keuangan Test Data seeded successfully!');
    }
    
    private function seedTagihanSpp(?User $admin): void
    {
        $murids = Murid::limit(10)->get();
        
        if ($murids->isEmpty()) {
            $this->command->warn('⚠️ No murid data found. Skipping tagihan seeding.');
            return;
        }
        
        $this->command->info('📋 Seeding Tagihan SPP...');
        
        foreach ($murids as $index => $murid) {
            // Tagihan bulan ini (UNPAID)
            TagihanSpp::create([
                'murid_id' => $murid->id,
                'periode' => now()->startOfMonth(),
                'jumlah' => 500000,
                'denda' => 0,
                'total' => 500000,
                'jatuh_tempo' => now()->addDays(10),
                'status' => 'UNPAID',
                'invoice_number' => sprintf('INV/SPP/%s/%05d', now()->format('Ym'), $index + 1),
                'created_by' => $admin?->id,
            ]);
            
            // Tagihan bulan lalu (LUNAS)
            if ($index < 5) {
                $tagihanLunas = TagihanSpp::create([
                    'murid_id' => $murid->id,
                    'periode' => now()->subMonth()->startOfMonth(),
                    'jumlah' => 500000,
                    'denda' => 0,
                    'total' => 500000,
                    'jatuh_tempo' => now()->subMonth()->addDays(10),
                    'status' => 'LUNAS',
                    'invoice_number' => sprintf('INV/SPP/%s/%05d', now()->subMonth()->format('Ym'), $index + 1),
                    'paid_at' => now()->subMonth()->addDays(8),
                    'kuitansi_path' => 'kuitansi/dummy-' . ($index + 1) . '.pdf',
                    'created_by' => $admin?->id,
                ]);
                
                // Create pembayaran for LUNAS tagihan
                Pembayaran::create([
                    'tagihan_id' => $tagihanLunas->id,
                    'gateway' => 'midtrans',
                    'transaction_id' => 'TEST-' . now()->timestamp . '-' . $index,
                    'snap_token' => 'dummy-snap-token-' . uniqid(),
                    'payment_type' => 'bank_transfer',
                    'va_number' => '8808' . str_pad((string)($index + 1), 12, '0', STR_PAD_LEFT),
                    'bank' => ['BCA', 'Mandiri', 'BNI', 'BRI'][$index % 4],
                    'gross_amount' => 500000,
                    'status' => 'SUCCESS',
                    'paid_at' => now()->subMonth()->addDays(8),
                    'signature_verified' => true,
                    'processed_by_job_at' => now()->subMonth()->addDays(8),
                ]);
            }
            
            // Tagihan 2 bulan lalu (EXPIRED karena belum bayar)
            if ($index >= 5 && $index < 7) {
                TagihanSpp::create([
                    'murid_id' => $murid->id,
                    'periode' => now()->subMonths(2)->startOfMonth(),
                    'jumlah' => 500000,
                    'denda' => 50000,
                    'total' => 550000,
                    'jatuh_tempo' => now()->subMonths(2)->addDays(10),
                    'status' => 'EXPIRED',
                    'invoice_number' => sprintf('INV/SPP/%s/%05d', now()->subMonths(2)->format('Ym'), $index + 1),
                    'created_by' => $admin?->id,
                ]);
            }
        }
        
        $this->command->info('✓ Created ' . TagihanSpp::count() . ' tagihan SPP');
    }
    
    private function seedPembayaran(): void
    {
        $unpaidTagihan = TagihanSpp::where('status', 'UNPAID')->limit(3)->get();
        
        if ($unpaidTagihan->isEmpty()) {
            return;
        }
        
        $this->command->info('💳 Seeding Pembayaran PENDING...');
        
        foreach ($unpaidTagihan as $index => $tagihan) {
            Pembayaran::create([
                'tagihan_id' => $tagihan->id,
                'gateway' => 'midtrans',
                'transaction_id' => 'PENDING-' . now()->timestamp . '-' . $index,
                'snap_token' => 'pending-snap-token-' . uniqid(),
                'payment_type' => 'bank_transfer',
                'va_number' => '8809' . str_pad((string)($index + 1), 12, '0', STR_PAD_LEFT),
                'bank' => ['BCA', 'Mandiri', 'BNI'][$index % 3],
                'gross_amount' => $tagihan->total,
                'status' => 'PENDING',
                'expired_at' => now()->addDay(),
            ]);
            
            $tagihan->update(['status' => 'PENDING']);
        }
        
        $this->command->info('✓ Created ' . Pembayaran::where('status', 'PENDING')->count() . ' pembayaran PENDING');
    }
    
    private function seedSlipGaji(?User $admin): void
    {
        $karyawans = Karyawan::limit(5)->get();
        
        if ($karyawans->isEmpty()) {
            $this->command->warn('⚠️ No karyawan data found. Skipping slip gaji seeding.');
            return;
        }
        
        $this->command->info('💰 Seeding Slip Gaji...');
        
        foreach ($karyawans as $index => $karyawan) {
            // Slip gaji bulan lalu (PAID)
            SlipGaji::create([
                'karyawan_id' => $karyawan->id,
                'periode' => now()->subMonth()->startOfMonth(),
                'gaji_pokok' => 5000000,
                'tunjangan' => 1000000,
                'bonus' => 500000,
                'potongan' => 200000,
                'total_gaji' => 6300000,
                'status' => 'PAID',
                'slip_number' => sprintf('SLIP/GAJ/%s/%05d', now()->subMonth()->format('Ym'), $index + 1),
                'file_path' => 'slip-gaji/dummy-' . ($index + 1) . '.pdf',
                'dibuat_oleh' => $admin?->id,
                'approved_oleh' => $admin?->id,
                'approved_at' => now()->subMonth()->addDays(25),
                'paid_at' => now()->subMonth()->addDays(28),
                'catatan' => 'Gaji bulan ' . now()->subMonth()->format('F Y'),
            ]);
            
            // Slip gaji bulan ini (APPROVED, belum paid)
            if ($index < 3) {
                SlipGaji::create([
                    'karyawan_id' => $karyawan->id,
                    'periode' => now()->startOfMonth(),
                    'gaji_pokok' => 5000000,
                    'tunjangan' => 1000000,
                    'bonus' => 0,
                    'potongan' => 200000,
                    'total_gaji' => 5800000,
                    'status' => 'APPROVED',
                    'slip_number' => sprintf('SLIP/GAJ/%s/%05d', now()->format('Ym'), $index + 1),
                    'dibuat_oleh' => $admin?->id,
                    'approved_oleh' => $admin?->id,
                    'approved_at' => now()->subDays(2),
                    'catatan' => 'Gaji bulan ' . now()->format('F Y'),
                ]);
            }
            
            // Slip gaji bulan ini (DRAFT)
            if ($index >= 3) {
                SlipGaji::create([
                    'karyawan_id' => $karyawan->id,
                    'periode' => now()->startOfMonth(),
                    'gaji_pokok' => 5000000,
                    'tunjangan' => 1000000,
                    'bonus' => 0,
                    'potongan' => 200000,
                    'total_gaji' => 5800000,
                    'status' => 'DRAFT',
                    'slip_number' => sprintf('SLIP/GAJ/%s/%05d', now()->format('Ym'), $index + 1),
                    'dibuat_oleh' => $admin?->id,
                    'catatan' => 'Gaji bulan ' . now()->format('F Y'),
                ]);
            }
        }
        
        $this->command->info('✓ Created ' . SlipGaji::count() . ' slip gaji');
    }
}
