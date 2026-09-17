<template>
  <DashboardLayout title="Kelola Guru" role-label="Admin" :navigation="navigation">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h2 class="page-title">Manajemen Guru</h2>
        <p class="page-subtitle">Kelola data guru dan pengajar</p>
      </div>
      <div class="page-actions">
        <Button variant="secondary" icon="download" @click="downloadTemplate('guru')">
          Template CSV
        </Button>
        <Button variant="secondary" icon="upload" :disabled="importing" @click="triggerImport('guru')">
          {{ importing ? 'Mengimpor...' : 'Import CSV' }}
        </Button>
        <Button variant="primary" icon="plus" @click="openCreateModal">
          Tambah Guru
        </Button>
      </div>
      <input
        ref="guruImportInput"
        type="file"
        accept=".csv,text/csv,.xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        hidden
        @change="handleImportCsv($event, 'guru')"
      />
    </div>

    <!-- Alert Message -->
    <Alert
      v-model="alert.show"
      :variant="alert.variant"
      :title="alert.title"
      :message="alert.message"
    />

    <!-- Data Table -->
    <Card>
      <DataTable
        :data="guruList"
        :columns="columns"
        :loading="loading"
        :actions="['view', 'edit', 'delete']"
        @edit="openEditModal"
        @delete="confirmDelete"
        @view="openDetailModal"
      >
        <!-- Custom cell for status -->
        <template #cell-is_active="{ value }">
          <Badge :variant="value ? 'success' : 'danger'" rounded>
            {{ value ? 'Aktif' : 'Nonaktif' }}
          </Badge>
        </template>

        <!-- Custom cell for gender -->
        <template #cell-gender="{ value }">
          {{ value === 'L' ? 'Laki-laki' : 'Perempuan' }}
        </template>
      </DataTable>
    </Card>

    <!-- Create/Edit Modal -->
    <Modal
      v-model="formModal.show"
      :title="formModal.isEdit ? 'Edit Data Guru' : 'Tambah Data Guru'"
      size="xl"
    >
      <GuruForm
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
    <Modal v-model="detailModal.show" title="Detail Guru" size="lg">
      <div v-if="selectedGuru" class="detail-content">
        <div class="detail-section">
          <h4 class="detail-section-title">Informasi Akun</h4>
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">Email</span>
              <span class="detail-value">{{ selectedGuru.user?.email || '-' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Status</span>
              <Badge
                :variant="selectedGuru.user?.is_active ? 'success' : 'danger'"
                rounded
              >
                {{ selectedGuru.user?.is_active ? 'Aktif' : 'Nonaktif' }}
              </Badge>
            </div>
          </div>
        </div>

        <div class="detail-section">
          <h4 class="detail-section-title">Data Guru</h4>
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">NIP</span>
              <span class="detail-value">{{ selectedGuru.nip }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Nama Lengkap</span>
              <span class="detail-value">{{ selectedGuru.nama_lengkap_guru }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Jenis Kelamin</span>
              <span class="detail-value">{{
                selectedGuru.gender === 'L' ? 'Laki-laki' : 'Perempuan'
              }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Tanggal Lahir</span>
              <span class="detail-value">{{
                formatDate(selectedGuru.tanggal_lahir)
              }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Nomor Telepon</span>
              <span class="detail-value">{{ selectedGuru.nomor_telepon }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Pendidikan Terakhir</span>
              <span class="detail-value">{{ selectedGuru.pendidikan_terakhir || '-' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Mata Pelajaran</span>
              <span class="detail-value">{{ selectedGuru.mata_pelajaran || '-' }}</span>
            </div>
            <div class="detail-item detail-item-full">
              <span class="detail-label">Alamat</span>
              <span class="detail-value">{{ selectedGuru.alamat }}</span>
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
      title="Hapus Data Guru"
      message="Apakah Anda yakin ingin menghapus data guru ini?"
      description="Data yang sudah dihapus tidak dapat dikembalikan."
      variant="danger"
      confirm-text="Ya, Hapus"
      :loading="deleteDialog.loading"
      @confirm="handleDelete"
    />
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
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
import GuruForm from '@/components/admin/forms/GuruForm.vue'

// Shared admin navigation for consistent sidebar across all admin pages
const navigation = adminNavigation

// State
const guruList = ref([])
const loading = ref(false)
const selectedGuru = ref(null)

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
const guruImportInput = ref(null)

const triggerImport = (type) => {
  const input = type === 'guru' ? guruImportInput.value : null
  if (input) input.click()
}

const downloadTemplate = (type) => {
  const csv = [
    'email,password,nip,nama_lengkap_guru,gender,tanggal_lahir,alamat,nomor_telepon',
    'guru1@example.com,Secret123,9001,Guru Satu,L,1990-05-10,Jl. Kenanga 8,0812222222',
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
    await fetchData()
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
  { key: 'nip', label: 'NIP', sortable: true },
  { key: 'nama_lengkap_guru', label: 'Nama Lengkap', sortable: true },
  { key: 'gender', label: 'Jenis Kelamin' },
  { key: 'nomor_telepon', label: 'No. Telepon' },
  { key: 'mata_pelajaran', label: 'Mata Pelajaran' },
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
    const response = await api.get('/guru')
    guruList.value = response.data.data
  } catch (error) {
    console.error('Error fetching guru:', error)
    showAlert('danger', 'Error', 'Gagal memuat data guru')
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  formModal.value.isEdit = false
  formData.value = {
    email: '',
    password: '',
    nip: '',
    nama_lengkap_guru: '',
    gender: '',
    alamat: '',
    nomor_telepon: '',
    tanggal_lahir: '',
    pendidikan_terakhir: '',
    mata_pelajaran: '',
  }
  formErrors.value = {}
  formModal.value.show = true
}

const openEditModal = (guru) => {
  formModal.value.isEdit = true
  formData.value = {
    id: guru.id,
    user_id: guru.user_id,
    email: guru.user?.email || '',
    nip: guru.nip,
    nama_lengkap_guru: guru.nama_lengkap_guru,
    gender: guru.gender,
    alamat: guru.alamat,
    nomor_telepon: guru.nomor_telepon,
    tanggal_lahir: guru.tanggal_lahir,
    pendidikan_terakhir: guru.pendidikan_terakhir,
    mata_pelajaran: guru.mata_pelajaran,
  }
  formErrors.value = {}
  formModal.value.show = true
}

const openDetailModal = (guru) => {
  selectedGuru.value = guru
  detailModal.value.show = true
}

const editFromDetail = () => {
  detailModal.value.show = false
  openEditModal(selectedGuru.value)
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
      await api.put(`/guru/${formData.value.id}`, formData.value)
      showAlert('success', 'Berhasil', 'Data guru berhasil diupdate')
    } else {
      await api.post('/guru', formData.value)
      showAlert('success', 'Berhasil', 'Data guru berhasil ditambahkan')
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

const confirmDelete = (guru) => {
  selectedGuru.value = guru
  deleteDialog.value.show = true
}

const handleDelete = async () => {
  deleteDialog.value.loading = true

  try {
    await api.delete(`/guru/${selectedGuru.value.id}`)
    deleteDialog.value.show = false
    showAlert('success', 'Berhasil', 'Data guru berhasil dihapus')
    fetchData()
  } catch (error) {
    showAlert('danger', 'Error', 'Gagal menghapus data guru')
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
