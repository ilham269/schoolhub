<template>
  <DashboardLayout title="Kelola Tagihan SPP" role-label="Karyawan" :navigation="navigation">
    <!-- Header -->
    <section class="welcome">
      <div>
        <span class="eyebrow-dot dark">Keuangan Sekolah</span>
        <h2>Kelola Tagihan SPP</h2>
        <p>Kelola dan pantau pembayaran SPP siswa.</p>
      </div>
      <button @click="showCreateModal = true" class="btn-add">
        <i class="fas fa-plus"></i> Buat Tagihan
      </button>
    </section>

    <!-- Filters -->
    <section class="filter-bar">
      <div class="filter-item">
        <label>Status</label>
        <select v-model="filters.status" @change="loadTagihan">
          <option value="">Semua Status</option>
          <option value="UNPAID">Belum Bayar</option>
          <option value="PENDING">Pending</option>
          <option value="LUNAS">Lunas</option>
          <option value="EXPIRED">Kadaluarsa</option>
        </select>
      </div>

      <div class="filter-item">
        <label>Periode</label>
        <input type="month" v-model="filters.periode" @change="loadTagihan" />
      </div>

      <div class="filter-item grow">
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
    </section>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <i class="fas fa-spinner fa-spin"></i>
      <p>Memuat data tagihan...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <i class="fas fa-exclamation-circle"></i>
      <p>{{ error }}</p>
      <button @click="loadTagihan" class="btn-retry">
        <i class="fas fa-redo"></i> Coba Lagi
      </button>
    </div>

    <!-- Data Table -->
    <section v-else class="table-wrap">
      <div class="table-head">
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
        <table class="data-table">
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
                    <i class="fas fa-pen"></i>
                  </button>
                  <button
                    @click="deleteTagihan(item)"
                    class="btn-icon danger"
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
    </section>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-box">
        <div class="modal-header">
          <h3>{{ showEditModal ? 'Edit Tagihan SPP' : 'Buat Tagihan SPP' }}</h3>
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

          <div class="modal-actions">
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
  { label: 'Data Siswa', icon: 'fas fa-user-graduate', to: '/dashboard/karyawan/data-siswa' },
  { label: 'Keuangan', icon: 'fas fa-wallet', to: '/dashboard/karyawan/keuangan' },
  { label: 'Tagihan SPP', icon: 'fas fa-file-invoice', to: '/dashboard/karyawan/keuangan/tagihan', active: true },
  { label: 'Slip Gaji', icon: 'fas fa-money-check', to: '/dashboard/karyawan/keuangan/slip-gaji' },
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

.btn-add {
  background: var(--lime-400);
  color: var(--forest-950);
  border: none;
  padding: 12px 20px;
  border-radius: 10px;
  font-family: var(--font-head);
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: filter 0.2s;
}
.btn-add:hover {
  filter: brightness(1.05);
}

/* ---------- Filters ---------- */
.filter-bar {
  display: flex;
  gap: 16px;
  margin-bottom: 20px;
  flex-wrap: wrap;
  align-items: flex-end;
}
.filter-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 180px;
}
.filter-item.grow {
  flex: 1 1 220px;
}
.filter-item label {
  font-size: 0.78rem;
  color: var(--muted);
  font-weight: 600;
  font-family: var(--font-head);
}
.filter-item input,
.filter-item select {
  padding: 9px 14px;
  border-radius: 8px;
  border: 1.5px solid var(--line);
  font-size: 0.88rem;
  font-family: var(--font-body);
  background: var(--paper);
  color: var(--ink);
}
.filter-item input:focus,
.filter-item select:focus {
  border-color: var(--leaf-500);
  outline: none;
  box-shadow: 0 0 0 4px rgba(34, 181, 108, 0.14);
}

.btn-secondary {
  background: var(--cream);
  color: var(--forest-950);
  border: 1.5px solid var(--line);
  padding: 9px 18px;
  border-radius: 8px;
  cursor: pointer;
  font-family: var(--font-head);
  font-weight: 600;
  font-size: 0.85rem;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  height: fit-content;
}
.btn-secondary:hover {
  border-color: var(--leaf-500);
  color: var(--leaf-600);
}

/* ---------- Loading / Error ---------- */
.loading-state,
.error-state,
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: var(--muted);
}
.loading-state i {
  font-size: 2.5rem;
  margin-bottom: 16px;
  color: var(--leaf-500);
}
.error-state i {
  font-size: 2.5rem;
  margin-bottom: 16px;
  color: var(--red);
}
.empty-state i {
  font-size: 3rem;
  margin-bottom: 12px;
  color: var(--line);
}
.btn-retry {
  background: var(--leaf-500);
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 8px;
  cursor: pointer;
  margin-top: 12px;
  font-family: var(--font-head);
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.btn-retry:hover {
  background: var(--leaf-600);
}

/* ---------- Table ---------- */
.table-wrap {
  background: var(--paper);
  border-radius: var(--radius-md);
  overflow: hidden;
  border: 1px solid var(--line);
  box-shadow: var(--shadow-card);
}
.table-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 22px;
  border-bottom: 1px solid var(--line);
}
.table-head h3 {
  font-size: 1rem;
  margin: 0;
  color: var(--forest-950);
}
.record-count {
  background: var(--cream);
  color: var(--forest-900);
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 600;
  font-family: var(--font-head);
}

.table-responsive {
  overflow-x: auto;
}
.data-table {
  width: 100%;
  border-collapse: collapse;
}
.data-table th,
.data-table td {
  padding: 13px 18px;
  text-align: left;
  font-size: 0.86rem;
  border-bottom: 1px solid var(--line);
  white-space: nowrap;
}
.data-table thead th {
  background: var(--cream);
  font-family: var(--font-head);
  font-size: 0.76rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--forest-900);
}
.data-table tbody tr:hover {
  background: #f8fbf7;
}

.invoice-number {
  font-family: monospace;
  font-weight: 700;
  color: var(--leaf-600);
}

.student-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.student-info strong {
  color: var(--forest-950);
}
.student-info small {
  color: var(--muted);
  font-size: 0.78rem;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  display: inline-block;
  font-family: var(--font-head);
}
.status-success {
  background: #e3f6ea;
  color: var(--leaf-600);
}
.status-pending {
  background: #fdf1dc;
  color: #a9711f;
}
.status-warning {
  background: #fdeada;
  color: #b3540f;
}
.status-error {
  background: #fbe4e4;
  color: var(--red);
}

.action-buttons {
  display: flex;
  gap: 8px;
}
.btn-icon {
  border: none;
  background: var(--cream);
  color: var(--forest-900);
  width: 32px;
  height: 32px;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-icon:hover {
  background: var(--line);
}
.btn-icon.danger:hover {
  background: #fbe4e4;
  color: var(--red);
}

/* ---------- Pagination ---------- */
.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 22px;
  border-top: 1px solid var(--line);
}
.btn-page {
  background: var(--cream);
  color: var(--forest-950);
  border: 1.5px solid var(--line);
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.85rem;
  font-family: var(--font-head);
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
}
.btn-page:hover:not(:disabled) {
  border-color: var(--leaf-500);
  color: var(--leaf-600);
}
.btn-page:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.page-info {
  font-size: 0.85rem;
  color: var(--muted);
}

/* ---------- Modal ---------- */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(6, 20, 15, 0.55);
  backdrop-filter: blur(2px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 200;
  padding: 20px;
}
.modal-box {
  background: var(--paper);
  border-radius: var(--radius-lg);
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 30px 60px -20px rgba(0, 0, 0, 0.4);
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 26px;
  border-bottom: 1px solid var(--line);
}
.modal-header h3 {
  font-size: 1.05rem;
  color: var(--forest-950);
  margin: 0;
}
.btn-close {
  background: none;
  border: none;
  font-size: 1.1rem;
  color: var(--muted);
  cursor: pointer;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: background 0.2s;
}
.btn-close:hover {
  background: var(--cream);
}
.modal-body {
  padding: 24px 26px;
}

.form-group {
  margin-bottom: 18px;
}
.form-group label {
  display: block;
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--forest-950);
  margin-bottom: 6px;
  font-family: var(--font-head);
}
.required {
  color: var(--red);
}
.form-group input,
.form-group select {
  width: 100%;
  padding: 10px 13px;
  border: 1.5px solid var(--line);
  border-radius: 8px;
  font-size: 0.9rem;
  font-family: var(--font-body);
  color: var(--ink);
}
.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: var(--leaf-500);
  box-shadow: 0 0 0 4px rgba(34, 181, 108, 0.14);
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
  padding-top: 18px;
  border-top: 1px solid var(--line);
}
.btn-primary {
  background: var(--leaf-500);
  color: #fff;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-family: var(--font-head);
  font-weight: 600;
  font-size: 0.9rem;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: background 0.2s;
}
.btn-primary:hover {
  background: var(--leaf-600);
}
.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .welcome {
    flex-direction: column;
    align-items: flex-start;
  }
  .filter-bar {
    flex-direction: column;
    align-items: stretch;
  }
  .filter-item,
  .filter-item.grow {
    width: 100%;
    min-width: 0;
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