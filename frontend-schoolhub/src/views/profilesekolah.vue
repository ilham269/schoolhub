<template>
  <div class="profil-view">
    <Navbar />

    <!-- Breadcrumb Hero -->
    <section class="breadcrumb-hero">
      <div class="container">
        <h1
          v-motion
          :initial="{ opacity: 0, y: -20 }"
          :enter="{ opacity: 1, y: 0, transition: { duration: 600 } }"
        >
          Profil Sekolah
        </h1>
        <div 
          class="breadcrumb"
          v-motion
          :initial="{ opacity: 0 }"
          :enter="{ opacity: 1, transition: { duration: 600, delay: 200 } }"
        >
          <router-link to="/">Beranda</router-link><span class="sep">/</span>
          <span class="current">Profil Sekolah</span>
        </div>
      </div>
    </section>

    <!-- Main Section -->
    <section class="section">
      <div class="container split">
        <div
          v-motion
          :initial="{ opacity: 0, x: -50 }"
          :visible-once="{ opacity: 1, x: 0, transition: { duration: 700 } }"
        >
          <p class="eyebrow-dot dark">Profil Sekolah</p>
          <h2>Mengenal Lebih Dekat SMA Harapan Bangsa</h2>
          <p>Didirikan pada tahun 1998, SMA Harapan Bangsa telah berkembang menjadi salah satu sekolah menengah atas terpercaya di Bandung. Kami mengelola pendidikan dengan memadukan kurikulum nasional, penguatan karakter, dan fasilitas belajar modern.</p>
          <p>Dengan luas area 3,2 hektar, kampus kami memiliki 42 ruang kelas, 6 laboratorium, perpustakaan digital, dan gedung olahraga indoor yang mendukung kegiatan belajar dan pengembangan bakat siswa.</p>
          <div class="stat-row">
            <div class="stat"><b>1998</b><span>Tahun Berdiri</span></div>
            <div class="stat"><b>A</b><span>Akreditasi BAN-S/M</span></div>
            <div class="stat"><b>3,2 ha</b><span>Luas Kampus</span></div>
          </div>
        </div>
        <div
          v-motion
          :initial="{ opacity: 0, x: 50 }"
          :visible-once="{ opacity: 1, x: 0, transition: { duration: 700 } }"
        >
          <img src="https://images.unsplash.com/photo-1580582932707-520aed937b7b?q=80&w=900&auto=format&fit=crop" alt="Gedung sekolah">
        </div>
      </div>
    </section>

    <!-- Facilities Section -->
    <section class="section section-dark">
      <div class="container">
        <p class="eyebrow-dot" style="text-align:center;" v-motion :initial="{ opacity: 0 }" :visible-once="{ opacity: 1, transition: { duration: 500 } }">Fasilitas</p>
        <h2 style="text-align:center; margin-bottom:44px;" v-motion :initial="{ opacity: 0, y: 20 }" :visible-once="{ opacity: 1, y: 0, transition: { duration: 500, delay: 100 } }">Fasilitas Penunjang Belajar</h2>
        
        <div class="value-grid">
          <div 
            class="value-card" 
            style="border-color:rgba(255,255,255,.12); background:rgba(255,255,255,.03);"
            v-motion
            :initial="{ opacity: 0, y: 30 }"
            :visible-once="{ opacity: 1, y: 0, transition: { duration: 500, delay: 100 } }"
          >
            <div class="n">01</div>
            <h3 style="color:#fff;">Laboratorium Sains</h3>
            <p>Lab fisika, kimia, dan biologi dengan peralatan sesuai standar praktikum nasional.</p>
          </div>
          <div 
            class="value-card" 
            style="border-color:rgba(255,255,255,.12); background:rgba(255,255,255,.03);"
            v-motion
            :initial="{ opacity: 0, y: 30 }"
            :visible-once="{ opacity: 1, y: 0, transition: { duration: 500, delay: 200 } }"
          >
            <div class="n">02</div>
            <h3 style="color:#fff;">Perpustakaan Digital</h3>
            <p>Akses ke ribuan e-book dan jurnal untuk mendukung riset dan tugas siswa.</p>
          </div>
          <div 
            class="value-card" 
            style="border-color:rgba(255,255,255,.12); background:rgba(255,255,255,.03);"
            v-motion
            :initial="{ opacity: 0, y: 30 }"
            :visible-once="{ opacity: 1, y: 0, transition: { duration: 500, delay: 300 } }"
          >
            <div class="n">03</div>
            <h3 style="color:#fff;">Gedung Olahraga</h3>
            <p>Lapangan indoor multifungsi untuk basket, futsal, dan bulu tangkis.</p>
          </div>
          <div 
            class="value-card" 
            style="border-color:rgba(255,255,255,.12); background:rgba(255,255,255,.03);"
            v-motion
            :initial="{ opacity: 0, y: 30 }"
            :visible-once="{ opacity: 1, y: 0, transition: { duration: 500, delay: 400 } }"
          >
            <div class="n">04</div>
            <h3 style="color:#fff;">Ruang Kreativitas</h3>
            <p>Studio seni, musik, dan ruang komputer untuk kelas peminatan dan ekstrakurikuler.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Timeline / Sejarah Section -->
    <section class="section">
      <div class="container">
        <div v-motion :initial="{ opacity: 0, y: 20 }" :visible-once="{ opacity: 1, y: 0, transition: { duration: 500 } }">
          <p class="eyebrow-dot dark" style="text-align:center;">Perjalanan Kami</p>
          <h2 style="text-align:center; margin-bottom:44px;">Sejarah Singkat</h2>
        </div>

        <div class="timeline" style="max-width:640px; margin-inline:auto;">
          <div 
            class="timeline-item"
            v-motion
            :initial="{ opacity: 0, x: -30 }"
            :visible-once="{ opacity: 1, x: 0, transition: { duration: 500, delay: 100 } }"
          >
            <time>1998</time>
            <h4>Pendirian Sekolah</h4>
            <p>SMA Harapan Bangsa didirikan dengan tiga rombongan belajar pertama.</p>
          </div>
          <div 
            class="timeline-item"
            v-motion
            :initial="{ opacity: 0, x: -30 }"
            :visible-once="{ opacity: 1, x: 0, transition: { duration: 500, delay: 200 } }"
          >
            <time>2008</time>
            <h4>Akreditasi A</h4>
            <p>Meraih akreditasi A pertama kali dari Badan Akreditasi Nasional Sekolah/Madrasah.</p>
          </div>
          <div 
            class="timeline-item"
            v-motion
            :initial="{ opacity: 0, x: -30 }"
            :visible-once="{ opacity: 1, x: 0, transition: { duration: 500, delay: 300 } }"
          >
            <time>2016</time>
            <h4>Perluasan Kampus</h4>
            <p>Pembangunan gedung laboratorium dan perpustakaan digital baru.</p>
          </div>
          <div 
            class="timeline-item"
            v-motion
            :initial="{ opacity: 0, x: -30 }"
            :visible-once="{ opacity: 1, x: 0, transition: { duration: 500, delay: 400 } }"
          >
            <time>2026</time>
            <h4>Kelas Digital</h4>
            <p>Penerapan pembelajaran hybrid dan sistem informasi sekolah terintegrasi.</p>
          </div>
        </div>
      </div>
    </section>

    <Footer />
  </div>
</template>

<script setup>
import Navbar from '@/components/Navbar.vue'
import Footer from '@/components/Footer.vue'
</script>

<style scoped>
</style>