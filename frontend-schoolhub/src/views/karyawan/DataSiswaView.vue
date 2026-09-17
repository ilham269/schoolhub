<template>
  <DashboardLayout title="Data Siswa" role-label="Karyawan" :navigation="navigation">
    <!-- Header -->
    <section class="welcome">
      <div>
        <span class="eyebrow-dot dark">Manajemen akademik</span>
        <h2>Data Siswa</h2>
        <p>Kelola data siswa sekolah.</p>
      </div>
      <button @click="showCreateModal = true" class="btn-add">
        <i class="fas fa-plus"></i> Tambah Siswa
      </button>
    </section>

    <!-- Stats Cards -->
    <div class="summary-row" v-if="!loading && stats">
      <div class="summary-card total">
        <i class="fas fa-users"></i>
        <div>
          <b>{{ stats.total }}</b>
          <span>Total Siswa</span>
        </div>
      </div>
      <div class="summary-card active">
        <i class="fas fa-user-check"></i>
        <div>
          <b>{{ stats.active }}</b>
          <span>Siswa Aktif</span>
        </div>
      </div>
      <div class="summary-card male">
        <i class="fas fa-mars"></i>
        <div>
          <b>{{ stats.male }}</b>
          <span>Laki-laki</span>
        </div>
      </div>
      <div class="summary-card female">
        <i class="fas fa-venus"></i>
        <div>
          <b>{{ stats.female }}</b>
          <span>Perempuan</span>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <section class="filter-bar">
      <div class="filter-item">
        <label>Kelas</label>
        <select v-model="filters.kelas_id" @change="loadSiswa">
          <option value="">Semua Kelas</option>
          <option v-for="kelas in kelasList" :key="kelas.id" :value="kelas.id">
            {{ kelas.name }}
          </option>
        </select>
      </div>

      <div class="filter-item">
        <label>Gender</label>
        <select v-model="filters.gender" @change="loadSiswa">
          <option value="">Semua</option>
          <option value="L">Laki-laki</option>
          <option value="P">Perempuan</option>
        </select>
      </div>

      <div class="filter-item grow">
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
    </section>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <i class="fas fa-spinner fa-spin"></i>
      <p>Memuat data siswa...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <i class="fas fa-exclamation-circle"></i>
      <p>{{ error }}</p>
      <button @click="loadSiswa" class="btn-retry">
        <i class="fas fa-redo"></i> Coba Lagi
      </button>
    </div>

    <!-- Data Table -->
    <section v-else class="table-wrap">
      <div class="table-head">
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
        <table class="data-table">
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
                  <strong>{{ getNamaSiswa(siswa) }}</strong>
                  <small>{{ siswa.user?.email }}</small>
                </div>
              </td>
              <td>{{ getKelasLabel(siswa.kelas) }}</td>
              <td>
                <span class="gender-badge" :class="siswa.gender === 'L' ? 'male' : 'female'">
                  <i :class="siswa.gender === 'L' ? 'fas fa-mars' : 'fas fa-venus'"></i>
                  {{ siswa.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                </span>
              </td>
              <td>
                <div class="birth-info">
                  <div>{{ getTempatLahir(siswa) }}</div>
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
                    <i class="fas fa-pen"></i>
                  </button>
                  <button @click="deleteSiswa(siswa)" class="btn-icon danger" title="Hapus">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Detail Modal -->
    <div v-if="showDetailModal" class="modal-overlay" @click.self="closeDetailModal">
      <div class="modal-box modal-large">
        <div class="modal-header">
          <h3>Detail Siswa</h3>
          <button @click="closeDetailModal" class="btn-close">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="modal-body" v-if="selectedSiswa">
          <div class="detail-grid">
            <!-- Personal Info -->
            <div class="detail-section">
              <h4><i class="fas fa-user"></i> Data Pribadi</h4>
              <div class="detail-row">
                <span class="detail-label">NIS:</span>
                <span class="detail-value">{{ selectedSiswa.nis }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Nama Lengkap:</span>
                <span class="detail-value">{{ getNamaSiswa(selectedSiswa) }}</span>
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
                <span class="detail-value">{{ getTempatLahir(selectedSiswa) }}</span>
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
              <h4><i class="fas fa-school"></i> Data Akademik</h4>
              <div class="detail-row">
                <span class="detail-label">Kelas:</span>
                <span class="detail-value">{{ getKelasLabel(selectedSiswa.kelas) }}</span>
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
              <h4><i class="fas fa-address-book"></i> Kontak</h4>
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
              <h4><i class="fas fa-users"></i> Data Orang Tua</h4>
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
      <div class="modal-box modal-large">
        <div class="modal-header">
          <h3>{{ showEditModal ? 'Edit Siswa' : 'Tambah Siswa Baru' }}</h3>
          <button @click="closeModal" class="btn-close">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <form @submit.prevent="submitForm" class="modal-body">
          <!-- Personal Info Section -->
          <div class="form-section">
            <h4><i class="fas fa-user"></i> Data Pribadi</h4>
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
            <h4><i class="fas fa-address-book"></i> Kontak</h4>
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
            <h4><i class="fas fa-users"></i> Data Orang Tua</h4>
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
            <h4><i class="fas fa-info-circle"></i> Informasi Tambahan</h4>
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

const getNamaSiswa = (siswa) =>
  siswa?.nama_lengkap_murid ?? siswa?.Nama_lengkap_murid ?? siswa?.user?.name ?? '-'

const getKelasLabel = (kelas) => {
  if (!kelas) return '-'
  if (kelas.name) return kelas.name
  if (kelas.nama_kelas) return kelas.nama_kelas
  if (kelas.kelas) return `${kelas.kelas} ${kelas.nama_kelas || ''}`.trim()
  return '-'
}

const getTempatLahir = (siswa) => {
  if (!siswa) return '-'
  if (siswa.tempat_lahir) return siswa.tempat_lahir
  if (siswa.tanggal_lahir && typeof siswa.tanggal_lahir === 'object') {
    return siswa.tanggal_lahir.tempat_lahir || '-'
  }
  return '-'
}

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
    result = result.filter((s) => {
      const nama = getNamaSiswa(s).toLowerCase()
      return nama.includes(search) || s.nis?.toLowerCase().includes(search)
    })
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
    nama_lengkap_murid: getNamaSiswa(siswa),
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
  if (!confirm(`Apakah Anda yakin ingin menghapus siswa ${getNamaSiswa(siswa)}?`)) {
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
/* Semua warna & font di bawah memakai design tokens global situs
   (--forest-950, --leaf-500, --lime-400, dst dari main.css) supaya
   halaman ini konsisten dengan tema hijau/forest yang dipakai di
   seluruh bagian lain aplikasi. */

.welcome {
  background: linear-gradient(110deg, var(--forest-950), var(--leaf-600));
  padding: 27px 30px;
  border-radius: var(--radius-lg);
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  gap: 16px;
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

/* ---------- Summary cards ---------- */
.summary-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
  margin-bottom: 20px;
}
@media (max-width: 900px) {
  .summary-row {
    grid-template-columns: repeat(2, 1fr);
  }
}
.summary-card {
  background: var(--paper);
  border: 1px solid var(--line);
  border-radius: var(--radius-md);
  padding: 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: var(--shadow-card);
}
.summary-card i {
  font-size: 1.3rem;
  width: 46px;
  height: 46px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex: none;
}
.summary-card.total i {
  background: var(--cream);
  color: var(--forest-900);
}
.summary-card.active i {
  background: #e3f6ea;
  color: var(--leaf-600);
}
.summary-card.male i {
  background: #eaf1fd;
  color: #2563eb;
}
.summary-card.female i {
  background: #fce7f3;
  color: #db2777;
}
.summary-card b {
  display: block;
  font-family: var(--font-head);
  font-size: 1.4rem;
  color: var(--forest-950);
}
.summary-card span {
  font-size: 0.78rem;
  color: var(--muted);
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
}
.filter-item.grow {
  flex: 1;
  min-width: 200px;
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
  min-width: 160px;
  font-size: 0.88rem;
  font-family: var(--font-body);
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
  font-size: 2.5rem;
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
.data-table tbody tr:last-child td {
  border-bottom: 0;
}

.nis-badge {
  font-family: monospace;
  font-weight: 700;
  color: var(--leaf-600);
  background: #e3f6ea;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.82rem;
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

.gender-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 600;
  font-family: var(--font-head);
}
.gender-badge.male {
  background: #eaf1fd;
  color: #2563eb;
}
.gender-badge.female {
  background: #fce7f3;
  color: #db2777;
}

.birth-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.birth-info small {
  color: var(--muted);
  font-size: 0.78rem;
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
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 30px 60px -20px rgba(0, 0, 0, 0.4);
}
.modal-box.modal-large {
  max-width: 900px;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 26px;
  border-bottom: 1px solid var(--line);
  position: sticky;
  top: 0;
  background: var(--paper);
  z-index: 10;
}
.modal-header h3 {
  font-size: 1.05rem;
  margin: 0;
  color: var(--forest-950);
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
  padding: 26px;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
}
.detail-section {
  background: var(--cream);
  padding: 20px;
  border-radius: var(--radius-md);
}
.detail-section h4 {
  font-size: 0.95rem;
  margin: 0 0 14px;
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--forest-950);
}
.detail-section h4 i {
  color: var(--leaf-600);
}
.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 9px 0;
  border-bottom: 1px solid var(--line);
  gap: 12px;
}
.detail-row:last-child {
  border-bottom: none;
}
.detail-label {
  font-size: 0.82rem;
  color: var(--muted);
  font-weight: 500;
}
.detail-value {
  font-size: 0.88rem;
  color: var(--forest-950);
  font-weight: 600;
  text-align: right;
}

/* ---------- Form ---------- */
.form-section {
  margin-bottom: 28px;
}
.form-section h4 {
  font-size: 0.95rem;
  margin: 0 0 14px;
  padding-bottom: 10px;
  border-bottom: 2px solid var(--line);
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--forest-950);
}
.form-section h4 i {
  color: var(--leaf-600);
}
.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
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
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--forest-950);
  font-family: var(--font-head);
}
.required {
  color: var(--red);
}
.form-group input,
.form-group select,
.form-group textarea {
  padding: 10px 13px;
  border: 1.5px solid var(--line);
  border-radius: 8px;
  font-size: 0.9rem;
  font-family: var(--font-body);
  color: var(--ink);
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--leaf-500);
  box-shadow: 0 0 0 4px rgba(34, 181, 108, 0.14);
}
.form-group small {
  font-size: 0.74rem;
  color: var(--muted);
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
  padding-top: 20px;
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

/* ---------- Responsive ---------- */
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
  .summary-row {
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