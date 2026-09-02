# SchoolHub API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication
Semua endpoint (kecuali `/auth/login`) memerlukan **Bearer Token** yang didapat dari login.

```
Authorization: Bearer {your_token}
```

---

## 🔐 Authentication Endpoints

### 1. Login
```http
POST /api/auth/login
```

**Request Body:**
```json
{
  "email": "admin@schoolhub.com",
  "password": "password"
}
```

**Response Success (200):**
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "user": {
      "id": 1,
      "name": "Administrator",
      "email": "admin@schoolhub.com",
      "role": "Admin"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz..."
  }
}
```

### 2. Get Current User
```http
GET /api/auth/me
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Administrator",
    "email": "admin@schoolhub.com",
    "role": "Admin"
  }
}
```

### 3. Logout
```http
POST /api/auth/logout
```

**Response:**
```json
{
  "success": true,
  "message": "Logout berhasil"
}
```

---

## 👨‍🏫 Guru Endpoints

### 1. Get All Guru
```http
GET /api/guru
```

**Response:**
```json
{
  "success": true,
  "message": "Data guru berhasil diambil",
  "data": [
    {
      "id": 1,
      "nip": "1987654321000001",
      "nama_lengkap_guru": "Ahmad Fauzi, S.Pd",
      "gender": "L",
      "tanggal_lahir": "1985-03-15",
      "alamat": "Jl. Pendidikan No. 10, Jakarta",
      "nomor_telepon": "081234567890",
      "user": {
        "id": 2,
        "name": "Ahmad Fauzi, S.Pd",
        "email": "ahmad.fauzi@schoolhub.com",
        "role": "Guru"
      }
    }
  ]
}
```

### 2. Create Guru
```http
POST /api/guru
```

**Request Body:**
```json
{
  "name": "Dewi Sartika, S.Pd",
  "email": "dewi.sartika@schoolhub.com",
  "password": "password123",
  "nip": "1987654321000099",
  "nama_lengkap_guru": "Dewi Sartika, S.Pd",
  "gender": "P",
  "tanggal_lahir": "1990-05-15",
  "alamat": "Jl. Pendidikan No. 99",
  "nomor_telepon": "081234567899"
}
```

### 3. Get Guru by ID
```http
GET /api/guru/{id}
```

### 4. Update Guru
```http
PUT /api/guru/{id}
```

**Request Body (semua field optional):**
```json
{
  "name": "Dewi Sartika, M.Pd",
  "email": "dewi.new@schoolhub.com",
  "nomor_telepon": "081234567800"
}
```

### 5. Delete Guru
```http
DELETE /api/guru/{id}
```

---

## 👨‍🎓 Murid Endpoints

### 1. Get All Murid
```http
GET /api/murid
```

### 2. Create Murid
```http
POST /api/murid
```

**Request Body:**
```json
{
  "name": "Andi Saputra",
  "email": "andi.saputra@student.schoolhub.com",
  "password": "password123",
  "kelas_id": 1,
  "nis": "2024999001",
  "Nama_lengkap_murid": "Andi Saputra",
  "gender": "L",
  "tanggal_lahir": "2008-08-20",
  "alamat": "Jl. Siswa No. 10",
  "nomor_telepon": "082199999999",
  "nama_orangtua": "Bapak Andi"
}
```

### 3. Get Murid by ID
```http
GET /api/murid/{id}
```

### 4. Update Murid
```http
PUT /api/murid/{id}
```

### 5. Delete Murid
```http
DELETE /api/murid/{id}
```

### 6. Get Murid by Kelas
```http
GET /api/murid/kelas/{kelasId}
```

**Example:**
```http
GET /api/murid/kelas/1
```

---

## 👔 Karyawan Endpoints

### 1. Get All Karyawan
```http
GET /api/karyawan
```

### 2. Create Karyawan
```http
POST /api/karyawan
```

**Request Body:**
```json
{
  "name": "Budi Santoso",
  "email": "budi.santoso@schoolhub.com",
  "password": "password123",
  "nip": "1987654321100099",
  "nama_lengkap_karyawan": "Budi Santoso",
  "bagian": "Tata Usaha",
  "nomor_telepon": "081399999999",
  "alamat": "Jl. Karyawan No. 5"
}
```

### 3. Get Karyawan by ID
```http
GET /api/karyawan/{id}
```

### 4. Update Karyawan
```http
PUT /api/karyawan/{id}
```

### 5. Delete Karyawan
```http
DELETE /api/karyawan/{id}
```

### 6. Get Karyawan by Bagian
```http
GET /api/karyawan/bagian/{bagian}
```

**Example:**
```http
GET /api/karyawan/bagian/Tata%20Usaha
```

---

## 🏫 Kelas Endpoints

### 1. Get All Kelas
```http
GET /api/kelas
```

**Response:**
```json
{
  "success": true,
  "message": "Data kelas berhasil diambil",
  "data": [
    {
      "id": 1,
      "name": "XI RPL 1",
      "kelas": "XI",
      "jurusan": "RPL",
      "angkatan": 2025,
      "murids": []
    }
  ]
}
```

### 2. Create Kelas
```http
POST /api/kelas
```

**Request Body:**
```json
{
  "name": "X RPL 3",
  "kelas": "X",
  "jurusan": "RPL",
  "angkatan": 2026
}
```

### 3. Get Kelas by ID
```http
GET /api/kelas/{id}
```

### 4. Update Kelas
```http
PUT /api/kelas/{id}
```

### 5. Delete Kelas
```http
DELETE /api/kelas/{id}
```

### 6. Get Kelas by Jurusan
```http
GET /api/kelas/jurusan/{jurusan}
```

**Example:**
```http
GET /api/kelas/jurusan/RPL
GET /api/kelas/jurusan/TKR
GET /api/kelas/jurusan/TSM
```

### 7. Get Kelas by Tingkat
```http
GET /api/kelas/tingkat/{tingkat}
```

**Example:**
```http
GET /api/kelas/tingkat/X
GET /api/kelas/tingkat/XI
GET /api/kelas/tingkat/XII
```

---

## 🔑 Default Test Credentials

### Admin
```
Email: admin@schoolhub.com
Password: password
```

### Guru
```
Email: ahmad.fauzi@schoolhub.com
Password: password
```

### Murid
```
Email: ahmad.rizki@student.schoolhub.com
Password: password
```

### Karyawan
```
Email: rina.kusuma@schoolhub.com
Password: password
```

---

## 📝 Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operasi berhasil",
  "data": {}
}
```

### Error Response
```json
{
  "success": false,
  "message": "Operasi gagal",
  "errors": {}
}
```

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "email": ["Email sudah digunakan"],
    "password": ["Password minimal 8 karakter"]
  }
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Data tidak ditemukan"
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated."
}
```

---

## 🧪 Testing dengan Postman/Insomnia

### 1. Login untuk mendapatkan token
```http
POST http://localhost:8000/api/auth/login
Content-Type: application/json

{
  "email": "admin@schoolhub.com",
  "password": "password"
}
```

### 2. Copy token dari response

### 3. Gunakan token di Header untuk request berikutnya
```
Authorization: Bearer 1|abcdefg...
```

### 4. Test endpoint lainnya
```http
GET http://localhost:8000/api/guru
Authorization: Bearer {your_token}
```

---

## 🚀 Cara Jalankan Server

```bash
# Jalankan Laravel development server
php artisan serve

# Server akan berjalan di:
# http://localhost:8000
```

---

## 📊 Database Seeding

```bash
# Migrate dan seed database
php artisan migrate:fresh --seed
```

Data yang di-seed:
- 1 Admin
- 5 Guru
- 7 Murid
- 3 Karyawan
- 18 Kelas (X, XI, XII untuk RPL, TKR, TSM)
