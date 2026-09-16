import http from './kelasApi'

export const muridNilaiApi = {
  get: () => http.get('/murid/nilai').then((response) => response.data),
}
