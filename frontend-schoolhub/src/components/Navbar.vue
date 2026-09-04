<template>
  <div class="topbar">
    <div class="container">
      <div class="topbar-contacts">
        <span>&#9742; +62 812-3456-7890</span>
        <span>&#9993; info@harapanbangsa.sch.id</span>
      </div>
      <div class="topbar-social">
        <a href="#" aria-label="Facebook">FB</a>
        <a href="#" aria-label="Instagram">IG</a>
        <a href="#" aria-label="YouTube">YT</a>
      </div>
    </div>
  </div>

  <header class="navbar">
    <div class="container">
      <router-link class="brand" to="/">
        <span class="brand-mark">HB</span>
        SMA Harapan Bangsa
      </router-link>
      <nav>
        <ul class="nav-links" :class="{ 'open': isMenuOpen }">
          <li>
            <router-link to="/" exact-active-class="active">Beranda</router-link>
          </li>
          <li>
            <router-link to="/berita" active-class="active">Berita</router-link>
          </li>
          <li>
            <router-link to="/pengumuman" active-class="active">Pengumuman</router-link>
          </li>
          <li>
            <router-link to="/ppdb" active-class="active">PPDB</router-link>
          </li>
          <li>
            <router-link to="/kontak" active-class="active">Kontak</router-link>
          </li>

          <li class="has-dropdown" :class="{ 'open': isDropdownOpen }">
            <a href="#" @click.prevent="toggleDropdown">Lainnya</a>
            <div class="dropdown">
              <router-link to="/profile" active-class="active">Profil Sekolah</router-link>
              <router-link to="/visi-misi" active-class="active">Visi &amp; Misi</router-link>
              <router-link to="/pendaftaran" active-class="active">Pendaftaran Siswa Baru</router-link>
              <router-link to="/components" active-class="active">Komponen UI (Demo)</router-link>
              <router-link to="/login" active-class="active">Login / Sign Up</router-link>
            </div>
          </li>
        </ul>
      </nav>
    </div>
  </header>
</template>

<script setup>
import { ref } from 'vue'

const isMenuOpen = ref(false)
const isDropdownOpen = ref(false)

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

const toggleDropdown = () => {
  if (window.innerWidth <= 960) {
    isDropdownOpen.value = !isDropdownOpen.value
  }
}
</script>

<style scoped>
/* pindahkan style khusus navbar/topbar ke sini jika ada, atau tetap pakai assets/css/style.css global */
</style>