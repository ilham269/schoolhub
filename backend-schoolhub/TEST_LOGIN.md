# Test Login API

## Kredensial Login

```
Email: admin@schoolhub.com
Password: password
```

## Test dengan Browser Console

Buka browser console (F12) dan paste code ini:

```javascript
// Test login API
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
  console.log('Response:', data)
  if (data.success) {
    console.log('✅ Login berhasil!')
    console.log('Token:', data.data.token)
    console.log('User:', data.data.user)
  } else {
    console.log('❌ Login gagal:', data.message)
  }
})
.catch(err => console.error('Error:', err))
```

## Struktur Response yang Benar

```json
{
  "success": true,
  "message": "Login berhasil.",
  "data": {
    "user": {
      "id": 1,
      "name": "Administrator",
      "email": "admin@schoolhub.com",
      "role": "Admin",
      "is_active": true
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

## Troubleshooting

### 1. Error CORS
Pastikan backend Laravel sudah jalan di `http://localhost:8000`

### 2. Error 401 (Unauthorized)
- Cek email dan password
- Pastikan user ada di database
- Pastikan user is_active = 1

### 3. Error 500
- Cek Laravel log: `storage/logs/laravel.log`
- Pastikan database connection OK

### 4. Error pada Frontend
- Buka Network tab di browser DevTools
- Lihat request ke `/api/auth/login`
- Cek response body

## Cek User di Database

```bash
php artisan tinker
```

Lalu jalankan:
```php
User::where('email', 'admin@schoolhub.com')->first()
```

## Cek Password Hash

```bash
php artisan tinker
```

Lalu jalankan:
```php
$user = User::where('email', 'admin@schoolhub.com')->first();
Hash::check('password', $user->password); // harus return true
```
