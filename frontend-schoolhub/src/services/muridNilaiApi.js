import api from '@/utils/api'

/**
 * API untuk Nilai (Murid)
 * Endpoint: /api/murid/nilai
 */
export const muridNilaiApi = {
  // GET /api/murid/nilai - Ambil semua data nilai murid.
  // Laravel men-return { success, data: {...} }, jadi caller cukup pakai body.data.
  get: async () => {
    const response = await api.get('/murid/nilai')
    return response.data?.data ?? response.data ?? {}
  },
}

export default muridNilaiApi
