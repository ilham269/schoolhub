<template>
  <DashboardLayout title="Kelola Siswa" role-label="Admin" :navigation="navigation">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h2 class="page-title">Manajemen Siswa</h2>
        <p class="page-subtitle">Kelola data siswa sekolah</p>
      </div>
      <div class="page-actions">
        <Button variant="secondary" icon="download" @click="downloadTemplate('murid')">
          Template CSV
        </Button>
        <Button variant="secondary" icon="upload" :disabled="importing" @click="triggerImport('murid')">
          {{ importing ? 'Mengimpor...' : 'Import CSV' }}
        </Button>
        <Button variant="primary" icon="plus" @click="openCreateModal">
          Tambah Siswa
        </Button>
      </div>
      <input
        ref="muridImportInput"
        type="file"
        accept=".csv,text/csv,.xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        hidden
        @change="handleImportCsv($event, 'murid')"
      />
    </div>

    <!-- Alert Message -->
    <Alert
      v-model="alert.show"
      :variant="alert.variant"
      :title="alert.title"
      :message="alert.message"
    />

    <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:end; margin:16px 0;">
      <div style="flex:1 1 220px; min-width:220px;">
        <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px; color:#475569;">Cari siswa</label>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Nama, NIS, kelas..."
          style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; background:#fff;"
        />
      </div>

      <div style="flex:1 1 180px; min-width:180px;">
        <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px; color:#475569;">Kelas</label>
        <select
          v-model="kelasFilter"
          style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; background:#fff;"
        >
          <option value="all">Semua kelas</option>
          <option v-for="kelas in kelasOptions" :key="kelas" :value="kelas">{{ kelas }}</option>
        </select>
      </div>

      <div style="flex:1 1 160px; min-width:160px;">
        <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px; color:#475569;">Jenis kelamin</label>
        <select
          v-model="genderFilter"
          style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; background:#fff;"
        >
          <option value="all">Semua</option>
          <option value="L">Laki-laki</option>
          <option value="P">Perempuan</option>
        </select>
      </div>

      <div style="flex:1 1 160px; min-width:160px;">
        <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px; color:#475569;">Status</label>
        <select
          v-model="statusFilter"
          style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; background:#fff;"
        >
          <option value="all">Semua</option>
          <option value="active">Aktif</option>
          <option value="inactive">Nonaktif</option>
        </select>
      </div>
    </div>

    <!-- Data Table -->
    <Card>
      <DataTable
        :data="filteredMuridList"
        :columns="columns"
        :loading="loading"
        :actions="['view', 'edit', 'delete']"
        @edit="openEditModal"
        @delete="confirmDelete"
        @view="openDetailModal"
      >
        <!-- Custom cell for kelas -->
        <template #cell-kelas="{ item }">
          <span v-if="item.kelas">
            {{ item.kelas.kelas }} {{ item.kelas.nama_kelas }}
          </span>
          <span v-else class="text-muted">-</span>
        </template>

        <!-- Custom cell for gender -->
        <template #cell-gender="{ value }">
          {{ value === 'L' ? 'Laki-laki' : 'Perempuan' }}
        </template>

        <!-- Custom cell for status -->
        <template #cell-is_active="{ value }">
          <Badge :variant="value ? 'success' : 'danger'" rounded>
            {{ value ? 'Aktif' : 'Nonaktif' }}
          </Badge>
        </template>
      </DataTable>
    </Card>

    <!-- Create/Edit Modal -->
    <Modal
      v-model="formModal.show"
      :title="formModal.isEdit ? 'Edit Data Siswa' : 'Tambah Data Siswa'"
      size="xl"
    >
      <MuridForm
        v-model="formData"
        :errors="formErrors"
        :is-edit="formModal.isEdit"
        @submit="handleSubmit"
      />

      <template #footer>
        <Button variant="secondary" @click="closeFormModal" :disabled="submitting">
          Batal
        </Button>
        <Button variant="primary" @click="handleSubmit" :loading="submitting">
          {{ formModal.isEdit ? 'Update' : 'Simpan' }}
        </Button>
      </template>
    </Modal>

    <!-- Detail Modal -->
    <Modal v-model="detailModal.show" title="Detail Siswa" size="lg">
      <div v-if="selectedMurid" class="detail-content">
        <!-- Data Akun -->
        <div class="detail-section">
          <h4 class="detail-section-title">Informasi Akun</h4>
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">Email</span>
              <span class="detail-value">{{ selectedMurid.user?.email || '-' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Status</span>
              <Badge
                :variant="selectedMurid.user?.is_active ? 'success' : 'danger'"
                rounded
              >
                {{ selectedMurid.user?.is_active ? 'Aktif' : 'Nonaktif' }}
              </Badge>
            </div>
          </div>
        </div>

        <!-- Data Siswa -->
        <div class="detail-section">
          <h4 class="detail-section-title">Data Siswa</h4>
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">NIS</span>
              <span class="detail-value">{{ selectedMurid.nis }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Nama Lengkap</span>
              <span class="detail-value">{{ selectedMurid.nama_lengkap_murid }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Jenis Kelamin</span>
              <span class="detail-value">{{
                selectedMurid.gender === 'L' ? 'Laki-laki' : 'Perempuan'
              }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Kelas</span>
              <span class="detail-value">
                <span v-if="selectedMurid.kelas">
                  {{ selectedMurid.kelas.kelas }} {{ selectedMurid.kelas.nama_kelas }} - {{ selectedMurid.kelas.jurusan }}
                </span>
                <span v-else>-</span>
              </span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Tempat Lahir</span>
              <span class="detail-value">{{ selectedMurid.tempat_lahir || '-' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Tanggal Lahir</span>
              <span class="detail-value">{{
                formatDate(selectedMurid.tanggal_lahir)
              }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Nomor Telepon</span>
              <span class="detail-value">{{ selectedMurid.nomor_telepon || '-' }}</span>
            </div>
            <div class="detail-item detail-item-full">
              <span class="detail-label">Alamat</span>
              <span class="detail-value">{{ selectedMurid.alamat || '-' }}</span>
            </div>
          </div>
        </div>

        <!-- Data Orang Tua/Wali -->
        <div class="detail-section">
          <h4 class="detail-section-title">Data Orang Tua/Wali</h4>
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">Nama Ayah</span>
              <span class="detail-value">{{ selectedMurid.nama_ayah || '-' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Nama Ibu</span>
              <span class="detail-value">{{ selectedMurid.nama_ibu || '-' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Pekerjaan Ayah</span>
              <span class="detail-value">{{ selectedMurid.pekerjaan_ayah || '-' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Pekerjaan Ibu</span>
              <span class="detail-value">{{ selectedMurid.pekerjaan_ibu || '-' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">No. Telepon Orang Tua</span>
              <span class="detail-value">{{ selectedMurid.nomor_telepon_ortu || '-' }}</span>
            </div>
          </div>
        </div>

        <!-- Informasi Tambahan -->
        <div class="detail-section">
          <h4 class="detail-section-title">Informasi Tambahan</h4>
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">Agama</span>
              <span class="detail-value">{{ selectedMurid.agama || '-' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Anak Ke-</span>
              <span class="detail-value">{{ selectedMurid.anak_ke || '-' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Jumlah Saudara</span>
              <span class="detail-value">{{ selectedMurid.jumlah_saudara || '-' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Hobi</span>
              <span class="detail-value">{{ selectedMurid.hobi || '-' }}</span>
            </div>
            <div class="detail-item detail-item-full">
              <span class="detail-label">Cita-cita</span>
              <span class="detail-value">{{ selectedMurid.cita_cita || '-' }}</span>
            </div>
          </div>
        </div>
      </div>

      <template #footer>
        <Button variant="secondary" @click="detailModal.show = false"> Tutup </Button>
        <Button variant="primary" icon="edit" @click="editFromDetail">
          Edit Data
        </Button>
      </template>
    </Modal>

    <!-- Delete Confirmation -->
    <ConfirmDialog
      v-model="deleteDialog.show"
      title="Hapus Data Siswa"
      message="Apakah Anda yakin ingin menghapus data siswa ini?"
      description="Data yang sudah dihapus tidak dapat dikembalikan."
      variant="danger"
      confirm-text="Ya, Hapus"
      :loading="deleteDialog.loading"
      @confirm="handleDelete"
    />
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/api'
import DashboardLayout from '@/components/dashboard/DashboardLayout.vue'
import { adminNavigation } from '@/views/admin/adminNavigation'
import {
  Card,
  DataTable,
  Modal,
  Button,
  Badge,
  Alert,
  ConfirmDialog,
} from '@/components/ui'
import MuridForm from '@/components/admin/forms/MuridForm.vue'

// Shared admin navigation for consistent sidebar across all admin pages
const navigation = adminNavigation

// State
const muridList = ref([])
const loading = ref(false)
const selectedMurid = ref(null)
const searchQuery = ref('')
const kelasFilter = ref('all')
const genderFilter = ref('all')
const statusFilter = ref('all')

const kelasOptions = computed(() => {
  const uniqueKelas = new Set()

  muridList.value.forEach((murid) => {
    const kelasLabel = murid.kelas
      ? `${murid.kelas.kelas || ''} ${murid.kelas.nama_kelas || ''}`.trim()
      : ''

    if (kelasLabel) {
      uniqueKelas.add(kelasLabel)
    }
  })

  return Array.from(uniqueKelas).sort()
})

const filteredMuridList = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()

  return muridList.value.filter((murid) => {
    const nama = String(murid.nama_lengkap_murid || murid.user?.name || '').toLowerCase()
    const nis = String(murid.nis || '').toLowerCase()
    const kelasName = murid.kelas
      ? `${murid.kelas.kelas || ''} ${murid.kelas.nama_kelas || ''}`.trim().toLowerCase()
      : ''

    const matchesSearch =
      !query ||
      [nama, nis, kelasName].some((value) => value.includes(query))

    const kelasValue = murid.kelas
      ? `${murid.kelas.kelas || ''} ${murid.kelas.nama_kelas || ''}`.trim()
      : ''
    const matchesKelas = kelasFilter.value === 'all' || kelasValue === kelasFilter.value

    const matchesGender = genderFilter.value === 'all' || murid.gender === genderFilter.value

    const isActive = Boolean(murid.user?.is_active ?? murid.is_active)
    const matchesStatus =
      statusFilter.value === 'all' ||
      (statusFilter.value === 'active' && isActive) ||
      (statusFilter.value === 'inactive' && !isActive)

    return matchesSearch && matchesKelas && matchesGender && matchesStatus
  })
})

// Modal states
const formModal = ref({
  show: false,
  isEdit: false,
})

const detailModal = ref({
  show: false,
})

const deleteDialog = ref({
  show: false,
  loading: false,
})

// Form states
const formData = ref({})
const formErrors = ref({})
const submitting = ref(false)

// Alert state
const alert = ref({
  show: false,
  variant: 'success',
  title: '',
  message: '',
})

const importing = ref(false)
const muridImportInput = ref(null)

const triggerImport = (type) => {
  const input = type === 'murid' ? muridImportInput.value : null
  if (input) input.click()
}

const downloadTemplate = (type) => {
  const csv = [
    'email,password,nis,nama_lengkap_murid,gender,kelas_id,tanggal_lahir,tempat_lahir,alamat,nomor_telepon',
    'siswa1@example.com,Secret123,2001,Siswa Satu,L,1,2009-01-15,Jakarta,Jl. Mawar 1,0811111111',
  ].join('\n')

  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `${type}-template.csv`
  link.click()
  URL.revokeObjectURL(url)
}

const handleImportCsv = async (event, type) => {
  const file = event.target.files?.[0]

  if (!file) return

  const formData = new FormData()
  formData.append('file', file)

  importing.value = true

  try {
    const response = await api.post(`/${type}/import`, formData)
    const { imported = 0, failed = 0, errors = [] } = response.data || {}

    if (response.data?.success === false) {
      throw new Error(response.data.message || 'Import gagal')
    }

    const errorSummary = errors.length ? `\n${errors.slice(0, 3).map((item) => item.message).join('\n')}` : ''
    showAlert(
      'success',
      'Import selesai',
      `Berhasil mengimpor ${imported} data. Gagal: ${failed}.${errorSummary}`,
    )
  } catch (error) {
    const message = error.response?.data?.message || error.message || 'Gagal mengimpor data'
    showAlert('danger', 'Import gagal', message)
  } finally {
    importing.value = false
    event.target.value = ''
  }
}

// Table columns configuration
const columns = [
  { key: 'nis', label: 'NIS', sortable: true },
  { key: 'nama_lengkap_murid', label: 'Nama Lengkap', sortable: true },
  { key: 'kelas', label: 'Kelas' },
  { key: 'gender', label: 'Jenis Kelamin' },
  { key: 'nomor_telepon', label: 'No. Telepon' },
  {
    key: 'is_active',
    label: 'Status',
    format: (value) => (value ? 'Aktif' : 'Nonaktif'),
  },
]

// Methods
const fetchData = async () => {
  loading.value = true
  try {
    const response = await api.get('/murid')
    muridList.value = response.data.data
  } catch (error) {
    console.error('Error fetching murid:', error)
    showAlert('danger', 'Error', 'Gagal memuat data siswa')
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  formModal.value.isEdit = false
  formData.value = {
    email: '',
    password: '',
    nis: '',
    nama_lengkap_murid: '',
    gender: '',
    kelas_id: '',
    tanggal_lahir: '',
    tempat_lahir: '',
    alamat: '',
    nomor_telepon: '',
    nama_ayah: '',
    nama_ibu: '',
    pekerjaan_ayah: '',
    pekerjaan_ibu: '',
    nomor_telepon_ortu: '',
    agama: '',
    anak_ke: '',
    jumlah_saudara: '',
    hobi: '',
    cita_cita: '',
  }
  formErrors.value = {}
  formModal.value.show = true
}

const openEditModal = (murid) => {
  formModal.value.isEdit = true
  formData.value = {
    id: murid.id,
    user_id: murid.user_id,
    email: murid.user?.email || '',
    nis: murid.nis,
    nama_lengkap_murid: murid.nama_lengkap_murid,
    gender: murid.gender,
    kelas_id: murid.kelas_id,
    tanggal_lahir: murid.tanggal_lahir,
    tempat_lahir: murid.tempat_lahir,
    alamat: murid.alamat,
    nomor_telepon: murid.nomor_telepon,
    nama_ayah: murid.nama_ayah,
    nama_ibu: murid.nama_ibu,
    pekerjaan_ayah: murid.pekerjaan_ayah,
    pekerjaan_ibu: murid.pekerjaan_ibu,
    nomor_telepon_ortu: murid.nomor_telepon_ortu,
    agama: murid.agama,
    anak_ke: murid.anak_ke,
    jumlah_saudara: murid.jumlah_saudara,
    hobi: murid.hobi,
    cita_cita: murid.cita_cita,
  }
  formErrors.value = {}
  formModal.value.show = true
}

const openDetailModal = (murid) => {
  selectedMurid.value = murid
  detailModal.value.show = true
}

const editFromDetail = () => {
  detailModal.value.show = false
  openEditModal(selectedMurid.value)
}

const closeFormModal = () => {
  formModal.value.show = false
  formData.value = {}
  formErrors.value = {}
}

const handleSubmit = async () => {
  submitting.value = true
  formErrors.value = {}

  try {
    if (formModal.value.isEdit) {
      await api.put(`/murid/${formData.value.id}`, formData.value)
      showAlert('success', 'Berhasil', 'Data siswa berhasil diupdate')
    } else {
      await api.post('/murid', formData.value)
      showAlert('success', 'Berhasil', 'Data siswa berhasil ditambahkan')
    }

    closeFormModal()
    fetchData()
  } catch (error) {
    if (error.response?.status === 422) {
      formErrors.value = error.response.data.errors || {}
    } else {
      showAlert(
        'danger',
        'Error',
        error.response?.data?.message || 'Terjadi kesalahan',
      )
    }
  } finally {
    submitting.value = false
  }
}

const confirmDelete = (murid) => {
  selectedMurid.value = murid
  deleteDialog.value.show = true
}

const handleDelete = async () => {
  deleteDialog.value.loading = true

  try {
    await api.delete(`/murid/${selectedMurid.value.id}`)
    deleteDialog.value.show = false
    showAlert('success', 'Berhasil', 'Data siswa berhasil dihapus')
    fetchData()
  } catch (error) {
    showAlert('danger', 'Error', 'Gagal menghapus data siswa')
  } finally {
    deleteDialog.value.loading = false
  }
}

const showAlert = (variant, title, message) => {
  alert.value = { show: true, variant, title, message }
  setTimeout(() => {
    alert.value.show = false
  }, 5000)
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

// Lifecycle
onMounted(() => {
  fetchData()
})
</script>

<style scoped>
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  gap: 16px;
  flex-wrap: wrap;
}

.page-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
  margin: 0 0 4px 0;
}

.page-subtitle {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}

.text-muted {
  color: #9ca3af;
}

/* Detail Modal Styles */
.detail-content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.detail-section {
  padding: 20px;
  background: #f9fafb;
  border-radius: 8px;
}

.detail-section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #374151;
  margin: 0 0 16px 0;
  padding-bottom: 8px;
  border-bottom: 2px solid #e5e7eb;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-item-full {
  grid-column: 1 / -1;
}

.detail-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.detail-value {
  font-size: 0.875rem;
  color: #111827;
  font-weight: 500;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: stretch;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }
}
</style>
