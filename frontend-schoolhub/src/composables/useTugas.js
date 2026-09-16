import { ref } from 'vue'
import { tugasApi } from '../services/tugasApi'

// Data referensi. Di aplikasi nyata, ambil dari endpoint /kelas, /mapel, /materi.
export const KELAS_REF = [
  { id: 1, name: 'X RPL 1' }, { id: 2, name: 'X RPL 2' },
  { id: 3, name: 'X TKR 1' }, { id: 4, name: 'X TKR 2' },
  { id: 7, name: 'XI RPL 1' }, { id: 8, name: 'XI RPL 2' },
]
export const MAPEL_REF = [
  { id: 1, name: 'Pemrograman Web' },
  { id: 2, name: 'Basis Data' },
  { id: 3, name: 'Matematika' },
  { id: 4, name: 'Bahasa Indonesia' },
]
export const MATERI_REF = [
  { id: 1, mapel_id: 1, name: 'Dasar HTML & CSS' },
  { id: 2, mapel_id: 1, name: 'JavaScript Lanjutan' },
  { id: 3, mapel_id: 2, name: 'Normalisasi Database' },
  { id: 4, mapel_id: 3, name: 'Trigonometri' },
]

const MOCK = [
  {
    id: 1, kelas_id: 1, mapel_id: 1, materi_id: 1,
    judul: 'Membuat Halaman Profil HTML',
    deskripsi: 'Buat halaman profil sederhana menggunakan HTML dan CSS.',
    instruksi: 'Kumpulkan dalam format .zip berisi file index.html dan style.css.',
    file_path: null,
    tanggal_dibuat: '2026-09-01',
    deadline: '2026-09-20T23:59',
    nilai_maksimal: 100,
    is_active: true,
    jumlah_pengumpulan: 28,
  },
  {
    id: 2, kelas_id: 1, mapel_id: 1, materi_id: 2,
    judul: 'Latihan DOM Manipulation',
    deskripsi: 'Kerjakan 5 soal latihan manipulasi DOM dengan vanilla JavaScript.',
    instruksi: 'Tulis kode pada file soal.js yang sudah disediakan di LMS.',
    file_path: 'soal-dom.pdf',
    tanggal_dibuat: '2026-09-05',
    deadline: '2026-09-12T23:59',
    nilai_maksimal: 100,
    is_active: true,
    jumlah_pengumpulan: 30,
  },
  {
    id: 3, kelas_id: 3, mapel_id: 3, materi_id: 4,
    judul: 'Ulangan Harian Trigonometri',
    deskripsi: 'Kerjakan soal ulangan harian bab trigonometri.',
    instruksi: 'Tulis jawaban di kertas, foto, lalu unggah dalam satu file PDF.',
    file_path: 'soal-trigonometri.pdf',
    tanggal_dibuat: '2026-08-20',
    deadline: '2026-08-27T23:59',
    nilai_maksimal: 100,
    is_active: false,
    jumlah_pengumpulan: 29,
  },
  {
    id: 4, kelas_id: 7, mapel_id: 2, materi_id: 3,
    judul: 'Rancangan ERD Perpustakaan',
    deskripsi: 'Rancang ERD untuk sistem informasi perpustakaan sekolah.',
    instruksi: 'Gunakan draw.io, ekspor sebagai PNG, dan sertakan penjelasan singkat.',
    file_path: null,
    tanggal_dibuat: '2026-09-10',
    deadline: '2026-09-25T23:59',
    nilai_maksimal: 100,
    is_active: true,
    jumlah_pengumpulan: 12,
  },
]

const USE_MOCK = import.meta.env.VITE_USE_MOCK !== 'false'
const wait = (ms = 350) => new Promise((r) => setTimeout(r, ms))

// Payload form -> FormData, supaya file lampiran ikut terkirim.
export function createFormData(payload) {
  const fd = new FormData()
  Object.entries(payload).forEach(([key, value]) => {
    if (key === 'file' && value instanceof File) fd.append('file', value)
    else if (value !== null && value !== undefined) fd.append(key, value)
  })
  return fd
}

export function useTugas() {
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
        const res = await tugasApi.list()
        items.value = res.data ?? res
      }
    } catch (e) {
      error.value = e.response?.data?.message ?? 'Data tugas gagal dimuat. Periksa koneksi ke server.'
    } finally {
      loading.value = false
    }
  }

  const store = async (payload) => {
    if (USE_MOCK) {
      await wait(250)
      const id = Math.max(0, ...items.value.map((i) => i.id)) + 1
      const { file, ...rest } = payload
      items.value.unshift({ ...rest, id, jumlah_pengumpulan: 0, file_path: file?.name ?? null })
      return
    }
    const created = await tugasApi.create(createFormData(payload))
    items.value.unshift(created.data ?? created)
  }

  const update = async (id, payload) => {
    if (USE_MOCK) {
      await wait(250)
      const i = items.value.findIndex((t) => t.id === id)
      const { file, ...rest } = payload
      items.value[i] = { ...items.value[i], ...rest, file_path: file?.name ?? items.value[i].file_path }
      return
    }
    const updated = await tugasApi.update(id, createFormData(payload))
    const i = items.value.findIndex((t) => t.id === id)
    items.value[i] = updated.data ?? updated
  }

  const destroy = async (id) => {
    if (USE_MOCK) await wait(250)
    else await tugasApi.remove(id)
    items.value = items.value.filter((t) => t.id !== id)
  }

  const toggleActive = async (row) => {
    await update(row.id, { ...row, is_active: !row.is_active })
  }

  return { items, loading, error, fetchAll, store, update, destroy, toggleActive }
}
