# ✅ KEUANGAN CONTROLLER - API DOCUMENTATION

**Created:** 2026-09-15  
**Status:** ✅ Backend COMPLETE

---

## 🎯 **FITUR YANG SUDAH DIBUAT**

### **Backend Complete (100%)**
- ✅ KeuanganController dengan 11 endpoints
- ✅ Routes dengan role middleware (Karyawan & Admin)
- ✅ Test data seeder (KeuanganTestSeeder)
- ✅ Comprehensive validation
- ✅ Business logic (status flow, calculations)

---

## 📋 **API ENDPOINTS**

### **Base URL:** `/api/keuangan`
### **Auth:** Required (`auth:sanctum`)
### **Role:** `Karyawan` atau `Admin`

---

## 1. **Dashboard Keuangan**

### `GET /api/keuangan/dashboard`

**Description:** Rekap statistik tagihan, pembayaran, dan transaksi terbaru

**Response:**
```json
{
  "success": true,
  "data": {
    "stats": {
      "tagihan": {
        "total": 20,
        "unpaid": 10,
        "lunas": 7,
        "expired": 2
      },
      "nominal": {
        "total_tagihan": 10000000,
        "total_lunas": 3500000,
        "total_pending": 5000000
      },
      "pembayaran": {
        "total": 12,
        "success": 7,
        "pending": 3,
        "failed": 2
      }
    },
    "recent_transactions": [
      {
        "id": 1,
        "transaction_id": "TEST-12345",
        "murid_name": "Ahmad",
        "kelas": "XII RPL 1",
        "amount": 500000,
        "status": "SUCCESS",
        "payment_type": "bank_transfer",
        "paid_at": "2026-09-14T10:30:00.000000Z",
        "created_at": "2026-09-14T10:00:00.000000Z"
      }
    ],
    "jatuh_tempo_minggu_ini": [
      {
        "id": 5,
        "invoice_number": "INV/SPP/202609/00005",
        "murid_name": "Budi",
        "kelas": "XI TKJ 2",
        "total": 500000,
        "jatuh_tempo": "2026-09-20"
      }
    ]
  }
}
```

---

## 2. **Get Tagihan SPP (dengan filter)**

### `GET /api/keuangan/tagihan`

**Query Parameters:**
- `status` (optional): UNPAID, PENDING, LUNAS, EXPIRED, CANCELLED
- `periode` (optional): YYYY-MM-DD
- `kelas_id` (optional): integer
- `search` (optional): nama murid atau NIS
- `per_page` (optional): default 25

**Example:**
```
GET /api/keuangan/tagihan?status=UNPAID&per_page=10
GET /api/keuangan/tagihan?search=Ahmad&kelas_id=1
```

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "murid_id": 1,
        "periode": "2026-09-01",
        "jumlah": 500000,
        "denda": 0,
        "total": 500000,
        "jatuh_tempo": "2026-09-10",
        "status": "UNPAID",
        "invoice_number": "INV/SPP/202609/00001",
        "kuitansi_path": null,
        "paid_at": null,
        "notes": null,
        "murid": {
          "id": 1,
          "nis": "12345",
          "user": {
            "id": 2,
            "name": "Ahmad Fauzi"
          },
          "kelas": {
            "id": 1,
            "name": "XII RPL 1"
          }
        }
      }
    ],
    "total": 10
  }
}
```

---

## 3. **Create Tagihan SPP**

### `POST /api/keuangan/tagihan`

**Body:**
```json
{
  "murid_id": 1,
  "periode": "2026-10-01",
  "jumlah": 500000,
  "jatuh_tempo": "2026-10-10",
  "notes": "Tagihan Oktober 2026"
}
```

**Validation:**
- `murid_id`: required, exists:murids
- `periode`: required, date
- `jumlah`: required, numeric, min:0
- `jatuh_tempo`: required, date, after:periode
- `notes`: optional, string

**Business Logic:**
- Auto-generate invoice_number format: `INV/SPP/YYYYMM/00001`
- Check duplicate: 1 murid = 1 tagihan per periode
- Initial status: UNPAID
- Initial denda: 0
- created_by: current user

**Response (201):**
```json
{
  "success": true,
  "message": "Tagihan berhasil dibuat",
  "data": {
    "id": 15,
    "murid_id": 1,
    "periode": "2026-10-01",
    "jumlah": 500000,
    "denda": 0,
    "total": 500000,
    "jatuh_tempo": "2026-10-10",
    "status": "UNPAID",
    "invoice_number": "INV/SPP/202610/00001",
    "notes": "Tagihan Oktober 2026",
    "created_by": 5
  }
}
```

**Error (422):**
```json
{
  "success": false,
  "message": "Tagihan untuk periode ini sudah ada"
}
```

---

## 4. **Update Tagihan SPP**

### `PUT /api/keuangan/tagihan/{id}`

**Body:**
```json
{
  "jumlah": 550000,
  "denda": 50000,
  "jatuh_tempo": "2026-09-15",
  "status": "UNPAID",
  "notes": "Updated dengan denda"
}
```

**Validation:**
- `jumlah`: optional, numeric, min:0
- `denda`: optional, numeric, min:0
- `jatuh_tempo`: optional, date
- `status`: optional, in:UNPAID,PENDING,LUNAS,EXPIRED,CANCELLED
- `notes`: optional, string

**Business Logic:**
- Cannot update `jumlah` if status = LUNAS
- Auto-calculate `total` = jumlah + denda

**Response:**
```json
{
  "success": true,
  "message": "Tagihan berhasil diupdate",
  "data": {
    "id": 1,
    "jumlah": 550000,
    "denda": 50000,
    "total": 600000,
    "status": "UNPAID"
  }
}
```

---

## 5. **Delete Tagihan SPP**

### `DELETE /api/keuangan/tagihan/{id}`

**Business Logic:**
- Only allow delete if status = UNPAID or CANCELLED
- Cascade delete: pembayaran juga terhapus (via foreign key)

**Response:**
```json
{
  "success": true,
  "message": "Tagihan berhasil dihapus"
}
```

**Error (422):**
```json
{
  "success": false,
  "message": "Hanya dapat menghapus tagihan UNPAID atau CANCELLED"
}
```

---

## 6. **Get Slip Gaji (dengan filter)**

### `GET /api/keuangan/slip-gaji`

**Query Parameters:**
- `status` (optional): DRAFT, APPROVED, PAID
- `periode` (optional): YYYY-MM-DD
- `per_page` (optional): default 25

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "karyawan_id": 1,
        "periode": "2026-09-01",
        "gaji_pokok": 5000000,
        "tunjangan": 1000000,
        "bonus": 500000,
        "potongan": 200000,
        "total_gaji": 6300000,
        "status": "PAID",
        "slip_number": "SLIP/GAJ/202609/00001",
        "file_path": "slip-gaji/dummy-1.pdf",
        "paid_at": "2026-09-28T10:00:00.000000Z",
        "karyawan": {
          "id": 1,
          "nip": "198712340001",
          "bagian": "Tata Usaha",
          "user": {
            "name": "Siti Aminah"
          }
        }
      }
    ],
    "total": 5
  }
}
```

---

## 7. **Create Slip Gaji**

### `POST /api/keuangan/slip-gaji`

**Body:**
```json
{
  "karyawan_id": 1,
  "periode": "2026-10-01",
  "gaji_pokok": 5000000,
  "tunjangan": 1000000,
  "bonus": 500000,
  "potongan": 200000,
  "catatan": "Gaji Oktober 2026"
}
```

**Validation:**
- `karyawan_id`: required, exists:karyawans
- `periode`: required, date
- `gaji_pokok`: required, numeric, min:0
- `tunjangan`: optional, numeric, min:0 (default: 0)
- `bonus`: optional, numeric, min:0 (default: 0)
- `potongan`: optional, numeric, min:0 (default: 0)
- `catatan`: optional, string

**Business Logic:**
- Auto-generate slip_number: `SLIP/GAJ/YYYYMM/00001`
- Auto-calculate total_gaji = gaji_pokok + tunjangan + bonus - potongan
- Initial status: DRAFT
- dibuat_oleh: current user

**Response (201):**
```json
{
  "success": true,
  "message": "Slip gaji berhasil dibuat",
  "data": {
    "id": 10,
    "karyawan_id": 1,
    "periode": "2026-10-01",
    "gaji_pokok": 5000000,
    "tunjangan": 1000000,
    "bonus": 500000,
    "potongan": 200000,
    "total_gaji": 6300000,
    "status": "DRAFT",
    "slip_number": "SLIP/GAJ/202610/00001"
  }
}
```

---

## 8. **Update Slip Gaji**

### `PUT /api/keuangan/slip-gaji/{id}`

**Body:**
```json
{
  "gaji_pokok": 5500000,
  "tunjangan": 1200000,
  "bonus": 0,
  "potongan": 300000,
  "catatan": "Updated"
}
```

**Business Logic:**
- Only allow update if status = DRAFT
- Auto-recalculate total_gaji

**Response:**
```json
{
  "success": true,
  "message": "Slip gaji berhasil diupdate",
  "data": {
    "id": 1,
    "total_gaji": 6400000
  }
}
```

---

## 9. **Approve Slip Gaji (Admin Only)**

### `POST /api/keuangan/slip-gaji/{id}/approve`

**Business Logic:**
- Only allow approve if status = DRAFT
- Status change: DRAFT → APPROVED
- Set approved_oleh = current user
- Set approved_at = now()

**Response:**
```json
{
  "success": true,
  "message": "Slip gaji berhasil di-approve",
  "data": {
    "id": 1,
    "status": "APPROVED",
    "approved_oleh": 1,
    "approved_at": "2026-09-15T12:00:00.000000Z"
  }
}
```

---

## 10. **Mark Slip Gaji as PAID**

### `POST /api/keuangan/slip-gaji/{id}/mark-paid`

**Business Logic:**
- Only allow if status = APPROVED
- Status change: APPROVED → PAID
- Set paid_at = now()

**Response:**
```json
{
  "success": true,
  "message": "Slip gaji berhasil ditandai PAID",
  "data": {
    "id": 1,
    "status": "PAID",
    "paid_at": "2026-09-15T12:30:00.000000Z"
  }
}
```

---

## 11. **Delete Slip Gaji**

### `DELETE /api/keuangan/slip-gaji/{id}`

**Business Logic:**
- Only allow delete if status = DRAFT

**Response:**
```json
{
  "success": true,
  "message": "Slip gaji berhasil dihapus"
}
```

---

## 🔐 **AUTHORIZATION**

### Role Middleware
```php
Route::middleware('role:karyawan,Admin')
```

**Allowed Roles:**
- ✅ Karyawan (Tata Usaha, Keuangan)
- ✅ Admin

**Blocked Roles:**
- ❌ Murid
- ❌ Guru
- ❌ Calon Siswa

---

## 📊 **BUSINESS LOGIC SUMMARY**

### Tagihan SPP Status Flow
```
UNPAID → PENDING → LUNAS
  ↓         ↓
EXPIRED  CANCELLED
```

### Slip Gaji Status Flow
```
DRAFT → APPROVED → PAID
```

### Rules:
1. **Tagihan SPP:**
   - Unique per murid per periode
   - Cannot update jumlah if LUNAS
   - Cannot delete if LUNAS/PENDING
   - Auto-generate invoice number

2. **Slip Gaji:**
   - Unique per karyawan per periode
   - Can only edit if DRAFT
   - Must be APPROVED before PAID
   - Auto-calculate total_gaji

---

## 🧪 **TESTING WITH POSTMAN**

### Setup:
1. Login as Karyawan or Admin:
```
POST /api/auth/login
Body: { "email": "karyawan@schoolhub.com", "password": "password" }
```

2. Copy token dari response

3. Add to headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Test Scenarios:

#### 1. Dashboard
```
GET /api/keuangan/dashboard
```

#### 2. Get All Tagihan (Unpaid)
```
GET /api/keuangan/tagihan?status=UNPAID&per_page=10
```

#### 3. Create Tagihan
```
POST /api/keuangan/tagihan
Body: {
  "murid_id": 1,
  "periode": "2026-10-01",
  "jumlah": 500000,
  "jatuh_tempo": "2026-10-10"
}
```

#### 4. Update Tagihan (add denda)
```
PUT /api/keuangan/tagihan/1
Body: {
  "denda": 50000,
  "notes": "Denda keterlambatan"
}
```

#### 5. Create Slip Gaji
```
POST /api/keuangan/slip-gaji
Body: {
  "karyawan_id": 1,
  "periode": "2026-10-01",
  "gaji_pokok": 5000000,
  "tunjangan": 1000000,
  "bonus": 500000,
  "potongan": 200000
}
```

#### 6. Approve Slip Gaji
```
POST /api/keuangan/slip-gaji/1/approve
```

#### 7. Mark as Paid
```
POST /api/keuangan/slip-gaji/1/mark-paid
```

---

## ✅ **COMPLETION STATUS**

### Backend
- [x] Controller created
- [x] All 11 endpoints implemented
- [x] Validation added
- [x] Business logic implemented
- [x] Routes configured
- [x] Role middleware applied
- [x] Test seeder created

### Next Steps
- [ ] Frontend Dashboard Keuangan (Vue.js)
- [ ] Frontend Manage Tagihan (CRUD UI)
- [ ] Frontend Manage Slip Gaji (CRUD UI)
- [ ] PDF Export (dompdf integration)
- [ ] Excel Export (maatwebsite/excel)

---

**Backend Status:** ✅ **100% COMPLETE**  
**Ready for:** Frontend Development

