# 📚 SchoolHub - Dokumentasi Project

**Versi:** 1.0.0  
**Terakhir Diperbarui:** 17 September 2026  
**Stack:** Laravel 12 (REST API) + Vue 3 (Composition API, SPA) + Tailwind CSS

---

## 📋 Daftar Isi

1. [Gambaran Umum](#gambaran-umum)
2. [Struktur Project](#struktur-project)
3. [Fitur yang Selesai](#fitur-yang-selesai)
4. [User Roles](#user-roles)
5. [Database Schema](#database-schema)
6. [API Endpoints](#api-endpoints)
7. [Frontend Pages](#frontend-pages)
8. [Tech Stack](#tech-stack)
9. [Setup & Installation](#setup--installation)
10. [Default Credentials](#default-credentials)
11. [Fitur Per Role](#fitur-per-role)

---

## 🎯 Gambaran Umum

SchoolHub adalah sistem manajemen sekolah berbasis web untuk institusi pendidikan Indonesia. Platform ini mengelola seluruh aspek operasi sekolah termasuk manajemen siswa, guru, jadwal, tugas, nilai, keuangan (SPP & gaji), PPDB, berita, dan pengumuman.

### Karakteristik Utama:
- **Arsitektur:** Full-stack web application dengan backend REST API dan frontend SPA
- **Multi-role:** 5 peran user dengan akses dan fitur yang berbeda
- **Bahasa:** Interface dan database menggunakan Bahasa Indonesia
- **Responsif:** Tampilan yang responsif untuk desktop dan mobile

---

## 📁 Struktur Project

```
school-hub/
├── backend-schoolhub/          # Laravel REST API
│   ├── app/
│   │   ├── Http/
│   │   │   └── Controllers/Api/  # API Controllers
│   │   ├── Models/               # Eloquent Models
│   │   └── Providers/            # Service Providers
│   ├── database/
│   │   ├── migrations/           # Database migrations
│   │   └── seeders/              # Database seeders
│   ├── routes/
│   │   └── api.php               # API routes
│   ├── config/                   # Configuration files
│   └── storage/                  # Logs, uploads, cache
├── frontend-schoolhub/          # Vue.js SPA
│   ├── src/
│   │   ├── views/                # Page components (by role)
│   │   ├── components/           # Reusable components
│   │   ├── services/             # API services
│   │   ├── composables/          # Vue composables
│   │   ├── router/               # Vue Router config
│   │   └── utils/                # Utilities (axios config)
│   └── public/                   # Static assets
└── .kiro/                        # Kiro AI configuration
```

---

## ✅ Fitur yang Selesai

### 1. Autentikasi & Authorization
- Login/Logout dengan Laravel Sanctum
- Token-based authentication (Bearer token)
- Role-based access control (RBAC)
- Session management (sessionStorage di frontend)

### 2. Manajemen User
- CRUD untuk Admin, Guru, Murid, Karyawan
- Profile management per role
- User activation/deactivation

### 3. Manajemen Akademik
- **Kelas:** CRUD kelas (X, XI, XII RPL/TKR/TSM)
- **Mapel:** Mata pelajaran dengan kode dan KKM
- **Guru:** Profil guru dengan NIP dan relasi ke user
- **Murid:** Profil murid dengan NISN, kelas, dan relasi ke user

### 4. Sistem Tugas
- **Guru:** Create, Read, Update, Delete tugas
- Filter tugas berdasarkan kelas, mapel, status
- Upload lampiran file
- Toggle aktif/nonaktif tugas
- Deadline management
- **Murid:** Lihat tugas kelas, kumpulkan tugas, lihat nilai

### 5. Sistem Materi
- **Guru:** Upload dan publish materi pembelajaran
- Link eksternal atau file download
- Materi per kelas
- **Murid:** Lihat materi yang dipublikasikan untuk kelasnya

### 6. Sistem Nilai
- Breakdown nilai per mapel
- Rata-rata tugas dan ujian
- Bobot: Tugas 40% + Ujian 60%
- Status KKM (tuntas/belum)
- Grafik tren nilai
- Riwayat tugas dan ujian

### 7. Jadwal Pelajaran
- Jadwal per kelas dan hari
- Relasi guru-mapel-kelas

### 8. Keuangan (SPP & Gaji)
- **Tagihan SPP:** Generate otomatis, denda per hari, status pembayaran
- **Pembayaran:** Integration dengan payment gateway (Midtrans)
- **Slip Gaji:** Breakdown komponen gaji, approval workflow
- Dashboard keuangan untuk karyawan/admin

### 9. PPDB (Penerimaan Peserta Didik Baru)
- Registrasi calon siswa
- Sistem ujian PPDB dengan timer
- Monitoring ujian real-time
- Activity log untuk security

### 10. Content Management
- **Berita:** CRUD berita dengan kategori
- **Pengumuman:** CRUD pengumuman
- Status publish/draft

### 11. Dashboard
- **Admin:** Overview sistem lengkap
- **Guru:** Statistik tugas, nilai, jadwal
- **Murid:** Tugas, nilai, jadwal upcoming
- **Karyawan:** Keuangan, data siswa

---

## 👥 User Roles

| Role | Deskripsi | Akses Utama |
|------|-----------|-------------|
| **Admin** | Administrator sistem | Full access ke semua fitur |
| **Guru** | Guru pelajaran | Kelas yang diampu, tugas, materi, nilai |
| **Murid** | Siswa | Tugas, materi, nilai, jadwal |
| **Karyawan** | Staff sekolah | Keuangan, administrasi |
| **Calon Siswa** | PPDB | Registrasi dan ujian PPDB |

### Relasi User-Profile:
```
User (role='guru') ──hasOne──► Guru
User (role='murid') ──hasOne──► Murid
User (role='karyawan') ──hasOne──► Karyawan
User (role='calon_siswa') ──hasOne──► CalonSiswa
```

---

## 🗄️ Database Schema

### Tabel Utama

#### 1. **users** - User authentication
```php
id, name, profile, email, password, role, is_active, timestamps
```

#### 2. **gurus** - Profil guru
```php
id, user_id, nip, nama_lengkap_guru, gambar_guru, gender, 
tanggal_lahir, alamat, nomor_telepon, timestamps
```

#### 3. **murids** - Profil murid
```php
id, user_id, kelas_id, nisn, nama_lengkap_murid, tanggal_lahir,
gender, alamat, nomor_telepon, timestamps
```

#### 4. **karyawans** - Profil karyawan
```php
id, user_id, nip, nama_lengkap_karyawan, bagian, gender,
tanggal_lahir, alamat, nomor_telepon, timestamps
```

#### 5. **kelas** - Kelas
```php
id, nama_kelas, tingkat, jurusan, tahun_ajaran, is_active, timestamps
```

#### 6. **mapels** - Mata pelajaran
```php
id, kode_mapel, nama_mapel, jumlah_jam, kkm, timestamps
```

#### 7. **tugas** - Tugas pembelajaran
```php
id, kelas_id, guru_id, mapel_id, materi_id, judul, deskripsi,
instruksi, file_path, tanggal_dibuat, deadline, nilai_maksimal,
is_active, timestamps
```

#### 8. **materis** - Materi pembelajaran
```php
id, kelas_id, guru_id, mapel_id, judul, deskripsi, konten,
file_path, link, tanggal_upload, is_published, timestamps
```

#### 9. **pengumpulan_tugas** - Pengumpulan tugas murid
```php
id, tugas_id, murid_id, tanggal_pengumpulan, file_path, link,
catatan, nilai, feedback, status, timestamps
```

#### 10. **tagihan_spps** - Tagihan SPP
```php
id, murid_id, periode, nominal, denda, status, jatuh_tempo,
tanggal_lunas, created_by, timestamps
```

#### 11. **pembayarans** - Pembayaran SPP
```php
id, tagihan_id, transaction_id, payment_method, gross_amount,
status, raw_callback, created_by, timestamps
```

#### 12. **slip_gajis** - Slip gaji karyawan
```php
id, karyawan_id, periode, gaji_pokok, tunjangan, bonus,
potongan, total_gaji, status, dibuat_oleh, approved_oleh,
timestamps
```

---

## 🔌 API Endpoints

### Authentication
```
POST   /api/auth/login        # Login
POST   /api/auth/logout       # Logout
GET    /api/auth/me           # Get current user
```

### Guru Routes (role:guru)
```
GET    /api/guru/tugas              # List tugas guru
POST   /api/guru/tugas              # Create tugas
GET    /api/guru/tugas/{id}         # Detail tugas
PUT    /api/guru/tugas/{id}         # Update tugas
DELETE /api/guru/tugas/{id}         # Delete tugas
PATCH  /api/guru/tugas/{id}/toggle-active  # Toggle status

GET    /api/guru/materi             # List materi guru
POST   /api/guru/materi             # Create materi
GET    /api/guru/materi/{id}        # Detail materi
PUT    /api/guru/materi/{id}        # Update materi
DELETE /api/guru/materi/{id}        # Delete materi
```

### Murid Routes (role:murid)
```
GET    /api/murid/tugas              # List tugas untuk kelas murid
POST   /api/murid/tugas/{id}/kumpulkan   # Kumpulkan tugas

GET    /api/murid/materi             # List materi untuk kelas murid
GET    /api/murid/materi/{id}        # Detail materi
GET    /api/murid/materi/{id}/download  # Download materi

GET    /api/murid/nilai              # Get nilai murid
GET    /api/murid/profile            # Get profile murid
PUT    /api/murid/profile            # Update profile
```

### Umum Routes (auth:sanctum)
```
GET    /api/kelas              # List kelas
GET    /api/kelas/{id}         # Detail kelas

GET    /api/mapel              # List mapel
GET    /api/mapel/{id}         # Detail mapel

GET    /api/jadwal             # List jadwal
GET    /api/jadwal/kelas/{id}  # Jadwal per kelas
GET    /api/jadwal/guru/{id}   # Jadwal per guru

GET    /api/dashboard          # Dashboard by role
GET    /api/dashboard/guru     # Dashboard guru
GET    /api/dashboard/murid    # Dashboard murid
```

### Admin Routes (role:admin)
```
CRUD   /api/guru               # Manage guru
CRUD   /api/murid              # Manage murid
CRUD   /api/karyawan           # Manage karyawan
CRUD   /api/kelas              # Manage kelas
CRUD   /api/mapel              # Manage mapel
CRUD   /api/berita             # Manage berita
CRUD   /api/pengumuman         # Manage pengumuman
```

### Keuangan Routes (role:karyawan,admin)
```
GET    /api/keuangan/dashboard         # Dashboard keuangan
GET    /api/keuangan/tagihan           # List tagihan SPP
POST   /api/keuangan/tagihan           # Create tagihan
PUT    /api/keuangan/tagihan/{id}      # Update tagihan
DELETE /api/keuangan/tagihan/{id}      # Delete tagihan

GET    /api/keuangan/slip-gaji         # List slip gaji
POST   /api/keuangan/slip-gaji         # Create slip gaji
PUT    /api/keuangan/slip-gaji/{id}    # Update slip gaji
POST   /api/keuangan/slip-gaji/{id}/approve   # Approve slip gaji
POST   /api/keuangan/slip-gaji/{id}/mark-paid # Mark as paid
```

### PPDB Routes
```
POST   /api/public/ppdb/register       # Registrasi calon siswa (public)
GET    /api/ppdb/profile               # Profile calon siswa
GET    /api/ppdb/exams                 # List ujian PPDB
POST   /api/ppdb/exams/{id}/start      # Start ujian
POST   /api/ppdb/exams/{id}/submit     # Submit jawaban
```

### Public Routes
```
GET    /api/public/home                # Data homepage publik
GET    /api/public/guru                # Daftar guru
GET    /api/public/programs            # Program sekolah
GET    /api/public/berita              # Berita published
GET    /api/public/berita/{id}         # Detail berita
GET    /api/public/pengumuman          # Pengumuman published
GET    /api/public/pengumuman/{id}     # Detail pengumuman
```

---

## 🖥️ Frontend Pages

### Public Pages
| Route | File | Deskripsi |
|-------|------|-----------|
| `/` | HomeView.vue | Homepage sekolah |
| `/berita` | BeritaView.vue | Daftar berita |
| `/pengumuman` | pengumumanview.vue | Daftar pengumuman |
| `/login` | auth/LoginView.vue | Login page |

### Admin Pages (`/dashboard/admin`)
| Route | File | Fitur |
|-------|------|-------|
| `/dashboard/admin` | dashboard_admin.vue | Overview admin |
| `/dashboard/admin/data-guru` | - | Manage guru |
| `/dashboard/admin/data-murid` | - | Manage murid |
| `/dashboard/admin/data-karyawan` | - | Manage karyawan |
| `/dashboard/admin/data-kelas` | - | Manage kelas |
| `/dashboard/admin/data-mapel` | - | Manage mapel |

### Guru Pages (`/dashboard/guru`)
| Route | File | Fitur |
|-------|------|-------|
| `/dashboard/guru` | dashboard_guru.vue | Dashboard guru |
| `/dashboard/guru/kelas` | kelola_kelas.vue | Kelola kelas |
| `/dashboard/guru/tugas` | tugas.vue | CRUD tugas |
| `/dashboard/guru/nilai` | - | Input nilai |
| `/dashboard/guru/ujian-ppdb` | - | Ujian PPDB |

### Murid Pages (`/dashboard/murid`)
| Route | File | Fitur |
|-------|------|-------|
| `/dashboard/murid` | dashboard_murid.vue | Dashboard murid |
| `/dashboard/murid/tugas` | murid/Tugas.vue | Lihat & kumpulkan tugas |
| `/dashboard/murid/materi` | murid/Materi.vue | Lihat materi |
| `/dashboard/murid/nilai` | murid/Nilai.vue | Lihat nilai |
| `/dashboard/murid/jadwal` | murid/Jadwal.vue | Jadwal pelajaran |
| `/dashboard/murid/ujian` | murid/Ujian.vue | Ujian PPDB |
| `/dashboard/murid/profil` | murid/Profil.vue | Profile murid |
| `/dashboard/murid/administrasi` | murid/Administrasi.vue | Administrasi |
| `/dashboard/murid/keuangan` | murid/Keuangan.vue | Tagihan SPP |

### Karyawan Pages (`/dashboard/karyawan`)
| Route | File | Fitur |
|-------|------|-------|
| `/dashboard/karyawan` | dashboard_karyawan.vue | Dashboard karyawan |
| `/dashboard/karyawan/keuangan` | KeuanganController.php | Keuangan SPP & Gaji |
| `/dashboard/karyawan/data-siswa` | DataSiswaView.vue | Data siswa |

---

## 🛠️ Tech Stack

### Backend
```
Framework: Laravel 12
PHP: 8.2+
Database: SQLite (default), MySQL/PostgreSQL support
Authentication: Laravel Sanctum
CLI Tool: Artisan
Testing: PHPUnit
```

### Frontend
```
Framework: Vue.js 3 (Composition API, <script setup>)
Build Tool: Vite 5
Routing: Vue Router 4
HTTP Client: Axios
Styling: Tailwind CSS
Charts: Chart.js
State Management: Vue Reactivity (ref, computed)
```

### DevOps
```
OS: Windows / Linux / macOS
Package Managers: Composer (PHP), npm (Node.js)
Version Control: Git
Editor: VS Code (recommended)
```

---

## 🚀 Setup & Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 22.18.0 atau 24.12.0+
- npm 10+

### Backend Setup

```bash
# Navigate to backend directory
cd backend-schoolhub

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations (with seeders)
php artisan migrate --seed

# Start development server
php artisan serve
# Server runs on http://localhost:8000
```

### Frontend Setup

```bash
# Navigate to frontend directory
cd frontend-schoolhub

# Install dependencies
npm install --legacy-peer-deps

# Copy environment file
cp .env.example .env
# Set: VITE_API_BASE_URL=http://localhost:8000

# Start development server
npm run dev
# Server runs on http://localhost:5173
```

### Run Both Concurrently

```bash
# Backend (terminal 1)
cd backend-schoolhub
php artisan serve

# Frontend (terminal 2)
cd frontend-schoolhub
npm run dev
```

### Additional Commands

```bash
# Clear all caches
php artisan optimize:clear

# Run tests
composer run test
# atau
php artisan test

# Fix code style
./vendor/bin/pint

# Re-run migrations (WARNING: drops all data)
php artisan migrate:fresh --seed

# Database tinker
php artisan tinker
```

---

## 🔐 Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@schoolhub.com | password |
| Guru | ahmad.fauzi@schoolhub.com | password |
| Guru | siti.nurhaliza@schoolhub.com | password |
| Guru | budi.santoso@schoolhub.com | password |
| Guru | dewi.lestari@schoolhub.com | password |
| Guru | eko.prasetyo@schoolhub.com | password |

> ⚠️ **Security Note:** Ganti password ini sebelum deployment ke production!

---

## 📊 Fitur Per Role

### Admin
- ✅ Manage semua user (guru, murid, karyawan)
- ✅ Manage kelas, mapel, jadwal
- ✅ Manage berita dan pengumuman
- ✅ View semua dashboard dan laporan
- ✅ Konfigurasi sistem

### Guru
- ✅ Lihat dashboard dengan statistik
- ✅ CRUD tugas untuk kelas yang diampu
- ✅ Upload dan publish materi
- ✅ Input dan nilai tugas
- ✅ Kelola jadwal mengajar
- ✅ Monitoring ujian PPDB

### Murid
- ✅ Lihat dashboard personal
- ✅ Lihat tugas kelas dan deadline
- ✅ Kumpulkan tugas (upload file/link)
- ✅ Lihat materi yang dipublikasikan
- ✅ Lihat nilai per mapel
- ✅ Lihat jadwal pelajaran
- ✅ Ujian PPDB
- ✅ Cek tagihan SPP

### Karyawan
- ✅ Dashboard keuangan
- ✅ Manage tagihan SPP
- ✅ Process pembayaran
- ✅ Generate slip gaji
- ✅ Approval workflow slip gaji
- ✅ View data siswa

### Calon Siswa (PPDB)
- ✅ Registrasi online
- ✅ Ujian PPDB online
- ✅ Lihat hasil seleksi

---

## 📝 Catatan Teknis

### API Response Format

**Success Response:**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

**Error Response:**
```json
{
  "message": "Error description",
  "errors": { "field": ["error message"] }
}
```

**Validation Error (422):**
```json
{
  "message": "Data tidak valid.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

### Authentication Flow
1. User login dengan email/password
2. Backend returns token (Sanctum)
3. Frontend stores token di sessionStorage
4. Setiap request menyertakan header: `Authorization: Bearer {token}`
5. Token expires saat logout atau tab ditutup

### Role Middleware
- Middleware `auth:sanctum` memastikan user sudah login
- Middleware `role:guru|murid|admin|karyawan` memastikan akses sesuai role

### File Upload
- File disimpan di `storage/app/public/materi` dan `storage/app/public/tugas`
- Max size: 10MB
- Allowed types: pdf, doc, docx, ppt, pptx, xls, xlsx, zip, jpg, jpeg, png

### Payment Gateway
- Provider: Midtrans
- Supported methods: Bank Transfer, GOPay, ShopeePay, Alfamart, Indomaret
- Webhook callback: `/api/payment/callback`

---

## 🔧 Maintenance

### Daily Tasks
- Monitor `storage/logs/laravel.log` untuk errors
- Backup database secara berkala

### Weekly Tasks
- Review dan clear expired sessions
- Check storage usage (uploads, logs)

### Monthly Tasks
- Run `php artisan schedule:run` untuk auto-generate tagihan SPP
- Review financial reports

### Troubleshooting

**Error 401 (Unauthorized):**
- Token expired atau tidak valid
- Solution: Login ulang

**Error 403 (Forbidden):**
- Role tidak memiliki akses
- Solution: Contact admin

**Error 500 (Server Error):**
- Check Laravel log di `storage/logs/laravel.log`
- Common causes: database connection, file permissions, PHP memory limit

---

## 📚 Referensi Dokumen

| File | Deskripsi |
|------|-----------|
| `.kiro/steering/tech.md` | Tech stack documentation |
| `.kiro/steering/structure.md` | Project structure |
| `.kiro/steering/product.md` | Product requirements |
| `backend-schoolhub/_state.md` | Project development state |
| `.claude/output/KEUANGAN-*.md` | Keuangan feature docs |

---

## ✅ Status Fitur

| Modul | Status | Catatan |
|-------|--------|---------|
| Authentication | ✅ Selesai | Sanctum + role middleware |
| User Management | ✅ Selesai | CRUD + profile |
| Kelas | ✅ Selesai | CRUD + seeder |
| Mapel | ✅ Selesai | CRUD + seeder |
| Guru | ✅ Selesai | CRUD + seeder |
| Murid | ✅ Selesai | CRUD + seeder |
| Jadwal | ✅ Selesai | CRUD + relasi |
| Tugas | ✅ Selesai | CRUD + pengumpulan |
| Materi | ✅ Selesai | CRUD + download |
| Nilai | ✅ Selesai | Breakdown + grafik |
| Keuangan | ✅ Selesai | SPP + Gaji + Midtrans |
| PPDB | ✅ Selesai | Registrasi + Ujian |
| Berita | ✅ Selesai | CRUD + published |
| Pengumuman | ✅ Selesai | CRUD + published |
| Dashboard | ✅ Selesai | Per role |

---

**Dokumentasi ini dibuat pada 17 September 2026**  
**Untuk update dokumentasi, edit file ini langsung.**