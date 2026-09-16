import axios from 'axios'

const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api',
  headers: { Accept: 'application/json' },
})

http.interceptors.request.use((config) => {
  // Login menyimpan token pada sessionStorage. Fallback localStorage menjaga
  // kompatibilitas dengan sesi dari versi aplikasi sebelumnya.
  const token = sessionStorage.getItem('token') ?? localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// Endpoint Laravel: Route::apiResource('kelas', KelasController::class)
export const kelasApi = {
  list: (params) => http.get('/kelas', { params }).then((r) => r.data),
  show: (id) => http.get(`/kelas/${id}`).then((r) => r.data),
  create: (payload) => http.post('/kelas', payload).then((r) => r.data),
  update: (id, payload) => http.put(`/kelas/${id}`, payload).then((r) => r.data),
  remove: (id) => http.delete(`/kelas/${id}`).then((r) => r.data),
}

export default http
