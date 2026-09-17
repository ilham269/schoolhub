import api from '@/utils/api'

/**
 * API untuk Materi (Murid)
 *
 * Catatan konvensi response:
 * - list() dan show() me-return r.data (body JSON dari Laravel, mis. { data: [...] })
 *   karena caller hanya butuh payload JSON.
 * - download() me-return axios response object PENUH (bukan r.data), karena
 *   caller perlu mengakses response.headers (untuk Content-Disposition / nama file)
 *   dan response.data yang berupa Blob, bukan JSON.
 *   Jangan disamakan — perbedaan ini disengaja.
 */
export const muridMateriApi = {
  /** Mengambil daftar materi yang dipublikasikan untuk kelas murid. */
  list: () => api.get('/murid/materi').then((r) => r.data),

  /** Detail satu materi (termasuk relasi kelas, guru, mapel). */
  show: (id) => api.get(`/murid/materi/${id}`).then((r) => r.data),

  /**
   * Mengunduh file materi.
   * Me-return axios response penuh (bukan r.data) agar caller bisa
   * membaca header Content-Disposition untuk nama file asli dan
   * response.data sebagai Blob untuk URL.createObjectURL().
   */
  download: (id) =>
    api.get(`/murid/materi/${id}/download`, { responseType: 'blob' }),
}

export default muridMateriApi
