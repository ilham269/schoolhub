<template>
  <DashboardLayout title="Kelola Slip Gaji" role-label="Karyawan" :navigation="navigation">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1>Kelola Slip Gaji</h1>
        <p>Kelola slip gaji karyawan sekolah</p>
      </div>
      <button @click="showCreateModal = true" class="btn-primary">
        <i class="fas fa-plus"></i> Buat Slip Gaji
      </button>
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filter-group">
        <label>Status</label>
        <select v-model="filters.status" @change="loadSlipGaji">
          <option value="">Semua Status</option>
          <option value="DRAFT">Draft</option>
          <option value="APPROVED">Disetujui</option>
          <option value="PAID">Dibayar</option>
        </select>
      </div>

      <div class="filter-group">
        <label>Periode</label>
        <input type="month" v-model="filters.periode" @change="loadSlipGaji" />
      </div>

      <button @click="resetFilters" class="btn-secondary">
        <i class="fas fa-redo"></i> Reset
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <i class="fas fa-spinner fa-spin"></i>
      <p>Memuat data slip gaji...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <i class="fas fa-exclamation-circle"></i>
      <p>{{ error }}</p>
      <button @click="loadSlipGaji" class="btn-retry">Coba Lagi</button>
    </div>

    <!-- Data Table -->
    <div v-else class="table-card">
      <div class="table-header">
        <h3>Daftar Slip Gaji</h3>
        <span class="record-count">{{ pagination.total || 0 }} slip gaji</span>
      </div>

      <!-- Empty State -->
      <div v-if="!slipGaji || slipGaji.length === 0" class="empty-state">
        <i class="fas fa-inbox"></i>
        <p>Tidak ada slip gaji ditemukan</p>
      </div>

      <!-- Table -->
      <div v-else class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>Karyawan</th>
              <th>Bagian</th>
              <th>Periode</th>
              <th>Gaji Pokok</th>
              <th>Tunjangan</th>
              <th>Potongan</th>
              <th>Total</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in slipGaji" :key="item.id">
              <td>
                <div class="employee-info">
                  <strong>{{ item.karyawan?.nama_lengkap_karyawan }}</strong>
                  <small>NIP: {{ item.karyawan?.nip }}</small>
                </div>
              </td>
              <td>{{ item.karyawan?.bagian || '-' }}</td>
              <td>{{ formatPeriode(item.periode) }}</td>
              <td>{{ formatCurrency(item.gaji_pokok) }}</td>
              <td>{{ formatCurrency(item.tunjangan) }}</td>
              <td>{{ formatCurrency(item.potongan) }}</td>
              <td>
                <strong>{{ formatCurrency(item.total) }}</strong>
              </td>
              <td>
                <span class="status-badge" :class="getStatusClass(item.status)">
                  {{ getStatusLabel(item.status) }}
                </span>
              </td>
              <td>
                <div class="action-buttons">
                  <button
                    @click="approveSlipGaji(item)"
                    class="btn-icon btn-success"
                    title="Setujui"
                    v-if="item.status === 'DRAFT' && isAdmin"
                  >
                    <i class="fas fa-check"></i>
                  </button>
                  <button
                    @click="markAsPaid(item)"
                    class="btn-icon btn-primary"
                    title="Tandai Dibayar"
                    v-if="item.status === 'APPROVED'"
                  >
                    <i class="fas fa-money-bill"></i>
                  </button>
                  <button
                    @click="editSlipGaji(item)"
                    class="btn-icon"
                    title="Edit"
                    v-if="item.status === 'DRAFT'"
                  >
                    <i class="fas fa-edit"></i>
                  </button>
                  <button
                    @click="deleteSlipGaji(item)"
                    class="btn-icon btn-danger"
                    title="Hapus"
                    v-if="item.status === 'DRAFT'"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                  <button
                    v-if="item.file_path"
                    @click="downloadPdf(item)"
                    class="btn-icon"
                    title="Download PDF"
                  >
                    <i class="fas fa-download"></i>
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
          <h2>{{ showEditModal ? 'Edit Slip Gaji' : 'Buat Slip Gaji' }}</h2>
          <button @click="closeModal" class="btn-close">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <form @submit.prevent="submitForm" class="modal-body">
          <div class="form-group">
            <label>Karyawan <span class="required">*</span></label>
            <select v-model="form.karyawan_id" required :disabled="showEditModal">
              <option value="">Pilih Karyawan</option>
              <option v-for="karyawan in karyawanList" :key="karyawan.id" :value="karyawan.id">
                {{ karyawan.nama_lengkap_karyawan }} ({{ karyawan.nip }}) - {{ karyawan.bagian }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Periode <span class="required">*</span></label>
            <input type="month" v-model="form.periode" required />
          </div>

          <div class="form-group">
            <label>Gaji Pokok (Rp) <span class="required">*</span></label>
            <input type="number" v-model="form.gaji_pokok" required min="0" step="1000" />
          </div>

          <div class="form-group">
            <label>Tunjangan (Rp)</label>
            <input type="number" v-model="form.tunjangan" min="0" step="1000" />
          </div>

          <div class="form-group">
            <label>Potongan (Rp)</label>
            <input type="number" v-model="form.potongan" min="0" step="1000" />
          </div>

          <div class="form-group">
            <label>Keterangan</label>
            <textarea v-model="form.keterangan" rows="3" placeholder="Catatan tambahan..."></textarea>
          </div>

          <div class="total-preview">
            <strong>Total Gaji:</strong>
            <span>{{ formatCurrency(calculateTotal()) }}</span>
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
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'
import keuanganService from '../../utils/keuanganService'
import api from '../../utils/api'

const router = useRouter()

const navigation = [
  { label: 'Dashboard', icon: 'fas fa-chart-pie', to: '/dashboard/karyawan' },
  { label: 'Keuangan', icon: 'fas fa-wallet', to: '/dashboard/karyawan/keuangan' },
  { label: 'Slip Gaji', icon: 'fas fa-money-check', to: '/dashboard/karyawan/keuangan/slip-gaji', active: true },
]

const loading = ref(true)
const error = ref(null)
const submitting = ref(false)
const slipGaji = ref([])
const pagination = ref({})
const karyawanList = ref([])

const user = JSON.parse(sessionStorage.getItem('user') || '{}')
const isAdmin = computed(() => user.role?.toLowerCase() === 'admin')

const filters = ref({
  status: '',
  periode: '',
  per_page: 15,
})

const showCreateModal = ref(false)
const showEditModal = ref(false)
const editingId = ref(null)

const form = ref({
  karyawan_id: '',
  periode: '',
  gaji_pokok: 0,
  tunjangan: 0,
  potongan: 0,
  keterangan: '',
})

const loadSlipGaji = async (page = 1) => {
  try {
    loading.value = true
    error.value = null

    const params = {
      ...filters.value,
      page,
    }

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
    console.error('Error loading slip gaji:', err)
    error.value = err.response?.data?.message || 'Gagal memuat data slip gaji'
  } finally {
    loading.value = false
  }
}

const loadKaryawanList = async () => {
  try {
    const response = await api.get('/karyawan')
    if (response.data.success) {
      karyawanList.value = response.data.data
    }
  } catch (err) {
    console.error('Error loading karyawan list:', err)
  }
}

const resetFilters = () => {
  filters.value = {
    status: '',
    periode: '',
    per_page: 15,
  }
  loadSlipGaji()
}

const changePage = (page) => {
  loadSlipGaji(page)
}

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
  if (!confirm(`Apakah Anda yakin ingin menyetujui slip gaji ${item.karyawan?.nama_lengkap_karyawan}?`)) {
    return
  }

  try {
    const response = await keuanganService.approveSlipGaji(item.id)
    if (response.success) {
      alert('Slip gaji berhasil disetujui')
      loadSlipGaji()
    } else {
      alert(response.message || 'Gagal menyetujui slip gaji')
    }
  } catch (err) {
    console.error('Error approving slip gaji:', err)
    alert(err.response?.data?.message || 'Gagal menyetujui slip gaji')
  }
}

const markAsPaid = async (item) => {
  if (!confirm(`Apakah Anda yakin slip gaji ${item.karyawan?.nama_lengkap_karyawan} sudah dibayarkan?`)) {
    return
  }

  try {
    const response = await keuanganService.markSlipGajiAsPaid(item.id)
    if (response.success) {
      alert('Slip gaji berhasil ditandai sebagai dibayar')
      loadSlipGaji()
    } else {
      alert(response.message || 'Gagal menandai slip gaji')
    }
  } catch (err) {
    console.error('Error marking slip gaji as paid:', err)
    alert(err.response?.data?.message || 'Gagal menandai slip gaji')
  }
}

const deleteSlipGaji = async (item) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus slip gaji ${item.karyawan?.nama_lengkap_karyawan}?`)) {
    return
  }

  try {
    const response = await keuanganService.deleteSlipGaji(item.id)
    if (response.success) {
      alert('Slip gaji berhasil dihapus')
      loadSlipGaji()
    } else {
      alert(response.message || 'Gagal menghapus slip gaji')
    }
  } catch (err) {
    console.error('Error deleting slip gaji:', err)
    alert(err.response?.data?.message || 'Gagal menghapus slip gaji')
  }
}

const downloadPdf = (item) => {
  const baseUrl = import.meta.env.VITE_API_BASE_URL
  window.open(`${baseUrl}/storage/${item.file_path}`, '_blank')
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
      response = await keuanganService.updateSlipGaji(editingId.value, payload)
    } else {
      response = await keuanganService.createSlipGaji(payload)
    }

    if (response.success) {
      alert(response.message || 'Slip gaji berhasil disimpan')
      closeModal()
      loadSlipGaji()
    } else {
      alert(response.message || 'Gagal menyimpan slip gaji')
    }
  } catch (err) {
    console.error('Error submitting form:', err)
    alert(err.response?.data?.message || 'Gagal menyimpan slip gaji')
  } finally {
    submitting.value = false
  }
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  editingId.value = null
  form.value = {
    karyawan_id: '',
    periode: '',
    gaji_pokok: 0,
    tunjangan: 0,
    potongan: 0,
    keterangan: '',
  }
}

const calculateTotal = () => {
  const gaji = parseFloat(form.value.gaji_pokok) || 0
  const tunjangan = parseFloat(form.value.tunjangan) || 0
  const potongan = parseFloat(form.value.potongan) || 0
  return gaji + tunjangan - potongan
}

const formatCurrency = (amount) => {
  return keuanganService.formatCurrency(amount)
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
    DRAFT: 'status-secondary',
    APPROVED: 'status-pending',
    PAID: 'status-success',
  }
  return classes[status] || ''
}

onMounted(() => {
  loadSlipGaji()
  loadKaryawanList()
})
</script>

<style scoped>
/* Same styles as TagihanSppView.vue with minor adjustments */
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

.employee-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.employee-info strong {
  color: #1e293b;
}

.employee-info small {
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

.status-secondary {
  background: #f1f5f9;
  color: #64748b;
}

.action-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
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
  flex-shrink: 0;
}

.btn-icon:hover {
  background: #3b82f6;
  color: white;
}

.btn-icon.btn-primary:hover {
  background: #3b82f6;
  color: white;
}

.btn-icon.btn-success:hover {
  background: #10b981;
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
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.9rem;
  font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #3b82f6;
}

.total-preview {
  background: #f8fafc;
  padding: 16px;
  border-radius: 8px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  border: 2px solid #e2e8f0;
}

.total-preview strong {
  color: #1e293b;
  font-size: 1rem;
}

.total-preview span {
  color: #3b82f6;
  font-size: 1.1rem;
  font-weight: 700;
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

  .action-buttons {
    justify-content: flex-start;
  }
}
</style>
