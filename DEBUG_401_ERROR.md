# Debug Error 401 Unauthorized di Tugas

## Langkah-langkah Debug

### 1. Buka Browser Console (F12)

Saat di halaman tugas (`/dashboard/guru/tugas`), jalankan command berikut di console:

```javascript
// 1. Cek apakah token ada
console.log('Token:', sessionStorage.getItem('token'))

// 2. Cek user data
console.log('User:', JSON.parse(sessionStorage.getItem('user')))

// 3. Test API call manual
fetch('http://localhost:8000/api/auth/me', {
  headers: {
    'Authorization': 'Bearer ' + sessionStorage.getItem('token'),
    'Accept': 'application/json'
  }
})
.then(r => r.json())
.then(data => console.log('✅ Auth working:', data))
.catch(err => console.error('❌ Auth failed:', err))
```

### 2. Cek Output Console

Lihat apa yang tercetak:

#### Jika `Token: null`
**Masalah**: Anda belum login atau token sudah expired
**Solusi**: 
1. Logout dan login ulang
2. Refresh page setelah login

#### Jika `Token: "123|abc..."`
**Token ada**, lanjut cek step berikutnya

### 3. Test Auth Endpoint

Jalankan di console:

```javascript
// Test endpoint /auth/me
fetch('http://localhost:8000/api/auth/me', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer ' + sessionStorage.getItem('token'),
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
})
.then(response => {
  console.log('Status:', response.status)
  return response.json()
})
.then(data => {
  console.log('Response:', data)
  if (data.role) {
    console.log('✅ User role:', data.role)
  }
})
.catch(error => console.error('❌ Error:', error))
```

#### Output yang diharapkan:
```
Status: 200
Response: {id: 1, name: "...", email: "...", role: "guru", ...}
✅ User role: guru
```

#### Jika Status: 401
**Masalah**: Token tidak valid atau expired
**Solusi**:
1. Logout: `sessionStorage.clear()`
2. Login ulang sebagai guru
3. Pastikan backend running

### 4. Test Tugas Endpoint

Setelah auth/me berhasil, test endpoint tugas:

```javascript
// Test GET /guru/tugas
fetch('http://localhost:8000/api/guru/tugas', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer ' + sessionStorage.getItem('token'),
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
})
.then(response => {
  console.log('Status:', response.status)
  return response.json()
})
.then(data => {
  console.log('✅ Tugas Response:', data)
})
.catch(error => console.error('❌ Error:', error))
```

### 5. Kemungkinan Penyebab Error 401

| Penyebab | Solusi |
|----------|--------|
| Token tidak ada di sessionStorage | Login ulang |
| Token expired (sudah tidak valid) | Login ulang |
| User bukan role `guru` | Login dengan akun guru |
| Backend tidak running | Start backend: `php artisan serve` |
| CORS issue | Cek `config/cors.php` di backend |
| Sanctum config salah | Cek `config/sanctum.php` |

### 6. Quick Fix Commands

Di browser console:

```javascript
// 1. Clear all dan logout
sessionStorage.clear()
window.location.href = '/login'

// 2. Cek semua sessionStorage
console.log('All storage:', {
  token: sessionStorage.getItem('token'),
  user: sessionStorage.getItem('user')
})

// 3. Manual test dengan token hardcoded (untuk debug)
// Ambil token dari backend/database/tinker:
const testToken = 'PASTE_TOKEN_HERE'
sessionStorage.setItem('token', testToken)
location.reload()
```

### 7. Backend Verification

Di terminal backend, jalankan:

```bash
# Masuk ke tinker
php artisan tinker

# Cek token yang ada
>>> \Laravel\Sanctum\PersonalAccessToken::latest()->take(5)->get(['name', 'tokenable_type', 'tokenable_id', 'last_used_at'])

# Cek user guru
>>> \App\Models\User::where('role', 'guru')->get(['id', 'name', 'email', 'role'])

# Buat token baru untuk testing
>>> $user = \App\Models\User::where('role', 'guru')->first()
>>> $token = $user->createToken('test-token')->plainTextToken
>>> echo $token
```

Copy token yang dihasilkan dan paste di console:
```javascript
sessionStorage.setItem('token', 'PASTE_TOKEN_HERE')
location.reload()
```

## Checklist Debugging

- [ ] Token ada di sessionStorage?
- [ ] User role = "guru"?
- [ ] Backend running di port 8000?
- [ ] `/api/auth/me` return 200?
- [ ] `/api/guru/tugas` (GET) return 200?
- [ ] Browser console tidak ada CORS error?

## Jika Masih Gagal

Kirim screenshot atau copy-paste dari:
1. Output `sessionStorage.getItem('token')`
2. Output `sessionStorage.getItem('user')`
3. Network tab di DevTools untuk request yang gagal
4. Response body dari request yang gagal
