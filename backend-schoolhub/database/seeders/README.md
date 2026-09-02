# Database Seeders & Factories

## 📚 Struktur Data

### Users (4 Role)
- **Admin** - Administrator sistem
- **Guru** - Tenaga pengajar
- **Murid** - Siswa/Peserta didik
- **Karyawan** - Staff administrasi

### Kelas
Terdapat 18 kelas dengan struktur:
- **Kelas X**: RPL 1-2, TKR 1-2, TSM 1-2 (6 kelas)
- **Kelas XI**: RPL 1-2, TKR 1-2, TSM 1-2 (6 kelas)
- **Kelas XII**: RPL 1-2, TKR 1-2, TSM 1-2 (6 kelas)

### Jurusan
- **RPL** - Rekayasa Perangkat Lunak
- **TKR** - Teknik Kendaraan Ringan
- **TSM** - Teknik Sepeda Motor

## 🚀 Cara Menjalankan Seeder

### 1. Fresh Migration dengan Seeder
```bash
php artisan migrate:fresh --seed
```

### 2. Jalankan Seeder Saja
```bash
php artisan db:seed
```

### 3. Jalankan Seeder Spesifik
```bash
# Admin only
php artisan db:seed --class=UserSeeder

# Kelas only
php artisan db:seed --class=KelasSeeder

# Guru only
php artisan db:seed --class=GuruSeeder

# Murid only
php artisan db:seed --class=MuridSeeder

# Karyawan only
php artisan db:seed --class=KaryawanSeeder
```

## 🏭 Menggunakan Factory

### Generate Data Random dengan Factory

```php
// Generate 10 kelas random
Kelas::factory()->count(10)->create();

// Generate kelas spesifik
Kelas::factory()->rpl()->kelas('XI', 1)->create();
Kelas::factory()->tkr()->kelas('XII', 2)->create();
Kelas::factory()->tsm()->kelas('X', 1)->create();

// Generate 20 murid random dengan kelas random
Murid::factory()->count(20)->create();

// Generate 5 guru
Guru::factory()->count(5)->create();

// Generate 3 karyawan
Karyawan::factory()->count(3)->create();

// Generate murid untuk kelas tertentu
$kelasRPL = Kelas::where('name', 'XI RPL 1')->first();
Murid::factory()->count(30)->create(['kelas_id' => $kelasRPL->id]);
```

### Di Tinker
```bash
php artisan tinker
```

```php
// Generate 100 murid random
\App\Models\Murid::factory()->count(100)->create();

// Generate untuk setiap kelas 30 murid
$kelas = \App\Models\Kelas::all();
foreach($kelas as $k) {
    \App\Models\Murid::factory()->count(30)->create(['kelas_id' => $k->id]);
}
```

## 👤 Default Credentials

Setelah seeding, gunakan kredensial berikut:

### Admin
- Email: `admin@schoolhub.com`
- Password: `password`

### Guru (5 users)
- Email: `ahmad.fauzi@schoolhub.com`
- Email: `siti.nurhaliza@schoolhub.com`
- Email: `budi.santoso@schoolhub.com`
- Email: `dewi.lestari@schoolhub.com`
- Email: `eko.prasetyo@schoolhub.com`
- Password: `password` (semua)

### Murid (7 users - sample)
- Email: `ahmad.rizki@student.schoolhub.com` (XI RPL 1)
- Email: `budi.setiawan@student.schoolhub.com` (XI RPL 1)
- Email: `citra.dewi@student.schoolhub.com` (XI RPL 1)
- Email: `dedi.gunawan@student.schoolhub.com` (XI TKR 1)
- Email: `eka.pratama@student.schoolhub.com` (XI TKR 1)
- Email: `fahmi.rizal@student.schoolhub.com` (XI TSM 1)
- Email: `gita.permata@student.schoolhub.com` (XI TSM 1)
- Password: `password` (semua)

### Karyawan (3 users)
- Email: `rina.kusuma@schoolhub.com` (Tata Usaha)
- Email: `hendra.wijaya@schoolhub.com` (Perpustakaan)
- Email: `sari.indah@schoolhub.com` (IT Support)
- Password: `password` (semua)

## 📋 Data yang Di-seed

### UserSeeder
- 1 Admin

### KelasSeeder
- 18 Kelas (X, XI, XII untuk RPL, TKR, TSM masing-masing 2 kelas)

### GuruSeeder
- 5 Guru dengan profil lengkap

### MuridSeeder
- 7 Murid contoh (tersebar di XI RPL 1, XI TKR 1, XI TSM 1)

### KaryawanSeeder
- 3 Karyawan (Tata Usaha, Perpustakaan, IT Support)

## 🎯 Tips

1. **Gunakan Factory untuk data testing** - Factory akan generate data random dengan struktur yang benar
2. **Gunakan Seeder untuk data production** - Seeder berisi data yang sudah ditentukan
3. **Kombinasi keduanya** - Seed data dasar, lalu gunakan Factory untuk populasi

## 🔄 Reset Database

```bash
# Hapus semua data dan migrate ulang dengan seeding
php artisan migrate:fresh --seed

# Atau jika hanya ingin reset data (tanpa migrate ulang)
php artisan db:wipe
php artisan migrate
php artisan db:seed
```
