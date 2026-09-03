# Setup Laravel Backend untuk Frontend Vue

Dokumentasi ini menjelaskan konfigurasi yang diperlukan di Laravel backend agar bisa terhubung dengan Vue frontend.

## 1. Install Laravel CORS Package

Laravel 9+ sudah include CORS handling, tapi pastikan package terinstall:

```bash
composer require fruitcake/laravel-cors
```

## 2. Konfigurasi CORS

Edit file `config/cors.php`:

```php
<?php

return [
    /*
     * Paths yang akan enable CORS
     */
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /*
     * HTTP Methods yang diizinkan
     */
    'allowed_methods' => ['*'],

    /*
     * Origins yang diizinkan untuk akses API
     */
    'allowed_origins' => [
        'http://localhost:5173',      // Vite dev server
        'http://127.0.0.1:5173',
        'http://localhost:3000',      // Alternative port
    ],

    'allowed_origins_patterns' => [],

    /*
     * Headers yang diizinkan
     */
    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    /*
     * Allow credentials (cookies, authorization headers)
     */
    'supports_credentials' => true,
];
```

## 3. Register CORS Middleware

Edit file `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ...
    \Fruitcake\Cors\HandleCors::class,
];
```

Atau untuk Laravel 11+, edit `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\Fruitcake\Cors\HandleCors::class);
})
```

## 4. API Routes

Edit `routes/api.php` untuk menambahkan route login:

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// Protected routes (butuh authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});
```

## 5. Auth Controller Example

Buat controller `app/Http/Controllers/API/AuthController.php`:

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Generate token (Sanctum)
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role, // admin, guru, murid, karyawan
            ],
        ]);
    }

    /**
     * Register user
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'murid', // default role
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 201);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }
}
```

## 6. User Model

Pastikan User model punya field `role`. Edit migration `create_users_table`:

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->enum('role', ['admin', 'guru', 'murid', 'karyawan'])->default('murid');
    $table->rememberToken();
    $table->timestamps();
});
```

Edit `app/Models/User.php`:

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'role',
];
```

## 7. Laravel Sanctum Setup

Install dan configure Sanctum:

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

Edit `config/sanctum.php`:

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    'localhost,localhost:5173,127.0.0.1,127.0.0.1:5173,%s,%s',
    parse_url(env('APP_URL'), PHP_URL_HOST),
    parse_url(env('FRONTEND_URL'), PHP_URL_HOST)
))),
```

## 8. Environment Variables

Edit `.env`:

```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173

SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173
SESSION_DOMAIN=localhost
```

## 9. Testing

Test API dengan curl atau Postman:

```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Get user (dengan token)
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Troubleshooting

### CORS Error "No 'Access-Control-Allow-Origin'"

1. Cek apakah CORS middleware sudah terdaftar
2. Pastikan `allowed_origins` include `http://localhost:5173`
3. Clear cache: `php artisan config:clear`

### 401 Unauthorized

1. Cek token sudah dikirim di header `Authorization: Bearer TOKEN`
2. Pastikan route protected dengan `auth:sanctum` middleware
3. Cek token belum expired

### 419 CSRF Token Mismatch

Untuk API stateless dengan Sanctum token, tidak perlu CSRF token. Tapi kalau pakai session-based auth, perlu hit `/sanctum/csrf-cookie` dulu.

## Production Deployment

Untuk production, update `allowed_origins` dengan domain production:

```php
'allowed_origins' => [
    'https://yourdomain.com',
    'https://www.yourdomain.com',
],
```
