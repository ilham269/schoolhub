<template>
  <DashboardLayout title="Tugas" role-label="Guru" :navigation="navigation">
    <UiAlert v-if="alert.show" :type="alert.type" class="mb-4" @close="alert.show = false">
      {{ alert.message }}
    </UiAlert>
    <UiAlert v-if="error" type="danger" title="Gagal memuat data" class="mb-4" :dismissible="false">
      {{ error }}
    </UiAlert>

    <!-- Ringkasan -->
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
      <UiCard>
        <p class="text-sm text-slate-400">Total tugas</p>
        <p class="mt-1 text-3xl font-semibold text-slate-800">{{ items.length }}</p>
        <p class="mt-1 text-xs text-slate-400">Semua kelas yang diampu</p>
      </UiCard>
      <UiCard>
        <p class="text-sm text-slate-400">Tugas aktif</p>
        <p class="mt-1 text-3xl font-semibold text-slate-800">{{ aktifCount }}</p>
        <p class="mt-1 text-xs text-slate-400">Masih bisa dikumpulkan siswa</p>
      </UiCard>
      <UiCard>
        <p class="text-sm text-slate-400">Mendekati tenggat</p>
        <p class="mt-1 text-3xl font-semibold text-slate-800">{{ segeraCount }}</p>
        <p class="mt-1 text-xs text-slate-400">Deadline dalam 3 hari</p>
      </UiCard>
      <UiCard>
        <p class="text-sm text-slate-400">Tugas dinilai</p>
        <p class="mt-1 mb-3 text-3xl font-semibold text-slate-800">{{ Math.round(rataProgress) }}%</p>
        <UiProgress :value="rataProgress" />
      </UiCard>
    </div>

    <!-- Daftar tugas -->
    <UiCard title="Daftar Tugas" subtitle="Kelola tugas untuk setiap kelas yang Anda ampu" :padded="false">
      <template #actions>
        <UiDropdown>
          <template #trigger>
            <UiButton variant="secondary">
              Aksi lain
              <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
                <path d="M6 8l4 4 4-4" />
              </svg>
            </UiButton>
          </template>
          <UiDropdownItem @click="fetchAll">Muat ulang data</UiDropdownItem>
        </UiDropdown>
        <UiButton @click="openCreate">
          <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
            <path d="M10 5v10M5 10h10" />
          </svg>
          Buat tugas
        </UiButton>
      </template>

      <div class="space-y-4 px-6 pb-5">
        <UiTabs v-model="activeTab" :tabs="tabs" />
        <div class="flex flex-col gap-3 sm:flex-row">
          <div class="sm:max-w-xs sm:flex-1">
            <UiInput v-model="search" placeholder="Cari judul tugas...">
              <template #icon>
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
                  <circle cx="9" cy="9" r="5" /><path d="m13 13 4 4" stroke-linecap="round" />
                </svg>
              </template>
            </UiInput>
          </div>
          <div class="sm:w-52">
            <UiSelect v-model="kelasFilter" :options="kelasOptions" placeholder="Semua kelas" />
          </div>
          <div class="sm:w-52">
            <UiSelect v-model="mapelFilter" :options="mapelOptions" placeholder="Semua mapel" />
          </div>
        </div>
      </div>

      <UiTable :columns="columns" :rows="paged" :sort-key="sortKey" :sort-dir="sortDir" :loading="loading" @sort="toggleSort">
        <template #empty>
          <p class="font-medium text-slate-600">Belum ada tugas yang cocok</p>
          <p class="mt-1 text-sm text-slate-400">Ubah kata kunci atau filter, atau buat tugas baru.</p>
          <UiButton class="mt-4" variant="soft" size="sm" @click="openCreate">Buat tugas</UiButton>
        </template>

        <template #row="{ row }">
          <td class="px-6 py-4 max-w-xs">
            <p class="font-medium text-slate-800 truncate">{{ row.judul }}</p>
            <p class="text-xs text-slate-400">{{ mapelName(row.mapel_id) }}</p>
          </td>
          <td class="px-6 py-4 text-slate-600">{{ kelasName(row.kelas_id) }}</td>
          <td class="px-6 py-4">
            <p class="text-slate-700">{{ formatTanggal(row.deadline) }}</p>
            <UiBadge :variant="statusTone(row)" class="mt-1">{{ statusLabel(row) }}</UiBadge>
          </td>
          <td class="px-6 py-4">
            <div class="w-32">
              <p class="mb-1.5 text-xs text-slate-500">{{ row.jumlah_pengumpulan ?? 0 }} pengumpulan</p>
              <UiProgress :value="row.jumlah_pengumpulan ?? 0" :max="40" />
            </div>
          </td>
          <td class="px-6 py-4 text-slate-600">{{ row.nilai_maksimal }}</td>
          <td class="px-6 py-4">
            <button class="inline-flex" title="Aktifkan / nonaktifkan tugas" @click="toggleActive(row)">
              <UiBadge :variant="row.is_active ? 'success' : 'neutral'">
                {{ row.is_active ? 'Aktif' : 'Nonaktif' }}
              </UiBadge>
            </button>
          </td>
          <td class="px-6 py-4">
            <div class="flex items-center justify-end gap-1">
              <UiButton variant="ghost" size="icon" title="Lihat detail" @click="openDetail(row)">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M2 10s3-5 8-5 8 5 8 5-3 5-8 5-8-5-8-5Z" /><circle cx="10" cy="10" r="2" />
                </svg>
              </UiButton>
              <UiButton variant="ghost" size="icon" title="Ubah tugas" @click="openEdit(row)">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round">
                  <path d="M14 3l3 3-9 9H5v-3z" />
                </svg>
              </UiButton>
              <UiButton variant="ghost" size="icon" title="Hapus tugas"
                        class="text-rose-500 hover:bg-rose-50 hover:text-rose-600" @click="askDelete(row)">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                  <path d="M4 6h12M8 6V4h4v2M6 6l1 10h6l1-10" />
                </svg>
              </UiButton>
            </div>
          </td>
        </template>
      </UiTable>

      <UiPagination v-model:page="page" v-model:perPage="perPage" :total="sorted.length" />
    </UiCard>

    <!-- Modal tambah / ubah -->
    <UiModal v-model="formOpen" size="lg"
             :title="editingId ? 'Ubah tugas' : 'Buat tugas'"
             subtitle="Lengkapi detail tugas untuk kelas yang dituju.">
      <form class="grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
        <UiSelect v-model="form.kelas_id" label="Kelas" :options="kelasOptions" :error="errors.kelas_id" required placeholder="Pilih kelas" />
        <UiSelect v-model="form.mapel_id" label="Mata pelajaran" :options="mapelOptions" :error="errors.mapel_id" required placeholder="Pilih mapel" />
        <div class="sm:col-span-2">
          <UiSelect v-model="form.materi_id" label="Materi terkait" :options="materiOptionsForMapel" placeholder="Tanpa materi tertentu" />
        </div>
        <div class="sm:col-span-2">
          <UiInput v-model="form.judul" label="Judul tugas" placeholder="Contoh: Membuat Halaman Profil HTML" :error="errors.judul" required />
        </div>
        <div class="sm:col-span-2">
          <UiTextarea v-model="form.deskripsi" label="Deskripsi" placeholder="Jelaskan tugas secara singkat" :rows="3" :error="errors.deskripsi" />
        </div>
        <div class="sm:col-span-2">
          <UiTextarea v-model="form.instruksi" label="Instruksi pengumpulan" placeholder="Contoh: kumpulkan dalam format .zip" :rows="3" />
        </div>
        <UiInput v-model="form.deadline" label="Tenggat waktu" type="datetime-local" :error="errors.deadline" required />
        <UiInput v-model="form.nilai_maksimal" label="Nilai maksimal" type="number" :error="errors.nilai_maksimal" />
        <div class="sm:col-span-2">
          <span class="mb-1.5 block text-sm font-medium text-slate-700">Lampiran (opsional)</span>
          <input type="file" class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0
                                     file:bg-emerald-50 file:px-3.5 file:py-2 file:text-sm file:font-medium file:text-emerald-700
                                     hover:file:bg-emerald-100"
                 @change="form.file = $event.target.files[0]" />
          <p v-if="editingId && form.file_path && !form.file" class="mt-1.5 text-xs text-slate-400">
            File saat ini: {{ form.file_path }}
          </p>
        </div>
        <div class="sm:col-span-2">
          <UiSwitch v-model="form.is_active" label="Tugas aktif" hint="Siswa hanya bisa mengumpulkan saat tugas aktif" />
        </div>
      </form>

      <template #footer>
        <UiButton variant="secondary" @click="formOpen = false">Batal</UiButton>
        <UiButton :loading="saving" @click="submit">{{ editingId ? 'Simpan perubahan' : 'Simpan tugas' }}</UiButton>
      </template>
    </UiModal>

    <!-- Modal detail -->
    <UiModal v-model="detailOpen" :title="detail?.judul" subtitle="Detail tugas">
      <div v-if="detail" class="space-y-4">
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div><p class="text-slate-400">Kelas</p><p class="mt-0.5 font-medium">{{ kelasName(detail.kelas_id) }}</p></div>
          <div><p class="text-slate-400">Mapel</p><p class="mt-0.5 font-medium">{{ mapelName(detail.mapel_id) }}</p></div>
          <div><p class="text-slate-400">Tenggat</p><p class="mt-0.5 font-medium">{{ formatTanggal(detail.deadline) }}</p></div>
          <div><p class="text-slate-400">Nilai maksimal</p><p class="mt-0.5 font-medium">{{ detail.nilai_maksimal }}</p></div>
        </div>
        <div>
          <p class="text-sm text-slate-400">Deskripsi</p>
          <p class="mt-1 text-sm text-slate-700">{{ detail.deskripsi || '—' }}</p>
        </div>
        <div>
          <p class="text-sm text-slate-400">Instruksi pengumpulan</p>
          <p class="mt-1 text-sm text-slate-700">{{ detail.instruksi || '—' }}</p>
        </div>
        <UiProgress :value="detail.jumlah_pengumpulan ?? 0" :max="40"
                    :label="`${detail.jumlah_pengumpulan ?? 0} siswa sudah mengumpulkan`" />
      </div>
      <template #footer>
        <UiButton variant="secondary" @click="detailOpen = false">Tutup</UiButton>
        <UiButton @click="detailOpen = false; openEdit(detail)">Ubah tugas</UiButton>
      </template>
    </UiModal>

    <!-- Modal hapus -->
    <UiModal v-model="deleteOpen" size="sm" title="Hapus tugas?">
      <p class="text-sm text-slate-600">
        Tugas <span class="font-semibold text-slate-800">{{ deleteTarget?.judul }}</span> akan dihapus permanen,
        termasuk riwayat pengumpulan yang terkait.
      </p>
      <template #footer>
        <UiButton variant="secondary" @click="deleteOpen = false">Batal</UiButton>
        <UiButton variant="danger" :loading="deleting" @click="confirmDelete">Hapus tugas</UiButton>
      </template>
    </UiModal>
  </DashboardLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'
import UiCard from '@/components/ui/UiCard.vue'
import UiButton from '@/components/ui/UiButton.vue'
import UiInput from '@/components/ui/UiInput.vue'
import UiSelect from '@/components/ui/UiSelect.vue'
import UiTextarea from '@/components/ui/UiTextarea.vue'
import UiSwitch from '@/components/ui/UiSwitch.vue'
import UiModal from '@/components/ui/UiModal.vue'
import UiTable from '@/components/ui/UiTable.vue'
import UiBadge from '@/components/ui/UiBadge.vue'
import UiAlert from '@/components/ui/UiAlert.vue'
import UiTabs from '@/components/ui/UiTabs.vue'
import UiPagination from '@/components/ui/UiPagination.vue'
import UiProgress from '@/components/ui/UiProgress.vue'
import UiDropdown from '@/components/ui/UiDropdown.vue'
import UiDropdownItem from '@/components/ui/UiDropdownItem.vue'
import { useTugas, KELAS_REF, MAPEL_REF, MATERI_REF } from '@/composables/useTugas'

const navigation = [
  { label: 'Dashboard', icon: 'fas fa-chart-pie', to: '/dashboard/guru' },
  { label: 'Data Kelas', icon: 'fas fa-users', to: '/dashboard/guru/kelas' },
  { label: 'Tugas', icon: 'fas fa-book-open', to: '/dashboard/guru/tugas' },
  { label: 'Nilai', icon: 'fas fa-chart-bar', to: '/dashboard/guru/nilai' },
  { label: 'Ujian PPDB & Soal', icon: 'fas fa-file-circle-check', to: '/dashboard/guru/ujian-ppdb' },
]

const { items, loading, error, fetchAll, store, update, destroy, toggleActive } = useTugas()

/* ---------- opsi referensi ---------- */
const kelasOptions = KELAS_REF.map((k) => ({ value: k.id, label: k.name }))
const mapelOptions = MAPEL_REF.map((m) => ({ value: m.id, label: m.name }))
const kelasName = (id) => KELAS_REF.find((k) => k.id === Number(id))?.name ?? '—'
const mapelName = (id) => MAPEL_REF.find((m) => m.id === Number(id))?.name ?? '—'

const materiOptionsForMapel = computed(() =>
  MATERI_REF.filter((m) => m.mapel_id === Number(form.mapel_id)).map((m) => ({ value: m.id, label: m.name })),
)

/* ---------- status tenggat ---------- */
const now = new Date()
const statusInfo = (row) => {
  if (!row.is_active) return { label: 'Nonaktif', tone: 'neutral' }
  const deadline = new Date(row.deadline)
  const diffHari = (deadline - now) / (1000 * 60 * 60 * 24)
  if (diffHari < 0) return { label: 'Lewat tenggat', tone: 'danger' }
  if (diffHari <= 3) return { label: 'Segera tenggat', tone: 'warning' }
  return { label: 'Berlangsung', tone: 'success' }
}
const statusLabel = (row) => statusInfo(row).label
const statusTone = (row) => statusInfo(row).tone
const formatTanggal = (val) =>
  val ? new Date(val).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—'

/* ---------- filter, sort, paginasi ---------- */
const search = ref('')
const kelasFilter = ref('')
const mapelFilter = ref('')
const activeTab = ref('semua')
const sortKey = ref('deadline')
const sortDir = ref('asc')
const page = ref(1)
const perPage = ref(10)

const tabs = computed(() => [
  { value: 'semua', label: 'Semua tugas', count: items.value.length },
  { value: 'aktif', label: 'Aktif', count: items.value.filter((t) => t.is_active).length },
  { value: 'segera', label: 'Segera tenggat', count: items.value.filter((t) => statusLabel(t) === 'Segera tenggat').length },
  { value: 'lewat', label: 'Lewat tenggat', count: items.value.filter((t) => statusLabel(t) === 'Lewat tenggat').length },
])

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  return items.value.filter((t) => {
    const cocokTab =
      activeTab.value === 'semua' ||
      (activeTab.value === 'aktif' && t.is_active) ||
      (activeTab.value === 'segera' && statusLabel(t) === 'Segera tenggat') ||
      (activeTab.value === 'lewat' && statusLabel(t) === 'Lewat tenggat')
    const cocokKelas = !kelasFilter.value || Number(t.kelas_id) === Number(kelasFilter.value)
    const cocokMapel = !mapelFilter.value || Number(t.mapel_id) === Number(mapelFilter.value)
    const cocokCari = !q || t.judul.toLowerCase().includes(q)
    return cocokTab && cocokKelas && cocokMapel && cocokCari
  })
})

const sorted = computed(() =>
  [...filtered.value].sort((a, b) => {
    const dir = sortDir.value === 'asc' ? 1 : -1
    const x = a[sortKey.value] ?? ''
    const y = b[sortKey.value] ?? ''
    return typeof x === 'number' ? (x - y) * dir : String(x).localeCompare(String(y), 'id') * dir
  }),
)

const paged = computed(() => sorted.value.slice((page.value - 1) * perPage.value, page.value * perPage.value))

const toggleSort = (key) => {
  if (sortKey.value === key) sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  else { sortKey.value = key; sortDir.value = 'asc' }
}
watch([search, kelasFilter, mapelFilter, activeTab], () => (page.value = 1))

const columns = [
  { key: 'judul', label: 'Tugas', sortable: true },
  { key: 'kelas_id', label: 'Kelas', sortable: true },
  { key: 'deadline', label: 'Tenggat', sortable: true },
  { key: 'pengumpulan', label: 'Pengumpulan' },
  { key: 'nilai_maksimal', label: 'Nilai maks.', sortable: true },
  { key: 'is_active', label: 'Status' },
  { key: 'aksi', label: 'Aksi', class: 'text-right' },
]

/* ---------- ringkasan ---------- */
const aktifCount = computed(() => items.value.filter((t) => t.is_active).length)
const segeraCount = computed(() => items.value.filter((t) => statusLabel(t) === 'Segera tenggat').length)
const rataProgress = computed(() => {
  if (!items.value.length) return 0
  const totalDinilai = items.value.reduce((n, t) => n + Math.min(t.jumlah_pengumpulan ?? 0, 40), 0)
  return (totalDinilai / (items.value.length * 40)) * 100
})

/* ---------- form tambah / ubah ---------- */
const formOpen = ref(false)
const saving = ref(false)
const editingId = ref(null)
const form = reactive({
  kelas_id: '', mapel_id: '', materi_id: '', judul: '', deskripsi: '', instruksi: '',
  deadline: '', nilai_maksimal: 100, is_active: true, file: null, file_path: null,
})
const errors = reactive({})

const resetForm = () => {
  Object.assign(form, {
    kelas_id: '', mapel_id: '', materi_id: '', judul: '', deskripsi: '', instruksi: '',
    deadline: '', nilai_maksimal: 100, is_active: true, file: null, file_path: null,
  })
  Object.keys(errors).forEach((k) => delete errors[k])
}

const openCreate = () => { editingId.value = null; resetForm(); formOpen.value = true }
const openEdit = (row) => {
  editingId.value = row.id
  resetForm()
  Object.assign(form, { ...row, deadline: row.deadline?.slice(0, 16), file: null })
  formOpen.value = true
}

const validate = () => {
  Object.keys(errors).forEach((k) => delete errors[k])
  if (!form.kelas_id) errors.kelas_id = 'Pilih kelas tujuan.'
  if (!form.mapel_id) errors.mapel_id = 'Pilih mata pelajaran.'
  if (!form.judul.trim()) errors.judul = 'Judul tugas wajib diisi.'
  if (!form.deadline) errors.deadline = 'Tenggat waktu wajib diisi.'
  if (Number(form.nilai_maksimal) < 1) errors.nilai_maksimal = 'Nilai maksimal minimal 1.'
  return Object.keys(errors).length === 0
}

const submit = async () => {
  if (!validate()) return
  saving.value = true
  try {
    const payload = {
      kelas_id: Number(form.kelas_id),
      mapel_id: Number(form.mapel_id),
      materi_id: form.materi_id ? Number(form.materi_id) : null,
      judul: form.judul.trim(),
      deskripsi: form.deskripsi.trim(),
      instruksi: form.instruksi.trim(),
      deadline: form.deadline,
      nilai_maksimal: Number(form.nilai_maksimal),
      is_active: form.is_active,
      file: form.file,
      tanggal_dibuat: editingId.value ? undefined : new Date().toISOString().slice(0, 10),
    }
    if (editingId.value) {
      await update(editingId.value, payload)
      notify('success', `Tugas "${payload.judul}" diperbarui.`)
    } else {
      await store(payload)
      notify('success', `Tugas "${payload.judul}" dibuat.`)
    }
    formOpen.value = false
  } catch (e) {
    notify('danger', e.response?.data?.message ?? 'Tugas gagal disimpan. Coba lagi.')
  } finally {
    saving.value = false
  }
}

/* ---------- detail & hapus ---------- */
const detail = ref(null)
const detailOpen = ref(false)
const openDetail = (row) => { detail.value = row; detailOpen.value = true }

const deleteTarget = ref(null)
const deleteOpen = ref(false)
const deleting = ref(false)
const askDelete = (row) => { deleteTarget.value = row; deleteOpen.value = true }
const confirmDelete = async () => {
  deleting.value = true
  try {
    await destroy(deleteTarget.value.id)
    notify('success', `Tugas "${deleteTarget.value.judul}" dihapus.`)
    deleteOpen.value = false
    if (page.value > 1 && !paged.value.length) page.value--
  } catch (e) {
    notify('danger', 'Tugas gagal dihapus. Coba lagi.')
  } finally {
    deleting.value = false
  }
}

/* ---------- notifikasi ---------- */
const alert = reactive({ show: false, type: 'success', message: '' })
let alertTimer
const notify = (type, message) => {
  Object.assign(alert, { show: true, type, message })
  clearTimeout(alertTimer)
  alertTimer = setTimeout(() => (alert.show = false), 4000)
}

onMounted(fetchAll)
</script>
