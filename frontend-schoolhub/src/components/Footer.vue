<template>
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
</template>

<script setup>
// Tidak ada logic dinamis di footer, jadi tidak perlu script khusus
</script>

<style scoped>
</style>