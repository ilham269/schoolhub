import { createRouter, createWebHistory } from 'vue-router'
// 1. Import file halaman (view) yang sudah Anda buat
import HomeView from '../views/HomeView.vue'
import DashboardGuru from '../views/guru/dashboard_guru.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      // Route untuk redirect dashboard berdasarkan role
      path: '/dashboard',
      name: 'dashboard',
      redirect: () => {
        const user = JSON.parse(localStorage.getItem('user') || '{}')
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
          default:
            return '/login'
        }
      },
      meta: { requiresAuth: true }
    },
    {
      // Dashboard Admin
      path: '/dashboard/admin',
      name: 'dashboard_admin',
      component: () => import('../views/admin/dashboard_admin.vue'),
      meta: { requiresAuth: true, role: 'admin' }
    },
    {
      // Dashboard Guru
      path: '/dashboard/guru',
      name: 'dashboard_guru',
      component: DashboardGuru,
      meta: { requiresAuth: true, role: 'guru' } 
    },
    {
      // Dashboard Murid
      path: '/dashboard/murid',
      name: 'dashboard_murid',
      component: () => import('../views/murid/dashboard_murid.vue'),
      meta: { requiresAuth: true, role: 'murid' }
    },
    {
      // Dashboard Karyawan
      path: '/dashboard/karyawan',
      name: 'dashboard_karyawan',
      component: () => import('../views/karyawan/dashboard_karyawan.vue'),
      meta: { requiresAuth: true, role: 'karyawan' }
    },
    {
      path: '/dashboard/murid/:feature(profil|tugas|nilai|jadwal|ujian|administrasi|keuangan)',
      name: 'murid_feature',
      component: () => import('../views/murid/MuridFeatureView.vue'),
      meta: { requiresAuth: true, role: 'murid' }
    },
    {
      path: '/dashboard/admin/:resource(berita|pengumuman)',
      name: 'admin_crud_content',
      component: () => import('../views/admin/AdminCrudView.vue'),
      meta: { requiresAuth: true, role: 'admin' }
    },
    {
      path: '/',
      name: 'home',
      component: HomeView,
       meta: { requiresAuth: false } 
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/auth/LoginView.vue')
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: () => import('../views/auth/ForgotPasswordView.vue')
    },
    {
      path: '/pendaftaran',
      name: 'pendaftaran',
      component: () => import('../views/auth/PendaftaranView.vue')
    },
    {
      path: '/pengumuman',
      name: 'pengumuman',
      component: () => import('../views/pengumumanview.vue') // Sesuaikan dengan lokasi file komponen Vue-mu
    },
    {
      path: '/kontak',
      name: 'kontak',
      component: () => import('../views/kontakview.vue') // Sesuaikan dengan lokasi file komponen Vue-mu
    },
    {
      path: '/berita',
      name: 'berita',
      component: () => import('../views/BeritaView.vue') // Sesuaikan dengan lokasi file komponen Vue-mu
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('../views/profilesekolah.vue') // Sesuaikan dengan lokasi file komponen Vue-mu
    },
    {
      path: '/ppdb',
      name: 'ppdb',
      component: () => import('../views/ppdbviews.vue') // Sesuaikan dengan lokasi file komponen Vue-mu
    }
    // Tambahkan route lain sesuai kebutuhan
    /*
    {
      path: '/profil',
      name: 'profil',
      component: () => import('../views/ProfilView.vue') 
    },
    */
  ],
})

export default router
