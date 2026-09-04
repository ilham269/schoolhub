# Login Fix - School Hub

## 🐛 Masalah

Login tidak berhasil meskipun email dan password sudah benar.

## ✅ Solusi

### 1. **Perbaikan Structure Response**

**Masalah:** Frontend mengakses response dengan cara yang salah.

Backend mengembalikan:
```json
{
  "success": true,
  "data": {
    "user": {...},
    "token": "..."
  }
}
```

Frontend akses nya salah: `data.token` dan `data.user`  
Harusnya: `data.data.token` dan `data.data.user`

**Fix Applied:** ✅
- Update LoginView.vue untuk ekstrak data dengan benar
- Tambahkan console.log untuk debugging

### 2. **Case Sensitive Role**

**Masalah:** Role dari database mungkin "Admin" tapi di code nya cek "admin" (case sensitive)

**Fix Applied:** ✅
- Tambahkan `.toLowerCase()` saat cek role
- Sekarang "Admin", "ADMIN", "admin" semua akan work

---

## 🔍 Debug Steps

### 1. Buka Browser Console (F12)

Saat login, akan muncul log seperti ini:

```
🔐 Attempting login with: admin@schoolhub.com
📦 Full response: {...}
📦 Response data: {success: true, data: {...}}
✅ Login success!
👤 User: {id: 1, name: "Administrator", email: "...", role: "Admin"}
🔑 Token: 1|xxxxxxxxxxxx
💾 Token saved to localStorage
💾 User saved to localStorage
🎭 User role: admin
🚀 Redirecting to /dashboard/admin
```

### 2. Cek Network Tab

- Buka Network tab
- Filter: XHR
- Login
- Klik request `/api/auth/login`
- Cek Response body

### 3. Cek LocalStorage

Setelah login, cek localStorage di browser:
```
Application → Local Storage → http://localhost:5173
```

Harus ada:
- `token`: 1|xxxxxxxxx
- `user`: {"id":1,"name":"Administrator",...}

---

## 📝 Kredensial Login

```
Email: admin@schoolhub.com
Password: password
Role: Admin
```

---

## ⚠️ Jika Masih Error

### Error 401 (Unauthorized)

**Kemungkinan penyebab:**
1. User tidak ada di database
2. Password salah
3. User `is_active = 0`

**Solusi:**
```bash
# Re-seed database
php artisan migrate:fresh --seed
```

### Error 500 (Server Error)

**Kemungkinan penyebab:**
1. Database connection error
2. Bug di AuthController

**Solusi:**
- Cek Laravel log: `backend-schoolhub/storage/logs/laravel.log`
- Pastikan backend running: `php artisan serve`

### Error CORS

**Kemungkinan penyebab:**
- Backend tidak jalan
- Proxy Vite tidak config

**Solusi:**
Cek `vite.config.js` di frontend:
```js
export default defineConfig({
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true
      }
    }
  }
})
```

### Frontend tidak redirect

**Kemungkinan penyebab:**
- Token/user tidak tersimpan di localStorage
- Router error

**Solusi:**
1. Buka console, cek log "💾 Token saved"
2. Cek localStorage manually
3. Hard refresh browser (Ctrl+Shift+R)

---

## 🧪 Test Login Manual

Buka browser console dan paste:

```javascript
fetch('http://localhost:8000/api/auth/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    email: 'admin@schoolhub.com',
    password: 'password'
  })
})
.then(res => res.json())
.then(data => {
  console.log('Login Response:', data)
  if (data.success) {
    alert('✅ Login berhasil!')
    console.log('Token:', data.data.token)
  } else {
    alert('❌ Login gagal: ' + data.message)
  }
})
```

---

## ✅ Checklist

Pastikan semua ini sudah beres:

- [ ] Backend Laravel running (`php artisan serve`)
- [ ] Frontend Vue running (`npm run dev`)
- [ ] Database sudah di-seed (`php artisan migrate:fresh --seed`)
- [ ] User admin ada di database
- [ ] Browser console tidak ada error CORS
- [ ] LoginView.vue sudah updated (dengan console.log)
- [ ] localStorage accessible (not in private/incognito mode)

---

Status: ✅ FIXED
Date: September 4, 2026
