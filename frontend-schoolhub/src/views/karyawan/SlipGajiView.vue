<template>
  <DashboardLayout title="Kelola Slip Gaji" role-label="Karyawan" :navigation="navigation">
    <UiAlert v-if="alert.show" :type="alert.type" class="mb-4" @close="alert.show = false">
      {{ alert.message }}
    </UiAlert>
    <UiAlert v-if="error" type="danger" title="Gagal memuat data" class="mb-4" :dismissible="false">
      {{ error }}
    </UiAlert>

    <!-- Ringkasan -->
    <div class="grid gap-4 sm:grid-cols-3 mb-6">
      <UiCard>
        <p class="text-sm text-slate-400">Total slip gaji</p>
        <p class="mt-1 text-3xl font-semibold text-slate-800">{{ pagination.total || 0 }}</p>
      </UiCard>
      <UiCard>
        <p class="text-sm text-slate-400">Menunggu persetujuan</p>
        <p class="mt-1 text-3xl font-semibold text-amber-600">{{ draftCount }}</p>
      </UiCard>
      <UiCard>
        <p class="text-sm text-slate-400">Sudah dibayar</p>
        <p class="mt-1 text-3xl font-semibold text-emerald-600">{{ paidCount }}</p>
      </UiCard>
    </div>

    <UiCard title="Daftar Slip Gaji" subtitle="Kelola slip gaji karyawan sekolah" :padded="false">
      <template #actions>
        <UiButton @click="showCreateModal = true">
          <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
            <path d="M10 5v10M5 10h10" />
          </svg>
          Buat slip gaji
        </UiButton>
      </template>

      <div class="flex flex-col gap-3 px-6 pb-5 pt-1 sm:flex-row sm:items-end">
        <div class="sm:w-48">
          <UiSelect v-model="filters.status" label="Status" :options="statusOptions" placeholder="Semua status" @update:modelValue="loadSlipGaji()" />
        </div>
        <div class="sm:w-48">
          <UiInput v-model="filters.periode" label="Periode" type="month" @update:modelValue="loadSlipGaji()" />
        </div>
        <UiButton variant="secondary" @click="resetFilters">Reset filter</UiButton>
      </div>

      <UiTable :columns="columns" :rows="slipGaji" :loading="loading">
        <template #empty>
          <p class="font-medium text-slate-600">Tidak ada slip gaji ditemukan</p>
          <p class="mt-1 text-sm text-slate-400">Coba ubah filter, atau buat slip gaji baru.</p>
        </template>

        <template #row="{ row }">
          <td class="px-6 py-4">
            <p class="font-medium text-slate-800">{{ row.karyawan?.nama_lengkap_karyawan }}</p>
            <p class="text-xs text-slate-400">NIP: {{ row.karyawan?.nip }}</p>
          </td>
          <td class="px-6 py-4 text-slate-600">{{ row.karyawan?.bagian || '—' }}</td>
          <td class="px-6 py-4 text-slate-600">{{ formatPeriode(row.periode) }}</td>
          <td class="px-6 py-4 text-slate-600">{{ formatCurrency(row.gaji_pokok) }}</td>
          <td class="px-6 py-4 text-slate-600">{{ formatCurrency(row.tunjangan) }}</td>
          <td class="px-6 py-4 text-slate-600">{{ formatCurrency(row.potongan) }}</td>
          <td class="px-6 py-4 font-medium text-slate-800">{{ formatCurrency(row.total) }}</td>
          <td class="px-6 py-4"><UiBadge :variant="statusTone(row.status)">{{ statusLabel(row.status) }}</UiBadge></td>
          <td class="px-6 py-4">
            <div class="flex items-center justify-end gap-1">
              <UiButton v-if="row.status === 'DRAFT' && isAdmin" variant="ghost" size="icon" title="Setujui"
                        class="text-emerald-600 hover:bg-emerald-50" @click="approveSlipGaji(row)">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                  <path d="m4 10 4 4 8-8" />
                </svg>
              </UiButton>
              <UiButton v-if="row.status === 'APPROVED'" variant="ghost" size="icon" title="Tandai dibayar"
                        class="text-sky-600 hover:bg-sky-50" @click="markAsPaid(row)">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
                  <rect x="3" y="6" width="14" height="9" rx="1.5" /><circle cx="10" cy="10.5" r="2" />
                </svg>
              </UiButton>
              <UiButton v-if="row.status === 'DRAFT'" variant="ghost" size="icon" title="Ubah" @click="editSlipGaji(row)">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round">
                  <path d="M14 3l3 3-9 9H5v-3z" />
                </svg>
              </UiButton>
              <UiButton v-if="row.status === 'DRAFT'" variant="ghost" size="icon" title="Hapus"
                        class="text-rose-500 hover:bg-rose-50 hover:text-rose-600" @click="deleteSlipGaji(row)">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                  <path d="M4 6h12M8 6V4h4v2M6 6l1 10h6l1-10" />
                </svg>
              </UiButton>
              <UiButton v-if="row.file_path" variant="ghost" size="icon" title="Unduh PDF" @click="downloadPdf(row)">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M10 3v10m0 0-3-3m3 3 3-3M4 15v1a2 2 0 002 2h8a2 2 0 002-2v-1" />
                </svg>
              </UiButton>
            </div>
          </td>
        </template>
      </UiTable>

      <div v-if="pagination.last_page > 1" class="flex items-center justify-between border-t border-slate-100 px-6 py-4">
        <p class="text-sm text-slate-500">Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}</p>
        <div class="flex gap-2">
          <UiButton variant="secondary" size="sm" :disabled="pagination.current_page === 1" @click="changePage(pagination.current_page - 1)">Sebelumnya</UiButton>
          <UiButton variant="secondary" size="sm" :disabled="pagination.current_page === pagination.last_page" @click="changePage(pagination.current_page + 1)">Berikutnya</UiButton>
        </div>
      </div>
    </UiCard>

    <!-- Modal tambah / ubah -->
    <UiModal v-model="modalOpen" :title="showEditModal ? 'Ubah slip gaji' : 'Buat slip gaji'">
      <form class="grid gap-4 sm:grid-cols-2" @submit.prevent="submitForm">
        <div class="sm:col-span-2">
          <UiSelect v-model="form.karyawan_id" label="Karyawan" :options="karyawanOptions"
                    :disabled="showEditModal" placeholder="Pilih karyawan" required />
        </div>
        <UiInput v-model="form.periode" label="Periode" type="month" required />
        <UiInput v-model="form.gaji_pokok" label="Gaji pokok (Rp)" type="number" min="0" step="1000" required />
        <UiInput v-model="form.tunjangan" label="Tunjangan (Rp)" type="number" min="0" step="1000" />
        <UiInput v-model="form.potongan" label="Potongan (Rp)" type="number" min="0" step="1000" />
        <div class="sm:col-span-2">
          <UiTextarea v-model="form.keterangan" label="Keterangan" :rows="3" placeholder="Catatan tambahan..." />
        </div>
        <div class="sm:col-span-2 flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
          <span class="text-sm font-medium text-slate-600">Total gaji</span>
          <span class="text-lg font-semibold text-emerald-600">{{ formatCurrency(calculateTotal()) }}</span>
        </div>
      </form>
      <template #footer>
        <UiButton variant="secondary" @click="closeModal">Batal</UiButton>
        <UiButton :loading="submitting" @click="submitForm">Simpan</UiButton>
      </template>
    </UiModal>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'
import UiCard from '@/components/ui/UiCard.vue'
import UiButton from '@/components/ui/UiButton.vue'
import UiInput from '@/components/ui/UiInput.vue'
import UiSelect from '@/components/ui/UiSelect.vue'
import UiTextarea from '@/components/ui/UiTextarea.vue'
import UiModal from '@/components/ui/UiModal.vue'
import UiTable from '@/components/ui/UiTable.vue'
import UiBadge from '@/components/ui/UiBadge.vue'
import UiAlert from '@/components/ui/UiAlert.vue'
import keuanganService from '../../utils/keuanganService'
import api from '../../utils/api'

// Menu sama persis seperti dashboard_karyawan.vue, supaya sidebar
// konsisten di semua halaman portal karyawan.
const navigation = [
  { label: 'Dashboard', icon: 'fas fa-chart-pie', to: '/dashboard/karyawan' },
  { label: 'Data Siswa', icon: 'fas fa-user-graduate', to: '/dashboard/karyawan/data-siswa' },
  { label: 'Keuangan', icon: 'fas fa-wallet', to: '/dashboard/karyawan/keuangan' },
  { label: 'Tagihan SPP', icon: 'fas fa-file-invoice', to: '/dashboard/karyawan/keuangan/tagihan' },
  { label: 'Slip Gaji', icon: 'fas fa-money-check', to: '/dashboard/karyawan/keuangan/slip-gaji' },
]

const columns = [
  { key: 'karyawan', label: 'Karyawan' },
  { key: 'bagian', label: 'Bagian' },
  { key: 'periode', label: 'Periode' },
  { key: 'gaji_pokok', label: 'Gaji pokok' },
  { key: 'tunjangan', label: 'Tunjangan' },
  { key: 'potongan', label: 'Potongan' },
  { key: 'total', label: 'Total' },
  { key: 'status', label: 'Status' },
  { key: 'aksi', label: 'Aksi', class: 'text-right' },
]

const statusOptions = [
  { value: 'DRAFT', label: 'Draft' },
  { value: 'APPROVED', label: 'Disetujui' },
  { value: 'PAID', label: 'Dibayar' },
]

const loading = ref(true)
const error = ref(null)
const submitting = ref(false)
const slipGaji = ref([])
const pagination = ref({})
const karyawanList = ref([])

const user = JSON.parse(sessionStorage.getItem('user') || '{}')
const isAdmin = computed(() => user.role?.toLowerCase() === 'admin')

const karyawanOptions = computed(() =>
  karyawanList.value.map((k) => ({
    value: k.id,
    label: `${k.nama_lengkap_karyawan} (${k.nip}) - ${k.bagian}`,
  })),
)

const draftCount = computed(() => slipGaji.value.filter((s) => s.status === 'DRAFT').length)
const paidCount = computed(() => slipGaji.value.filter((s) => s.status === 'PAID').length)

const filters = ref({
  status: '',
  periode: '',
  per_page: 15,
})

const showCreateModal = ref(false)
const showEditModal = ref(false)
const modalOpen = computed({
  get: () => showCreateModal.value || showEditModal.value,
  set: (val) => { if (!val) closeModal() },
})
const editingId = ref(null)

const form = ref({
  karyawan_id: '',
  periode: '',
  gaji_pokok: 0,
  tunjangan: 0,
  potongan: 0,
  keterangan: '',
})

/* ---------- notifikasi ---------- */
const alert = ref({ show: false, type: 'success', message: '' })
let alertTimer
const notify = (type, message) => {
  alert.value = { show: true, type, message }
  clearTimeout(alertTimer)
  alertTimer = setTimeout(() => (alert.value.show = false), 4000)
}

const loadSlipGaji = async (page = 1) => {
  try {
    loading.value = true
    error.value = null

    const params = { ...filters.value, page }
    const response = await keuanganService.getSlipGaji(params)

    if (response.success) {
      slipGaji.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        total: response.data.total,
      }
    } else {
      error.value = response.message || 'Gagal memuat data slip gaji'
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal memuat data slip gaji'
  } finally {
    loading.value = false
  }
}

const loadKaryawanList = async () => {
  try {
    const response = await api.get('/karyawan')
    if (response.data.success) karyawanList.value = response.data.data
  } catch (err) {
    console.error('Error loading karyawan list:', err)
  }
}

const resetFilters = () => {
  filters.value = { status: '', periode: '', per_page: 15 }
  loadSlipGaji()
}

const changePage = (page) => loadSlipGaji(page)

const editSlipGaji = (item) => {
  editingId.value = item.id
  form.value = {
    karyawan_id: item.karyawan_id,
    periode: item.periode,
    gaji_pokok: item.gaji_pokok,
    tunjangan: item.tunjangan,
    potongan: item.potongan,
    keterangan: item.keterangan || '',
  }
  showEditModal.value = true
}

const approveSlipGaji = async (item) => {
  if (!confirm(`Setujui slip gaji ${item.karyawan?.nama_lengkap_karyawan}?`)) return
  try {
    const response = await keuanganService.approveSlipGaji(item.id)
    if (response.success) { notify('success', 'Slip gaji berhasil disetujui'); loadSlipGaji(pagination.value.current_page) }
    else notify('danger', response.message || 'Gagal menyetujui slip gaji')
  } catch (err) {
    notify('danger', err.response?.data?.message || 'Gagal menyetujui slip gaji')
  }
}

const markAsPaid = async (item) => {
  if (!confirm(`Tandai slip gaji ${item.karyawan?.nama_lengkap_karyawan} sudah dibayar?`)) return
  try {
    const response = await keuanganService.markSlipGajiAsPaid(item.id)
    if (response.success) { notify('success', 'Slip gaji ditandai sebagai dibayar'); loadSlipGaji(pagination.value.current_page) }
    else notify('danger', response.message || 'Gagal menandai slip gaji')
  } catch (err) {
    notify('danger', err.response?.data?.message || 'Gagal menandai slip gaji')
  }
}

const deleteSlipGaji = async (item) => {
  if (!confirm(`Hapus slip gaji ${item.karyawan?.nama_lengkap_karyawan}?`)) return
  try {
    const response = await keuanganService.deleteSlipGaji(item.id)
    if (response.success) { notify('success', 'Slip gaji berhasil dihapus'); loadSlipGaji(pagination.value.current_page) }
    else notify('danger', response.message || 'Gagal menghapus slip gaji')
  } catch (err) {
    notify('danger', err.response?.data?.message || 'Gagal menghapus slip gaji')
  }
}

const downloadPdf = (item) => {
  const baseUrl = import.meta.env.VITE_API_BASE_URL
  window.open(`${baseUrl}/storage/${item.file_path}`, '_blank')
}

const submitForm = async () => {
  try {
    submitting.value = true
    const payload = { ...form.value, periode: form.value.periode + '-01' }

    const response = showEditModal.value
      ? await keuanganService.updateSlipGaji(editingId.value, payload)
      : await keuanganService.createSlipGaji(payload)

    if (response.success) {
      notify('success', response.message || 'Slip gaji berhasil disimpan')
      closeModal()
      loadSlipGaji(pagination.value.current_page)
    } else {
      notify('danger', response.message || 'Gagal menyimpan slip gaji')
    }
  } catch (err) {
    notify('danger', err.response?.data?.message || 'Gagal menyimpan slip gaji')
  } finally {
    submitting.value = false
  }
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  editingId.value = null
  form.value = { karyawan_id: '', periode: '', gaji_pokok: 0, tunjangan: 0, potongan: 0, keterangan: '' }
}

const calculateTotal = () => {
  const gaji = parseFloat(form.value.gaji_pokok) || 0
  const tunjangan = parseFloat(form.value.tunjangan) || 0
  const potongan = parseFloat(form.value.potongan) || 0
  return gaji + tunjangan - potongan
}

const formatCurrency = (amount) => keuanganService.formatCurrency(amount)

const formatPeriode = (periode) => {
  if (!periode) return '—'
  return new Date(periode).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })
}

const statusLabel = (status) => keuanganService.getStatusLabel(status)
const statusTone = (status) => ({ DRAFT: 'neutral', APPROVED: 'warning', PAID: 'success' }[status] || 'neutral')

onMounted(() => {
  loadSlipGaji()
  loadKaryawanList()
})
</script>