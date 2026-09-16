# Phase 1: Database Schema Design - SPP & Payroll Karyawan

**Project:** SchoolHub - Payment Gateway Integration  
**Created:** 2026-09-15  
**Status:** Planning Phase

---

## 🎯 Tujuan

Merancang skema database Laravel yang:
1. **Robust** - Mendukung payment gateway callback dengan idempotency
2. **Scalable** - Siap handle traffic tinggi dengan queue processing
3. **Auditable** - Tracking lengkap untuk transaksi keuangan
4. **Flexible** - Mudah extend untuk provider payment gateway lain

---

## 📋 Context: Tabel Yang Sudah Ada

### 1. `users`
```sql
- id (PK)
- name
- email
- password
- role (Admin, Guru, Murid, Karyawan, calon_siswa)
- is_active (boolean)
- timestamps
```

### 2. `murids`
```sql
- id (PK)
- nis (unique)
- gambar_murid
- gender (L/P)
- tanggal_lahir, tempat_lahir
- alamat
- nomor_telepon
- nama_orangtua, nama_ayah, nama_ibu
- pekerjaan_ayah, pekerjaan_ibu
- nomor_telepon_ortu
- agama, anak_ke, jumlah_saudara
- hobi, cita_cita
- user_id (FK → users)
- kelas_id (FK → kelas, nullable)
- timestamps
```

### 3. `karyawans`
```sql
- id (PK)
- nip (unique, nullable)
- gambar_karyawan
- bagian
- nomor_telepon
- alamat
- user_id (FK → users)
- timestamps
```

### 4. `kelas`
```sql
- id (PK)
- name
- jurusan
- angkatan
- timestamps
```

### 5. `settings`
```sql
- id (PK)
- key
- value
- type
- description
- timestamps
```

---

## 🗂️ Skema Baru: 4 Tabel Utama

---

## 1. Tabel `tagihan_spps` (Invoice SPP)

**Purpose:** Menyimpan tagihan bulanan SPP untuk setiap murid

### Fields

| Field | Type | Constraint | Description |
|-------|------|------------|-------------|
| `id` | BIGINT UNSIGNED | PK, Auto | Primary Key |
| `murid_id` | BIGINT UNSIGNED | FK, Index | Foreign Key ke `murids.id` |
| `periode` | DATE | Not Null, Index | Periode tagihan (format: YYYY-MM-01) |
| `jumlah` | DECIMAL(12,2) | Not Null | Nominal tagihan SPP (ambil dari settings atau override) |
| `denda` | DECIMAL(12,2) | Default 0 | Denda keterlambatan (dihitung otomatis) |
| `total` | DECIMAL(12,2) | Not Null | jumlah + denda |
| `jatuh_tempo` | DATE | Not Null | Tanggal maksimal pembayaran |
| `status` | ENUM | Not Null | UNPAID, PENDING, LUNAS, EXPIRED, CANCELLED |
| `invoice_number` | VARCHAR(50) | Unique, Index | Format: INV/SPP/YYYYMM/00001 |
| `kuitansi_path` | VARCHAR(255) | Nullable | Path file PDF kuitansi (setelah LUNAS) |
| `paid_at` | TIMESTAMP | Nullable | Waktu pembayaran berhasil |
| `notes` | TEXT | Nullable | Catatan tambahan (mis: dispensasi, keringanan) |
| `created_by` | BIGINT UNSIGNED | FK, Nullable | User yang generate invoice (sistem/admin) |
| `created_at` | TIMESTAMP | Auto | Tanggal dibuat |
| `updated_at` | TIMESTAMP | Auto | Tanggal terakhir update |

### Indexes
```sql
INDEX idx_murid_periode (murid_id, periode)
INDEX idx_status (status)
INDEX idx_jatuh_tempo (jatuh_tempo)
UNIQUE idx_invoice_number (invoice_number)
```

### Foreign Keys
```sql
FK murid_id → murids(id) ON DELETE CASCADE
FK created_by → users(id) ON DELETE SET NULL
```

### Status Flow
```
UNPAID → PENDING → LUNAS
   ↓        ↓
EXPIRED  CANCELLED
```

### Business Rules
1. **Generate Otomatis:** Setiap tanggal 1 via Scheduler Laravel untuk semua murid `is_active=true`
2. **Nominal Default:** Ambil dari `settings.key='nominal_spp_default'`
3. **Override Nominal:** Bisa di-custom per murid (misal: beasiswa)
4. **Denda:** Hitung otomatis jika `paid_at > jatuh_tempo` (configurable di settings)
5. **Unique Constraint:** Satu murid hanya boleh punya 1 invoice per periode

---

## 2. Tabel `pembayarans` (Payment Transactions)

**Purpose:** Tracking detail transaksi pembayaran SPP via payment gateway

### Fields

| Field | Type | Constraint | Description |
|-------|------|------------|-------------|
| `id` | BIGINT UNSIGNED | PK, Auto | Primary Key |
| `tagihan_id` | BIGINT UNSIGNED | FK, Index | Foreign Key ke `tagihan_spps.id` |
| `gateway` | VARCHAR(50) | Not Null | Provider: midtrans, xendit, manual |
| `transaction_id` | VARCHAR(100) | Nullable, Index | Order ID dari gateway |
| `snap_token` | TEXT | Nullable | Midtrans Snap Token (untuk re-display popup) |
| `payment_type` | VARCHAR(50) | Nullable | bank_transfer, gopay, alfamart, etc |
| `va_number` | VARCHAR(50) | Nullable | Virtual Account Number (jika VA) |
| `bank` | VARCHAR(50) | Nullable | Nama bank (BCA, Mandiri, BNI, dll) |
| `gross_amount` | DECIMAL(12,2) | Not Null | Total yang dibayar (sesuai tagihan.total) |
| `status` | ENUM | Not Null | PENDING, SUCCESS, FAILED, EXPIRED, CANCELLED |
| `paid_at` | TIMESTAMP | Nullable | Waktu pembayaran sukses dari gateway |
| `expired_at` | TIMESTAMP | Nullable | Waktu expired transaksi |
| `raw_callback` | JSON | Nullable | Payload lengkap dari gateway webhook |
| `signature_verified` | BOOLEAN | Default false | Flag validasi signature key |
| `callback_received_at` | TIMESTAMP | Nullable | Waktu pertama terima callback |
| `processed_by_job_at` | TIMESTAMP | Nullable | Waktu job selesai proses |
| `created_at` | TIMESTAMP | Auto | Tanggal transaksi dibuat |
| `updated_at` | TIMESTAMP | Auto | Tanggal terakhir update |

### Indexes
```sql
INDEX idx_tagihan_id (tagihan_id)
INDEX idx_transaction_id (transaction_id)
INDEX idx_status (status)
INDEX idx_gateway (gateway)
```

### Foreign Keys
```sql
FK tagihan_id → tagihan_spps(id) ON DELETE CASCADE
```

### Status Flow
```
PENDING → SUCCESS
   ↓         ↓
FAILED   (trigger kuitansi PDF)
   ↓
EXPIRED
```

### Business Rules
1. **Multiple Attempts:** Satu tagihan bisa punya banyak record pembayaran (retry beda metode)
2. **Idempotency:** Job harus cek `status` sebelum update ke SUCCESS (anti-duplikasi)
3. **Signature Verification:** Wajib validasi signature dari gateway di Job
4. **Callback Storage:** Simpan raw JSON untuk audit & debugging
5. **One Success Only:** Hanya boleh ada 1 pembayaran SUCCESS per tagihan

---

## 3. Tabel `slip_gajis` (Payroll for Karyawan)

**Purpose:** Data slip gaji karyawan yang di-generate oleh bagian Tata Usaha

### Fields

| Field | Type | Constraint | Description |
|-------|------|------------|-------------|
| `id` | BIGINT UNSIGNED | PK, Auto | Primary Key |
| `karyawan_id` | BIGINT UNSIGNED | FK, Index | Foreign Key ke `karyawans.id` |
| `periode` | DATE | Not Null, Index | Periode gaji (YYYY-MM-01) |
| `gaji_pokok` | DECIMAL(12,2) | Not Null | Gaji pokok bulanan |
| `tunjangan` | DECIMAL(12,2) | Default 0 | Total tunjangan (jabatan, transport, dll) |
| `bonus` | DECIMAL(12,2) | Default 0 | Bonus/insentif |
| `potongan` | DECIMAL(12,2) | Default 0 | Potongan (kasbon, BPJS, dll) |
| `total_gaji` | DECIMAL(12,2) | Not Null | gaji_pokok + tunjangan + bonus - potongan |
| `status` | ENUM | Not Null | DRAFT, APPROVED, PAID |
| `slip_number` | VARCHAR(50) | Unique, Index | Format: SLIP/GAJ/YYYYMM/00001 |
| `file_path` | VARCHAR(255) | Nullable | Path file PDF slip gaji |
| `catatan` | TEXT | Nullable | Catatan khusus (mis: potongan kasbon) |
| `dibuat_oleh` | BIGINT UNSIGNED | FK | User (Admin/Karyawan TU) yang generate |
| `approved_oleh` | BIGINT UNSIGNED | FK, Nullable | User yang approve |
| `approved_at` | TIMESTAMP | Nullable | Waktu approval |
| `paid_at` | TIMESTAMP | Nullable | Waktu pembayaran (status → PAID) |
| `created_at` | TIMESTAMP | Auto | Tanggal dibuat |
| `updated_at` | TIMESTAMP | Auto | Tanggal terakhir update |

### Indexes
```sql
INDEX idx_karyawan_periode (karyawan_id, periode)
INDEX idx_status (status)
UNIQUE idx_slip_number (slip_number)
```

### Foreign Keys
```sql
FK karyawan_id → karyawans(id) ON DELETE CASCADE
FK dibuat_oleh → users(id) ON DELETE SET NULL
FK approved_oleh → users(id) ON DELETE SET NULL
```

### Status Flow
```
DRAFT → APPROVED → PAID
```

### Business Rules
1. **Manual Entry:** Admin/Karyawan TU input data gaji manual (bukan auto-generate)
2. **Approval Flow:** Harus diapprove Admin sebelum generate PDF
3. **Unique Constraint:** Satu karyawan hanya boleh 1 slip per periode
4. **Komponen Gaji:** Breakdown detail (gaji pokok, tunjangan, bonus, potongan)

---

## 4. Tabel `payment_gateway_logs` (Audit Trail)

**Purpose:** Logging semua interaksi dengan payment gateway untuk debugging & audit

### Fields

| Field | Type | Constraint | Description |
|-------|------|------------|-------------|
| `id` | BIGINT UNSIGNED | PK, Auto | Primary Key |
| `pembayaran_id` | BIGINT UNSIGNED | FK, Nullable | Foreign Key ke `pembayarans.id` |
| `gateway` | VARCHAR(50) | Not Null | Provider: midtrans, xendit |
| `action` | VARCHAR(50) | Not Null | create_token, callback, refund, cancel |
| `request_payload` | JSON | Nullable | Data yang dikirim ke gateway |
| `response_payload` | JSON | Nullable | Response dari gateway |
| `http_status` | SMALLINT | Nullable | HTTP status code (200, 400, 500, dll) |
| `error_message` | TEXT | Nullable | Error message jika gagal |
| `ip_address` | VARCHAR(45) | Nullable | IP address gateway/server |
| `user_agent` | TEXT | Nullable | User agent dari request |
| `created_at` | TIMESTAMP | Auto | Waktu event terjadi |

### Indexes
```sql
INDEX idx_pembayaran_id (pembayaran_id)
INDEX idx_gateway (gateway)
INDEX idx_action (action)
INDEX idx_created_at (created_at)
```

### Foreign Keys
```sql
FK pembayaran_id → pembayarans(id) ON DELETE SET NULL
```

### Business Rules
1. **Comprehensive Logging:** Log setiap API call ke gateway (request & response)
2. **Security:** Jangan log data sensitif (credit card, password)
3. **Retention:** Keep logs minimal 6 bulan untuk compliance

---

## 🔄 Relasi Antar Tabel

```
users (1) ─────┬──────── (1) murids (1) ────────── (*) tagihan_spps
               │                                          │
               │                                          │
               │                                    (1) pembayarans (*)
               │                                          │
               │                                          │
               │                                    (1) payment_gateway_logs (*)
               │
               └──────── (1) karyawans (1) ────────── (*) slip_gajis
```

---

## 🛠️ Data Settings yang Diperlukan

Tambahkan ke tabel `settings` untuk konfigurasi dinamis:

| Key | Value (Contoh) | Type | Description |
|-----|----------------|------|-------------|
| `nominal_spp_default` | 500000 | decimal | Nominal SPP default per bulan |
| `denda_per_hari` | 5000 | decimal | Denda keterlambatan per hari |
| `max_denda_spp` | 50000 | decimal | Maksimal denda SPP |
| `jatuh_tempo_spp_hari` | 10 | integer | Jatuh tempo SPP (tanggal 10 setiap bulan) |
| `payment_gateway_provider` | midtrans | string | Provider aktif (midtrans/xendit) |
| `midtrans_server_key` | (encrypted) | string | Server key Midtrans |
| `midtrans_client_key` | (encrypted) | string | Client key Midtrans |
| `midtrans_is_production` | false | boolean | Mode sandbox/production |
| `xendit_api_key` | (encrypted) | string | API Key Xendit |

**⚠️ Security Note:** Credential gateway sebaiknya di `.env`, bukan di database!

---

## 📝 Naming Conventions Laravel

### Tabel (Plural, snake_case)
- ✅ `tagihan_spps`
- ✅ `pembayarans`
- ✅ `slip_gajis`
- ✅ `payment_gateway_logs`

### Model (Singular, PascalCase)
- ✅ `TagihanSpp`
- ✅ `Pembayaran`
- ✅ `SlipGaji`
- ✅ `PaymentGatewayLog`

### Foreign Keys
- Format: `{table_singular}_id`
- ✅ `murid_id`, `karyawan_id`, `tagihan_id`

---

## 🚀 Migration Strategy

### Step-by-Step Execution Order:

1. **Migration 1:** `create_tagihan_spps_table` (depends on: murids, users)
2. **Migration 2:** `create_pembayarans_table` (depends on: tagihan_spps)
3. **Migration 3:** `create_slip_gajis_table` (depends on: karyawans, users)
4. **Migration 4:** `create_payment_gateway_logs_table` (depends on: pembayarans)
5. **Seeder:** Insert default settings untuk SPP & payment gateway

### File Naming (Laravel 11 Convention):
```
YYYY_MM_DD_HHMMSS_create_table_name.php
```

### Rollback Safety:
- Semua migration harus punya method `down()` yang proper
- Foreign keys di-drop dulu sebelum drop table
- Check `Schema::hasTable()` sebelum create

---

## 🎨 Diagram ERD (Simplified)

```
┌─────────────┐
│   users     │
└──────┬──────┘
       │
       ├────────────────┬─────────────────┐
       │                │                 │
       ▼                ▼                 ▼
┌─────────────┐  ┌──────────────┐  ┌──────────────┐
│   murids    │  │  karyawans   │  │   (others)   │
└──────┬──────┘  └──────┬───────┘  └──────────────┘
       │                │
       │                │
       ▼                ▼
┌─────────────────┐  ┌─────────────────┐
│  tagihan_spps   │  │   slip_gajis    │
│                 │  │                 │
│  - periode      │  │  - periode      │
│  - jumlah       │  │  - gaji_pokok   │
│  - status       │  │  - total_gaji   │
│  - invoice_no   │  │  - slip_number  │
└────────┬────────┘  └─────────────────┘
         │
         ▼
┌─────────────────┐
│  pembayarans    │
│                 │
│  - gateway      │
│  - status       │
│  - snap_token   │
└────────┬────────┘
         │
         ▼
┌──────────────────────┐
│ payment_gateway_logs │
│                      │
│  - action            │
│  - request/response  │
└──────────────────────┘
```

---

## ✅ Validation Checklist

Sebelum eksekusi ke kode:

- [x] Semua FK mereferensi tabel yang sudah exist
- [x] Tipe data DECIMAL untuk nominal keuangan (bukan FLOAT)
- [x] Index pada kolom yang sering di-query (status, periode, user_id)
- [x] Unique constraint pada invoice_number & slip_number
- [x] Enum status dengan flow yang clear
- [x] Timestamps pada semua tabel (created_at, updated_at)
- [x] Soft deletes TIDAK dipakai (cascade delete untuk data integritas)
- [x] JSON column untuk raw_callback (MySQL 5.7+/PostgreSQL support)
- [x] Constraint check untuk tanggal (jatuh_tempo >= periode)

---

## 🔐 Security Considerations

1. **Payment Gateway Credentials:**
   - WAJIB di `.env`, bukan di database/config yang ter-commit
   - Encrypt via `php artisan env:encrypt` jika perlu

2. **Webhook Signature Validation:**
   - Validasi signature SETIAP request callback
   - Reject request jika signature tidak match
   - Log failed attempts di `payment_gateway_logs`

3. **Idempotency Key:**
   - Gunakan `transaction_id` sebagai idempotency key
   - Cek duplikasi sebelum proses payment

4. **SQL Injection:**
   - Pakai Eloquent ORM untuk semua query
   - JANGAN raw query untuk insert/update

5. **Authorization:**
   - Murid hanya bisa akses tagihan_spp miliknya (Policy)
   - Karyawan TU akses semua data keuangan (RoleMiddleware)

---

## 📊 Performance Optimization

1. **Indexes:**
   - Composite index pada `(murid_id, periode)` untuk query cepat
   - Index pada `status` untuk dashboard filtering

2. **Queue Processing:**
   - Payment callback wajib masuk queue (bukan sync)
   - PDF generation via Job (bukan inline)

3. **Caching:**
   - Cache settings SPP (invalidate saat update)
   - Cache dashboard stats (TTL 5 menit)

4. **Pagination:**
   - List tagihan/pembayaran pakai `paginate(25)`
   - JANGAN `get()->all()` untuk table besar

---

## 🧪 Test Data Strategy

Untuk Seeder:

1. **Settings:** 5 config SPP default
2. **Tagihan SPP:** 
   - 10 tagihan UNPAID (periode bulan ini)
   - 5 tagihan LUNAS (bulan lalu)
   - 2 tagihan EXPIRED (bulan lalu yang belum bayar)
3. **Pembayaran:**
   - 5 SUCCESS dengan kuitansi PDF
   - 2 PENDING (menunggu bayar)
   - 1 FAILED (transaksi gagal)
4. **Slip Gaji:**
   - 3 PAID (gaji bulan lalu)
   - 2 APPROVED (belum dibayar)
   - 1 DRAFT (belum approved)

---

## 🚦 Next Steps (Phase 2)

Setelah skema DB di-approve:

1. ✅ Generate 4 migration files
2. ✅ Generate 4 model files dengan relasi
3. ✅ Buat DatabaseSeeder untuk test data
4. ✅ Run `php artisan migrate:fresh --seed`
5. ✅ Validasi struktur tabel di database

**Siap lanjut ke implementasi kode?** 🚀

---

**Approval Status:** ⏳ **WAITING FOR REVIEW**

**Reviewer:** @Developer  
**Estimated Migration Time:** ~5 menit (4 tables + seeder)

---

> 💡 **Pro Tip:** Backup database sebelum migrate jika ada data production!

