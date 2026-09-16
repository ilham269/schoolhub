import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import DashboardGuru from '../views/guru/dashboard_guru.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/dashboard',
      name: 'dashboard',
      redirect: () => {
        const user = JSON.parse(sessionStorage.getItem('user') || '{}')
        const role = user.role?.toLowerCase()

        switch (role) {
          case 'admin':
            return '/dashboard/admin'
          case 'guru':
            return '/dashboard/guru'
          case 'murid':
            return '/dashboard/murid'
          case 'karyawan':
            return '/dashboard/karyawan'
          case 'calon_siswa':
            return '/dashboard/casis'
          default:
            return '/login'
        }
      },
      meta: { requiresAuth: true },
    },
    {
      path: '/dashboard/admin/pendaftaran',
      name: 'admin_ppdb',
      component: () => import('../views/admin/PpdbCandidates.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/dashboard/admin',
      name: 'dashboard_admin',
      component: () => import('../views/admin/dashboard_admin.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/dashboard/admin/guru',
      name: 'admin_guru',
      component: () => import('../views/admin/GuruManagement.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/dashboard/admin/murid',
      name: 'admin_murid',
      component: () => import('../views/admin/MuridManagement.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/dashboard/admin/karyawan',
      name: 'admin_karyawan',
      component: () => import('../views/admin/KaryawanManagement.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/dashboard/casis',
      name: 'dashboard_casis',
      component: () => import('../views/casis/CalonSiswaDashboard.vue'),
      meta: { requiresAuth: true, role: 'calon_siswa' },
    },
    {
      path: '/dashboard/casis/ujian/:id',
      name: 'casis_ujian',
      component: () => import('../views/casis/CalonSiswaExam.vue'),
      meta: { requiresAuth: true, role: 'calon_siswa' },
    },
    {
      path: '/dashboard/guru',
      name: 'dashboard_guru',
      component: DashboardGuru,
      meta: { requiresAuth: true, role: 'guru' },
    },
    {
      path: '/dashboard/guru/ujian-ppdb',
      name: 'guru_ujian_ppdb',
      component: () => import('../views/guru/PpdbExamManager.vue'),
      meta: { requiresAuth: true, role: 'guru' },
    },
    {
      path: '/dashboard/guru/monitoring-ujian-ppdb',
      name: 'guru_monitoring_ujian_ppdb',
      component: () => import('../views/guru/MonitoringUjianPpdb.vue'),
      meta: { requiresAuth: true, role: 'guru' },
    },
    {
      path: '/dashboard/guru/kelas',
      name: 'guru_kelola_kelas',
      component: () => import('../views/guru/kelola_kelas.vue'),
      meta: { requiresAuth: true, role: 'guru' },
    },
    {
      path: '/dashboard/guru/tugas',
      name: 'guru_tugas',
      component: () => import('../views/guru/tugas.vue'),
      meta: { requiresAuth: true, role: 'guru' },
    },
    {
      path: '/dashboard/murid',
      name: 'dashboard_murid',
      component: () => import('../views/murid/dashboard_murid.vue'),
      meta: { requiresAuth: true, role: 'murid' },
    },
    {
      path: '/dashboard/murid/profil',
      name: 'murid_profil',
      component: () => import('../views/murid/Profil.vue'),
      meta: { requiresAuth: true, role: 'murid' },
    },
    {
<<<<<<< Updated upstream
      path: '/dashboard/murid/tugas',
      name: 'murid_tugas',
      component: () => import('../views/murid/Tugas.vue'),
=======
      path: '/dashboard/murid/nilai',
      name: 'murid_nilai',
      component: () => import('../views/murid/Nilai.vue'),
>>>>>>> Stashed changes
      meta: { requiresAuth: true, role: 'murid' },
    },
    {
      path: '/dashboard/karyawan',
      name: 'dashboard_karyawan',
      component: () => import('../views/karyawan/dashboard_karyawan.vue'),
      meta: { requiresAuth: true, role: 'karyawan' },
    },
    {
      path: '/dashboard/karyawan/keuangan',
      name: 'karyawan_keuangan',
      component: () => import('../views/karyawan/KeuanganDashboard.vue'),
      meta: { requiresAuth: true, role: 'karyawan' },
    },
    {
      path: '/dashboard/karyawan/keuangan/tagihan',
      name: 'karyawan_tagihan_spp',
      component: () => import('../views/karyawan/TagihanSppView.vue'),
      meta: { requiresAuth: true, role: 'karyawan' },
    },
    {
      path: '/dashboard/karyawan/keuangan/slip-gaji',
      name: 'karyawan_slip_gaji',
      component: () => import('../views/karyawan/SlipGajiView.vue'),
      meta: { requiresAuth: true, role: 'karyawan' },
    },
    {
      path: '/dashboard/karyawan/data-siswa',
      name: 'karyawan_data_siswa',
      component: () => import('../views/karyawan/DataSiswaView.vue'),
      meta: { requiresAuth: true, role: 'karyawan' },
    },
    // Admin Keuangan Routes (reuse karyawan components)
    {
      path: '/dashboard/admin/keuangan',
      name: 'admin_keuangan',
      component: () => import('../views/karyawan/KeuanganDashboard.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/dashboard/admin/keuangan/tagihan',
      name: 'admin_tagihan_spp',
      component: () => import('../views/karyawan/TagihanSppView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/dashboard/admin/keuangan/slip-gaji',
      name: 'admin_slip_gaji',
      component: () => import('../views/karyawan/SlipGajiView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/dashboard/admin/data-siswa',
      name: 'admin_data_siswa',
      component: () => import('../views/karyawan/DataSiswaView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/dashboard/murid/:feature(profil|tugas|nilai|jadwal|ujian|administrasi|keuangan)',
      name: 'murid_feature',
      component: () => import('../views/murid/MuridFeatureView.vue'),
      meta: { requiresAuth: true, role: 'murid' },
    },
    {
      path: '/dashboard/admin/:resource(berita|pengumuman)',
      name: 'admin_crud_content',
      component: () => import('../views/admin/AdminCrudView.vue'),
      meta: { requiresAuth: true, role: 'admin' },
    },
    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: { requiresAuth: false },
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/auth/LoginView.vue'),
    },
    {
      path: '/ujian-ppdb',
      name: 'ujian-ppdb',
      component: () => import('../views/murid/UjianPpdbView.vue'),
      meta: { requiresAuth: true, role: 'calon_siswa' },
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: () => import('../views/auth/ForgotPasswordView.vue'),
    },
    {
      path: '/pendaftaran',
      name: 'pendaftaran',
      component: () => import('../views/auth/PendaftaranView.vue'),
    },
    {
      path: '/pengumuman',
      name: 'pengumuman',
      component: () => import('../views/pengumumanview.vue'),
    },
    {
      path: '/kontak',
      name: 'kontak',
      component: () => import('../views/kontakview.vue'),
    },
    {
      path: '/berita',
      name: 'berita',
      component: () => import('../views/BeritaView.vue'),
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('../views/profilesekolah.vue'),
    },
    {
      path: '/ppdb',
      name: 'ppdb',
      component: () => import('../views/ppdbviews.vue'),
    },
  ],
})

router.beforeEach((to) => {
  const token = sessionStorage.getItem('token')
  const user = JSON.parse(sessionStorage.getItem('user') || '{}')
  const userRole = user.role?.toLowerCase()

  // Jika halaman memerlukan autentikasi tapi tidak ada token
  if (to.meta.requiresAuth && !token) {
    // Simpan intended URL untuk redirect setelah login
    sessionStorage.setItem('intendedUrl', to.fullPath)
    return { name: 'login' }
  }

  // Jika ada token tapi tidak ada data user, mungkin session corrupt
  if (token && to.meta.requiresAuth && !userRole) {
    sessionStorage.clear()
    return { name: 'login' }
  }

  // Jika halaman memerlukan role tertentu dan role tidak sesuai
  if (to.meta.role && to.meta.role !== userRole) {
    return { name: 'dashboard' }
  }

  // Jika user sudah login dan mencoba akses halaman login, redirect ke dashboard
  if (token && to.name === 'login') {
    return { name: 'dashboard' }
  }
})

export default router
