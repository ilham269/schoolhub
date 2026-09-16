import http from './kelasApi'

export const muridMateriApi = {
  list: () => http.get('/murid/materi').then((response) => response.data),
  show: (id) => http.get(`/murid/materi/${id}`).then((response) => response.data),
  download: (id) => http.get(`/murid/materi/${id}/download`, { responseType: 'blob' }),
}

