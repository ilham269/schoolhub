<template>
  <div class="dashboard-shell">
    <button class="mobile-menu" type="button" aria-label="Buka menu" @click="menuOpen = !menuOpen">
      <i class="fas fa-bars"></i>
    </button>
    <aside class="dashboard-sidebar" :class="{ open: menuOpen }">
      <router-link class="dashboard-brand" to="/">
        <span class="brand-mark">HB</span>
        <span><b>School</b> Hub</span>
      </router-link>
      <p class="role-label">Portal {{ roleLabel }}</p>

      <nav class="dashboard-nav" aria-label="Navigasi dashboard">
        <router-link
          v-for="item in navigation"
          :key="item.label"
          :to="item.to"
          active-class="active"
          @click="menuOpen = false"
        >
          <i :class="item.icon"></i><span>{{ item.label }}</span>
        </router-link>
      </nav>

      <div class="sidebar-user">
        <span class="user-avatar">{{ initials }}</span>
        <div><b>{{ user.name || roleLabel }}</b><small>{{ roleLabel }}</small></div>
      </div>
      <button class="logout-button" type="button" @click="logout"><i class="fas fa-arrow-right-from-bracket"></i> Keluar</button>
    </aside>
    <div v-if="menuOpen" class="menu-backdrop" @click="menuOpen = false"></div>

    <main class="dashboard-main">
      <header class="dashboard-topbar">
        <div>
          <p class="dashboard-breadcrumb">SMA Harapan Bangsa / {{ roleLabel }}</p>
          <h1>{{ title }}</h1>
        </div>
        <div class="topbar-actions">
          <button type="button" class="notification" aria-label="Notifikasi"><i class="far fa-bell"></i><span></span></button>
          <span class="topbar-avatar">{{ initials }}</span>
        </div>
      </header>
      <section class="dashboard-content"><slot /></section>
      <footer class="dashboard-footer">© 2026 SMA Harapan Bangsa <span>•</span> Portal {{ roleLabel }}</footer>
    </main>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  title: { type: String, required: true },
  roleLabel: { type: String, required: true },
  navigation: { type: Array, default: () => [] },
})

const router = useRouter()
const menuOpen = ref(false)
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'))
const initials = computed(() => (user.value.name || props.roleLabel).split(' ').map((word) => word[0]).slice(0, 2).join('').toUpperCase())

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}
</script>

<style scoped>
.dashboard-shell{min-height:100vh;background:#f4f8f5;display:flex;color:#183329}.dashboard-sidebar{width:270px;background:#06231a;color:#dbece1;padding:26px 16px 18px;display:flex;flex-direction:column;flex-shrink:0;position:sticky;top:0;height:100vh;z-index:20}.dashboard-brand{color:white;display:flex;align-items:center;gap:10px;font:600 1.15rem var(--font-head)}.dashboard-brand b{color:var(--lime-400)}.role-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.11em;color:#8fb7a0;margin:26px 12px 12px}.dashboard-nav{display:grid;gap:5px;overflow-y:auto}.dashboard-nav a,.logout-button{border:0;background:none;color:#b7d1c0;text-align:left;display:flex;align-items:center;gap:13px;padding:12px;border-radius:10px;font:500 .9rem var(--font-body);cursor:pointer}.dashboard-nav a i,.logout-button i{width:18px;text-align:center}.dashboard-nav a:hover,.dashboard-nav a.active{background:#11553b;color:#fff}.sidebar-user{border-top:1px solid rgba(255,255,255,.12);margin-top:auto;padding:18px 8px 12px;display:flex;gap:10px;align-items:center}.sidebar-user b,.sidebar-user small{display:block}.sidebar-user b{font-size:.86rem;color:#fff}.sidebar-user small{font-size:.72rem;color:#92b6a0}.user-avatar,.topbar-avatar{width:36px;height:36px;border-radius:50%;display:grid;place-items:center;background:#1c9c5f;color:white;font-size:.75rem;font-weight:700;flex:none}.logout-button{color:#ffb4b4;width:100%}.logout-button:hover{background:#56252a;color:#fff}.dashboard-main{min-width:0;flex:1;display:flex;flex-direction:column}.dashboard-topbar{background:#fff;padding:24px 38px;border-bottom:1px solid #e3e9de;display:flex;justify-content:space-between;align-items:center}.dashboard-topbar h1{font-size:1.5rem;margin:0}.dashboard-breadcrumb{font-size:.78rem;margin:0 0 4px;color:#6d8175}.topbar-actions{display:flex;align-items:center;gap:18px}.notification{border:0;background:#f2f7f3;width:38px;height:38px;border-radius:10px;color:#295a43;position:relative;cursor:pointer}.notification span{position:absolute;width:7px;height:7px;background:#e8a53d;border-radius:50%;right:9px;top:8px;border:1px solid #fff}.dashboard-content{width:min(1440px,100%);margin:0 auto;padding:30px 38px;flex:1}.dashboard-footer{padding:18px 38px;color:#6d8175;font-size:.78rem;border-top:1px solid #e3e9de}.dashboard-footer span{margin:0 7px}.mobile-menu,.menu-backdrop{display:none}@media(max-width:800px){.dashboard-sidebar{position:fixed;left:-285px;transition:left .2s ease}.dashboard-sidebar.open{left:0}.menu-backdrop{display:block;position:fixed;inset:0;background:rgba(6,35,26,.35);z-index:10}.mobile-menu{display:grid;place-items:center;position:fixed;z-index:30;left:16px;top:16px;width:40px;height:40px;border:0;border-radius:10px;background:#06231a;color:white}.dashboard-topbar{padding:18px 20px 18px 72px}.dashboard-topbar h1{font-size:1.2rem}.dashboard-content{padding:24px 20px}.dashboard-footer{padding:16px 20px}.topbar-avatar{display:none}}
</style>
