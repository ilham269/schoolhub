<template>
  <DashboardLayout title="Dashboard Karyawan" role-label="Karyawan" :navigation="navigation">
    <section class="welcome-card"><div><span class="eyebrow-dot dark">Administrasi sekolah</span><h2>Selamat datang, {{ user.name || 'Karyawan' }}.</h2><p>Kelola administrasi sekolah dari satu dashboard yang rapi.</p></div><i class="fas fa-clipboard-check"></i></section>
    <DashboardMetrics :items="metrics" />
    <section class="section-heading"><div><h2>Menu administrasi</h2><p>Pilih fitur untuk melanjutkan pekerjaan Anda.</p></div></section>
    <DashboardFeatureGrid :features="features" />
  </DashboardLayout>
</template>
<script setup>
import { ref } from 'vue'
import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'
import DashboardMetrics from '../../components/dashboard/DashboardMetrics.vue'
import DashboardFeatureGrid from '../../components/dashboard/DashboardFeatureGrid.vue'
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'))
const navigation = [
  { label: 'Dashboard', icon: 'fas fa-chart-pie', to: '/dashboard/karyawan' }, { label: 'Data Siswa', icon: 'fas fa-user-graduate', to: '/dashboard/karyawan/data-siswa' }, { label: 'Administrasi', icon: 'fas fa-folder-open', to: '/dashboard/karyawan/administrasi' }, { label: 'Pembayaran', icon: 'fas fa-credit-card', to: '/dashboard/karyawan/pembayaran' }, { label: 'Keuangan', icon: 'fas fa-wallet', to: '/dashboard/karyawan/keuangan' }, { label: 'Laporan', icon: 'fas fa-file-lines', to: '/dashboard/karyawan/laporan' },
]
const metrics = [
  { label: 'Administrasi hari ini', value: '24', note: '6 perlu ditinjau', icon: 'fas fa-folder', color: 'green' }, { label: 'Pembayaran masuk', value: '18', note: 'Hari ini', icon: 'fas fa-money-bill-wave', color: 'amber' }, { label: 'Data siswa aktif', value: '300', note: 'Tahun ajaran 2026/2027', icon: 'fas fa-users', color: 'blue' }, { label: 'Tagihan tertunda', value: '12', note: 'Butuh tindak lanjut', icon: 'fas fa-clock', color: 'red' },
]
const features = [
  { title: 'Data Siswa', description: 'Mengelola data administrasi siswa.', icon: 'fas fa-user-graduate', to: '/dashboard/karyawan/data-siswa' }, { title: 'Administrasi', description: 'Mengelola berkas dan administrasi siswa.', icon: 'fas fa-folder-open', to: '/dashboard/karyawan/administrasi' }, { title: 'Pembayaran', description: 'Memeriksa serta mencatat pembayaran.', icon: 'fas fa-credit-card', to: '/dashboard/karyawan/pembayaran' }, { title: 'Keuangan', description: 'Mengelola pemasukan dan pengeluaran.', icon: 'fas fa-wallet', to: '/dashboard/karyawan/keuangan' }, { title: 'Laporan', description: 'Membuat laporan administrasi dan keuangan.', icon: 'fas fa-file-lines', to: '/dashboard/karyawan/laporan' },
]
</script>
<style scoped>
.welcome-card{background:linear-gradient(110deg,#0a3a2a,#1c9c5f);padding:26px 30px;border-radius:18px;color:white;display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}.welcome-card h2{color:white;font-size:1.35rem}.welcome-card p{color:#d9efe0;margin:0}.welcome-card>i{font-size:3rem;color:#8fd94a;opacity:.8}.section-heading{margin:34px 0 15px}.section-heading h2{font-size:1.1rem;margin:0}.section-heading p{font-size:.82rem;margin:3px 0 0}
</style>
