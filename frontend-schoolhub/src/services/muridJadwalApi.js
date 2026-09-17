import http from './kelasApi'

// Tidak ada endpoint "/murid/jadwal" tersendiri — pakai endpoint umum
// /jadwal/kelas/{kelasId} yang sudah ada, dengan kelasId didapat dari
// profil murid yang sedang login.
export const muridJadwalApi = {
  byKelas: (kelasId) => http.get(`/jadwal/kelas/${kelasId}`).then((r) => r.data),
}