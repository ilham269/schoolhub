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
  { label: 'Keuangan', icon: 'fas fa-wallet', to: '/dashboard/karyawan/keuangan', active: true },
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
.welcome-card {
  background: linear-gradient(110deg, #1e3a8a, #3b82f6);
  padding: 26px 30px;
  border-radius: 18px;
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
  color: #dbeafe;
  margin: 0;
  font-size: 0.9rem;
}

.welcome-card > i {
  font-size: 3rem;
  color: #60a5fa;
  opacity: 0.8;
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
}

.btn-retry:hover {
  background: #2563eb;
}

.section-heading {
  margin: 34px 0 15px;
}

.section-heading h2 {
  font-size: 1.1rem;
  margin: 0;
  color: #1e293b;
}

.section-heading p {
  font-size: 0.82rem;
  margin: 3px 0 0;
  color: #64748b;
}

/* Recent Transactions */
.recent-transactions,
.upcoming-dues {
  margin-top: 32px;
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e2e8f0;
}

.section-header h3 {
  font-size: 1rem;
  color: #1e293b;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.transaction-count,
.dues-count {
  background: #f1f5f9;
  color: #475569;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #94a3b8;
}

.empty-state i {
  font-size: 2.5rem;
  margin-bottom: 12px;
  opacity: 0.5;
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
  background: #f8fafc;
  border-radius: 10px;
  transition: all 0.2s;
}

.transaction-item:hover,
.due-item:hover {
  background: #f1f5f9;
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
  background: #dbeafe;
  color: #3b82f6;
}

.transaction-icon.status-success {
  background: #d1fae5;
  color: #10b981;
}

.transaction-icon.status-pending {
  background: #fef3c7;
  color: #f59e0b;
}

.transaction-icon.status-error {
  background: #fee2e2;
  color: #ef4444;
}

.due-icon {
  background: #fef3c7;
  color: #f59e0b;
}

.transaction-info,
.due-info {
  flex: 1;
}

.transaction-name,
.due-name {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.95rem;
  margin-bottom: 4px;
}

.transaction-meta,
.due-meta {
  font-size: 0.8rem;
  color: #64748b;
}

.transaction-details,
.due-details {
  text-align: right;
}

.transaction-amount,
.due-amount {
  font-weight: 700;
  color: #1e293b;
  font-size: 0.95rem;
  margin-bottom: 4px;
}

.transaction-status {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 12px;
  display: inline-block;
}

.transaction-status.status-success {
  background: #d1fae5;
  color: #059669;
}

.transaction-status.status-pending {
  background: #fef3c7;
  color: #d97706;
}

.transaction-status.status-warning {
  background: #fed7aa;
  color: #c2410c;
}

.transaction-status.status-error {
  background: #fee2e2;
  color: #dc2626;
}

.due-date {
  font-size: 0.8rem;
  color: #f59e0b;
  font-weight: 500;
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
