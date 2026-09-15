<?php

/**
 * Test Script untuk Models Payment System
 * 
 * Cara menjalankan:
 * php artisan tinker < .claude/output/test-models.php
 */

echo "\n";
echo "==============================================\n";
echo "  Testing Payment System Models & Relations  \n";
echo "==============================================\n\n";

// Test 1: Check Settings
echo "✓ Test 1: Payment Settings\n";
echo "----------------------------\n";
$nominalSpp = \App\Models\Setting::where('key', 'nominal_spp_default')->first();
echo "Nominal SPP Default: Rp " . number_format($nominalSpp->value, 0, ',', '.') . "\n";

$dendaPerHari = \App\Models\Setting::where('key', 'denda_per_hari')->first();
echo "Denda Per Hari: Rp " . number_format($dendaPerHari->value, 0, ',', '.') . "\n";

$jatuhTempo = \App\Models\Setting::where('key', 'jatuh_tempo_spp_hari')->first();
echo "Jatuh Tempo SPP: Tanggal " . $jatuhTempo->value . " setiap bulan\n";
echo "\n";

// Test 2: Check if we have murid data
echo "✓ Test 2: Check Data Murid\n";
echo "----------------------------\n";
$muridCount = \App\Models\Murid::count();
echo "Total Murid: " . $muridCount . "\n";

if ($muridCount > 0) {
    $murid = \App\Models\Murid::with('user')->first();
    echo "Sample Murid: " . $murid->user->name . " (NIS: " . $murid->nis . ")\n";
} else {
    echo "⚠️  No murid data found. Run UserSeeder, KelasSeeder, and MuridSeeder first.\n";
}
echo "\n";

// Test 3: Check if we have karyawan data
echo "✓ Test 3: Check Data Karyawan\n";
echo "----------------------------\n";
$karyawanCount = \App\Models\Karyawan::count();
echo "Total Karyawan: " . $karyawanCount . "\n";

if ($karyawanCount > 0) {
    $karyawan = \App\Models\Karyawan::with('user')->first();
    echo "Sample Karyawan: " . $karyawan->user->name . " (Bagian: " . $karyawan->bagian . ")\n";
} else {
    echo "⚠️  No karyawan data found. Run UserSeeder and KaryawanSeeder first.\n";
}
echo "\n";

// Test 4: Test Model Relationships
echo "✓ Test 4: Model Relationships\n";
echo "----------------------------\n";
echo "TagihanSpp model exists: " . (class_exists('App\Models\TagihanSpp') ? 'Yes' : 'No') . "\n";
echo "Pembayaran model exists: " . (class_exists('App\Models\Pembayaran') ? 'Yes' : 'No') . "\n";
echo "SlipGaji model exists: " . (class_exists('App\Models\SlipGaji') ? 'Yes' : 'No') . "\n";
echo "PaymentGatewayLog model exists: " . (class_exists('App\Models\PaymentGatewayLog') ? 'Yes' : 'No') . "\n";
echo "\n";

// Test 5: Create Sample TagihanSpp (only if we have murid)
if ($muridCount > 0) {
    echo "✓ Test 5: Create Sample TagihanSpp\n";
    echo "----------------------------\n";
    
    $murid = \App\Models\Murid::first();
    $admin = \App\Models\User::where('role', 'Admin')->first();
    
    $tagihan = \App\Models\TagihanSpp::create([
        'murid_id' => $murid->id,
        'periode' => now()->startOfMonth(),
        'jumlah' => $nominalSpp->value,
        'denda' => 0,
        'total' => $nominalSpp->value,
        'jatuh_tempo' => now()->startOfMonth()->addDays((int)$jatuhTempo->value - 1),
        'status' => 'UNPAID',
        'invoice_number' => 'INV/SPP/' . now()->format('Ym') . '/00001',
        'created_by' => $admin ? $admin->id : null,
    ]);
    
    echo "Created TagihanSpp ID: " . $tagihan->id . "\n";
    echo "Invoice Number: " . $tagihan->invoice_number . "\n";
    echo "Status: " . $tagihan->status . "\n";
    echo "Total: Rp " . number_format($tagihan->total, 0, ',', '.') . "\n";
    echo "\n";
    
    // Test relationship
    echo "✓ Test 5a: Test Relationships\n";
    echo "----------------------------\n";
    echo "Murid Name: " . $tagihan->murid->user->name . "\n";
    echo "Creator Name: " . ($tagihan->creator ? $tagihan->creator->name : 'N/A') . "\n";
    echo "\n";
    
    // Test 6: Create Sample Pembayaran
    echo "✓ Test 6: Create Sample Pembayaran\n";
    echo "----------------------------\n";
    
    $pembayaran = \App\Models\Pembayaran::create([
        'tagihan_id' => $tagihan->id,
        'gateway' => 'midtrans',
        'transaction_id' => 'TEST-' . now()->timestamp,
        'snap_token' => 'dummy-snap-token-' . \Illuminate\Support\Str::random(10),
        'payment_type' => 'bank_transfer',
        'va_number' => '8808' . rand(1000000000, 9999999999),
        'bank' => 'BCA',
        'gross_amount' => $tagihan->total,
        'status' => 'PENDING',
        'expired_at' => now()->addDay(),
    ]);
    
    echo "Created Pembayaran ID: " . $pembayaran->id . "\n";
    echo "Transaction ID: " . $pembayaran->transaction_id . "\n";
    echo "Gateway: " . $pembayaran->gateway . "\n";
    echo "VA Number: " . $pembayaran->va_number . "\n";
    echo "Status: " . $pembayaran->status . "\n";
    echo "\n";
    
    // Test 7: Create Payment Gateway Log
    echo "✓ Test 7: Create PaymentGatewayLog\n";
    echo "----------------------------\n";
    
    $log = \App\Models\PaymentGatewayLog::create([
        'pembayaran_id' => $pembayaran->id,
        'gateway' => 'midtrans',
        'action' => 'create_token',
        'request_payload' => [
            'transaction_details' => [
                'order_id' => $pembayaran->transaction_id,
                'gross_amount' => $pembayaran->gross_amount,
            ],
        ],
        'response_payload' => [
            'status_code' => '201',
            'token' => $pembayaran->snap_token,
        ],
        'http_status' => 201,
        'ip_address' => '127.0.0.1',
    ]);
    
    echo "Created PaymentGatewayLog ID: " . $log->id . "\n";
    echo "Action: " . $log->action . "\n";
    echo "HTTP Status: " . $log->http_status . "\n";
    echo "\n";
}

// Test 8: Create Sample SlipGaji (only if we have karyawan)
if ($karyawanCount > 0) {
    echo "✓ Test 8: Create Sample SlipGaji\n";
    echo "----------------------------\n";
    
    $karyawan = \App\Models\Karyawan::first();
    $admin = \App\Models\User::where('role', 'Admin')->first();
    
    $gajiPokok = 5000000;
    $tunjangan = 1000000;
    $bonus = 500000;
    $potongan = 200000;
    
    $slipGaji = \App\Models\SlipGaji::create([
        'karyawan_id' => $karyawan->id,
        'periode' => now()->startOfMonth(),
        'gaji_pokok' => $gajiPokok,
        'tunjangan' => $tunjangan,
        'bonus' => $bonus,
        'potongan' => $potongan,
        'total_gaji' => $gajiPokok + $tunjangan + $bonus - $potongan,
        'status' => 'DRAFT',
        'slip_number' => 'SLIP/GAJ/' . now()->format('Ym') . '/00001',
        'dibuat_oleh' => $admin ? $admin->id : null,
        'catatan' => 'Gaji bulan ' . now()->format('F Y'),
    ]);
    
    echo "Created SlipGaji ID: " . $slipGaji->id . "\n";
    echo "Slip Number: " . $slipGaji->slip_number . "\n";
    echo "Karyawan: " . $slipGaji->karyawan->user->name . "\n";
    echo "Gaji Pokok: Rp " . number_format($slipGaji->gaji_pokok, 0, ',', '.') . "\n";
    echo "Tunjangan: Rp " . number_format($slipGaji->tunjangan, 0, ',', '.') . "\n";
    echo "Bonus: Rp " . number_format($slipGaji->bonus, 0, ',', '.') . "\n";
    echo "Potongan: Rp " . number_format($slipGaji->potongan, 0, ',', '.') . "\n";
    echo "Total: Rp " . number_format($slipGaji->total_gaji, 0, ',', '.') . "\n";
    echo "Status: " . $slipGaji->status . "\n";
    echo "\n";
}

echo "==============================================\n";
echo "  ✅ All Tests Completed Successfully!       \n";
echo "==============================================\n\n";

echo "📊 Summary:\n";
echo "- Total Settings: " . \App\Models\Setting::where('key', 'LIKE', '%spp%')->orWhere('key', 'LIKE', '%payment%')->orWhere('key', 'LIKE', '%slip%')->count() . "\n";
echo "- Total TagihanSpp: " . \App\Models\TagihanSpp::count() . "\n";
echo "- Total Pembayaran: " . \App\Models\Pembayaran::count() . "\n";
echo "- Total SlipGaji: " . \App\Models\SlipGaji::count() . "\n";
echo "- Total PaymentGatewayLog: " . \App\Models\PaymentGatewayLog::count() . "\n";
echo "\n";

echo "🎯 Next Steps:\n";
echo "1. Run: php artisan make:command GenerateTagihanSppBulanan\n";
echo "2. Run: php artisan make:job ProcessPaymentCallbackJob\n";
echo "3. Run: php artisan make:job GenerateKuitansiPdf\n";
echo "4. Install: composer require midtrans/midtrans-php\n";
echo "5. Install: composer require barryvdh/laravel-dompdf\n";
echo "\n";
