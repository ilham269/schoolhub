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
          <li><router-link class="active" to="/">Beranda</router-link></li>
          <li><router-link to="/berita">Berita</router-link></li>
          <li><router-link to="/pengumuman">Pengumuman</router-link></li>
          <li><router-link to="/ppdb">PPDB</router-link></li>
          <li><router-link to="/kontak">Kontak</router-link></li>

          <li class="has-dropdown" :class="{ 'open': isDropdownOpen }">
            <a href="#" @click.prevent="toggleDropdown">Lainnya</a>
            <div class="dropdown">
              <router-link to="/profil">Profil Sekolah</router-link>
              <router-link to="/visi-misi">Visi & Misi</router-link>
              <router-link to="/pendaftaran">Pendaftaran Siswa Baru</router-link>
              <router-link to="/components">Komponen UI (Demo)</router-link>
              <router-link to="/login">Login / Sign Up</router-link>
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