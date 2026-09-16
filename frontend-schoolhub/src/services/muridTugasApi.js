import http from './kelasApi'

// Endpoint khusus murid: hanya tugas dari kelas murid yang sedang login,
// beserta status pengumpulannya sendiri.
export const muridTugasApi = {
  list: () => http.get('/murid/tugas').then((r) => r.data),
  submit: (tugasId, formData) =>
    http.post(`/murid/tugas/${tugasId}/kumpulkan`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }).then((r) => r.data),
}
