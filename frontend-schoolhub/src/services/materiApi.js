import http from './kelasApi'

export const materiApi = {
  list: () => http.get('/guru/materi').then((response) => response.data),
  create: (payload) => http.post('/guru/materi', payload).then((response) => response.data),
  update: (id, payload) => http.post(`/guru/materi/${id}?_method=PUT`, payload).then((response) => response.data),
  remove: (id) => http.delete(`/guru/materi/${id}`).then((response) => response.data),
}

