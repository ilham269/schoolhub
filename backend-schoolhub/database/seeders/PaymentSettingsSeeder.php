<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class PaymentSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // SPP Configuration
            [
                'key' => 'nominal_spp_default',
                'value' => '500000',
                'type' => 'decimal',
                'description' => 'Nominal SPP default per bulan (dalam Rupiah)',
            ],
            [
                'key' => 'denda_per_hari',
                'value' => '5000',
                'type' => 'decimal',
                'description' => 'Denda keterlambatan pembayaran SPP per hari (dalam Rupiah)',
            ],
            [
                'key' => 'max_denda_spp',
                'value' => '50000',
                'type' => 'decimal',
                'description' => 'Maksimal denda SPP yang bisa dikenakan (dalam Rupiah)',
            ],
            [
                'key' => 'jatuh_tempo_spp_hari',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Tanggal jatuh tempo SPP setiap bulan (tanggal 1-31)',
            ],
            
            // Payment Gateway Configuration
            [
                'key' => 'payment_gateway_provider',
                'value' => 'midtrans',
                'type' => 'string',
                'description' => 'Provider payment gateway aktif (midtrans, xendit, manual)',
            ],
            [
                'key' => 'payment_gateway_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Aktifkan/nonaktifkan payment gateway online',
            ],
            [
                'key' => 'payment_methods_enabled',
                'value' => 'bank_transfer,gopay,shopeepay,alfamart,indomaret',
                'type' => 'string',
                'description' => 'Metode pembayaran yang diaktifkan (comma-separated)',
            ],
            
            // Invoice Configuration
            [
                'key' => 'invoice_prefix',
                'value' => 'INV/SPP',
                'type' => 'string',
                'description' => 'Prefix untuk nomor invoice SPP (format: PREFIX/YYYYMM/00001)',
            ],
            [
                'key' => 'slip_gaji_prefix',
                'value' => 'SLIP/GAJ',
                'type' => 'string',
                'description' => 'Prefix untuk nomor slip gaji (format: PREFIX/YYYYMM/00001)',
            ],
            
            // Auto Generate Configuration
            [
                'key' => 'auto_generate_tagihan',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Auto-generate tagihan SPP setiap awal bulan via scheduler',
            ],
            [
                'key' => 'tagihan_generate_date',
                'value' => '1',
                'type' => 'integer',
                'description' => 'Tanggal auto-generate tagihan setiap bulan (1-31)',
            ],
            
            // Notification Configuration
            [
                'key' => 'notification_payment_success',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Kirim notifikasi saat pembayaran berhasil',
            ],
            [
                'key' => 'notification_payment_reminder',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Kirim reminder sebelum jatuh tempo',
            ],
            [
                'key' => 'reminder_days_before_due',
                'value' => '3',
                'type' => 'integer',
                'description' => 'Kirim reminder X hari sebelum jatuh tempo',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('✅ Payment settings seeded successfully!');
        $this->command->info('📊 Total settings: ' . count($settings));
    }
}
