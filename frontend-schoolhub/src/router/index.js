import { createRouter, createWebHistory } from 'vue-router'
// 1. Import file halaman (view) yang sudah Anda buat
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
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