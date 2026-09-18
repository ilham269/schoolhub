<template>
  <DashboardLayout title="Dashboard Keuangan" role-label="Karyawan" :navigation="navigation">
    <!-- Welcome Card -->
    <section class="welcome-card">
      <div>
        <span class="eyebrow-dot dark">Keuangan Sekolah</span>
        <h2>Dashboard Keuangan</h2>
        <p>Kelola tagihan SPP, slip gaji, dan keuangan sekolah.</p>
      </div>
      <i class="fas fa-wallet"></i>
    </section>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <i class="fas fa-spinner fa-spin"></i>
      <p>Memuat data keuangan...</p>
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
      <!-- Statistics -->
      <DashboardMetrics :items="metrics" />

      <!-- Quick Actions -->
      <section class="section-heading">
        <div>
          <h2>Menu Keuangan</h2>
          <p>Kelola tagihan SPP dan slip gaji karyawan.</p>
        </div>
      </section>

      <DashboardFeatureGrid :features="features" />

      <!-- Recent Transactions -->
      <section class="recent-transactions">
        <div class="section-header">
          <h3>
            <i class="fas fa-history"></i> Transaksi Terbaru
          </h3>
          <span class="transaction-count">{{ dashboardData?.recent_transactions?.length || 0 }} transaksi</span>
        </div>

        <div v-if="!dashboardData?.recent_transactions || dashboardData.recent_transactions.length === 0" class="empty-state">
          <i class="fas fa-inbox"></i>
          <p>Belum ada transaksi</p>
        </div>

        <div v-else class="transactions-list">
          <div
            v-for="transaction in dashboardData.recent_transactions"
            :key="transaction.id"
            class="transaction-item"
          >
            <div class="transaction-icon" :class="getStatusClass(transaction.status)">
              <i :class="getStatusIcon(transaction.status)"></i>
            </div>
            <div class="transaction-info">
              <div class="transaction-name">{{ transaction.murid_name }}</div>
              <div class="transaction-meta">
                {{ transaction.kelas }} • {{ formatDate(transaction.created_at) }}
              </div>
            </div>
            <div class="transaction-details">
              <div class="transaction-amount">{{ formatCurrency(transaction.amount) }}</div>
              <div class="transaction-status" :class="getStatusClass(transaction.status)">
                {{ getStatusLabel(transaction.status) }}
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Jatuh Tempo Minggu Ini -->
      <section class="upcoming-dues" v-if="dashboardData?.jatuh_tempo_minggu_ini?.length > 0">
        <div class="section-header">
          <h3>
            <i class="fas fa-bell"></i> Jatuh Tempo Minggu Ini
          </h3>
          <span class="dues-count">{{ dashboardData.jatuh_tempo_minggu_ini.length }} tagihan</span>
        </div>

        <div class="dues-list">
          <div
            v-for="tagihan in dashboardData.jatuh_tempo_minggu_ini"
            :key="tagihan.id"
            class="due-item"
          >
            <div class="due-icon">
              <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="due-info">
              <div class="due-name">{{ tagihan.murid_name }}</div>
              <div class="due-meta">
                {{ tagihan.kelas }} • {{ tagihan.invoice_number }}
              </div>
            </div>
            <div class="due-details">
              <div class="due-amount">{{ formatCurrency(tagihan.total) }}</div>
              <div class="due-date">{{ formatDate(tagihan.jatuh_tempo) }}</div>
            </div>
          </div>
        </div>
      </section>
    </template>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'
import DashboardMetrics from '../../components/dashboard/DashboardMetrics.vue'
import DashboardFeatureGrid from '../../components/dashboard/DashboardFeatureGrid.vue'
import keuanganService from '../../utils/keuanganService'

const router = useRouter()
const loading = ref(true)
const error = ref(null)
const dashboardData = ref(null)

const navigation = [
  { label: 'Dashboard', icon: 'fas fa-chart-pie', to: '/dashboard/karyawan' },
  { label: 'Data Siswa', icon: 'fas fa-user-graduate', to: '/dashboard/karyawan/data-siswa' },
  { label: 'Keuangan', icon: 'fas fa-wallet', to: '/dashboard/karyawan/keuangan' },
  { label: 'Tagihan SPP', icon: 'fas fa-file-invoice', to: '/dashboard/karyawan/keuangan/tagihan' },
  { label: 'Slip Gaji', icon: 'fas fa-money-check', to: '/dashboard/karyawan/keuangan/slip-gaji' },
]

const features = [
  {
    title: 'Tagihan SPP',
    description: 'Kelola tagihan SPP siswa per bulan.',
    icon: 'fas fa-file-invoice',
    to: '/dashboard/karyawan/keuangan/tagihan',
  },
  {
    title: 'Slip Gaji',
    description: 'Kelola slip gaji karyawan.',
    icon: 'fas fa-money-check',
    to: '/dashboard/karyawan/keuangan/slip-gaji',
  },
  {
    title: 'Laporan Keuangan',
    description: 'Export laporan keuangan (PDF/Excel).',
    icon: 'fas fa-file-export',
    to: '/dashboard/karyawan/keuangan/laporan',
  },
]

const metrics = computed(() => {
  if (!dashboardData.value) return []

  const { stats } = dashboardData.value
  
  return [
    {
      label: 'Total Tagihan',
      value: stats?.tagihan?.total || 0,
      note: `${stats?.tagihan?.lunas || 0} Lunas`,
      icon: 'fas fa-file-invoice',
      color: 'blue',
    },
    {
      label: 'Belum Bayar',
      value: stats?.tagihan?.unpaid || 0,
      note: formatCurrency(stats?.nominal?.total_pending || 0),
      icon: 'fas fa-exclamation-circle',
      color: 'amber',
    },
    {
      label: 'Total Terkumpul',
      value: formatCurrency(stats?.nominal?.total_lunas || 0),
      note: 'SPP yang sudah dibayar',
      icon: 'fas fa-money-bill-wave',
      color: 'green',
    },
    {
      label: 'Pembayaran Sukses',
      value: stats?.pembayaran?.success || 0,
      note: `${stats?.pembayaran?.pending || 0} Pending`,
      icon: 'fas fa-check-circle',
      color: 'green',
    },
  ]
})

const loadDashboard = async () => {
  try {
    loading.value = true
    error.value = null
    
    const response = await keuanganService.getDashboard()
    
    if (response.success) {
      dashboardData.value = response.data
    } else {
      error.value = response.message || 'Gagal memuat data dashboard'
    }
  } catch (err) {
    console.error('Error loading dashboard:', err)
    error.value = err.response?.data?.message || 'Gagal memuat data dashboard. Silakan coba lagi.'
  } finally {
    loading.value = false
  }
}

const formatCurrency = (amount) => {
  return keuanganService.formatCurrency(amount)
}

const formatDate = (date) => {
  return keuanganService.formatDate(date)
}

const getStatusLabel = (status) => {
  return keuanganService.getStatusLabel(status)
}

const getStatusClass = (status) => {
  const classes = {
    SUCCESS: 'status-success',
    LUNAS: 'status-success',
    PENDING: 'status-pending',
    UNPAID: 'status-warning',
    FAILED: 'status-error',
    EXPIRED: 'status-error',
  }
  return classes[status] || ''
}

const getStatusIcon = (status) => {
  const icons = {
    SUCCESS: 'fas fa-check-circle',
    LUNAS: 'fas fa-check-circle',
    PENDING: 'fas fa-clock',
    UNPAID: 'fas fa-exclamation-circle',
    FAILED: 'fas fa-times-circle',
    EXPIRED: 'fas fa-times-circle',
  }
  return icons[status] || 'fas fa-circle'
}

onMounted(() => {
  loadDashboard()
})
</script>

<style scoped>
/* Semua warna & font memakai design tokens global situs
   (--forest-950, --leaf-500, --lime-400, dst dari main.css) supaya
   konsisten dengan tema hijau/forest yang dipakai di seluruh aplikasi. */

.welcome-card {
  background: linear-gradient(110deg, var(--forest-950), var(--leaf-600));
  padding: 26px 30px;
  border-radius: var(--radius-lg);
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}
.welcome-card h2 {
  color: white;
  font-size: 1.35rem;
  margin: 8px 0 4px;
}
.welcome-card p {
  color: #d9efe0;
  margin: 0;
  font-size: 0.9rem;
}
.welcome-card > i {
  font-size: 3rem;
  color: var(--lime-400);
  opacity: 0.85;
}
.eyebrow-dot.dark {
  color: var(--lime-400);
}
.eyebrow-dot.dark::before {
  background: var(--lime-400);
}

.loading-state,
.error-state {
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
.error-state p {
  margin-bottom: 20px;
  font-size: 0.95rem;
}
.btn-retry {
  background: var(--leaf-500);
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  font-family: var(--font-head);
  font-weight: 600;
  transition: background 0.2s;
}
.btn-retry:hover {
  background: var(--leaf-600);
}

.section-heading {
  margin: 34px 0 15px;
}
.section-heading h2 {
  font-size: 1.1rem;
  margin: 0;
  color: var(--forest-950);
}
.section-heading p {
  font-size: 0.82rem;
  margin: 3px 0 0;
  color: var(--muted);
}

/* Recent Transactions */
.recent-transactions,
.upcoming-dues {
  margin-top: 32px;
  background: var(--paper);
  border-radius: var(--radius-md);
  padding: 24px;
  border: 1px solid var(--line);
  box-shadow: var(--shadow-card);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--line);
}
.section-header h3 {
  font-size: 1rem;
  color: var(--forest-950);
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}
.section-header h3 i {
  color: var(--leaf-600);
}

.transaction-count,
.dues-count {
  background: var(--cream);
  color: var(--forest-900);
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 600;
  font-family: var(--font-head);
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: var(--muted);
}
.empty-state i {
  font-size: 2.5rem;
  margin-bottom: 12px;
  color: var(--line);
}
.empty-state p {
  margin: 0;
  font-size: 0.9rem;
}

/* Transaction Item */
.transactions-list,
.dues-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.transaction-item,
.due-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: var(--cream);
  border-radius: var(--radius-sm);
  transition: all 0.2s;
}
.transaction-item:hover,
.due-item:hover {
  background: #eef4ea;
  transform: translateX(4px);
}

.transaction-icon,
.due-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  flex-shrink: 0;
}
.transaction-icon {
  background: #eaf1fd;
  color: #2563eb;
}
.transaction-icon.status-success {
  background: #e3f6ea;
  color: var(--leaf-600);
}
.transaction-icon.status-pending {
  background: #fdf1dc;
  color: var(--amber);
}
.transaction-icon.status-error {
  background: #fbe4e4;
  color: var(--red);
}

.due-icon {
  background: #fdf1dc;
  color: var(--amber);
}

.transaction-info,
.due-info {
  flex: 1;
}
.transaction-name,
.due-name {
  font-weight: 600;
  color: var(--forest-950);
  font-size: 0.95rem;
  margin-bottom: 4px;
  font-family: var(--font-head);
}
.transaction-meta,
.due-meta {
  font-size: 0.8rem;
  color: var(--muted);
}

.transaction-details,
.due-details {
  text-align: right;
}
.transaction-amount,
.due-amount {
  font-weight: 700;
  color: var(--forest-950);
  font-size: 0.95rem;
  margin-bottom: 4px;
  font-family: var(--font-head);
}

.transaction-status {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 999px;
  display: inline-block;
  font-family: var(--font-head);
}
.transaction-status.status-success {
  background: #e3f6ea;
  color: var(--leaf-600);
}
.transaction-status.status-pending {
  background: #fdf1dc;
  color: #a9711f;
}
.transaction-status.status-warning {
  background: #fdeada;
  color: #b3540f;
}
.transaction-status.status-error {
  background: #fbe4e4;
  color: var(--red);
}

.due-date {
  font-size: 0.8rem;
  color: var(--amber);
  font-weight: 600;
  font-family: var(--font-head);
}

@media (max-width: 768px) {
  .welcome-card {
    flex-direction: column;
    text-align: center;
    gap: 16px;
  }
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  .transaction-item,
  .due-item {
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
  }
  .transaction-details,
  .due-details {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
}
</style>