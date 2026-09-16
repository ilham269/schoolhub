import http from './kelasApi'

// GET /api/guru -> daftar guru untuk pilihan wali kelas (id, nama)
export const guruApi = {
  list: () => http.get('/guru').then((r) => r.data),
}