import api from '@/utils/api'

export const mapelApi = {
  list: (params) => api.get('/mapel', { params }).then((response) => response.data),

  getActive: () => api.get('/mapel/active').then((response) => response.data),

  show: (id) => api.get(`/mapel/${id}`).then((response) => response.data),

  create: (data) => api.post('/mapel', data).then((response) => response.data),

  update: (id, data) => api.put(`/mapel/${id}`, data).then((response) => response.data),

  remove: (id) => api.delete(`/mapel/${id}`).then((response) => response.data),
}
