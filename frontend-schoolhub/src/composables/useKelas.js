import { ref } from 'vue'
import { kelasApi } from '../services/kelasApi'

// Data contoh dipakai selama API belum tersambung (VITE_USE_MOCK=true).
const MOCK = [
  { id: 1,  name: 'X RPL 1',   kelas: 'X',   jurusan: 'RPL', angkatan: 2026, wali_kelas: 'Ahmad Fauzi, S.Pd', kapasitas: 36, jumlah_siswa: 34 },
  { id: 2,  name: 'X RPL 2',   kelas: 'X',   jurusan: 'RPL', angkatan: 2026, wali_kelas: 'Rina Marlina, S.Kom', kapasitas: 36, jumlah_siswa: 33 },
  { id: 3,  name: 'X TKR 1',   kelas: 'X',   jurusan: 'TKR', angkatan: 2026, wali_kelas: 'Budi Santoso, S.T', kapasitas: 36, jumlah_siswa: 30 },
  { id: 4,  name: 'X TKR 2',   kelas: 'X',   jurusan: 'TKR', angkatan: 2026, wali_kelas: 'Dedi Kurniawan, S.Pd', kapasitas: 36, jumlah_siswa: 28 },
  { id: 5,  name: 'X TSM 1',   kelas: 'X',   jurusan: 'TSM', angkatan: 2026, wali_kelas: 'Sri Wahyuni, S.Pd', kapasitas: 36, jumlah_siswa: 31 },
  { id: 6,  name: 'X TSM 2',   kelas: 'X',   jurusan: 'TSM', angkatan: 2026, wali_kelas: 'Agus Salim, S.Pd', kapasitas: 36, jumlah_siswa: 29 },
  { id: 7,  name: 'XI RPL 1',  kelas: 'XI',  jurusan: 'RPL', angkatan: 2025, wali_kelas: 'Nur Aisyah, S.Kom', kapasitas: 36, jumlah_siswa: 35 },
  { id: 8,  name: 'XI RPL 2',  kelas: 'XI',  jurusan: 'RPL', angkatan: 2025, wali_kelas: 'Hendra Gunawan, S.Kom', kapasitas: 36, jumlah_siswa: 32 },
  { id: 9,  name: 'XI TKR 1',  kelas: 'XI',  jurusan: 'TKR', angkatan: 2025, wali_kelas: 'Yusuf Maulana, S.T', kapasitas: 36, jumlah_siswa: 30 },
  { id: 10, name: 'XI TKR 2',  kelas: 'XI',  jurusan: 'TKR', angkatan: 2025, wali_kelas: 'Lia Puspita, S.Pd', kapasitas: 36, jumlah_siswa: 27 },
  { id: 11, name: 'XI TSM 1',  kelas: 'XI',  jurusan: 'TSM', angkatan: 2025, wali_kelas: 'Rahmat Hidayat, S.Pd', kapasitas: 36, jumlah_siswa: 33 },
  { id: 12, name: 'XI TSM 2',  kelas: 'XI',  jurusan: 'TSM', angkatan: 2025, wali_kelas: 'Dewi Lestari, S.Pd', kapasitas: 36, jumlah_siswa: 26 },
  { id: 13, name: 'XII RPL 1', kelas: 'XII', jurusan: 'RPL', angkatan: 2024, wali_kelas: 'Fajar Nugroho, S.Kom', kapasitas: 36, jumlah_siswa: 34 },
  { id: 14, name: 'XII RPL 2', kelas: 'XII', jurusan: 'RPL', angkatan: 2024, wali_kelas: 'Siti Rahayu, S.Pd', kapasitas: 36, jumlah_siswa: 31 },
  { id: 15, name: 'XII TKR 1', kelas: 'XII', jurusan: 'TKR', angkatan: 2024, wali_kelas: 'Bambang Irawan, S.T', kapasitas: 36, jumlah_siswa: 29 },
  { id: 16, name: 'XII TKR 2', kelas: 'XII', jurusan: 'TKR', angkatan: 2024, wali_kelas: 'Anisa Rahma, S.Pd', kapasitas: 36, jumlah_siswa: 28 },
  { id: 17, name: 'XII TSM 1', kelas: 'XII', jurusan: 'TSM', angkatan: 2024, wali_kelas: 'Taufik Hidayat, S.Pd', kapasitas: 36, jumlah_siswa: 30 },
  { id: 18, name: 'XII TSM 2', kelas: 'XII', jurusan: 'TSM', angkatan: 2024, wali_kelas: 'Maya Sari, S.Pd', kapasitas: 36, jumlah_siswa: 25 },
]

const USE_MOCK = import.meta.env.VITE_USE_MOCK !== 'false'
const wait = (ms = 350) => new Promise((r) => setTimeout(r, ms))

export function useKelas() {
  const items = ref([])
  const loading = ref(false)
  const error = ref('')

  const fetchAll = async () => {
    loading.value = true
    error.value = ''
    try {
      if (USE_MOCK) {
        await wait()
        items.value = JSON.parse(JSON.stringify(MOCK))
      } else {
        const res = await kelasApi.list()
        items.value = res.data ?? res
      }
    } catch (e) {
      error.value = e.response?.data?.message ?? 'Data kelas gagal dimuat. Periksa koneksi ke server.'
    } finally {
      loading.value = false
    }
  }

  const store = async (payload) => {
    if (USE_MOCK) {
      await wait(250)
      const id = Math.max(0, ...items.value.map((i) => i.id)) + 1
      items.value.unshift({ ...payload, id, jumlah_siswa: 0 })
      return
    }
    const created = await kelasApi.create(payload)
    items.value.unshift(created.data ?? created)
  }

  const update = async (id, payload) => {
    if (USE_MOCK) {
      await wait(250)
      const i = items.value.findIndex((k) => k.id === id)
      items.value[i] = { ...items.value[i], ...payload }
      return
    }
    const updated = await kelasApi.update(id, payload)
    const i = items.value.findIndex((k) => k.id === id)
    items.value[i] = updated.data ?? updated
  }

  const destroy = async (id) => {
    if (USE_MOCK) await wait(250)
    else await kelasApi.remove(id)
    items.value = items.value.filter((k) => k.id !== id)
  }

  return { items, loading, error, fetchAll, store, update, destroy }
}
