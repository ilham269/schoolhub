import axios from 'axios'

const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api',
  headers: { Accept: 'application/json' },
})

http.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// Endpoint Laravel: Route::apiResource('tugas', TugasController::class)
// Untuk upload lampiran, gunakan multipart/form-data (lihat createFormData di useTugas.js).
export const tugasApi = {
  list: (params) => http.get('/tugas', { params }).then((r) => r.data),
  show: (id) => http.get(`/tugas/${id}`).then((r) => r.data),
  create: (formData) =>
    http.post('/tugas', formData, { headers: { 'Content-Type': 'multipart/form-data' } }).then((r) => r.data),
  update: (id, formData) =>
    http.post(`/tugas/${id}?_method=PUT`, formData, { headers: { 'Content-Type': 'multipart/form-data' } }).then((r) => r.data),
  remove: (id) => http.delete(`/tugas/${id}`).then((r) => r.data),
}

export default http
