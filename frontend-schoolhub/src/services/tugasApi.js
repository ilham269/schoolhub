import api from '@/utils/api'

// Endpoint Laravel: Route::prefix('guru')->group(...) dengan TugasController
// Untuk upload lampiran, gunakan multipart/form-data (lihat createFormData di useTugas.js).
export const tugasApi = {
  list: (params) => api.get('/guru/tugas', { params }).then((r) => r.data),
  show: (id) => api.get(`/guru/tugas/${id}`).then((r) => r.data),
  create: (formData) => {
    // Don't set Content-Type header - let browser set it automatically for FormData
    return api.post('/guru/tugas', formData).then((r) => r.data)
  },
  update: (id, formData) => {
    return api.post(`/guru/tugas/${id}`, formData).then((r) => r.data)
  },
  toggleActive: (id) => api.patch(`/guru/tugas/${id}/toggle-active`).then((r) => r.data),
  remove: (id) => api.delete(`/guru/tugas/${id}`).then((r) => r.data),
}

export default tugasApi
