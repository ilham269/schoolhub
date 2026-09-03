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
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/auth/LoginView.vue')
    },
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