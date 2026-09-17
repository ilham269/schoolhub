# Fix untuk Error 404 dan 401 di Tugas.vue

## Masalah yang Ditemukan

1. **Endpoint API Tidak Cocok**: Frontend memanggil `/tugas` tetapi backend mengharapkan `/guru/tugas`
2. **Axios Instance Terpisah**: `tugasApi.js` membuat axios instance sendiri yang tidak punya interceptor untuk menambahkan token
3. **Error 401 Unauthorized**: Token tidak terkirim karena menggunakan axios instance yang berbeda
4. **Toggle Active Endpoint Kurang**: Endpoint untuk toggle status aktif tugas tidak ada di API service

## Perubahan yang Dilakukan

### 1. File: `frontend-schoolhub/src/services/tugasApi.js`

**Perubahan Utama:**
- **Mengganti axios instance custom dengan instance dari `@/utils/api.js`** - Ini yang paling penting!
- Mengubah semua endpoint dari `/tugas` ke `/guru/tugas`
- Menambahkan endpoint `toggleActive` untuk toggle status aktif tugas

**Sebelum:**
```javascript
import axios from 'axios'

const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api',
  headers: { Accept: 'application/json' },
})

http.interceptors.request.use((config) => {
  const token = sessionStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

export const tugasApi = {
  list: (params) => http.get('/tugas', { params }).then((r) => r.data),
  show: (id) => http.get(`/tugas/${id}`).then((r) => r.data),
  // dst...
}
```

**Sesudah:**
```javascript
import api from '@/utils/api'

export const tugasApi = {
  list: (params) => api.get('/guru/tugas', { params }).then((r) => r.data),
  show: (id) => api.get(`/guru/tugas/${id}`).then((r) => r.data),
  create: (formData) => api.post('/guru/tugas', formData, ...).then((r) => r.data),
  update: (id, formData) => api.post(`/guru/tugas/${id}`, formData, ...).then((r) => r.data),
  toggleActive: (id) => api.patch(`/guru/tugas/${id}/toggle-active`).then((r) => r.data),
  remove: (id) => api.delete(`/guru/tugas/${id}`).then((r) => r.data),
}
```

### 2. File: `frontend-schoolhub/src/composables/useTugas.js`

**Perubahan:**
- Mengupdate fungsi `toggleActive` untuk memanggil endpoint API yang benar

**Sebelum:**
```javascript
const toggleActive = async (row) => {
  await update(row.id, { ...row, is_active: !row.is_active })
}
```

**Sesudah:**
```javascript
const toggleActive = async (row) => {
  if (USE_MOCK) {
    await wait(250)
    const i = items.value.findIndex((t) => t.id === row.id)
    if (i >= 0) items.value[i].is_active = !items.value[i].is_active
    return
  }
  const updated = await tugasApi.toggleActive(row.id)
  const i = items.value.findIndex((t) => t.id === row.id)
  if (i >= 0) items.value[i] = updated.data ?? updated
}
```

## Mengapa Error 401 Terjadi?

`tugasApi.js` sebelumnya membuat axios instance sendiri yang:
1. Tidak terintegrasi dengan sistem auth utama
2. Interceptor-nya sederhana dan tidak lengkap
3. Tidak menangani kasus edge-cases seperti public endpoints
4. Tidak punya error handling yang sama dengan `api.js`

Dengan menggunakan instance dari `@/utils/api.js`, sekarang tugasApi mendapat:
- ✅ Automatic token injection dari sessionStorage
- ✅ Proper error handling & redirect ke login jika token expired
- ✅ Logging untuk debugging
- ✅ FormData handling otomatis

## Backend Routes (Sudah Benar)

Backend sudah memiliki routes yang benar di `routes/api.php`:

```php
Route::middleware(['auth:sanctum'])
  ->prefix('guru')
  ->group(function () {
    Route::get('tugas', [TugasController::class, 'index']);
    Route::post('tugas', [TugasController::class, 'store']);
    Route::get('tugas/{tugas}', [TugasController::class, 'show']);
    Route::put('tugas/{tugas}', [TugasController::class, 'update']);
    Route::post('tugas/{tugas}', [TugasController::class, 'update']); // untuk multipart/form-data
    Route::patch('tugas/{tugas}/toggle-active', [TugasController::class, 'toggleActive']);
    Route::delete('tugas/{tugas}', [TugasController::class, 'destroy']);
  });
```

## Cara Testing

1. Login sebagai guru
2. Buka halaman Tugas (`/dashboard/guru/tugas`)
3. Coba fitur berikut:
   - ✅ Melihat daftar tugas
   - ✅ Menambah tugas baru
   - ✅ Mengubah tugas
   - ✅ Toggle status aktif/nonaktif
   - ✅ Menghapus tugas

## Catatan

- Pastikan backend sudah running: `php artisan serve` di port 8000
- Pastikan frontend sudah running: `npm run dev` di port 5173
- Pastikan sudah login sebagai user dengan role `guru`
- Token disimpan di `sessionStorage` (akan hilang saat tab ditutup)

## Endpoint API yang Digunakan

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/guru/tugas` | Ambil semua tugas guru |
| POST | `/api/guru/tugas` | Buat tugas baru |
| GET | `/api/guru/tugas/{id}` | Detail satu tugas |
| POST | `/api/guru/tugas/{id}` | Update tugas (dengan file upload) |
| PATCH | `/api/guru/tugas/{id}/toggle-active` | Toggle status aktif |
| DELETE | `/api/guru/tugas/{id}` | Hapus tugas |

Semua endpoint memerlukan:
- Header: `Authorization: Bearer {token}`
- Middleware: `auth:sanctum`
- Token dari: `sessionStorage.getItem('token')`
