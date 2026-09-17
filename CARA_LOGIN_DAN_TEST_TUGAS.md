# 🚀 Cara Login dan Test Fitur Tugas

## PENTING: Harus Login Dulu!

Error 401 = **Belum Login** atau **Token Tidak Ada**

## Langkah-Langkah BENAR:

### 1️⃣ Pastikan Backend & Frontend Running

**Terminal 1 - Backend:**
```bash
cd backend-schoolhub
php artisan serve
```
Output: `Server started on http://localhost:8000`

**Terminal 2 - Frontend:**
```bash
cd frontend-schoolhub
npm run dev
```
Output: `Local: http://localhost:5173/`

### 2️⃣ Buka Browser ke `http://localhost:5173`

### 3️⃣ Klik "Login" atau buka `http://localhost:5173/login`

### 4️⃣ Login dengan Akun Guru

Gunakan salah satu akun guru ini:

| Email | Password | Nama |
|-------|----------|------|
| `ahmad.fauzi@schoolhub.com` | `password` | Ahmad Fauzi, S.Pd |
| `siti.nurhaliza@schoolhub.com` | `password` | Siti Nurhaliza, S.Pd |
| `budi.santoso@schoolhub.com` | `password` | Budi Santoso, S.Pd |

### 5️⃣ Setelah Login, Buka Console Browser (F12)

Cek apakah token tersimpan:

```javascript
console.log('Token:', sessionStorage.getItem('token'))
console.log('User:', sessionStorage.getItem('user'))
```

**Output yang BENAR:**
```
Token: 5|abc123def456...
User: {"id":2,"name":"Ahmad Fauzi, S.Pd","email":"ahmad.fauzi@schoolhub.com","role":"guru"}
```

**Jika NULL** → Logout dan login ulang!

### 6️⃣ Buka Halaman Tugas

Setelah login berhasil, buka:
- Via menu: Dashboard Guru → Tugas
- Atau langsung: `http://localhost:5173/dashboard/guru/tugas`

### 7️⃣ Coba Buat Tugas Baru

1. Klik tombol **"Buat Tugas"**
2. Isi form:
   - **Kelas**: Pilih kelas (contoh: X RPL 1)
   - **Mata Pelajaran**: Pilih mapel (ada 1 mapel di database)
   - **Judul**: "Test Tugas 1"
   - **Deskripsi**: "Ini test tugas"
   - **Deadline**: Pilih tanggal di masa depan
   - **Nilai Maksimal**: 100
3. Klik **"Simpan tugas"**

**Jika BERHASIL:**
- Muncul alert sukses
- Tugas muncul di daftar

**Jika GAGAL dengan 401:**
- Token expired atau tidak valid
- Logout dan login ulang

---

## 🐛 Troubleshooting

### Problem: Error 401 saat buat tugas

**Solusi 1: Clear storage dan login ulang**

Di browser console:
```javascript
sessionStorage.clear()
localStorage.clear()
window.location.href = '/login'
```

Lalu login lagi.

**Solusi 2: Set token manual (untuk testing)**

Di browser console:
```javascript
sessionStorage.setItem('token', '4|GbsermwD4LQJIIs0WluH7z5XDdWMvkevLvsjBAykb4b6bbf3')
sessionStorage.setItem('user', '{"id":2,"name":"Ahmad Fauzi, S.Pd","email":"ahmad.fauzi@schoolhub.com","role":"guru"}')
location.reload()
```

### Problem: "Data guru tidak ditemukan"

Berarti user tidak punya data di tabel `gurus`. 

**Solusi:**
```bash
cd backend-schoolhub
php artisan db:seed --class=GuruSeeder
```

### Problem: Tidak bisa pilih kelas atau mapel

Database belum ada kelas/mapel.

**Solusi:**
```bash
cd backend-schoolhub
php artisan migrate:fresh --seed
```

⚠️ **Warning**: Ini akan **hapus semua data** dan seed ulang!

### Problem: Backend tidak jalan

```bash
cd backend-schoolhub
php artisan serve
```

Pastikan port 8000 tidak dipakai aplikasi lain.

### Problem: Frontend tidak jalan

```bash
cd frontend-schoolhub
npm install --legacy-peer-deps
npm run dev
```

---

## ✅ Checklist Sebelum Test

- [ ] Backend running di `http://localhost:8000`
- [ ] Frontend running di `http://localhost:5173`
- [ ] Database sudah di-migrate: `php artisan migrate`
- [ ] Database sudah di-seed: `php artisan db:seed`
- [ ] **SUDAH LOGIN** dengan akun guru
- [ ] Token ada di sessionStorage (cek via console)
- [ ] User role = "guru" (cek via console)

---

## 🎯 Testing Flow Lengkap

### Test Guru - Buat Tugas

1. Login sebagai guru (`ahmad.fauzi@schoolhub.com` / `password`)
2. Buka `/dashboard/guru/tugas`
3. Klik "Buat tugas"
4. Isi form dan simpan
5. **Expected**: Tugas muncul di daftar

### Test Murid - Lihat Tugas

1. Login sebagai murid (cek email murid di database)
2. Buka `/dashboard/murid/tugas`
3. **Expected**: Muncul daftar tugas dari kelas murid

---

## 📝 Catatan Penting

1. **Token disimpan di sessionStorage** (hilang saat tab ditutup)
2. **Harus login setiap kali buka tab baru**
3. **Role harus lowercase**: "guru" bukan "Guru"
4. **Guru harus punya data di tabel gurus**: User → Guru relation
5. **Backend harus running** saat frontend diakses

---

## 🆘 Masih Error?

Jalankan test script untuk cek setup:

```bash
cd backend-schoolhub
php test_tugas_setup.php
```

Lihat output - akan kasih tau masalah apa yang ada.

Atau buka issue dengan screenshot:
1. Browser console (F12 → Console tab)
2. Network tab (F12 → Network tab) - klik request yang error
3. Backend terminal log
