import api from '@/utils/api'

/**
 * API untuk Nilai (Murid)
 * Endpoint: /api/murid/nilai
 */
export const muridNilaiApi = {
  // GET /api/murid/nilai - Ambil semua data nilai murid
  get: () => api.get('/murid/nilai').then((r) => r.data),
}

export default muridNilaiApi
