import { createRouter, createWebHistory } from 'vue-router'
// 1. Import file halaman (view) yang sudah Anda buat
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    // 2. Contoh cara mendaftarkan halaman lain (seperti Profil atau Login)
    // Pastikan Anda sudah membuat file ProfilView.vue dan LoginView.vue di folder src/views/ sebelum menghapus tanda //
    
    /*
    {
      path: '/profil',
      name: 'profil',
      component: () => import('../views/ProfilView.vue') 
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue')
    }
    */
  ],
})

export default router