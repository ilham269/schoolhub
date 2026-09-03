<template>
  <div class="home-view">
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
          <!-- Tambahkan :class binding untuk 'open' -->
          <ul class="nav-links" :class="{ 'open': isMenuOpen }">
            <li><router-link class="active" to="/">Beranda</router-link></li>
            <li><router-link to="/profil">Profil Sekolah</router-link></li>
            <li><router-link to="/visi-misi">Visi & Misi</router-link></li>
            <li><router-link to="/berita">Berita</router-link></li>
            <li><router-link to="/pengumuman">Pengumuman</router-link></li>
            <li><router-link to="/ppdb">PPDB</router-link></li>
            <li><router-link to="/kontak">Kontak</router-link></li>
            
            <!-- Tambahkan :class binding dan event @click.prevent -->
            <li class="has-dropdown" :class="{ 'open': isDropdownOpen }">
              <a href="#" @click.prevent="toggleDropdown">Lainnya</a>
              <div class="dropdown">
                <router-link to="/pendaftaran">Pendaftaran Siswa Baru</router-link>
                <router-link to="/components">Komponen UI (Demo)</router-link>
                <router-link to="/login">Login</router-link>
              </div>
            </li>
          </ul>
        </nav>
        <div class="nav-cta">
          <router-link class="btn btn-primary btn-sm" to="/login">Login / Sign Up</router-link>
          <!-- Tambahkan event @click="toggleMenu" -->
          <button class="nav-toggle" aria-label="Buka menu" @click="toggleMenu">&#9776;</button>
        </div>
      </div>
    </header>

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
        <div class="people-grid">
          <div class="person-card">
            <div class="photo"><img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=500&auto=format&fit=crop" alt="Guru" /></div>
            <div class="info"><h4>Andra Wibowo</h4><span>Guru Fisika</span></div>
          </div>
          <div class="person-card">
            <div class="photo"><img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?q=80&w=500&auto=format&fit=crop" alt="Guru" /></div>
            <div class="info"><h4>Salsabila Putri</h4><span>Guru Bahasa Inggris</span></div>
          </div>
          <div class="person-card">
            <div class="photo"><img src="https://images.unsplash.com/photo-1633332755192-727a05c4013d?q=80&w=500&auto=format&fit=crop" alt="Guru" /></div>
            <div class="info"><h4>Rizky Maulana</h4><span>Guru Matematika</span></div>
          </div>
          <div class="person-card">
            <div class="photo"><img src="https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?q=80&w=500&auto=format&fit=crop" alt="Guru" /></div>
            <div class="info"><h4>Dewi Anggraini</h4><span>Guru Biologi</span></div>
          </div>
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

    <footer class="footer">
      <div class="container">
        <div class="footer-grid">
          <div>
            <router-link class="brand" to="/" style="color:#fff; margin-bottom:14px;">
              <span class="brand-mark">HB</span> SMA Harapan Bangsa
            </router-link>
            <p>Membentuk generasi unggul melalui pendidikan berkarakter, akademik yang kuat, dan lingkungan belajar yang menyenangkan.</p>
          </div>
          <div>
            <h4>Tautan Cepat</h4>
            <ul>
              <li><router-link to="/profil">Profil Sekolah</router-link></li>
              <li><router-link to="/berita">Berita</router-link></li>
              <li><router-link to="/pengumuman">Pengumuman</router-link></li>
              <li><router-link to="/ppdb">Informasi PPDB</router-link></li>
            </ul>
          </div>
          <div>
            <h4>Informasi Kontak</h4>
            <ul>
              <li>Jl. Pendidikan No. 45, Bandung</li>
              <li>+62 812-3456-7890</li>
              <li>info@harapanbangsa.sch.id</li>
              <li>Senin–Jumat, 07.00–15.00</li>
            </ul>
          </div>
          <div>
            <h4>Galeri Kami</h4>
            <div class="footer-gallery">
              <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=200&auto=format&fit=crop" alt="" />
              <img src="https://images.unsplash.com/photo-1497633762265-9d179a990aa6?q=80&w=200&auto=format&fit=crop" alt="" />
              <img src="https://images.unsplash.com/photo-1580582932707-520aed937b7b?q=80&w=200&auto=format&fit=crop" alt="" />
              <img src="https://images.unsplash.com/photo-1546410531-bb4caa6b424d?q=80&w=200&auto=format&fit=crop" alt="" />
              <img src="https://images.unsplash.com/photo-1509062522246-3755977927d7?q=80&w=200&auto=format&fit=crop" alt="" />
              <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?q=80&w=200&auto=format&fit=crop" alt="" />
            </div>
          </div>
        </div>
        <div class="footer-bottom">
          <span>&copy; 2026 SMA Harapan Bangsa. Semua hak dilindungi.</span>
          <span><a href="#">Kebijakan Privasi</a> &middot; <a href="#">Syarat &amp; Ketentuan</a></span>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// State untuk Mobile Menu dan Dropdown
const isMenuOpen = ref(false)
const isDropdownOpen = ref(false)

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

const toggleDropdown = () => {
  // Hanya buka dropdown dengan klik jika di tampilan mobile
  if (window.innerWidth <= 960) {
    isDropdownOpen.value = !isDropdownOpen.value
  }
}

// State untuk Filter Jurusan
const activeFilter = ref('Semua')

const setFilter = (filterName) => {
  activeFilter.value = filterName
}
</script>

<style scoped>
/* Anda tetap bisa menggunakan file assets/css/style.css Anda secara global di main.js/main.ts, 
   atau memindahkan styling khusus komponen ini ke sini */
</style>