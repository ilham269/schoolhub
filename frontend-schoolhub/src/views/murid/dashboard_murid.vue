<template>
  <DashboardLayout title="Dashboard Murid" role-label="Murid" :navigation="navigation">
    <section class="welcome-card"><div><span class="eyebrow-dot dark">Portal akademik</span><h2>Halo, {{ user.name || 'Murid' }}!</h2><p>Akses kegiatan belajar, nilai, dan kebutuhan administrasi dari satu tempat.</p></div><i class="fas fa-graduation-cap"></i></section>
    <DashboardMetrics :items="metrics" />
    <section class="section-heading"><div><h2>Menu murid</h2><p>Pilih menu untuk melanjutkan aktivitas Anda.</p></div></section>
    <DashboardFeatureGrid :features="features" />
  </DashboardLayout>
</template>
<script setup>
import { ref } from 'vue'
import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'
import DashboardMetrics from '../../components/dashboard/DashboardMetrics.vue'
import DashboardFeatureGrid from '../../components/dashboard/DashboardFeatureGrid.vue'
import { muridNavigation } from './muridNavigation'
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'))
const navigation = muridNavigation
const metrics = [
  { label: 'Tugas berjalan', value: '3', note: '1 mendekati tenggat', icon: 'fas fa-book-open', color: 'green' },
  { label: 'Rata-rata nilai', value: '86', note: 'Semester ini', icon: 'fas fa-chart-line', color: 'blue' },
  { label: 'Kehadiran', value: '98%', note: 'Bulan ini', icon: 'fas fa-calendar-check', color: 'amber' },
  { label: 'Tagihan aktif', value: '1', note: 'Perlu diperiksa', icon: 'fas fa-wallet', color: 'red' },
]
const features = [
  { title: 'Profil', description: 'Lihat dan perbarui data profil Anda.', icon: 'fas fa-user', to: '/dashboard/murid/profil' },
  { title: 'Tugas', description: 'Lihat, kerjakan, dan kumpulkan tugas.', icon: 'fas fa-book-open', to: '/dashboard/murid/tugas' },
  { title: 'Nilai', description: 'Pantau nilai dan hasil belajar.', icon: 'fas fa-chart-bar', to: '/dashboard/murid/nilai' },
  { title: 'Jadwal', description: 'Lihat jadwal pelajaran harian.', icon: 'fas fa-calendar-days', to: '/dashboard/murid/jadwal' },
  { title: 'Ujian', description: 'Ikuti ujian dan cek jadwal ujian.', icon: 'fas fa-laptop', to: '/dashboard/murid/ujian' },
  { title: 'Administrasi', description: 'Periksa dokumen dan administrasi sekolah.', icon: 'fas fa-folder-open', to: '/dashboard/murid/administrasi' },
  { title: 'Keuangan', description: 'Lihat tagihan dan riwayat pembayaran.', icon: 'fas fa-wallet', to: '/dashboard/murid/keuangan' },
]
</script>
<style scoped>
.welcome-card{background:linear-gradient(110deg,#0a3a2a,#1c9c5f);padding:26px 30px;border-radius:18px;color:white;display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}.welcome-card h2{color:white;font-size:1.35rem}.welcome-card p{color:#d9efe0;margin:0}.welcome-card>i{font-size:3rem;color:#8fd94a;opacity:.8}.section-heading{margin:34px 0 15px}.section-heading h2{font-size:1.1rem;margin:0}.section-heading p{font-size:.82rem;margin:3px 0 0}
</style>
