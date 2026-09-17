<template>
  <DashboardLayout title="Administrasi" role-label="Murid" :navigation="navigation">
    <section class="welcome">
      <div>
        <span class="eyebrow-dot dark">Administrasi Siswa</span>
        <h2>Absensi & Kehadiran</h2>
        <p>Pantau riwayat kehadiran, jam hadir, dan status absensi kamu.</p>
      </div>
      <i class="fas fa-clipboard-check"></i>
    </section>

    <!-- Filter bulan -->
    <section class="filter-bar">
      <div class="filter-item">
        <label>Bulan</label>
        <select v-model="filterBulan">
          <option value="">Semua Bulan</option>
          <option v-for="(nama, idx) in namaBulan" :key="idx" :value="idx + 1">
            {{ nama }}
          </option>
        </select>
      </div>
      <div class="filter-item">
        <label>Status</label>
        <select v-model="filterStatus">
          <option value="">Semua Status</option>
          <option value="hadir">Hadir</option>
          <option value="izin">Izin</option>
          <option value="sakit">Sakit</option>
          <option value="alpa">Alpa</option>
        </select>
      </div>
    </section>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <i class="fas fa-spinner fa-spin"></i>
      <p>Memuat data absensi...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="error-state">
      <i class="fas fa-exclamation-circle"></i>
      <p>{{ error }}</p>
      <button class="btn-retry" @click="loadAbsensi">
        <i class="fas fa-redo"></i> Coba Lagi
      </button>
    </div>

    <template v-else>
      <!-- Ringkasan -->
      <section class="summary-row">
        <div class="summary-card hadir">
          <i class="fas fa-circle-check"></i>
          <div>
            <b>{{ summary.hadir }}</b>
            <span>Hadir</span>
          </div>
        </div>
        <div class="summary-card izin">
          <i class="fas fa-file-lines"></i>
          <div>
            <b>{{ summary.izin }}</b>
            <span>Izin</span>
          </div>
        </div>
        <div class="summary-card sakit">
          <i class="fas fa-briefcase-medical"></i>
          <div>
            <b>{{ summary.sakit }}</b>
            <span>Sakit</span>
          </div>
        </div>
        <div class="summary-card alpa">
          <i class="fas fa-circle-xmark"></i>
          <div>
            <b>{{ summary.alpa }}</b>
            <span>Alpa</span>
          </div>
        </div>
        <div class="summary-card persen">
          <i class="fas fa-chart-pie"></i>
          <div>
            <b>{{ persentaseKehadiran }}%</b>
            <span>Tingkat Kehadiran</span>
          </div>
        </div>
      </section>

      <!-- Tabel absensi -->
      <section class="table-wrap">
        <table class="data-table" v-if="filteredAbsensi.length">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Status</th>
              <th>Jam Masuk</th>
              <th>Jam Pulang</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="a in filteredAbsensi" :key="a.id">
              <td>{{ formatTanggal(a.tanggal) }}</td>
              <td>
                <span class="status-badge" :class="a.status">{{ statusLabel(a.status) }}</span>
              </td>
              <td>{{ a.jam_masuk || '-' }}</td>
              <td>{{ a.jam_keluar || '-' }}</td>
              <td>{{ a.keterangan || '-' }}</td>
            </tr>
          </tbody>
        </table>
        <div v-else class="empty-state">
          <i class="fas fa-calendar-xmark"></i>
          <p>Belum ada data absensi{{ filterBulan ? ' untuk bulan ini' : '' }}.</p>
        </div>
      </section>
    </template>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'
import api from '../../utils/api'

const muridItems = [
  ['Profil', 'fas fa-id-badge', 'profil'],
  ['Tugas', 'fas fa-list-check', 'tugas'],
  ['Nilai', 'fas fa-star', 'nilai'],
  ['Jadwal', 'fas fa-calendar-days', 'jadwal'],
  ['Ujian', 'fas fa-file-pen', 'ujian'],
  ['Administrasi', 'fas fa-clipboard-check', 'administrasi'],
  ['Keuangan', 'fas fa-wallet', 'keuangan'],
]

const navigation = [
  { label: 'Dashboard', icon: 'fas fa-chart-pie', to: '/dashboard/murid' },
  ...muridItems.map(([label, icon, slug]) => ({ label, icon, to: `/dashboard/murid/${slug}` })),
]

const namaBulan = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
]

const loading = ref(true)
const error = ref(null)
const absensiList = ref([])

const filterBulan = ref('')
const filterStatus = ref('')

const statusLabel = (status) => {
  const map = { hadir: 'Hadir', izin: 'Izin', sakit: 'Sakit', alpa: 'Alpa' }
  return map[status] || status || '-'
}

const formatTanggal = (tgl) => {
  if (!tgl) return '-'
  const date = new Date(tgl)
  if (isNaN(date.getTime())) return tgl
  return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
}

const filteredAbsensi = computed(() => {
  return absensiList.value.filter((a) => {
    const tglDate = new Date(a.tanggal)
    const matchBulan = !filterBulan.value || (!isNaN(tglDate.getTime()) && tglDate.getMonth() + 1 === Number(filterBulan.value))
    const matchStatus = !filterStatus.value || a.status === filterStatus.value
    return matchBulan && matchStatus
  })
})

const summary = computed(() => {
  const result = { hadir: 0, izin: 0, sakit: 0, alpa: 0 }
  for (const a of absensiList.value) {
    if (result[a.status] !== undefined) result[a.status]++
  }
  return result
})

const persentaseKehadiran = computed(() => {
  const total = absensiList.value.length
  if (!total) return 0
  return Math.round((summary.value.hadir / total) * 100)
})

const loadAbsensi = async () => {
  loading.value = true
  error.value = null

  // --- DATA CONTOH (sementara, sebelum backend absensi dibuat) ---
  // Setelah backend siap, ganti isi fungsi ini kembali ke pemanggilan:
  // const res = await api.get('/murid/absensi')
  // absensiList.value = res.data?.data ?? []
  await new Promise((resolve) => setTimeout(resolve, 300)) // simulasi loading sebentar

  absensiList.value = [
    { id: 1, tanggal: '2026-09-01', status: 'hadir', jam_masuk: '07:02', jam_keluar: '15:05', keterangan: null },
    { id: 2, tanggal: '2026-09-02', status: 'hadir', jam_masuk: '06:58', jam_keluar: '15:00', keterangan: null },
    { id: 3, tanggal: '2026-09-03', status: 'sakit', jam_masuk: null, jam_keluar: null, keterangan: 'Demam, surat dokter terlampir' },
    { id: 4, tanggal: '2026-09-04', status: 'hadir', jam_masuk: '07:10', jam_keluar: '15:03', keterangan: null },
    { id: 5, tanggal: '2026-09-05', status: 'izin', jam_masuk: null, jam_keluar: null, keterangan: 'Acara keluarga' },
    { id: 6, tanggal: '2026-09-08', status: 'hadir', jam_masuk: '06:55', jam_keluar: '15:00', keterangan: null },
    { id: 7, tanggal: '2026-09-09', status: 'hadir', jam_masuk: '07:00', jam_keluar: '15:02', keterangan: null },
    { id: 8, tanggal: '2026-09-10', status: 'alpa', jam_masuk: null, jam_keluar: null, keterangan: 'Tanpa keterangan' },
    { id: 9, tanggal: '2026-09-11', status: 'hadir', jam_masuk: '07:05', jam_keluar: '15:00', keterangan: null },
    { id: 10, tanggal: '2026-09-12', status: 'hadir', jam_masuk: '06:59', jam_keluar: '15:01', keterangan: null },
    { id: 11, tanggal: '2026-09-15', status: 'hadir', jam_masuk: '07:03', jam_keluar: '15:00', keterangan: null },
    { id: 12, tanggal: '2026-09-16', status: 'hadir', jam_masuk: '07:00', jam_keluar: '15:00', keterangan: null },
  ]

  loading.value = false
}

onMounted(() => {
  loadAbsensi()
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
.welcome > i {
  font-size: 3rem;
  color: #8fd94a;
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
  min-width: 160px;
  font-size: 0.88rem;
}

.summary-row {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 14px;
  margin-bottom: 22px;
}
@media (max-width: 900px) {
  .summary-row {
    grid-template-columns: repeat(2, 1fr);
  }
}
.summary-card {
  background: #fff;
  border: 1px solid #eef2f0;
  border-radius: 14px;
  padding: 18px;
  display: flex;
  align-items: center;
  gap: 14px;
}
.summary-card i {
  font-size: 1.4rem;
  width: 42px;
  height: 42px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.summary-card.hadir i { background: #e3f6ea; color: #1c9c5f; }
.summary-card.izin i { background: #eaf1fd; color: #2563eb; }
.summary-card.sakit i { background: #fdf1dc; color: #a9711f; }
.summary-card.alpa i { background: #fbe4e4; color: #d64545; }
.summary-card.persen i { background: #f1f5f9; color: #475569; }
.summary-card b {
  display: block;
  font-size: 1.3rem;
  color: #06231a;
}
.summary-card span {
  font-size: 0.78rem;
  color: #64748b;
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

.status-badge {
  display: inline-flex;
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 600;
}
.status-badge.hadir { background: #e3f6ea; color: #1c9c5f; }
.status-badge.izin { background: #eaf1fd; color: #2563eb; }
.status-badge.sakit { background: #fdf1dc; color: #a9711f; }
.status-badge.alpa { background: #fbe4e4; color: #d64545; }

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
</style>