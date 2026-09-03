<template>
  <div class="home-view">
    <Navbar />

    <section class="hero">
      <div class="container">
        <p class="eyebrow-dot">Selamat Datang di SMA Harapan Bangsa</p>
        <h1>Memimpin Jalan Menuju Pendidikan Tinggi Berkualitas</h1>
        <p>Tempat keunggulan akademik bertemu dengan pembentukan karakter, membekali setiap siswa untuk masa depan yang mereka pilih sendiri.</p>
        <div class="hero-cta">
          <router-link class="btn btn-primary" to="/profil">Selengkapnya &rarr;</router-link>
          <router-link class="btn btn-outline-light" to="/ppdb">Info PPDB</router-link>
        </div>
      </div>
    </section>

    <div class="container">
      <div class="feature-row">
        <div class="feature-card hi">
          <div class="feature-icon">&#128218;</div>
          <h3>Perpustakaan &amp; Buku</h3>
          <p>Koleksi lebih dari 12.000 judul buku fisik dan digital untuk menunjang riset siswa.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">&#127891;</div>
          <h3>Pengajar Berpengalaman</h3>
          <p>Tenaga pendidik tersertifikasi dengan rata-rata 10 tahun pengalaman mengajar.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">&#127942;</div>
          <h3>Beasiswa Prestasi</h3>
          <p>Program beasiswa penuh dan sebagian bagi siswa berprestasi akademik maupun non-akademik.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">&#128179;</div>
          <h3>Pembayaran Daring</h3>
          <p>Gerbang pembayaran SPP dan biaya sekolah yang aman, cepat, dan dapat dipantau orang tua.</p>
        </div>
      </div>
    </div>

    <section class="section">
      <div class="container split">
        <div>
          <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=900&auto=format&fit=crop" alt="Siswa lulusan SMA Harapan Bangsa" />
        </div>
        <div>
          <p class="eyebrow-dot dark">Tentang Sekolah Kami</p>
          <h2>Kami Akan Memberikan Masa Depan Untukmu</h2>
          <p>Sudah menjadi kepercayaan luas bahwa pembaca akan teralihkan oleh isi bacaan yang mudah dibaca dan menarik, dibandingkan hanya melihat tata letaknya. Di SMA Harapan Bangsa, kami memadukan kurikulum nasional dengan pendekatan pembelajaran berbasis proyek.</p>
          <p>Kami percaya setiap siswa memiliki potensi unik. Karena itu, kami menyediakan ruang eksplorasi minat lewat lebih dari 20 ekstrakurikuler dan kelas peminatan sejak kelas 10.</p>
          <div class="stat-row">
            <div class="stat"><b>1.240+</b><span>Siswa Aktif</span></div>
            <div class="stat"><b>86</b><span>Tenaga Pengajar</span></div>
            <div class="stat"><b>98%</b><span>Kelulusan PTN</span></div>
          </div>
        </div>
      </div>
    </section>

    <section class="section section-dark">
      <div class="container">
        <p class="eyebrow-dot" style="text-align:center;">Guru Kami</p>
        <h2 style="text-align:center; margin-bottom:44px;">Kenali Para Pengajar Terbaik Kami</h2>
        
        <!-- Loading state -->
        <div v-if="loadingGuru" style="text-align:center; padding:40px;">
          <p>Memuat data guru...</p>
        </div>
        
        <!-- Error state (optional) -->
        <div v-else-if="errorGuru && daftarGuru.length === 0" style="text-align:center; padding:40px;">
          <p style="color: var(--slate-400);">Data guru belum tersedia saat ini.</p>
        </div>
        
        <!-- Data guru -->
        <div v-else-if="daftarGuru.length > 0" class="people-grid">
          <div class="person-card" v-for="guru in daftarGuru.slice(0, 4)" :key="guru.id">
            <div class="photo"><img :src="guru.gambar_guru" :alt="guru.nama_lengkap_guru" /></div>
            <div class="info">
              <h4>{{ guru.nama_lengkap_guru}}</h4>
            </div>
          </div>
        </div>
        
        <!-- Fallback jika data kosong -->
        <div v-else style="text-align:center; padding:40px;">
          <p style="color: var(--slate-400);">Data guru akan segera ditampilkan.</p>
        </div>
        
        <div style="text-align:center; margin-top:36px;">
          <router-link class="btn btn-primary" to="/profil">Lihat Semua Guru &rarr;</router-link>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <p class="eyebrow-dot dark" style="text-align:center;">Temukan Sekolah Kami</p>
        <h2 style="text-align:center; margin-bottom:12px;">Jenjang &amp; Program Peminatan</h2>
        <p style="text-align:center; max-width:560px; margin-inline:auto 36px;">Pilih jalur peminatan yang paling sesuai dengan minat dan rencana masa depanmu.</p>
        <div class="filter-pills" style="justify-content:center; margin-top:28px;">
          <button
            :class="{ 'active': activeFilter === 'Semua' }"
            @click="setFilter('Semua')">Semua
          </button>
          <button
            :class="{ 'active': activeFilter === 'MIPA' }"
            @click="setFilter('MIPA')">MIPA
          </button>
          <button
            :class="{ 'active': activeFilter === 'IPS' }"
            @click="setFilter('IPS')">IPS
          </button>
          <button
            :class="{ 'active': activeFilter === 'Bahasa' }"
            @click="setFilter('Bahasa')">Bahasa
          </button>
        </div>
        <div class="news-grid">
          <div class="news-card">
            <img src="https://images.unsplash.com/photo-1532094349884-543bc11b234d?q=80&w=700&auto=format&fit=crop" alt="" />
            <div class="body">
              <span class="badge badge-green">MIPA</span>
              <h3 style="font-size:1.1rem;">Sains &amp; Teknologi</h3>
              <p>Fokus pada matematika, fisika, kimia, dan biologi dengan laboratorium lengkap.</p>
              <a class="link-arrow" href="#">Pelajari program &rarr;</a>
            </div>
          </div>
          <div class="news-card">
            <img src="https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?q=80&w=700&auto=format&fit=crop" alt="" />
            <div class="body">
              <span class="badge badge-amber">IPS</span>
              <h3 style="font-size:1.1rem;">Ilmu Sosial</h3>
              <p>Ekonomi, sosiologi, dan geografi untuk memahami dinamika masyarakat.</p>
              <a class="link-arrow" href="#">Pelajari program &rarr;</a>
            </div>
          </div>
          <div class="news-card">
            <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?q=80&w=700&auto=format&fit=crop" alt="" />
            <div class="body">
              <span class="badge badge-dark">Bahasa</span>
              <h3 style="font-size:1.1rem;">Bahasa &amp; Budaya</h3>
              <p>Penguasaan bahasa asing dan sastra untuk komunikasi global.</p>
              <a class="link-arrow" href="#">Pelajari program &rarr;</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section-tight" style="background:var(--cream);">
      <div class="container" style="text-align:center;">
        <h2>Siap Bergabung dengan Kami?</h2>
        <p style="max-width:480px; margin-inline:auto 24px;">Pendaftaran siswa baru tahun ajaran 2027/2028 telah dibuka. Amankan kursimu sekarang.</p>
        <router-link class="btn btn-primary" to="/pendaftaran">Daftar Sekarang &rarr;</router-link>
      </div>
    </section>

    <Footer />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Navbar from '@/components/Navbar.vue'
import Footer from '@/components/Footer.vue'
import api from '@/utils/api'

// State untuk Filter Jurusan
const activeFilter = ref('Semua')
const setFilter = (filterName) => {
  activeFilter.value = filterName
}

const daftarGuru = ref([])
const loadingGuru = ref(false)
const errorGuru = ref(null)

const fetchGuru = async () => {
  loadingGuru.value = true
  errorGuru.value = null
  
  try {
    // Menggunakan axios instance dari utils/api.js
    // Request akan otomatis di-proxy ke http://localhost:8000/api/guru
    const response = await api.get('/public/guru')
    const result = response.data

    console.log('Hasil API:', result)

    if (result.data) {
      daftarGuru.value = result.data
    } else {
      daftarGuru.value = result
    }
  } catch (error) {
    console.error('Gagal mengambil data guru:', error)
    errorGuru.value = error.message
    // Jangan throw error, biar page tetap bisa diakses
    // Set daftarGuru jadi empty array supaya page tidak crash
    daftarGuru.value = []
  } finally {
    loadingGuru.value = false
  }
}

onMounted(() => {
  fetchGuru()
})
</script>

<style scoped>
</style>