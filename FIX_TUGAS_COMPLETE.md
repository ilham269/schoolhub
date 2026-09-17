# Fix Lengkap Sistem Tugas - Guru & Murid

## Masalah yang Ditemukan dan Diperbaiki

### 1. ❌ TugasController Pakai User ID, Bukan Guru ID

**Struktur Database:**
```
users (id, role='guru')
  └── gurus (id, user_id, nip, nama_lengkap_guru)
       └── tugas (guru_id = gurus.id)  ← BUKAN users.id!
```

**TugasController SALAH:**
```php
protected function guruId(Request $request): int
{
    return (int) $request->user()->id;  // ❌ Ini user_id, bukan guru_id!
}
```

**SUDAH DIPERBAIKI:**
```php
protected function guruId(Request $request): int
{
    $user = $request->user();
    
    if (!$user || $user->role !== 'guru') {
        abort(403, 'Hanya guru yang dapat mengakses resource ini.');
    }
    
    $guru = $user->guru;  // Ambil relasi guru dari user
    
    if (!$guru) {
        abort(404, 'Data guru tidak ditemukan untuk user ini.');
    }
    
    return (int) $guru->id;  // ✅ Ini guru_id yang benar!
}
```

### 2. ❌ Frontend API Endpoint Salah

**SUDAH DIPERBAIKI:**
- Endpoint dari `/tugas` → `/guru/tugas`
- Menggunakan axios instance dari `@/utils/api` (punya interceptor token)
- FormData dikirim tanpa manual Content-Type header

## Cara Testing

### A. Cek Database Dulu

Jalankan di backend terminal:

```bash
cd backend-schoolhub
php artisan tinker
```

Kemudian di tinker:

```php
// 1. Cek user guru
$users = \App\Models\User::where('role', 'guru')->orWhere('role', 'Guru')->get(['id', 'name', 'email', 'role']);
print_r($users->toArray());

// 2. Cek data guru (harus ada user_id yang match)
$gurus = \App\Models\Guru::with('user')->get(['id', 'user_id', 'nip', 'nama_lengkap_guru']);
print_r($gurus->toArray());

// 3. Test login guru dan buat token
$user = \App\Models\User::where('email', 'ahmad.fauzi@schoolhub.com')->first();
if ($user) {
    echo "User found: " . $user->name . " (role: " . $user->role . ")\n";
    echo "Guru data: " . ($user->guru ? "YES (guru_id: " . $user->guru->id . ")" : "NO") . "\n";
    
    // Buat token baru
    $token = $user->createToken('test-token')->plainTextToken;
    echo "\n✅ Token untuk testing:\n";
    echo $token . "\n";
}
```

**Copy token yang dihasilkan!**

### B. Test di Frontend

1. **Buka browser console (F12)** di page login

2. **Set token manual** (pakai token dari tinker di atas):
```javascript
sessionStorage.setItem('token', 'PASTE_TOKEN_DARI_TINKER')
sessionStorage.setItem('user', JSON.stringify({
  id: 2,  // sesuaikan dengan user_id dari tinker
  name: 'Ahmad Fauzi, S.Pd',
  email: 'ahmad.fauzi@schoolhub.com',
  role: 'guru'  // PENTING: lowercase!
}))

// Redirect ke dashboard guru
window.location.href = '/dashboard/guru/tugas'
```

3. **Atau login normal** dengan:
   - Email: `ahmad.fauzi@schoolhub.com`
   - Password: `password`

4. **Coba buat tugas baru** di halaman tugas

### C. Jika Role Tidak Match

Kalau di database role nya `'Guru'` (kapital), tapi RoleMiddleware expect lowercase `'guru'`, ada 2 solusi:

**Solusi 1: Update data di database (REKOMENDASI)**
```bash
php artisan tinker
```

```php
// Update semua role jadi lowercase
\App\Models\User::where('role', 'Guru')->update(['role' => 'guru']);
\App\Models\User::where('role', 'Murid')->update(['role' => 'murid']);
\App\Models\User::where('role', 'Karyawan')->update(['role' => 'karyawan']);
\App\Models\User::where('role', 'Admin')->update(['role' => 'admin']);
\App\Models\User::where('role', 'Calon Siswa')->update(['role' => 'calon_siswa']);

echo "✅ Role sudah diupdate ke lowercase\n";
```

**Solusi 2: Update seeder untuk next time**
Edit `database/seeders/GuruSeeder.php`:
```php
'role' => 'guru',  // lowercase!
```

### D. Test API dengan cURL (Optional)

Test endpoint secara langsung:

```bash
# Ganti TOKEN dengan token dari tinker
TOKEN="PASTE_TOKEN_HERE"

# Test GET /guru/tugas
curl -X GET "http://localhost:8000/api/guru/tugas" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Harusnya return 200 dengan data tugas (array kosong kalau belum ada)
```

## Endpoints yang Sudah Benar

### Guru:
- `GET /api/guru/tugas` - List tugas yang dibuat guru
- `POST /api/guru/tugas` - Buat tugas baru
- `GET /api/guru/tugas/{id}` - Detail tugas
- `POST /api/guru/tugas/{id}` - Update tugas (FormData)
- `PATCH /api/guru/tugas/{id}/toggle-active` - Toggle aktif/nonaktif
- `DELETE /api/guru/tugas/{id}` - Hapus tugas

### Murid:
- `GET /api/murid/tugas` - List tugas untuk kelas murid
- `POST /api/murid/tugas/{id}/kumpulkan` - Kumpulkan tugas

## Struktur Relasi yang Benar

```
User (id=1, role='guru', email='ahmad.fauzi@schoolhub.com')
  └─→ Guru (id=1, user_id=1, nip='...', nama_lengkap_guru='Ahmad Fauzi, S.Pd')
       └─→ Tugas (id=1, guru_id=1, kelas_id=1, ...)  ✅ Pakai guru.id bukan user.id!

User (id=10, role='murid', email='murid@schoolhub.com')
  └─→ Murid (id=1, user_id=10, kelas_id=1, nisn='...')
       └─→ PengumpulanTugas (tugas_id=1, murid_id=1, ...)  ✅ Pakai murid.id bukan user.id!
```

## Checklist Sebelum Test

- [ ] Backend running: `php artisan serve`
- [ ] Frontend running: `npm run dev`
- [ ] Database sudah di-migrate: `php artisan migrate`
- [ ] Database sudah di-seed: `php artisan db:seed`
- [ ] Role di database sudah lowercase (guru, murid, admin)
- [ ] User guru punya data di tabel `gurus` (cek via tinker)
- [ ] Token valid di sessionStorage

## Troubleshooting

### Error: "Data guru tidak ditemukan untuk user ini"
**Penyebab**: User role='guru' tapi tidak ada data di tabel `gurus`
**Solusi**: 
```bash
php artisan db:seed --class=GuruSeeder
```

### Error: 403 Forbidden
**Penyebab**: Role tidak match (kapital vs lowercase)
**Solusi**: Update role ke lowercase (lihat Solusi 1 di atas)

### Error: 401 Unauthorized
**Penyebab**: Token tidak ada atau tidak valid
**Solusi**: Login ulang atau set token manual dari tinker

### Tugas tidak muncul di murid
**Penyebab**: 
- Tugas `is_active = false`
- Tugas `kelas_id` tidak match dengan `murid.kelas_id`
**Solusi**: Cek data tugas dan pastikan kelas_id sesuai
