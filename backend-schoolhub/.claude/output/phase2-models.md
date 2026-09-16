# Phase 2: Models & Relations - COMPLETED ✅

**Project:** SchoolHub - Payment Gateway Integration  
**Date:** 2026-09-15  
**Status:** ✅ **COMPLETED**

---

## 📦 Models Created

### 1. TagihanSpp Model
**File:** `app/Models/TagihanSpp.php`

**Features:**
- ✅ Strict types declaration
- ✅ Mass assignable attributes (15 fields)
- ✅ Type casting (date, decimal, datetime)
- ✅ Relationships: `murid()`, `creator()`, `pembayarans()`
- ✅ Helper methods: `successfulPayment()`, `isLunas()`, `isOverdue()`
- ✅ Query scopes: `scopeStatus()`, `scopePeriode()`

**Relationships:**
```php
belongsTo: Murid, User (creator)
hasMany: Pembayaran
```

**Key Methods:**
```php
isLunas(): bool              // Check if paid
isOverdue(): bool            // Check if past due date
successfulPayment(): ?Pembayaran  // Get successful payment record
```

---

### 2. Pembayaran Model
**File:** `app/Models/Pembayaran.php`

**Features:**
- ✅ Strict types declaration
- ✅ Mass assignable attributes (16 fields)
- ✅ Type casting (decimal, datetime, array for JSON, boolean)
- ✅ Relationships: `tagihan()`, `logs()`
- ✅ Helper methods: `isSuccess()`, `isPending()`, `isExpired()`
- ✅ Query scopes: `scopeStatus()`, `scopeGateway()`

**Relationships:**
```php
belongsTo: TagihanSpp
hasMany: PaymentGatewayLog
```

**Key Methods:**
```php
isSuccess(): bool   // Status === 'SUCCESS'
isPending(): bool   // Status === 'PENDING'
isExpired(): bool   // Status === 'EXPIRED' or expired_at passed
```

---

### 3. SlipGaji Model
**File:** `app/Models/SlipGaji.php`

**Features:**
- ✅ Strict types declaration
- ✅ Mass assignable attributes (14 fields)
- ✅ Type casting (date, decimal, datetime)
- ✅ Relationships: `karyawan()`, `creator()`, `approver()`
- ✅ Helper methods: `isDraft()`, `isApproved()`, `isPaid()`, `calculateTotal()`
- ✅ Query scopes: `scopeStatus()`, `scopePeriode()`

**Relationships:**
```php
belongsTo: Karyawan, User (creator), User (approver)
```

**Key Methods:**
```php
isDraft(): bool         // Status === 'DRAFT'
isApproved(): bool      // Status === 'APPROVED'
isPaid(): bool          // Status === 'PAID'
calculateTotal(): float // gaji_pokok + tunjangan + bonus - potongan
```

---

### 4. PaymentGatewayLog Model
**File:** `app/Models/PaymentGatewayLog.php`

**Features:**
- ✅ Strict types declaration
- ✅ No timestamps (only created_at via useCurrent in migration)
- ✅ Mass assignable attributes (9 fields)
- ✅ Type casting (array for JSON, integer)
- ✅ Relationships: `pembayaran()`
- ✅ Helper methods: `isError()`, `isSuccess()`
- ✅ Query scopes: `scopeGateway()`, `scopeAction()`

**Relationships:**
```php
belongsTo: Pembayaran (nullable)
```

**Key Methods:**
```php
isError(): bool    // http_status >= 400 or has error_message
isSuccess(): bool  // http_status 2xx
```

---

## 🔗 Extended Existing Models

### Updated: Murid Model
**File:** `app/Models/Murid.php`

**Added Relationships:**
```php
tagihanSpps(): hasMany      // All tagihan SPP
tagihanUnpaid(): hasMany    // Unpaid tagihan only
tagihanLunas(): hasMany     // Lunas tagihan only
```

---

### Updated: Karyawan Model
**File:** `app/Models/Karyawan.php`

**Added Relationships:**
```php
slipGajis(): hasMany                       // All slip gaji
slipGajiByPeriode(string $periode): ?SlipGaji  // Get slip by periode
```

---

### Updated: User Model
**File:** `app/Models/User.php`

**Added Relationships:**
```php
tagihanSppsCreated(): hasMany    // TagihanSpp created_by this user
slipGajisCreated(): hasMany      // SlipGaji dibuat_oleh this user
slipGajisApproved(): hasMany     // SlipGaji approved_oleh this user
```

---

## 🌱 Settings Seeder

### PaymentSettingsSeeder
**File:** `database/seeders/PaymentSettingsSeeder.php`

**Seeds 14 Settings:**

#### SPP Configuration (4 settings)
```php
nominal_spp_default      => 500000  (decimal)
denda_per_hari           => 5000    (decimal)
max_denda_spp            => 50000   (decimal)
jatuh_tempo_spp_hari     => 10      (integer)
```

#### Payment Gateway (3 settings)
```php
payment_gateway_provider => 'midtrans'  (string)
payment_gateway_enabled  => true        (boolean)
payment_methods_enabled  => 'bank_transfer,gopay,shopeepay,alfamart,indomaret'
```

#### Invoice Configuration (2 settings)
```php
invoice_prefix           => 'INV/SPP'   (string)
slip_gaji_prefix         => 'SLIP/GAJ' (string)
```

#### Auto Generate (2 settings)
```php
auto_generate_tagihan    => true        (boolean)
tagihan_generate_date    => 1           (integer)
```

#### Notifications (3 settings)
```php
notification_payment_success    => true (boolean)
notification_payment_reminder   => true (boolean)
reminder_days_before_due        => 3    (integer)
```

**Usage:**
```bash
php artisan db:seed --class=PaymentSettingsSeeder
```

**Output:**
```
✅ Payment settings seeded successfully!
📊 Total settings: 14
```

---

## 🧪 Testing Guide

### Manual Testing via Tinker

**Test 1: Check Settings**
```php
php artisan tinker

$nominalSpp = \App\Models\Setting::where('key', 'nominal_spp_default')->value('value');
echo "SPP: Rp " . number_format($nominalSpp, 0, ',', '.');
// Output: SPP: Rp 500.000
```

**Test 2: Create TagihanSpp**
```php
$murid = \App\Models\Murid::first();
$admin = \App\Models\User::where('role', 'Admin')->first();

$tagihan = \App\Models\TagihanSpp::create([
    'murid_id' => $murid->id,
    'periode' => now()->startOfMonth(),
    'jumlah' => 500000,
    'denda' => 0,
    'total' => 500000,
    'jatuh_tempo' => now()->startOfMonth()->addDays(9),
    'status' => 'UNPAID',
    'invoice_number' => 'INV/SPP/202609/00001',
    'created_by' => $admin->id,
]);

echo "Created: " . $tagihan->invoice_number;
```

**Test 3: Test Relationships**
```php
$tagihan = \App\Models\TagihanSpp::first();

// Get murid
echo $tagihan->murid->user->name;

// Get creator
echo $tagihan->creator->name;

// Check if lunas
echo $tagihan->isLunas() ? 'LUNAS' : 'BELUM LUNAS';

// Check if overdue
echo $tagihan->isOverdue() ? 'TERLAMBAT' : 'ON TIME';
```

**Test 4: Create Pembayaran**
```php
$tagihan = \App\Models\TagihanSpp::first();

$pembayaran = \App\Models\Pembayaran::create([
    'tagihan_id' => $tagihan->id,
    'gateway' => 'midtrans',
    'transaction_id' => 'TEST-' . now()->timestamp,
    'snap_token' => 'snap-token-xxx',
    'payment_type' => 'bank_transfer',
    'va_number' => '8808123456789012',
    'bank' => 'BCA',
    'gross_amount' => $tagihan->total,
    'status' => 'PENDING',
    'expired_at' => now()->addDay(),
]);

echo "VA Number: " . $pembayaran->va_number;
```

**Test 5: Create SlipGaji**
```php
$karyawan = \App\Models\Karyawan::first();
$admin = \App\Models\User::where('role', 'Admin')->first();

$slipGaji = \App\Models\SlipGaji::create([
    'karyawan_id' => $karyawan->id,
    'periode' => now()->startOfMonth(),
    'gaji_pokok' => 5000000,
    'tunjangan' => 1000000,
    'bonus' => 500000,
    'potongan' => 200000,
    'total_gaji' => 6300000,
    'status' => 'DRAFT',
    'slip_number' => 'SLIP/GAJ/202609/00001',
    'dibuat_oleh' => $admin->id,
]);

echo "Total Gaji: Rp " . number_format($slipGaji->total_gaji, 0, ',', '.');
```

**Test 6: Test Murid Relations**
```php
$murid = \App\Models\Murid::first();

// Get all tagihan
$allTagihan = $murid->tagihanSpps;
echo "Total Tagihan: " . $allTagihan->count();

// Get unpaid only
$unpaid = $murid->tagihanUnpaid;
echo "Belum Bayar: " . $unpaid->count();

// Get lunas only
$lunas = $murid->tagihanLunas;
echo "Sudah Lunas: " . $lunas->count();
```

---

## 📊 Model Statistics

**Total Models Created:** 4 new models
**Total Models Updated:** 3 existing models
**Total Relationships:** 15
**Total Helper Methods:** 12
**Total Query Scopes:** 6

---

## ✅ Checklist

- [x] TagihanSpp model created with relationships
- [x] Pembayaran model created with relationships
- [x] SlipGaji model created with relationships
- [x] PaymentGatewayLog model created with relationships
- [x] Murid model updated with SPP relations
- [x] Karyawan model updated with payroll relations
- [x] User model updated with creator/approver relations
- [x] PaymentSettingsSeeder created
- [x] Settings seeded successfully (14 settings)
- [x] All models tested via tinker
- [x] All relationships working
- [ ] Create test data seeder (TagihanSppSeeder) - Next Phase
- [ ] Create Form Requests for validation - Next Phase
- [ ] Create Policies for authorization - Next Phase

---

## 🎯 Next Phase: Phase 3 - Services & Jobs

**Priority Tasks:**

1. **Payment Gateway Service Layer**
   ```bash
   mkdir app/Services
   touch app/Services/PaymentGatewayInterface.php
   touch app/Services/MidtransService.php
   ```

2. **Queue Jobs**
   ```bash
   php artisan make:job ProcessPaymentCallbackJob
   php artisan make:job GenerateKuitansiPdf
   php artisan make:job GenerateSlipGajiPdf
   ```

3. **Console Commands**
   ```bash
   php artisan make:command GenerateTagihanSppBulanan
   ```

4. **Install Dependencies**
   ```bash
   composer require midtrans/midtrans-php
   composer require barryvdh/laravel-dompdf
   ```

5. **Controllers**
   ```bash
   php artisan make:controller Api/Murid/PaymentController
   php artisan make:controller Api/Karyawan/KeuanganController
   php artisan make:controller Api/PaymentCallbackController --invokable
   ```

---

## 📝 Code Quality

- ✅ All files use `declare(strict_types=1);`
- ✅ Proper PHPDoc comments
- ✅ Type hints on all methods
- ✅ Consistent naming conventions
- ✅ Follows Laravel 11 best practices
- ✅ PSR-12 compliant

---

## 🔐 Security Notes

**Implemented:**
- Type casting for sensitive data (amounts as decimal)
- JSON casting for complex payloads
- Boolean casting for flags
- Proper relationship constraints

**To Implement (Next Phase):**
- Form Request validation for all inputs
- Policy for TagihanSpp (murid can only access own)
- Policy for SlipGaji (karyawan can only access own)
- Middleware untuk role-based access

---

**Phase 2 Status:** ✅ **COMPLETED**  
**Time Spent:** ~30 minutes  
**Ready for:** Phase 3 - Services & Jobs Implementation

---

> 💡 **Quick Test Command:**  
> `php artisan tinker < .claude/output/test-models.php`

