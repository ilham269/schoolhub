<template>
  <DashboardLayout title="Kelola Tagihan SPP" role-label="Karyawan" :navigation="navigation">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1>Kelola Tagihan SPP</h1>
        <p>Kelola dan pantau pembayaran SPP siswa</p>
      </div>
      <button @click="showCreateModal = true" class="btn-primary">
        <i class="fas fa-plus"></i> Buat Tagihan
      </button>
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filter-group">
        <label>Status</label>
        <select v-model="filters.status" @change="loadTagihan">
          <option value="">Semua Status</option>
          <option value="UNPAID">Belum Bayar</option>
          <option value="PENDING">Pending</option>
          <option value="LUNAS">Lunas</option>
          <option value="EXPIRED">Kadaluarsa</option>
        </select>
      </div>

      <div class="filter-group">
        <label>Periode</label>
        <input type="month" v-model="filters.periode" @change="loadTagihan" />
      </div>

      <div class="filter-group">
        <label>Cari</label>
        <input
          type="text"
          v-model="filters.search"
          placeholder="Nama siswa atau NIS..."
          @input="debounceSearch"
        />
      </div>

      <button @click="resetFilters" class="btn-secondary">
        <i class="fas fa-redo"></i> Reset
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <i class="fas fa-spinner fa-spin"></i>
      <p>Memuat data tagihan...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <i class="fas fa-exclamation-circle"></i>
      <p>{{ error }}</p>
      <button @click="loadTagihan" class="btn-retry">Coba Lagi</button>
    </div>

    <!-- Data Table -->
    <div v-else class="table-card">
      <div class="table-header">
        <h3>Daftar Tagihan SPP</h3>
        <span class="record-count">{{ pagination.total || 0 }} tagihan</span>
      </div>

      <!-- Empty State -->
      <div v-if="!tagihan || tagihan.length === 0" class="empty-state">
        <i class="fas fa-inbox"></i>
        <p>Tidak ada tagihan ditemukan</p>
      </div>

      <!-- Table -->
      <div v-else class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>Invoice</th>
              <th>Siswa</th>
              <th>Kelas</th>
              <th>Periode</th>
              <th>Jumlah</th>
              <th>Jatuh Tempo</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in tagihan" :key="item.id">
              <td>
                <span class="invoice-number">{{ item.invoice_number }}</span>
              </td>
              <td>
                <div class="student-info">
                  <strong>{{ item.murid?.nama_lengkap_murid }}</strong>
                  <small>NIS: {{ item.murid?.nis }}</small>
                </div>
              </td>
              <td>{{ item.murid?.kelas?.name || '-' }}</td>
              <td>{{ formatPeriode(item.periode) }}</td>
              <td>
                <strong>{{ formatCurrency(item.total) }}</strong>
              </td>
              <td>{{ formatDate(item.jatuh_tempo) }}</td>
              <td>
                <span class="status-badge" :class="getStatusClass(item.status)">
                  {{ getStatusLabel(item.status) }}
                </span>
              </td>
              <td>
                <div class="action-buttons">
                  <button
                    @click="editTagihan(item)"
                    class="btn-icon"
                    title="Edit"
                    v-if="item.status !== 'LUNAS'"
                  >
                    <i class="fas fa-edit"></i>
                  </button>
                  <button
                    @click="deleteTagihan(item)"
                    class="btn-icon btn-danger"
                    title="Hapus"
                    v-if="item.status === 'UNPAID'"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="pagination">
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="btn-page"
        >
          <i class="fas fa-chevron-left"></i> Prev
        </button>
        <span class="page-info">
          Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}
        </span>
        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="btn-page"
        >
          Next <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>{{ showEditModal ? 'Edit Tagihan SPP' : 'Buat Tagihan SPP' }}</h2>
          <button @click="closeModal" class="btn-close">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <form @submit.prevent="submitForm" class="modal-body">
          <div class="form-group">
            <label>Siswa <span class="required">*</span></label>
            <select v-model="form.murid_id" required :disabled="showEditModal">
              <option value="">Pilih Siswa</option>
              <option v-for="murid in muridList" :key="murid.id" :value="murid.id">
                {{ murid.nama_lengkap_murid }} ({{ murid.nis }})
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Periode <span class="required">*</span></label>
            <input type="month" v-model="form.periode" required />
          </div>

          <div class="form-group">
            <label>Jumlah (Rp) <span class="required">*</span></label>
            <input type="number" v-model="form.jumlah" required min="0" step="1000" />
          </div>

          <div class="form-group">
            <label>Jatuh Tempo <span class="required">*</span></label>
            <input type="date" v-model="form.jatuh_tempo" required />
          </div>

          <div class="form-actions">
            <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
            <button type="submit" class="btn-primary" :disabled="submitting">
              <i v-if="submitting" class="fas fa-spinner fa-spin"></i>
              <i v-else class="fas fa-save"></i>
              {{ submitting ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'
import keuanganService from '../../utils/keuanganService'
import api from '../../utils/api'

const router = useRouter()

const navigation = [
  { label: 'Dashboard', icon: 'fas fa-chart-pie', to: '/dashboard/karyawan' },
  { label: 'Keuangan', icon: 'fas fa-wallet', to: '/dashboard/karyawan/keuangan' },
  { label: 'Tagihan SPP', icon: 'fas fa-file-invoice', to: '/dashboard/karyawan/keuangan/tagihan', active: true },
]

const loading = ref(true)
const error = ref(null)
const submitting = ref(false)
const tagihan = ref([])
const pagination = ref({})
const muridList = ref([])

const filters = ref({
  status: '',
  periode: '',
  search: '',
  per_page: 15,
})

const showCreateModal = ref(false)
const showEditModal = ref(false)
const editingId = ref(null)

const form = ref({
  murid_id: '',
  periode: '',
  jumlah: '',
  jatuh_tempo: '',
})

let searchTimeout = null

const loadTagihan = async (page = 1) => {
  try {
    loading.value = true
    error.value = null

    const params = {
      ...filters.value,
      page,
    }

    const response = await keuanganService.getTagihanSpp(params)

    if (response.success) {
      tagihan.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        total: response.data.total,
      }
    } else {
      error.value = response.message || 'Gagal memuat data tagihan'
    }
  } catch (err) {
    console.error('Error loading tagihan:', err)
    error.value = err.response?.data?.message || 'Gagal memuat data tagihan'
  } finally {
    loading.value = false
  }
}

const loadMuridList = async () => {
  try {
    const response = await api.get('/murid')
    if (response.data.success) {
      muridList.value = response.data.data
    }
  } catch (err) {
    console.error('Error loading murid list:', err)
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadTagihan()
  }, 500)
}

const resetFilters = () => {
  filters.value = {
    status: '',
    periode: '',
    search: '',
    per_page: 15,
  }
  loadTagihan()
}

const changePage = (page) => {
  loadTagihan(page)
}

const editTagihan = (item) => {
  editingId.value = item.id
  form.value = {
    murid_id: item.murid_id,
    periode: item.periode,
    jumlah: item.jumlah,
    jatuh_tempo: item.jatuh_tempo,
  }
  showEditModal.value = true
}

const deleteTagihan = async (item) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus tagihan ${item.invoice_number}?`)) {
    return
  }

  try {
    const response = await keuanganService.deleteTagihan(item.id)
    if (response.success) {
      alert('Tagihan berhasil dihapus')
      loadTagihan()
    } else {
      alert(response.message || 'Gagal menghapus tagihan')
    }
  } catch (err) {
    console.error('Error deleting tagihan:', err)
    alert(err.response?.data?.message || 'Gagal menghapus tagihan')
  }
}

const submitForm = async () => {
  try {
    submitting.value = true

    const payload = {
      ...form.value,
      periode: form.value.periode + '-01', // Convert YYYY-MM to YYYY-MM-DD
    }

    let response
    if (showEditModal.value) {
      response = await keuanganService.updateTagihan(editingId.value, payload)
    } else {
      response = await keuanganService.createTagihan(payload)
    }

    if (response.success) {
      alert(response.message || 'Tagihan berhasil disimpan')
      closeModal()
      loadTagihan()
    } else {
      alert(response.message || 'Gagal menyimpan tagihan')
    }
  } catch (err) {
    console.error('Error submitting form:', err)
    alert(err.response?.data?.message || 'Gagal menyimpan tagihan')
  } finally {
    submitting.value = false
  }
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  editingId.value = null
  form.value = {
    murid_id: '',
    periode: '',
    jumlah: '',
    jatuh_tempo: '',
  }
}

const formatCurrency = (amount) => {
  return keuanganService.formatCurrency(amount)
}

const formatDate = (date) => {
  return keuanganService.formatDate(date)
}

const formatPeriode = (periode) => {
  if (!periode) return '-'
  const date = new Date(periode)
  return date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })
}

const getStatusLabel = (status) => {
  return keuanganService.getStatusLabel(status)
}

const getStatusClass = (status) => {
  const classes = {
    UNPAID: 'status-warning',
    PENDING: 'status-pending',
    LUNAS: 'status-success',
    EXPIRED: 'status-error',
  }
  return classes[status] || ''
}

onMounted(() => {
  loadTagihan()
  loadMuridList()
})
</script>

<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.page-header h1 {
  font-size: 1.5rem;
  color: #1e293b;
  margin: 0 0 4px;
}

.page-header p {
  color: #64748b;
  margin: 0;
  font-size: 0.9rem;
}

.btn-primary {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background 0.2s;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-primary:disabled {
  background: #94a3b8;
  cursor: not-allowed;
}

.filters-card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 20px;
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  align-items: flex-end;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 200px;
}

.filter-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #475569;
}

.filter-group input,
.filter-group select {
  padding: 8px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 0.9rem;
}

.btn-secondary {
  background: #f1f5f9;
  color: #475569;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: background 0.2s;
}

.btn-secondary:hover {
  background: #e2e8f0;
}

.loading-state,
.error-state {
  text-align: center;
  padding: 60px 20px;
  color: #64748b;
}

.loading-state i {
  font-size: 2.5rem;
  margin-bottom: 16px;
  color: #3b82f6;
}

.error-state i {
  font-size: 2.5rem;
  margin-bottom: 16px;
  color: #ef4444;
}

.btn-retry {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 8px;
  cursor: pointer;
  margin-top: 12px;
}

.table-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
}

.table-header h3 {
  font-size: 1rem;
  color: #1e293b;
  margin: 0;
}

.record-count {
  background: #f1f5f9;
  color: #475569;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #94a3b8;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 12px;
  opacity: 0.5;
}

.table-responsive {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead {
  background: #f8fafc;
}

th {
  text-align: left;
  padding: 12px 16px;
  font-size: 0.8rem;
  font-weight: 600;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

td {
  padding: 16px;
  border-top: 1px solid #f1f5f9;
  font-size: 0.9rem;
  color: #334155;
}

.invoice-number {
  font-family: monospace;
  font-weight: 600;
  color: #3b82f6;
}

.student-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.student-info strong {
  color: #1e293b;
}

.student-info small {
  color: #64748b;
  font-size: 0.8rem;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  display: inline-block;
}

.status-success {
  background: #d1fae5;
  color: #059669;
}

.status-pending {
  background: #fef3c7;
  color: #d97706;
}

.status-warning {
  background: #fed7aa;
  color: #c2410c;
}

.status-error {
  background: #fee2e2;
  color: #dc2626;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.btn-icon {
  background: #f1f5f9;
  color: #475569;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.btn-icon:hover {
  background: #3b82f6;
  color: white;
}

.btn-icon.btn-danger:hover {
  background: #ef4444;
  color: white;
}

.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  border-top: 1px solid #e2e8f0;
}

.btn-page {
  background: #f1f5f9;
  color: #475569;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: background 0.2s;
}

.btn-page:hover:not(:disabled) {
  background: #e2e8f0;
}

.btn-page:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-size: 0.85rem;
  color: #64748b;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h2 {
  font-size: 1.1rem;
  color: #1e293b;
  margin: 0;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.2rem;
  color: #64748b;
  cursor: pointer;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: background 0.2s;
}

.btn-close:hover {
  background: #f1f5f9;
}

.modal-body {
  padding: 24px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: #475569;
  margin-bottom: 6px;
}

.required {
  color: #ef4444;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.9rem;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #3b82f6;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }

  .filters-card {
    flex-direction: column;
  }

  .filter-group {
    width: 100%;
  }

  .table-responsive {
    font-size: 0.8rem;
  }

  td,
  th {
    padding: 10px;
  }
}
</style>
