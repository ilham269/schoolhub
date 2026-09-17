import api from './api'

export const mapelApi = {
  getAll() {
    return api.get('/mapel')
  },

  getActive() {
    return api.get('/mapel/active')
  },

  getById(id) {
    return api.get(`/mapel/${id}`)
  },

  create(data) {
    return api.post('/mapel', data)
  },

  update(id, data) {
    return api.put(`/mapel/${id}`, data)
  },

  delete(id) {
    return api.delete(`/mapel/${id}`)
  },
}