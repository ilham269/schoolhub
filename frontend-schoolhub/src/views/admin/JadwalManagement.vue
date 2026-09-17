<template>
  <DashboardLayout title="Kelola Jadwal" role-label="Admin" :navigation="navigation">
    <section class="welcome">
      <div>
        <span class="eyebrow-dot dark">Manajemen akademik</span>
        <h2>Kelola Jadwal Pelajaran</h2>
        <p>Atur jadwal pelajaran mingguan untuk setiap kelas.</p>
      </div>
      <button class="btn-add" @click="openAddModal">
        <i class="fas fa-plus"></i> Tambah Jadwal
      </button>
    </section>

    <!-- Filter -->
    <section class="filter-bar">
      <div class="filter-item">
        <label>Filter Kelas</label>
        <select v-model="filterKelas">
          <option value="">Semua Kelas</option>
          <option v-for="k in kelasList" :key="k.id" :value="k.id">
            {{ kelasLabel(k) }}
          </option>
        </select>
      </div>
      <div class="filter-item">
        <label>Filter Hari</label>
        <select v-model="filterHari">
          <option value="">Semua Hari</option>
          <option v-for="h in hariOptions" :key="h" :value="h">{{ h }}</option>
        </select>
      </div>
    </section>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <i class="fas fa-spinner fa-spin"></i>
      <p>Memuat data jadwal...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="error-state">
      <i class="fas fa-exclamation-circle"></i>
      <p>{{ error }}</p>
      <button class="btn-retry" @click="loadAll">
        <i class="fas fa-redo"></i> Coba Lagi
      </button>
    </div>

    <!-- Table -->
    <section v-else class="table-wrap">
      <table class="data-table" v-if="filteredJadwal.length">
        <thead>
          <tr>
            <th>Kelas</th>
            <th>Hari</th>
            <th>Jam</th>
            <th>Mapel</th>
            <th>Guru</th>
            <th>Ruang</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="j in filteredJadwal" :key="j.id">
            <td>{{ j.kelas_name || kelasLabelById(j.kelas_id) }}</td>
            <td>{{ j.hari }}</td>
            <td>{{ formatJam(j.jam_mulai) }} - {{ formatJam(j.jam_selesai) }}</td>
            <td>{{ j.mapel_name || '-' }}</td>
            <td>{{ j.guru_name || '-' }}</td>
            <td>{{ j.ruang || '-' }}</td>
            <td class="actions">
              <button class="btn-icon" title="Ubah" @click="openEditModal(j)">
                <i class="fas fa-pen"></i>
              </button>
              <button class="btn-icon danger" title="Hapus" @click="confirmDelete(j)">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-else class="empty-state">
        <i class="fas fa-calendar-xmark"></i>
        <p>Belum ada jadwal{{ filterKelas ? ' untuk kelas ini' : '' }}.</p>
      </div>
    </section>

    <!-- Modal Tambah/Ubah -->
    <div v-if="showFormModal" class="modal-overlay" @click.self="closeFormModal">
      <div class="modal-box">
        <h3>{{ isEditing ? 'Ubah Jadwal' : 'Tambah Jadwal' }}</h3>

        <form @submit.prevent="submitForm">
          <div class="form-group">
            <label>Kelas</label>
            <select v-model="form.kelas_id" required>
              <option value="" disabled>Pilih kelas</option>
              <option v-for="k in kelasList" :key="k.id" :value="k.id">
                {{ kelasLabel(k) }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Mata Pelajaran</label>
            <select v-model="form.mapel_id" required>
              <option value="" disabled>Pilih mapel</option>
              <option v-for="m in mapelList" :key="m.id" :value="m.id">
                {{ m.nama_mapel }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Guru</label>
            <select v-model="form.guru_id" required>
              <option value="" disabled>Pilih guru</option>
              <option v-for="g in guruList" :key="g.id" :value="g.id">
                {{ guruLabel(g) }}
              </option>
            </select>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Hari</label>
              <select v-model="form.hari" required>
                <option value="" disabled>Pilih hari</option>
                <option v-for="h in hariOptions" :key="h" :value="h">{{ h }}</option>
              </select>
            </div>
            <div class="form-group">
              <label>Ruang</label>
              <input type="text" v-model="form.ruang" placeholder="mis. R.101" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Jam Mulai</label>
              <input type="time" v-model="form.jam_mulai" required />
            </div>
            <div class="form-group">
              <label>Jam Selesai</label>
              <input type="time" v-model="form.jam_selesai" required />
            </div>
          </div>

          <p v-if="formError" class="form-error">{{ formError }}</p>

          <div class="modal-actions">
            <button type="button" class="btn-secondary" @click="closeFormModal">Batal</button>
            <button type="submit" class="btn-primary" :disabled="submitting">
              <i v-if="submitting" class="fas fa-spinner fa-spin"></i>
              {{ isEditing ? 'Simpan Perubahan' : 'Tambah Jadwal' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Hapus -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal-box small">
        <h3>Hapus Jadwal?</h3>
        <p>
          Jadwal <strong>{{ deleteTarget?.hari }}, {{ formatJam(deleteTarget?.jam_mulai) }}</strong>
          untuk kelas <strong>{{ kelasLabelById(deleteTarget?.kelas_id) }}</strong> akan dihapus permanen.
        </p>
        <div class="modal-actions">
          <button class="btn-secondary" @click="showDeleteModal = false">Batal</button>
          <button class="btn-danger" :disabled="deleting" @click="doDelete">
            <i v-if="deleting" class="fas fa-spinner fa-spin"></i>
            Hapus
          </button>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'
import api from '../../utils/api'

const adminItems = [
  ['Kelola Siswa', 'fas fa-user-graduate', 'murid'],
  ['Kelola Guru', 'fas fa-chalkboard-user', 'guru'],
  ['Kelola Karyawan', 'fas fa-id-card', 'karyawan'],
  ['Kelola Kelas', 'fas fa-school', 'kelas'],
  ['Kelola Jadwal', 'fas fa-calendar-days', 'jadwal'],
  ['Kelola Pendaftaran', 'fas fa-clipboard-list', 'pendaftaran'],
  ['Kelola Berita', 'fas fa-newspaper', 'berita'],
  ['Kelola Pengumuman', 'fas fa-bullhorn', 'pengumuman'],
  ['Kelola Keuangan', 'fas fa-wallet', 'keuangan'],
]

const navigation = [
  { label: 'Dashboard', icon: 'fas fa-chart-pie', to: '/dashboard/admin' },
  ...adminItems.map(([label, icon, slug]) => ({ label, icon, to: `/dashboard/admin/${slug}` })),
]

const hariOptions = ['Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu', 'Minggu']

const loading = ref(true)
const error = ref(null)

const jadwalList = ref([])
const kelasList = ref([])
const guruList = ref([])
const mapelList = ref([])

const filterKelas = ref('')
const filterHari = ref('')

const showFormModal = ref(false)
const isEditing = ref(false)
const submitting = ref(false)
const formError = ref('')
const editingId = ref(null)

const showDeleteModal = ref(false)
const deleteTarget = ref(null)
const deleting = ref(false)

const emptyForm = () => ({
  kelas_id: '',
  mapel_id: '',
  guru_id: '',
  hari: '',
  jam_mulai: '',
  jam_selesai: '',
  ruang: '',
})

const form = ref(emptyForm())

// --- Label helpers (sesuaikan nama field kalau berbeda dengan skema kamu) ---
const kelasLabel = (k) => k?.name || k?.nama_kelas || k?.nama || `Kelas #${k?.id ?? ''}`
const guruLabel = (g) => g?.nama_guru || g?.nama_lengkap || g?.nama || `Guru #${g?.id ?? ''}`
const kelasLabelById = (id) => {
  const k = kelasList.value.find((x) => x.id === id)
  return k ? kelasLabel(k) : '-'
}
const formatJam = (t) => (t ? t.toString().slice(0, 5) : '-')

const filteredJadwal = computed(() => {
  return jadwalList.value.filter((j) => {
    const matchKelas = !filterKelas.value || j.kelas_id === filterKelas.value
    const matchHari = !filterHari.value || j.hari === filterHari.value
    return matchKelas && matchHari
  })
})

const loadAll = async () => {
  loading.value = true
  error.value = null
  try {
    const [jadwalRes, kelasRes, guruRes, mapelRes] = await Promise.all([
      api.get('/jadwal'),
      api.get('/kelas'),
      api.get('/guru'),
      api.get('/mapel'),
    ])

    jadwalList.value = jadwalRes.data?.data ?? []
    kelasList.value = kelasRes.data?.data ?? []
    guruList.value = guruRes.data?.data ?? []
    mapelList.value = mapelRes.data?.data ?? []
  } catch (err) {
    console.error('Error loading jadwal:', err)
    error.value = err.response?.data?.message || 'Gagal memuat data jadwal. Silakan coba lagi.'
  } finally {
    loading.value = false
  }
}

const openAddModal = () => {
  isEditing.value = false
  editingId.value = null
  formError.value = ''
  form.value = emptyForm()
  showFormModal.value = true
}

const openEditModal = (j) => {
  isEditing.value = true
  editingId.value = j.id
  formError.value = ''
  form.value = {
    kelas_id: j.kelas_id,
    mapel_id: j.mapel_id,
    guru_id: j.guru_id,
    hari: j.hari,
    jam_mulai: formatJam(j.jam_mulai),
    jam_selesai: formatJam(j.jam_selesai),
    ruang: j.ruang || '',
  }
  showFormModal.value = true
}

const closeFormModal = () => {
  showFormModal.value = false
}

const submitForm = async () => {
  formError.value = ''

  if (form.value.jam_selesai <= form.value.jam_mulai) {
    formError.value = 'Jam selesai harus setelah jam mulai.'
    return
  }

  submitting.value = true
  try {
    if (isEditing.value) {
      await api.put(`/jadwal/${editingId.value}`, form.value)
    } else {
      await api.post('/jadwal', form.value)
    }
    showFormModal.value = false
    await loadAll()
  } catch (err) {
    console.error('Error saving jadwal:', err)
    formError.value = err.response?.data?.message || 'Gagal menyimpan jadwal. Cek kembali data yang diisi.'
  } finally {
    submitting.value = false
  }
}

const confirmDelete = (j) => {
  deleteTarget.value = j
  showDeleteModal.value = true
}

const doDelete = async () => {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await api.delete(`/jadwal/${deleteTarget.value.id}`)
    showDeleteModal.value = false
    deleteTarget.value = null
    await loadAll()
  } catch (err) {
    console.error('Error deleting jadwal:', err)
    error.value = err.response?.data?.message || 'Gagal menghapus jadwal.'
    showDeleteModal.value = false
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  loadAll()
})
</script>

<style scoped>
.welcome {
  background: linear-gradient(110deg, #06231a, #1c9c5f);
  padding: 27px 30px;
  border-radius: 18px;
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

.btn-add {
  background: #8fd94a;
  color: #06231a;
  border: none;
  padding: 12px 20px;
  border-radius: 10px;
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

.filter-bar {
  display: flex;
  gap: 16px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.filter-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.filter-item label {
  font-size: 0.78rem;
  color: #64748b;
  font-weight: 600;
}

.filter-item select {
  padding: 9px 14px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  min-width: 180px;
  font-size: 0.88rem;
}

.table-wrap {
  background: #fff;
  border-radius: 14px;
  overflow-x: auto;
  border: 1px solid #eef2f0;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 13px 16px;
  text-align: left;
  font-size: 0.86rem;
  border-bottom: 1px solid #f1f5f4;
  white-space: nowrap;
}

.data-table th {
  background: #f7faf8;
  color: #475569;
  font-weight: 600;
}

.actions {
  display: flex;
  gap: 8px;
}

.btn-icon {
  border: none;
  background: #f1f5f4;
  color: #334155;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-icon:hover {
  background: #e2e8f0;
}

.btn-icon.danger:hover {
  background: #fee2e2;
  color: #dc2626;
}

.loading-state,
.error-state,
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #64748b;
}

.loading-state i,
.empty-state i {
  font-size: 2.5rem;
  margin-bottom: 16px;
  color: #3b82f6;
}

.empty-state i {
  color: #94a3b8;
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
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(6, 35, 26, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  padding: 20px;
}

.modal-box {
  background: #fff;
  border-radius: 16px;
  padding: 26px 28px;
  width: 100%;
  max-width: 480px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-box.small {
  max-width: 400px;
}

.modal-box h3 {
  margin: 0 0 18px;
  font-size: 1.1rem;
  color: #06231a;
}

.form-group {
  margin-bottom: 14px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  flex: 1;
}

.form-group label {
  font-size: 0.82rem;
  font-weight: 600;
  color: #475569;
}

.form-group select,
.form-group input {
  padding: 10px 12px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  font-size: 0.9rem;
}

.form-row {
  display: flex;
  gap: 12px;
}

.form-error {
  color: #dc2626;
  font-size: 0.82rem;
  margin: -4px 0 12px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.btn-secondary {
  background: #f1f5f4;
  color: #334155;
  border: none;
  padding: 10px 18px;
  border-radius: 8px;
  cursor: pointer;
}

.btn-primary {
  background: #1c9c5f;
  color: #fff;
  border: none;
  padding: 10px 18px;
  border-radius: 8px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-danger {
  background: #dc2626;
  color: #fff;
  border: none;
  padding: 10px 18px;
  border-radius: 8px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-danger:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
</style>
