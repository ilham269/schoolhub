# Laravel 11 Coding Standards

## Architecture Rules
- **Routing:** Semua API routes harus berada di `routes/api.php` menggunakan struktur Controller modern.
- **Controllers:** Gunakan Single Action Controller (`__invoke`) untuk fitur kompleks seperti `PaymentCallbackController`.
- **Business Logic:** Pindahkan logika pembayaran atau perhitungan gaji dari Controller ke **Services Layer** (`app/Services/PaymentService.php`).
- **Database:** Gunakan Form Requests untuk validasi input data Murid/Karyawan.

## Code Style
- Gunakan *Strict Types* (`declare(strict_types=1);`) di setiap file baru.
- Penamaan tabel database harus *plural* dan *snake_case*.
