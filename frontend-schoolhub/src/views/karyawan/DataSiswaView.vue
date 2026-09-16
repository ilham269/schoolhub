<template>
  <DashboardLayout title="Data Siswa" role-label="Karyawan" :navigation="navigation">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1>Data Siswa</h1>
        <p>Kelola data siswa sekolah</p>
      </div>
      <button @click="showCreateModal = true" class="btn-primary">
        <i class="fas fa-plus"></i> Tambah Siswa
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid" v-if="!loading && stats">
      <div class="stat-card">
        <div class="stat-icon blue">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ stats.total }}</div>
          <div class="stat-label">Total Siswa</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green">
          <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ stats.active }}</div>
          <div class="stat-label">Siswa Aktif</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon amber">
          <i class="fas fa-mars"></i>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ stats.male }}</div>
          <div class="stat-label">Laki-laki</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon pink">
          <i class="fas fa-venus"></i>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ stats.female }}</div>
          <div class="stat-label">Perempuan</div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filter-group">
        <label>Kelas</label>
        <select v-model="filters.kelas_id" @change="loadSiswa">
          <option value="">Semua Kelas</option>
          <option v-for="kelas in kelasList" :key="kelas.id" :value="kelas.id">
            {{ kelas.name }}
          </option>
        </select>
      </div>

      <div class="filter-group">
        <label>Gender</label>
        <select v-model="filters.gender" @change="loadSiswa">
          <option value="">Semua</option>
          <option value="L">Laki-laki</option>
          <option value="P">Perempuan</option>
        </select>
      </div>

      <div class="filter-group">
        <label>Cari</label>
        <input
          type="text"
          v-model="filters.search"
          placeholder="Nama atau NIS..."
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
      <p>Memuat data siswa...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <i class="fas fa-exclamation-circle"></i>
      <p>{{ error }}</p>
      <button @click="loadSiswa" class="btn-retry">Coba Lagi</button>
    </div>

    <!-- Data Table -->
    <div v-else class="table-card">
      <div class="table-header">
        <h3>Daftar Siswa</h3>
        <span class="record-count">{{ filteredSiswa.length }} siswa</span>
      </div>

      <!-- Empty State -->
      <div v-if="!filteredSiswa || filteredSiswa.length === 0" class="empty-state">
        <i class="fas fa-inbox"></i>
        <p>Tidak ada siswa ditemukan</p>
      </div>

      <!-- Table -->
      <div v-else class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>NIS</th>
              <th>Nama Lengkap</th>
              <th>Kelas</th>
              <th>Gender</th>
              <th>Tempat, Tanggal Lahir</th>
              <th>No. Telepon</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="siswa in filteredSiswa" :key="siswa.id">
              <td>
                <span class="nis-badge">{{ siswa.nis }}</span>
              </td>
              <td>
                <div class="student-info">
                  <strong>{{ siswa.Nama_lengkap_murid }}</strong>
                  <small>{{ siswa.user?.email }}</small>
                </div>
              </td>
              <td>{{ siswa.kelas?.name || '-' }}</td>
              <td>
                <span class="gender-badge" :class="siswa.gender === 'L' ? 'male' : 'female'">
                  <i :class="siswa.gender === 'L' ? 'fas fa-mars' : 'fas fa-venus'"></i>
                  {{ siswa.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                </span>
              </td>
              <td>
                <div class="birth-info">
                  <div>{{ siswa.tanggal_lahir.tempat_lahir || '-' }}</div>
                  <small>{{ formatDate(siswa.tanggal_lahir) }}</small>
                </div>
              </td>
              <td>{{ siswa.nomor_telepon || '-' }}</td>
              <td>
                <div class="action-buttons">
                  <button @click="viewDetail(siswa)" class="btn-icon" title="Lihat Detail">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button @click="editSiswa(siswa)" class="btn-icon" title="Edit">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button @click="deleteSiswa(siswa)" class="btn-icon btn-danger" title="Hapus">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="showDetailModal" class="modal-overlay" @click.self="closeDetailModal">
      <div class="modal-content modal-large">
        <div class="modal-header">
          <h2>Detail Siswa</h2>
          <button @click="closeDetailModal" class="btn-close">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="modal-body" v-if="selectedSiswa">
          <div class="detail-grid">
            <!-- Personal Info -->
            <div class="detail-section">
              <h3><i class="fas fa-user"></i> Data Pribadi</h3>
              <div class="detail-row">
                <span class="detail-label">NIS:</span>
                <span class="detail-value">{{ selectedSiswa.nis }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Nama Lengkap:</span>
                <span class="detail-value">{{ selectedSiswa.Nama_lengkap_murid }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-value">{{ selectedSiswa.user?.email }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Gender:</span>
                <span class="detail-value">{{ selectedSiswa.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Tempat Lahir:</span>
                <span class="detail-value">{{ selectedSiswa.tempat_lahir || '-' }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Tanggal Lahir:</span>
                <span class="detail-value">{{ formatDate(selectedSiswa.tanggal_lahir) }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Agama:</span>
                <span class="detail-value">{{ selectedSiswa.agama || '-' }}</span>
              </div>
            </div>

            <!-- Academic Info -->
            <div class="detail-section">
              <h3><i class="fas fa-school"></i> Data Akademik</h3>
              <div class="detail-row">
                <span class="detail-label">Kelas:</span>
                <span class="detail-value">{{ selectedSiswa.kelas?.name || '-' }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Hobi:</span>
                <span class="detail-value">{{ selectedSiswa.hobi || '-' }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Cita-cita:</span>
                <span class="detail-value">{{ selectedSiswa.cita_cita || '-' }}</span>
              </div>
            </div>

            <!-- Contact Info -->
            <div class="detail-section">
              <h3><i class="fas fa-address-book"></i> Kontak</h3>
              <div class="detail-row">
                <span class="detail-label">Alamat:</span>
                <span class="detail-value">{{ selectedSiswa.alamat || '-' }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">No. Telepon:</span>
                <span class="detail-value">{{ selectedSiswa.nomor_telepon || '-' }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">No. Telepon Ortu:</span>
                <span class="detail-value">{{ selectedSiswa.nomor_telepon_ortu || '-' }}</span>
              </div>
            </div>

            <!-- Parent Info -->
            <div class="detail-section">
              <h3><i class="fas fa-users"></i> Data Orang Tua</h3>
              <div class="detail-row">
                <span class="detail-label">Nama Ayah:</span>
                <span class="detail-value">{{ selectedSiswa.nama_ayah || '-' }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Pekerjaan Ayah:</span>
                <span class="detail-value">{{ selectedSiswa.pekerjaan_ayah || '-' }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Nama Ibu:</span>
                <span class="detail-value">{{ selectedSiswa.nama_ibu || '-' }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Pekerjaan Ibu:</span>
                <span class="detail-value">{{ selectedSiswa.pekerjaan_ibu || '-' }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Anak Ke:</span>
                <span class="detail-value">{{ selectedSiswa.anak_ke || '-' }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Jumlah Saudara:</span>
                <span class="detail-value">{{ selectedSiswa.jumlah_saudara || '-' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content modal-large">
        <div class="modal-header">
          <h2>{{ showEditModal ? 'Edit Siswa' : 'Tambah Siswa Baru' }}</h2>
          <button @click="closeModal" class="btn-close">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <form @submit.prevent="submitForm" class="modal-body">
          <!-- Personal Info Section -->
          <div class="form-section">
            <h3><i class="fas fa-user"></i> Data Pribadi</h3>
            <div class="form-grid">
              <div class="form-group">
                <label>NIS <span class="required">*</span></label>
                <input type="text" v-model="form.nis" required :disabled="showEditModal" />
              </div>

              <div class="form-group">
                <label>Nama Lengkap <span class="required">*</span></label>
                <input type="text" v-model="form.nama_lengkap_murid" required />
              </div>

              <div class="form-group">
                <label>Email <span class="required">*</span></label>
                <input type="email" v-model="form.email" required />
              </div>

              <div class="form-group" v-if="!showEditModal">
                <label>Password <span class="required">*</span></label>
                <input type="password" v-model="form.password" :required="!showEditModal" />
                <small>Min. 8 karakter</small>
              </div>

              <div class="form-group">
                <label>Gender <span class="required">*</span></label>
                <select v-model="form.gender" required>
                  <option value="">Pilih Gender</option>
                  <option value="L">Laki-laki</option>
                  <option value="P">Perempuan</option>
                </select>
              </div>

              <div class="form-group">
                <label>Kelas <span class="required">*</span></label>
                <select v-model="form.kelas_id" required>
                  <option value="">Pilih Kelas</option>
                  <option v-for="kelas in kelasList" :key="kelas.id" :value="kelas.id">
                    {{ kelas.name }}
                  </option>
                </select>
              </div>

              <div class="form-group">
                <label>Tempat Lahir</label>
                <input type="text" v-model="form.tempat_lahir" />
              </div>

              <div class="form-group">
                <label>Tanggal Lahir <span class="required">*</span></label>
                <input type="date" v-model="form.tanggal_lahir" required />
              </div>

              <div class="form-group">
                <label>Agama</label>
                <select v-model="form.agama">
                  <option value="">Pilih Agama</option>
                  <option value="Islam">Islam</option>
                  <option value="Kristen">Kristen</option>
                  <option value="Katolik">Katolik</option>
                  <option value="Hindu">Hindu</option>
                  <option value="Buddha">Buddha</option>
                  <option value="Konghucu">Konghucu</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Contact Section -->
          <div class="form-section">
            <h3><i class="fas fa-address-book"></i> Kontak</h3>
            <div class="form-grid">
              <div class="form-group full-width">
                <label>Alamat <span class="required">*</span></label>
                <textarea v-model="form.alamat" rows="3" required></textarea>
              </div>

              <div class="form-group">
                <label>No. Telepon <span class="required">*</span></label>
                <input type="tel" v-model="form.nomor_telepon" required />
              </div>

              <div class="form-group">
                <label>No. Telepon Orang Tua</label>
                <input type="tel" v-model="form.nomor_telepon_ortu" />
              </div>
            </div>
          </div>

          <!-- Parent Info Section -->
          <div class="form-section">
            <h3><i class="fas fa-users"></i> Data Orang Tua</h3>
            <div class="form-grid">
              <div class="form-group">
                <label>Nama Ayah</label>
                <input type="text" v-model="form.nama_ayah" />
              </div>

              <div class="form-group">
                <label>Pekerjaan Ayah</label>
                <input type="text" v-model="form.pekerjaan_ayah" />
              </div>

              <div class="form-group">
                <label>Nama Ibu</label>
                <input type="text" v-model="form.nama_ibu" />
              </div>

              <div class="form-group">
                <label>Pekerjaan Ibu</label>
                <input type="text" v-model="form.pekerjaan_ibu" />
              </div>

              <div class="form-group">
                <label>Anak Ke</label>
                <input type="number" v-model="form.anak_ke" min="1" />
              </div>

              <div class="form-group">
                <label>Jumlah Saudara</label>
                <input type="number" v-model="form.jumlah_saudara" min="0" />
              </div>
            </div>
          </div>

          <!-- Additional Info -->
          <div class="form-section">
            <h3><i class="fas fa-info-circle"></i> Informasi Tambahan</h3>
            <div class="form-grid">
              <div class="form-group">
                <label>Hobi</label>
                <input type="text" v-model="form.hobi" />
              </div>

              <div class="form-group">
                <label>Cita-cita</label>
                <input type="text" v-model="form.cita_cita" />
              </div>
            </div>
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
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'
import api from '../../utils/api'

const navigation = [
  { label: 'Dashboard', icon: 'fas fa-chart-pie', to: '/dashboard/karyawan' },
  { label: 'Data Siswa', icon: 'fas fa-user-graduate', to: '/dashboard/karyawan/data-siswa', active: true },
  { label: 'Keuangan', icon: 'fas fa-wallet', to: '/dashboard/karyawan/keuangan' },
]

const loading = ref(true)
const error = ref(null)
const submitting = ref(false)
const siswaList = ref([])
const kelasList = ref([])

const filters = ref({
  kelas_id: '',
  gender: '',
  search: '',
})

const showDetailModal = ref(false)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const selectedSiswa = ref(null)
const editingId = ref(null)

const form = ref({
  nis: '',
  nama_lengkap_murid: '',
  email: '',
  password: '',
  gender: '',
  kelas_id: '',
  tempat_lahir: '',
  tanggal_lahir: '',
  agama: '',
  alamat: '',
  nomor_telepon: '',
  nomor_telepon_ortu: '',
  nama_ayah: '',
  pekerjaan_ayah: '',
  nama_ibu: '',
  pekerjaan_ibu: '',
  anak_ke: null,
  jumlah_saudara: null,
  hobi: '',
  cita_cita: '',
})

let searchTimeout = null

const stats = computed(() => {
  if (!siswaList.value.length) return null
  
  return {
    total: siswaList.value.length,
    active: siswaList.value.filter(s => s.user?.is_active).length,
    male: siswaList.value.filter(s => s.gender === 'L').length,
    female: siswaList.value.filter(s => s.gender === 'P').length,
  }
})

const filteredSiswa = computed(() => {
  let result = siswaList.value

  if (filters.value.kelas_id) {
    result = result.filter(s => s.kelas_id === parseInt(filters.value.kelas_id))
  }

  if (filters.value.gender) {
    result = result.filter(s => s.gender === filters.value.gender)
  }

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    result = result.filter(
      s =>
        s.Nama_lengkap_murid?.toLowerCase().includes(search) ||
        s.nis?.toLowerCase().includes(search)
    )
  }

  return result
})

const loadSiswa = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await api.get('/murid')

    if (response.data.success) {
      siswaList.value = response.data.data
    } else {
      error.value = response.data.message || 'Gagal memuat data siswa'
    }
  } catch (err) {
    console.error('Error loading siswa:', err)
    if (err.response?.status === 401) {
      error.value = 'Sesi Anda telah berakhir. Silakan login kembali.'
      setTimeout(() => {
        sessionStorage.clear()
        window.location.href = '/login'
      }, 2000)
    } else {
      error.value = err.response?.data?.message || 'Gagal memuat data siswa'
    }
  } finally {
    loading.value = false
  }
}

const loadKelas = async () => {
  try {
    const response = await api.get('/kelas')
    if (response.data.success) {
      kelasList.value = response.data.data
    }
  } catch (err) {
    console.error('Error loading kelas:', err)
    if (err.response?.status === 401) {
      // Token expired, akan redirect otomatis
      return
    }
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    // Filtering handled by computed property
  }, 300)
}

const resetFilters = () => {
  filters.value = {
    kelas_id: '',
    gender: '',
    search: '',
  }
}

const viewDetail = (siswa) => {
  selectedSiswa.value = siswa
  showDetailModal.value = true
}

const editSiswa = (siswa) => {
  editingId.value = siswa.id
  form.value = {
    nis: siswa.nis,
    nama_lengkap_murid: siswa.Nama_lengkap_murid,
    email: siswa.user?.email,
    password: '',
    gender: siswa.gender,
    kelas_id: siswa.kelas_id,
    tempat_lahir: siswa.tempat_lahir,
    tanggal_lahir: siswa.tanggal_lahir,
    agama: siswa.agama,
    alamat: siswa.alamat,
    nomor_telepon: siswa.nomor_telepon,
    nomor_telepon_ortu: siswa.nomor_telepon_ortu,
    nama_ayah: siswa.nama_ayah,
    pekerjaan_ayah: siswa.pekerjaan_ayah,
    nama_ibu: siswa.nama_ibu,
    pekerjaan_ibu: siswa.pekerjaan_ibu,
    anak_ke: siswa.anak_ke,
    jumlah_saudara: siswa.jumlah_saudara,
    hobi: siswa.hobi,
    cita_cita: siswa.cita_cita,
  }
  showEditModal.value = true
}

const deleteSiswa = async (siswa) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus siswa ${siswa.Nama_lengkap_murid}?`)) {
    return
  }

  try {
    const response = await api.delete(`/murid/${siswa.id}`)
    if (response.data.success) {
      alert('Siswa berhasil dihapus')
      loadSiswa()
    } else {
      alert(response.data.message || 'Gagal menghapus siswa')
    }
  } catch (err) {
    console.error('Error deleting siswa:', err)
    alert(err.response?.data?.message || 'Gagal menghapus siswa')
  }
}

const submitForm = async () => {
  try {
    submitting.value = true

    let response
    if (showEditModal.value) {
      response = await api.put(`/murid/${editingId.value}`, form.value)
    } else {
      response = await api.post('/murid', form.value)
    }

    if (response.data.success) {
      alert(response.data.message || 'Siswa berhasil disimpan')
      closeModal()
      loadSiswa()
    } else {
      alert(response.data.message || 'Gagal menyimpan siswa')
    }
  } catch (err) {
    console.error('Error submitting form:', err)
    const errors = err.response?.data?.errors
    if (errors) {
      const errorMessages = Object.values(errors).flat().join('\n')
      alert(`Validasi gagal:\n${errorMessages}`)
    } else {
      alert(err.response?.data?.message || 'Gagal menyimpan siswa')
    }
  } finally {
    submitting.value = false
  }
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  editingId.value = null
  form.value = {
    nis: '',
    nama_lengkap_murid: '',
    email: '',
    password: '',
    gender: '',
    kelas_id: '',
    tempat_lahir: '',
    tanggal_lahir: '',
    agama: '',
    alamat: '',
    nomor_telepon: '',
    nomor_telepon_ortu: '',
    nama_ayah: '',
    pekerjaan_ayah: '',
    nama_ibu: '',
    pekerjaan_ibu: '',
    anak_ke: null,
    jumlah_saudara: null,
    hobi: '',
    cita_cita: '',
  }
}

const closeDetailModal = () => {
  showDetailModal.value = false
  selectedSiswa.value = null
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  })
}

onMounted(() => {
  loadSiswa()
  loadKelas()
})
</script>

<style scoped>
/* Continue in next message due to length... */
/* Page Header */
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

/* Buttons */
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

/* Stats Cards */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.stat-icon.blue {
  background: #dbeafe;
  color: #3b82f6;
}

.stat-icon.green {
  background: #d1fae5;
  color: #10b981;
}

.stat-icon.amber {
  background: #fef3c7;
  color: #f59e0b;
}

.stat-icon.pink {
  background: #fce7f3;
  color: #ec4899;
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  line-height: 1;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 0.85rem;
  color: #64748b;
}

/* Filters */
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

/* Loading & Error States */
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

/* Table */
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

.nis-badge {
  font-family: monospace;
  font-weight: 600;
  color: #3b82f6;
  background: #dbeafe;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.85rem;
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

.gender-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
}

.gender-badge.male {
  background: #dbeafe;
  color: #3b82f6;
}

.gender-badge.female {
  background: #fce7f3;
  color: #ec4899;
}

.birth-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.birth-info small {
  color: #64748b;
  font-size: 0.8rem;
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
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-content.modal-large {
  max-width: 900px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
  position: sticky;
  top: 0;
  background: white;
  z-index: 10;
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

/* Detail Grid */
.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
}

.detail-section {
  background: #f8fafc;
  padding: 20px;
  border-radius: 10px;
}

.detail-section h3 {
  font-size: 1rem;
  color: #1e293b;
  margin: 0 0 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.detail-section h3 i {
  color: #3b82f6;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid #e2e8f0;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-label {
  font-size: 0.85rem;
  color: #64748b;
  font-weight: 500;
}

.detail-value {
  font-size: 0.9rem;
  color: #1e293b;
  font-weight: 500;
  text-align: right;
}

/* Form */
.form-section {
  margin-bottom: 32px;
}

.form-section h3 {
  font-size: 1rem;
  color: #1e293b;
  margin: 0 0 16px;
  padding-bottom: 12px;
  border-bottom: 2px solid #e2e8f0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.form-section h3 i {
  color: #3b82f6;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #475569;
}

.required {
  color: #ef4444;
}

.form-group input,
.form-group select,
.form-group textarea {
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

.form-group small {
  font-size: 0.75rem;
  color: #64748b;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid #e2e8f0;
}

/* Responsive */
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

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .table-responsive {
    font-size: 0.8rem;
  }

  td,
  th {
    padding: 10px;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
