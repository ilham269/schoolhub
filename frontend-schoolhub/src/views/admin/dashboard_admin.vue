<template>
  <DashboardLayout title="Dashboard Admin" role-label="Admin" :navigation="navigation">
    <section class="welcome">
      <div>
        <span class="eyebrow-dot dark">Pusat kendali sekolah</span>
        <h2>Semua operasional sekolah dalam satu layar.</h2>
        <p>Kelola data akademik, pengguna, PPDB, serta konten publik dengan cepat.</p>
      </div>
      <i class="fas fa-shield-halved"></i>
    </section>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <i class="fas fa-spinner fa-spin"></i>
      <p>Memuat data dashboard...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <i class="fas fa-exclamation-circle"></i>
      <p>{{ error }}</p>
      <button @click="loadDashboard" class="btn-retry">
        <i class="fas fa-redo"></i> Coba Lagi
      </button>
    </div>

    <!-- Dashboard Content -->
    <template v-else>
      <DashboardMetrics :items="metrics" />
      <section class="head">
        <h2>Manajemen sistem</h2>
        <p>{{ features.length }} fitur tersedia untuk administrator.</p>
      </section>
      <DashboardFeatureGrid :features="features" />
    </template>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'
import DashboardMetrics from '../../components/dashboard/DashboardMetrics.vue'
import DashboardFeatureGrid from '../../components/dashboard/DashboardFeatureGrid.vue'
import api from '../../utils/api'

const loading = ref(true)
const error = ref(null)
const dashboardData = ref(null)

// Only show implemented features
const adminItems = [
  // ['Kelola User', 'fas fa-users', 'users'], // TODO: Not implemented yet
  ['Kelola Siswa', 'fas fa-user-graduate', 'murid'],
  ['Kelola Guru', 'fas fa-chalkboard-user', 'guru'],
  ['Kelola Karyawan', 'fas fa-id-card', 'karyawan'],
  ['Kelola Kelas', 'fas fa-school', 'kelas'],
  ['Kelola Jadwal', 'fas fa-calendar-days', 'jadwal'],
  // ['Kelola Mata Pelajaran', 'fas fa-book-open', 'mapel'], // TODO: Not implemented yet
  // ['Kelola Ujian', 'fas fa-file-circle-check', 'ujian'], // TODO: Not implemented yet
  // ['Kelola Soal', 'fas fa-list-ol', 'soal'], // TODO: Not implemented yet
  ['Kelola Pendaftaran', 'fas fa-clipboard-list', 'pendaftaran'],
  ['Kelola Berita', 'fas fa-newspaper', 'berita'],
  ['Kelola Pengumuman', 'fas fa-bullhorn', 'pengumuman'],
  ['Kelola Keuangan', 'fas fa-wallet', 'keuangan'],
  // ['Kelola Sistem', 'fas fa-gears', 'sistem'], // TODO: Not implemented yet
  // ['Laporan', 'fas fa-chart-line', 'laporan'], // TODO: Not implemented yet
]

const navigation = [
  { label: 'Dashboard', icon: 'fas fa-chart-pie', to: '/dashboard/admin' },
  ...adminItems.map(([label, icon, slug]) => ({ label, icon, to: `/dashboard/admin/${slug}` })),
]

const metrics = computed(() => {
  if (!dashboardData.value) {
    return [
      {
        label: 'Total siswa',
        value: 0,
        note: 'Siswa aktif',
        icon: 'fas fa-user-graduate',
        color: 'green',
      },
      {
        label: 'Total guru',
        value: 0,
        note: 'Tenaga pengajar',
        icon: 'fas fa-chalkboard-user',
        color: 'blue',
      },
      {
        label: 'Karyawan',
        value: 0,
        note: 'Staf aktif',
        icon: 'fas fa-id-card',
        color: 'amber',
      },
      {
        label: 'Total kelas',
        value: 0,
        note: 'Kelas aktif',
        icon: 'fas fa-school',
        color: 'blue',
      },
    ]
  }

  const summary = dashboardData.value.summary

  return [
    {
      label: 'Total siswa',
      value: summary?.total_murid || 0,
      note: 'Siswa aktif',
      icon: 'fas fa-user-graduate',
      color: 'green',
    },
    {
      label: 'Total guru',
      value: summary?.total_guru || 0,
      note: 'Tenaga pengajar',
      icon: 'fas fa-chalkboard-user',
      color: 'blue',
    },
    {
      label: 'Karyawan',
      value: summary?.total_karyawan || 0,
      note: 'Staf aktif',
      icon: 'fas fa-id-card',
      color: 'amber',
    },
    {
      label: 'Total kelas',
      value: summary?.total_kelas || 0,
      note: 'Kelas aktif',
      icon: 'fas fa-school',
      color: 'blue',
    },
  ]
})

const features = adminItems.map(([title, icon, slug]) => ({
  title,
  icon,
  description: `Kelola ${title.replace('Kelola ', '').toLowerCase()} sekolah.`,
  to: `/dashboard/admin/${slug}`,
}))

const loadDashboard = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await api.get('/dashboard/admin')

    if (response.data.success) {
      dashboardData.value = response.data.data
    } else {
      error.value = response.data.message || 'Gagal memuat data dashboard'
    }
  } catch (err) {
    console.error('Error loading dashboard:', err)
    
    if (err.response?.status === 401) {
      error.value = 'Sesi Anda telah berakhir. Silakan login kembali.'
    } else {
      error.value = err.response?.data?.message || 'Gagal memuat data dashboard. Silakan coba lagi.'
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadDashboard()
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
}

.welcome p {
  color: #d9efe0;
  margin: 0;
}

.welcome > i {
  font-size: 3rem;
  color: #8fd94a;
}

.head {
  margin: 34px 0 15px;
}

.head h2 {
  font-size: 1.1rem;
  margin: 0;
}

.head p {
  font-size: 0.82rem;
  margin: 3px 0;
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

.error-state p {
  margin-bottom: 20px;
  font-size: 0.95rem;
}

.btn-retry {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-retry:hover {
  background: #2563eb;
}
</style>
