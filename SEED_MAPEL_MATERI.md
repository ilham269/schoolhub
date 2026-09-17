# 🌱 Seed Data Mapel & Materi

## Masalah

Error validation:
```
"mapel_id": ["Mata pelajaran yang dipilih tidak ditemukan."]
"materi_id": ["Materi yang dipilih tidak ditemukan."]
```

Artinya: **Database belum ada data mapel dan materi!**

## Solusi Cepat

Jalankan di **terminal backend**:

```bash
cd backend-schoolhub
php artisan db:seed --class=MapelSeeder
php artisan db:seed --class=MateriSeeder
```

Output yang diharapkan:
```
✅ Mapel seeded successfully!
✅ Materi seeded successfully!
```

## Atau Seed Semua Data Sekaligus

Kalau mau reset semua dan seed ulang:

```bash
cd backend-schoolhub
php artisan migrate:fresh --seed
```

⚠️ **Warning**: Ini akan **HAPUS SEMUA DATA** dan buat ulang dari awal!

## Verify Data Ter-seed

Cek di tinker:

```bash
php artisan tinker
```

Lalu:
```php
// Cek mapel
DB::table('mapels')->get(['id', 'nama_mapel', 'kode_mapel']);

// Cek materi
DB::table('materis')->get(['id', 'judul', 'mapel_id']);

// Exit
exit
```

Harusnya ada data mapel dan materi.

## Data yang Di-seed

### Mapel (8 mata pelajaran):
1. Pemrograman Web (PWB)
2. Basis Data (BD)
3. Pemrograman Berorientasi Objek (PBO)
4. Matematika (MTK)
5. Bahasa Indonesia (BIND)
6. Bahasa Inggris (BING)
7. Pendidikan Agama (PAI)
8. PJOK (PJOK)

### Materi (5 materi):
1. Dasar HTML & CSS (Pemrograman Web)
2. JavaScript Fundamental (Pemrograman Web)
3. Pengenalan Database (Basis Data)
4. SQL Query (Basis Data)
5. Konsep OOP (PBO)

## Setelah Seed

1. **Refresh halaman tugas** di browser
2. **Coba buat tugas lagi**
3. Sekarang dropdown Mapel dan Materi sudah ada isinya!

## Troubleshooting

### Error: "Class 'MapelSeeder' not found"

Cek apakah file seeder ada:
```bash
ls database/seeders/MapelSeeder.php
ls database/seeders/MateriSeeder.php
```

Kalau tidak ada, file seeder sudah saya buat di:
- `backend-schoolhub/database/seeders/MapelSeeder.php`
- `backend-schoolhub/database/seeders/MateriSeeder.php`

### Seeder Jalan Tapi Dropdown Masih Kosong

Frontend pakai MOCK data. Pastikan ID nya match:
- Mapel ID: 1, 2, 3, 4, 5, 6, 7, 8
- Materi ID: 1, 2, 3, 4, 5

Atau lebih baik: **fetch data dari API** bukan hardcode!

## Next: Fetch Data dari API (Optional Improvement)

Untuk production, sebaiknya fetch mapel dan materi dari API, bukan hardcode.

Tambahkan di `useTugas.js`:

```javascript
const fetchMapels = async () => {
  const res = await api.get('/mapel')
  return res.data
}

const fetchMateris = async () => {
  const res = await api.get('/guru/materi')
  return res.data
}
```

Tapi untuk sekarang, MOCK data sudah cukup!
