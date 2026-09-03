# Quick Start Guide

## 🚀 Yang Sudah Dikonfigurasi

✅ **Router Vue** - Login route sudah terdaftar di `/login`  
✅ **Axios** - Library HTTP client untuk API calls  
✅ **API Utils** - Axios instance dengan interceptor untuk token management  
✅ **Vite Proxy** - Request `/api/*` otomatis di-forward ke Laravel backend  
✅ **Environment Config** - File `.env` untuk konfigurasi backend URL  

## 📝 Langkah Cepat

### 1. Jalankan Frontend

```bash
npm run dev
```

Frontend akan berjalan di: http://localhost:5173

### 2. Jalankan Backend Laravel

Di terminal terpisah, masuk ke folder Laravel backend:

```bash
cd ../backend-folder  # sesuaikan path
php artisan serve
```

Backend akan berjalan di: http://localhost:8000

### 3. Akses Aplikasi

Buka browser: http://localhost:5173/login

## 🔧 Konfigurasi Backend Laravel (PENTING!)

Sebelum login bisa berfungsi, pastikan Laravel sudah dikonfigurasi:

1. **Install CORS** (jika belum):
   ```bash
   composer require fruitcake/laravel-cors
   ```

2. **Edit `config/cors.php`**:
   ```php
   'allowed_origins' => [
       'http://localhost:5173',
       'http://127.0.0.1:5173',
   ],
   'supports_credentials' => true,
   ```

3. **Tambah route di `routes/api.php`**:
   ```php
   Route::post('/auth/login', [AuthController::class, 'login']);
   ```

4. **Create AuthController** (lihat file `LARAVEL_SETUP.md` untuk detail)

📖 **Dokumentasi lengkap**: Lihat file `LARAVEL_SETUP.md`

## 🔐 Login Flow

1. User mengisi form login di `/login`
2. Vue kirim POST ke `/api/auth/login`
3. Vite proxy forward ke `http://localhost:8000/api/auth/login`
4. Laravel return token + user data
5. Token disimpan di localStorage
6. Redirect ke dashboard sesuai role user

## 📁 File Penting

| File | Fungsi |
|------|--------|
| `.env` | Konfigurasi backend URL |
| `vite.config.js` | Proxy configuration |
| `src/utils/api.js` | Axios instance & interceptors |
| `src/router/index.js` | Route definitions |
| `src/views/auth/LoginView.vue` | Login page |

## 🐛 Troubleshooting

### Error: "No match found for location with path /LoginView"
✅ **FIXED** - Route sudah didaftarkan dengan benar

### Error: "Cannot find module '../../services/api'"
✅ **FIXED** - Import path sudah diubah ke `../../utils/api`

### Error: CORS policy blocked
➡️ Setup CORS di Laravel (lihat section di atas)

### Error: Network Error
➡️ Pastikan Laravel backend berjalan di port 8000

### Frontend tidak bisa koneksi ke backend
➡️ Cek file `.env`, pastikan `VITE_API_BASE_URL=http://localhost:8000`  
➡️ Restart Vite dev server setelah ubah `.env`

## 📞 Next Steps

1. ✅ Jalankan `npm run dev`
2. ⚠️ Setup Laravel backend (CORS, routes, AuthController)
3. ✅ Test login di http://localhost:5173/login

Happy coding! 🎉
