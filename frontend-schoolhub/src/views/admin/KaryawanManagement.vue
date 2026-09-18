<template>
  <DashboardLayout title="Kelola Karyawan" role-label="Admin" :navigation="navigation">
    <!-- Page Header -->
    <section class="welcome">
      <div>
        <span class="eyebrow-dot dark">Manajemen sumber daya</span>
        <h2>Manajemen Karyawan</h2>
        <p>Kelola data karyawan dan staf sekolah.</p>
      </div>
      <div class="page-actions">
        <Button variant="secondary" icon="download" @click="downloadTemplate('karyawan')">
          Template CSV
        </Button>
        <Button variant="secondary" icon="upload" :disabled="importing" @click="triggerImport('karyawan')">
          {{ importing ? 'Mengimpor...' : 'Import CSV' }}
        </Button>
        <Button variant="primary" icon="plus" @click="openCreateModal">
          Tambah Karyawan
        </Button>
      </div>
      <input
        ref="karyawanImportInput"
        type="file"
        accept=".csv,text/csv,.xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        hidden
        @change="handleImportCsv($event, 'karyawan')"
      />
    </section>

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
        :data="karyawanList"
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
      </DataTable>
    </Card>

    <!-- Create/Edit Modal -->
    <Modal
      v-model="formModal.show"
      :title="formModal.isEdit ? 'Edit Data Karyawan' : 'Tambah Data Karyawan'"
      size="xl"
    >
      <KaryawanForm
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
    <Modal v-model="detailModal.show" title="Detail Karyawan" size="lg">
      <div v-if="selectedKaryawan" class="detail-content">
        <!-- Data Akun -->
        <div class="detail-section">
          <h4 class="detail-section-title">Informasi Akun</h4>
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">Email</span>
              <span class="detail-value">{{ selectedKaryawan.user?.email || '-' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Status</span>
              <Badge
                :variant="selectedKaryawan.user?.is_active ? 'success' : 'danger'"
                rounded
              >
                {{ selectedKaryawan.user?.is_active ? 'Aktif' : 'Nonaktif' }}
              </Badge>
            </div>
          </div>
        </div>

        <!-- Data Karyawan -->
        <div class="detail-section">
          <h4 class="detail-section-title">Data Karyawan</h4>
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">NIP</span>
              <span class="detail-value">{{ selectedKaryawan.nip }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Nama Lengkap</span>
              <span class="detail-value">{{ selectedKaryawan.nama_lengkap_karyawan }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Bagian/Departemen</span>
              <span class="detail-value">{{ selectedKaryawan.bagian }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Nomor Telepon</span>
              <span class="detail-value">{{ selectedKaryawan.nomor_telepon || '-' }}</span>
            </div>
            <div class="detail-item detail-item-full">
              <span class="detail-label">Alamat</span>
              <span class="detail-value">{{ selectedKaryawan.alamat || '-' }}</span>
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
      title="Hapus Data Karyawan"
      message="Apakah Anda yakin ingin menghapus data karyawan ini?"
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
import KaryawanForm from '@/components/admin/forms/KaryawanForm.vue'

// Shared admin navigation for consistent sidebar across all admin pages
const navigation = adminNavigation

// State
const karyawanList = ref([])
const loading = ref(false)
const selectedKaryawan = ref(null)

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
const karyawanImportInput = ref(null)

const triggerImport = (type) => {
  const input = type === 'karyawan' ? karyawanImportInput.value : null
  if (input) input.click()
}

const downloadTemplate = (type) => {
  const csv = [
    'email,password,nip,nama_lengkap_karyawan,bagian,nomor_telepon,alamat',
    'staff1@example.com,Secret123,8001,Staff Satu,Tata Usaha,0813333333,Jl. Melati 9',
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
  { key: 'nama_lengkap_karyawan', label: 'Nama Lengkap', sortable: true },
  { key: 'bagian', label: 'Bagian', sortable: true },
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
    const response = await api.get('/karyawan')
    karyawanList.value = response.data.data
  } catch (error) {
    console.error('Error fetching karyawan:', error)
    showAlert('danger', 'Error', 'Gagal memuat data karyawan')
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
    nama_lengkap_karyawan: '',
    bagian: '',
    nomor_telepon: '',
    alamat: '',
  }
  formErrors.value = {}
  formModal.value.show = true
}

const openEditModal = (karyawan) => {
  formModal.value.isEdit = true
  formData.value = {
    id: karyawan.id,
    user_id: karyawan.user_id,
    email: karyawan.user?.email || '',
    nip: karyawan.nip,
    nama_lengkap_karyawan: karyawan.nama_lengkap_karyawan,
    bagian: karyawan.bagian,
    nomor_telepon: karyawan.nomor_telepon,
    alamat: karyawan.alamat,
  }
  formErrors.value = {}
  formModal.value.show = true
}

const openDetailModal = (karyawan) => {
  selectedKaryawan.value = karyawan
  detailModal.value.show = true
}

const editFromDetail = () => {
  detailModal.value.show = false
  openEditModal(selectedKaryawan.value)
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
      await api.put(`/karyawan/${formData.value.id}`, formData.value)
      showAlert('success', 'Berhasil', 'Data karyawan berhasil diupdate')
    } else {
      await api.post('/karyawan', formData.value)
      showAlert('success', 'Berhasil', 'Data karyawan berhasil ditambahkan')
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

const confirmDelete = (karyawan) => {
  selectedKaryawan.value = karyawan
  deleteDialog.value.show = true
}

const handleDelete = async () => {
  deleteDialog.value.loading = true

  try {
    await api.delete(`/karyawan/${selectedKaryawan.value.id}`)
    deleteDialog.value.show = false
    showAlert('success', 'Berhasil', 'Data karyawan berhasil dihapus')
    fetchData()
  } catch (error) {
    showAlert('danger', 'Error', 'Gagal menghapus data karyawan')
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

// Lifecycle
onMounted(() => {
  fetchData()
})
</script>

<style scoped>
/* Semua warna & font memakai design tokens global situs
   (--forest-950, --leaf-500, --lime-400, dst dari main.css). */

.welcome {
  background: linear-gradient(110deg, var(--forest-950), var(--leaf-600));
  padding: 27px 30px;
  border-radius: var(--radius-lg);
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  gap: 16px;
  flex-wrap: wrap;
}
.welcome h2 {
  color: #fff;
  font-size: 1.35rem;
  margin: 4px 0;
}
.welcome p {
  color: #d9efe0;
  margin: 0;
}
.eyebrow-dot.dark {
  color: var(--lime-400);
}
.eyebrow-dot.dark::before {
  background: var(--lime-400);
}

.page-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

/* ---------- Detail Modal ---------- */
.detail-content {
  display: flex;
  flex-direction: column;
  gap: 22px;
}
.detail-section {
  padding: 20px;
  background: var(--cream);
  border-radius: var(--radius-md);
}
.detail-section-title {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--forest-950);
  margin: 0 0 14px 0;
  padding-bottom: 10px;
  border-bottom: 2px solid var(--line);
  font-family: var(--font-head);
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
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-family: var(--font-head);
}
.detail-value {
  font-size: 0.88rem;
  color: var(--forest-950);
  font-weight: 600;
}

@media (max-width: 768px) {
  .welcome {
    flex-direction: column;
    align-items: flex-start;
  }
  .page-actions {
    width: 100%;
  }
  .detail-grid {
    grid-template-columns: 1fr;
  }
}
</style>