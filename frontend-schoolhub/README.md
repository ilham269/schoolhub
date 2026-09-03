# Frontend SchoolHub

Frontend Vue.js untuk sistem manajemen sekolah dengan backend Laravel.

## Prerequisites

- Node.js v22.18.0 atau v24.12.0+
- npm 10+
- Laravel backend berjalan di http://localhost:8000 (atau sesuai konfigurasi)

## Setup & Installation

1. **Install dependencies**
   ```bash
   npm install --legacy-peer-deps
   ```

2. **Konfigurasi Environment**
   
   Edit file `.env` dan sesuaikan URL backend Laravel:
   ```env
   VITE_API_BASE_URL=http://localhost:8000
   ```

3. **Jalankan Development Server**
   ```bash
   npm run dev
   ```
   
   Aplikasi akan berjalan di http://localhost:5173

## Koneksi ke Backend Laravel

### CORS Setup (Laravel)

Pastikan Laravel backend sudah dikonfigurasi untuk menerima request dari frontend. Edit file `config/cors.php`:

```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    
    'allowed_methods' => ['*'],
    
    'allowed_origins' => [
        'http://localhost:5173', // Vue dev server
        'http://127.0.0.1:5173',
    ],
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => ['*'],
    
    'exposed_headers' => [],
    
    'max_age' => 0,
    
    'supports_credentials' => true,
];
```

### API Routes (Laravel)

Pastikan route Laravel menggunakan prefix `/api`:

```php
// routes/api.php
Route::post('/auth/login', [AuthController::class, 'login']);
```

Maka endpoint yang dipanggil dari Vue: `/api/auth/login`

### Proxy Vite

Vite sudah dikonfigurasi untuk proxy request `/api/*` ke Laravel backend. Lihat `vite.config.js`:

```javascript
server: {
  proxy: {
    '/api': {
      target: 'http://localhost:8000',
      changeOrigin: true,
      secure: false,
    }
  }
}
```

## Authentication Flow

1. User login via `/login`
2. API mengirim request ke `/api/auth/login`
3. Laravel mengembalikan token JWT/Sanctum
4. Token disimpan di localStorage
5. Setiap request selanjutnya otomatis menyertakan token via interceptor axios

## Project Structure

```
src/
├── assets/         # CSS dan asset lainnya
├── components/     # Komponen reusable (Navbar, Footer, dll)
├── router/         # Vue Router configuration
├── utils/          # Utility functions (api.js untuk axios)
├── views/          # Halaman/Views
│   ├── auth/       # Login, Register, dll
│   └── ...
├── App.vue         # Root component
└── main.js         # Entry point
```

## Available Scripts

- `npm run dev` - Start development server
- `npm run build` - Build untuk production
- `npm run preview` - Preview production build
- `npm run lint` - Lint & fix code
- `npm run format` - Format code dengan Prettier

## Troubleshooting

### Error: CORS policy

Pastikan Laravel CORS sudah dikonfigurasi dengan benar (lihat section CORS Setup)

### Error: Network Error / Connection Refused

- Pastikan Laravel backend berjalan di port yang benar
- Cek konfigurasi `VITE_API_BASE_URL` di file `.env`
- Restart Vite dev server setelah mengubah `.env`

### Error: 401 Unauthorized

- Token expired atau invalid
- Cek apakah token tersimpan di localStorage
- Cek Laravel authentication middleware

## Notes

- Gunakan `--legacy-peer-deps` saat install package jika ada conflict dependency
- Development server Vite berjalan di port 5173 (default)
- Laravel backend harus berjalan bersamaan saat development

## Recommended IDE Setup

[VS Code](https://code.visualstudio.com/) + [Vue (Official)](https://marketplace.visualstudio.com/items?itemName=Vue.volar) (and disable Vetur).

## Recommended Browser Setup

- Chromium-based browsers (Chrome, Edge, Brave, etc.):
  - [Vue.js devtools](https://chromewebstore.google.com/detail/vuejs-devtools/nhdogjmejiglipccpnnnanhbledajbpd)
  - [Turn on Custom Object Formatter in Chrome DevTools](http://bit.ly/object-formatters)
- Firefox:
  - [Vue.js devtools](https://addons.mozilla.org/en-US/firefox/addon/vue-js-devtools/)
  - [Turn on Custom Object Formatter in Firefox DevTools](https://fxdx.dev/firefox-devtools-custom-object-formatters/)
