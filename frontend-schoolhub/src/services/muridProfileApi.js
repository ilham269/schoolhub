import http from './kelasApi'

// Endpoint khusus "profil saya" untuk murid yang sedang login.
// Backend cukup ambil data murid lewat user_id dari token yang login,
// jadi tidak perlu kirim id di URL.
export const muridProfileApi = {
  get: () => http.get('/murid/profile').then((r) => r.data),
  update: (payload) => http.put('/murid/profile', payload).then((r) => r.data),
}
