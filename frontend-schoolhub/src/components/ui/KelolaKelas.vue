<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import AppSidebar from '../components/layout/AppSidebar.vue'
import AppNavbar from '../components/layout/AppNavbar.vue'
import UiCard from '../components/ui/UiCard.vue'
import UiButton from '../components/ui/UiButton.vue'
import UiInput from '../components/ui/UiInput.vue'
import UiSelect from '../components/ui/UiSelect.vue'
import UiModal from '../components/ui/UiModal.vue'
import UiTable from '../components/ui/UiTable.vue'
import UiBadge from '../components/ui/UiBadge.vue'
import UiAlert from '../components/ui/UiAlert.vue'
import UiTabs from '../components/ui/UiTabs.vue'
import UiPagination from '../components/ui/UiPagination.vue'
import UiProgress from '../components/ui/UiProgress.vue'
import UiDropdown from '../components/ui/UiDropdown.vue'
import UiDropdownItem from '../components/ui/UiDropdownItem.vue'
import { useKelas } from '../composables/useKelas'

const { items, loading, error, fetchAll, store, update, destroy } = useKelas()

/* ---------- opsi form ---------- */
const tingkatOptions = [
  { value: 'X', label: 'X' },
  { value: 'XI', label: 'XI' },
  { value: 'XII', label: 'XII' },
]
const jurusanOptions = [
  { value: 'RPL', label: 'RPL — Rekayasa Perangkat Lunak' },
  { value: 'TKR', label: 'TKR — Teknik Kendaraan Ringan' },
  { value: 'TSM', label: 'TSM — Teknik Sepeda Motor' },
]
const jurusanFilterOptions = [
  { value: 'RPL', label: 'RPL' },
  { value: 'TKR', label: 'TKR' },
  { value: 'TSM', label: 'TSM' },
]
const jurusanTone = { RPL: 'brand', TKR: 'info', TSM: 'warning' }

/* ---------- filter, sort, paginasi ---------- */
const search = ref('')
const jurusanFilter = ref('')
const activeTab = ref('semua')
const sortKey = ref('name')
const sortDir = ref('asc')
const page = ref(1)
const perPage = ref(10)

const tabs = computed(() => [
  { value: 'semua', label: 'Semua kelas', count: items.value.length },
  ...tingkatOptions.map((t) => ({
    value: t.value,
    label: `Kelas ${t.value}`,
    count: items.value.filter((k) => k.kelas === t.value).length,
  })),
])

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  return items.value.filter((k) => {
    const cocokTab = activeTab.value === 'semua' || k.kelas === activeTab.value
    const cocokJurusan = !jurusanFilter.value || k.jurusan === jurusanFilter.value
    const cocokCari =
      !q ||
      k.name.toLowerCase().includes(q) ||
      (k.wali_kelas ?? '').toLowerCase().includes(q) ||
      String(k.angkatan).includes(q)
    return cocokTab && cocokJurusan && cocokCari
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
watch([search, jurusanFilter, activeTab], () => (page.value = 1))

/* ---------- ringkasan ---------- */
const totalSiswa = computed(() => items.value.reduce((n, k) => n + (k.jumlah_siswa ?? 0), 0))
const totalKapasitas = computed(() => items.value.reduce((n, k) => n + (k.kapasitas ?? 0), 0))
const rataIsi = computed(() => (totalKapasitas.value ? (totalSiswa.value / totalKapasitas.value) * 100 : 0))

/* ---------- form tambah / ubah ---------- */
const formOpen = ref(false)
const saving = ref(false)
const editingId = ref(null)
const form = reactive({ name: '', kelas: '', jurusan: '', angkatan: '', wali_kelas: '', kapasitas: 36 })
const errors = reactive({})

const columns = [
  { key: 'name', label: 'Nama kelas', sortable: true },
  { key: 'kelas', label: 'Tingkat', sortable: true },
  { key: 'jurusan', label: 'Jurusan', sortable: true },
  { key: 'angkatan', label: 'Angkatan', sortable: true },
  { key: 'wali_kelas', label: 'Wali kelas' },
  { key: 'daya_tampung', label: 'Daya tampung' },
  { key: 'aksi', label: 'Aksi', class: 'text-right' },
]

const resetForm = () => {
  Object.assign(form, { name: '', kelas: '', jurusan: '', angkatan: new Date().getFullYear(), wali_kelas: '', kapasitas: 36 })
  Object.keys(errors).forEach((k) => delete errors[k])
}

const openCreate = () => { editingId.value = null; resetForm(); formOpen.value = true }
const openEdit = (row) => {
  editingId.value = row.id
  resetForm()
  Object.assign(form, { ...row })
  formOpen.value = true
}

// Nama kelas terbentuk otomatis dari tingkat + jurusan, tetap bisa diubah manual.
watch([() => form.kelas, () => form.jurusan], ([tingkat, jurusan]) => {
  if (editingId.value || !tingkat || !jurusan) return
  const urutan = items.value.filter((k) => k.kelas === tingkat && k.jurusan === jurusan).length + 1
  form.name = `${tingkat} ${jurusan} ${urutan}`
})

const validate = () => {
  Object.keys(errors).forEach((k) => delete errors[k])
  if (!form.name.trim()) errors.name = 'Nama kelas wajib diisi.'
  else if (items.value.some((k) => k.name.toLowerCase() === form.name.trim().toLowerCase() && k.id !== editingId.value))
    errors.name = 'Nama kelas ini sudah dipakai.'
  if (!form.kelas) errors.kelas = 'Pilih tingkat kelas.'
  if (!form.jurusan) errors.jurusan = 'Pilih jurusan.'
  if (!form.angkatan) errors.angkatan = 'Angkatan wajib diisi.'
  else if (Number(form.angkatan) < 2000 || Number(form.angkatan) > 2100) errors.angkatan = 'Gunakan tahun antara 2000–2100.'
  if (Number(form.kapasitas) < 1) errors.kapasitas = 'Daya tampung minimal 1 siswa.'
  return Object.keys(errors).length === 0
}

const submit = async () => {
  if (!validate()) return
  saving.value = true
  try {
    const payload = {
      name: form.name.trim(),
      kelas: form.kelas,
      jurusan: form.jurusan,
      angkatan: Number(form.angkatan),
      wali_kelas: form.wali_kelas.trim(),
      kapasitas: Number(form.kapasitas),
    }
    if (editingId.value) {
      await update(editingId.value, payload)
      notify('success', `Kelas ${payload.name} diperbarui.`)
    } else {
      await store(payload)
      notify('success', `Kelas ${payload.name} ditambahkan.`)
    }
    formOpen.value = false
  } catch (e) {
    notify('danger', e.response?.data?.message ?? 'Data kelas gagal disimpan. Coba lagi.')
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
    notify('success', `Kelas ${deleteTarget.value.name} dihapus.`)
    deleteOpen.value = false
    if (page.value > 1 && !paged.value.length) page.value--
  } catch (e) {
    notify('danger', 'Kelas gagal dihapus. Coba lagi.')
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

const exportCsv = () => {
  const head = ['Nama kelas', 'Tingkat', 'Jurusan', 'Angkatan', 'Wali kelas', 'Jumlah siswa']
  const body = sorted.value.map((k) => [k.name, k.kelas, k.jurusan, k.angkatan, k.wali_kelas, k.jumlah_siswa])
  const csv = [head, ...body].map((r) => r.join(';')).join('\n')
  const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }))
  const a = document.createElement('a')
  a.href = url
  a.download = 'data-kelas.csv'
  a.click()
  URL.revokeObjectURL(url)
  notify('success', 'Data kelas diunduh sebagai CSV.')
}

const sidebarOpen = ref(false)
onMounted(fetchAll)
</script>

<template>
  <div class="flex min-h-screen bg-slate-50 text-slate-800">
    <!-- Sidebar -->
    <div class="hidden lg:block"><AppSidebar active="kelas" /></div>
    <Transition enter-active-class="transition duration-200" enter-from-class="-translate-x-full"
                leave-active-class="transition duration-150" leave-to-class="-translate-x-full">
      <div v-if="sidebarOpen" class="fixed inset-0 z-40 flex lg:hidden">
        <AppSidebar active="kelas" />
        <div class="flex-1 bg-slate-900/50" @click="sidebarOpen = false" />
      </div>
    </Transition>

    <div class="flex min-w-0 flex-1 flex-col">
      <AppNavbar
        title="Kelola Kelas"
        :breadcrumb="[{ label: 'SMA Harapan Bangsa', to: '/' }, { label: 'Admin', to: '/dashboard/admin' }, { label: 'Kelas' }]"
        @toggle-sidebar="sidebarOpen = true"
      />

      <main class="mx-auto w-full max-w-7xl space-y-6 px-6 py-6">
        <!-- Alert -->
        <Transition enter-active-class="transition duration-150" enter-from-class="opacity-0 -translate-y-1"
                    leave-active-class="transition duration-100" leave-to-class="opacity-0">
          <UiAlert v-if="alert.show" :type="alert.type" @close="alert.show = false">{{ alert.message }}</UiAlert>
        </Transition>
        <UiAlert v-if="error" type="danger" title="Gagal memuat data" :dismissible="false">{{ error }}</UiAlert>

        <!-- Ringkasan -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <UiCard>
            <p class="text-sm text-slate-400">Total kelas</p>
            <p class="mt-1 text-3xl font-semibold text-slate-800">{{ items.length }}</p>
            <p class="mt-1 text-xs text-slate-400">Tahun ajaran aktif</p>
          </UiCard>
          <UiCard>
            <p class="text-sm text-slate-400">Siswa terdaftar</p>
            <p class="mt-1 text-3xl font-semibold text-slate-800">{{ totalSiswa }}</p>
            <p class="mt-1 text-xs text-slate-400">Dari {{ totalKapasitas }} kursi tersedia</p>
          </UiCard>
          <UiCard>
            <p class="text-sm text-slate-400">Jurusan aktif</p>
            <p class="mt-1 text-3xl font-semibold text-slate-800">{{ jurusanOptions.length }}</p>
            <div class="mt-2 flex gap-1.5">
              <UiBadge v-for="j in jurusanFilterOptions" :key="j.value" :variant="jurusanTone[j.value]">{{ j.label }}</UiBadge>
            </div>
          </UiCard>
          <UiCard>
            <p class="text-sm text-slate-400">Keterisian kelas</p>
            <p class="mt-1 mb-3 text-3xl font-semibold text-slate-800">{{ Math.round(rataIsi) }}%</p>
            <UiProgress :value="rataIsi" />
          </UiCard>
        </div>

        <!-- Tabel -->
        <UiCard title="Manajemen Kelas" subtitle="Atur rombongan belajar, jurusan, dan wali kelas" :padded="false">
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
              <UiDropdownItem @click="exportCsv">Unduh CSV</UiDropdownItem>
              <UiDropdownItem @click="fetchAll">Muat ulang data</UiDropdownItem>
            </UiDropdown>
            <UiButton @click="openCreate">
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                <path d="M10 5v10M5 10h10" />
              </svg>
              Tambah kelas
            </UiButton>
          </template>

          <div class="space-y-4 px-6 pb-5">
            <UiTabs v-model="activeTab" :tabs="tabs" />
            <div class="flex flex-col gap-3 sm:flex-row">
              <div class="sm:max-w-xs sm:flex-1">
                <UiInput v-model="search" placeholder="Cari kelas atau wali kelas...">
                  <template #icon>
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
                      <circle cx="9" cy="9" r="5" /><path d="m13 13 4 4" stroke-linecap="round" />
                    </svg>
                  </template>
                </UiInput>
              </div>
              <div class="sm:w-48">
                <UiSelect v-model="jurusanFilter" :options="jurusanFilterOptions" placeholder="Semua jurusan" />
              </div>
            </div>
          </div>

          <UiTable :columns="columns" :rows="paged" :sort-key="sortKey" :sort-dir="sortDir" :loading="loading" @sort="toggleSort">
            <template #empty>
              <p class="font-medium text-slate-600">Tidak ada kelas yang cocok</p>
              <p class="mt-1 text-sm text-slate-400">Ubah kata kunci pencarian, atau tambah kelas baru.</p>
              <UiButton class="mt-4" variant="soft" size="sm" @click="openCreate">Tambah kelas</UiButton>
            </template>

            <template #row="{ row }">
              <td class="px-6 py-4">
                <p class="font-medium text-slate-800">{{ row.name }}</p>
                <p class="text-xs text-slate-400">ID #{{ row.id }}</p>
              </td>
              <td class="px-6 py-4 text-slate-600">{{ row.kelas }}</td>
              <td class="px-6 py-4"><UiBadge :variant="jurusanTone[row.jurusan]">{{ row.jurusan }}</UiBadge></td>
              <td class="px-6 py-4 text-slate-600">{{ row.angkatan }}</td>
              <td class="px-6 py-4 text-slate-600">{{ row.wali_kelas || '—' }}</td>
              <td class="px-6 py-4">
                <div class="w-36">
                  <p class="mb-1.5 text-xs text-slate-500">{{ row.jumlah_siswa ?? 0 }} / {{ row.kapasitas ?? 0 }} siswa</p>
                  <UiProgress :value="row.jumlah_siswa ?? 0" :max="row.kapasitas || 1" />
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-end gap-1">
                  <UiButton variant="ghost" size="icon" title="Lihat detail" @click="openDetail(row)">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                      <path d="M2 10s3-5 8-5 8 5 8 5-3 5-8 5-8-5-8-5Z" /><circle cx="10" cy="10" r="2" />
                    </svg>
                  </UiButton>
                  <UiButton variant="ghost" size="icon" title="Ubah kelas" @click="openEdit(row)">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round">
                      <path d="M14 3l3 3-9 9H5v-3z" />
                    </svg>
                  </UiButton>
                  <UiButton variant="ghost" size="icon" title="Hapus kelas"
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
      </main>
    </div>

    <!-- Modal tambah / ubah -->
    <UiModal v-model="formOpen"
             :title="editingId ? 'Ubah kelas' : 'Tambah kelas'"
             subtitle="Nama kelas terisi otomatis dari tingkat dan jurusan.">
      <form class="grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
        <UiSelect v-model="form.kelas" label="Tingkat" :options="tingkatOptions" :error="errors.kelas" required placeholder="Pilih tingkat" />
        <UiSelect v-model="form.jurusan" label="Jurusan" :options="jurusanOptions" :error="errors.jurusan" required placeholder="Pilih jurusan" />
        <div class="sm:col-span-2">
          <UiInput v-model="form.name" label="Nama kelas" placeholder="Contoh: XI RPL 1" :error="errors.name" required />
        </div>
        <UiInput v-model="form.angkatan" label="Angkatan" type="number" placeholder="2026" :error="errors.angkatan" required />
        <UiInput v-model="form.kapasitas" label="Daya tampung" type="number" hint="Jumlah kursi maksimal" :error="errors.kapasitas" />
        <div class="sm:col-span-2">
          <UiInput v-model="form.wali_kelas" label="Wali kelas" placeholder="Contoh: Ahmad Fauzi, S.Pd" />
        </div>
      </form>

      <template #footer>
        <UiButton variant="secondary" @click="formOpen = false">Batal</UiButton>
        <UiButton :loading="saving" @click="submit">{{ editingId ? 'Simpan perubahan' : 'Simpan kelas' }}</UiButton>
      </template>
    </UiModal>

    <!-- Modal detail -->
    <UiModal v-model="detailOpen" :title="detail?.name" subtitle="Detail rombongan belajar">
      <div v-if="detail" class="space-y-5">
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div><p class="text-slate-400">Tingkat</p><p class="mt-0.5 font-medium">{{ detail.kelas }}</p></div>
          <div><p class="text-slate-400">Jurusan</p><UiBadge class="mt-1" :variant="jurusanTone[detail.jurusan]">{{ detail.jurusan }}</UiBadge></div>
          <div><p class="text-slate-400">Angkatan</p><p class="mt-0.5 font-medium">{{ detail.angkatan }}</p></div>
          <div><p class="text-slate-400">Wali kelas</p><p class="mt-0.5 font-medium">{{ detail.wali_kelas || '—' }}</p></div>
        </div>
        <UiProgress :value="detail.jumlah_siswa ?? 0" :max="detail.kapasitas || 1"
                    :label="`Keterisian ${detail.jumlah_siswa ?? 0} dari ${detail.kapasitas ?? 0} kursi`" />
      </div>
      <template #footer>
        <UiButton variant="secondary" @click="detailOpen = false">Tutup</UiButton>
        <UiButton @click="detailOpen = false; openEdit(detail)">Ubah kelas</UiButton>
      </template>
    </UiModal>

    <!-- Modal hapus -->
    <UiModal v-model="deleteOpen" size="sm" title="Hapus kelas?">
      <p class="text-sm text-slate-600">
        Kelas <span class="font-semibold text-slate-800">{{ deleteTarget?.name }}</span> akan dihapus permanen.
        Siswa di dalamnya perlu dipindahkan ke kelas lain terlebih dahulu.
      </p>
      <template #footer>
        <UiButton variant="secondary" @click="deleteOpen = false">Batal</UiButton>
        <UiButton variant="danger" :loading="deleting" @click="confirmDelete">Hapus kelas</UiButton>
      </template>
    </UiModal>
  </div>
</template>
