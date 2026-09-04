<template>
  <DashboardLayout :title="title" role-label="Murid" :navigation="muridNavigation">
    <section class="feature-placeholder"><span><i :class="icon"></i></span><h2>{{ title }}</h2><p>{{ description }}</p><router-link class="btn btn-primary" to="/dashboard/murid">Kembali ke dashboard</router-link></section>
  </DashboardLayout>
</template>
<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'
import { muridNavigation } from './muridNavigation'
const route = useRoute()
const details = { profil: ['Profil Murid', 'fas fa-user', 'Kelola informasi profil dan data pribadi Anda.'], tugas: ['Tugas', 'fas fa-book-open', 'Lihat daftar tugas serta status pengumpulannya.'], nilai: ['Nilai', 'fas fa-chart-bar', 'Pantau nilai mata pelajaran dan hasil belajar.'], jadwal: ['Jadwal', 'fas fa-calendar-days', 'Lihat jadwal pelajaran Anda.'], ujian: ['Ujian', 'fas fa-laptop', 'Akses jadwal dan pelaksanaan ujian.'], administrasi: ['Administrasi', 'fas fa-folder-open', 'Lihat kebutuhan administrasi sekolah.'], keuangan: ['Keuangan', 'fas fa-wallet', 'Periksa tagihan dan riwayat pembayaran.'] }
const current = computed(() => details[route.params.feature] || details.profil)
const title = computed(() => current.value[0]); const icon = computed(() => current.value[1]); const description = computed(() => current.value[2])
</script>
<style scoped>.feature-placeholder{background:#fff;border:1px solid var(--line);border-radius:18px;padding:55px 28px;text-align:center}.feature-placeholder span{width:62px;height:62px;margin:0 auto 16px;border-radius:16px;background:#eaf7ee;color:var(--leaf-600);display:grid;place-items:center;font-size:1.5rem}.feature-placeholder h2{font-size:1.25rem}.feature-placeholder p{max-width:420px;margin:0 auto 22px}</style>
