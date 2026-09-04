<template>
  <div class="ppdb-view">
    <Navbar />

    <section class="breadcrumb-hero">
      <div class="container">
        <h1
          v-motion
          :initial="{ opacity: 0, y: -20 }"
          :enter="{ opacity: 1, y: 0, transition: { duration: 600 } }"
        >
          Informasi PPDB
        </h1>
        <div 
          class="breadcrumb"
          v-motion
          :initial="{ opacity: 0 }"
          :enter="{ opacity: 1, transition: { duration: 600, delay: 200 } }"
        >
          <router-link to="/">Beranda</router-link><span class="sep">/</span>
          <span class="current">Informasi PPDB</span>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        
        <div
          class="alert alert-info"
          v-motion
          :initial="{ opacity: 0, y: -15 }"
          :enter="{ opacity: 1, y: 0, transition: { duration: 500, delay: 100 } }"
        >
          <span>&#8505;</span>
          <div><strong>Pendaftaran Gelombang 2 Dibuka</strong> Periode pendaftaran: 1&ndash;30 September 2026. Kuota tersisa 45 dari 320 kursi.</div>
        </div>

        <div 
          class="tabs"
          v-motion
          :initial="{ opacity: 0, y: 20 }"
          :enter="{ opacity: 1, y: 0, transition: { duration: 500, delay: 200 } }"
        >
          <button 
            class="tab-btn" 
            :class="{ active: activeTab === 'alur' }" 
            @click="activeTab = 'alur'"
          >
            Alur Pendaftaran
          </button>
          <button 
            class="tab-btn" 
            :class="{ active: activeTab === 'syarat' }" 
            @click="activeTab = 'syarat'"
          >
            Persyaratan
          </button>
          <button 
            class="tab-btn" 
            :class="{ active: activeTab === 'biaya' }" 
            @click="activeTab = 'biaya'"
          >
            Biaya &amp; Jadwal
          </button>
          <button 
            class="tab-btn" 
            :class="{ active: activeTab === 'faq' }" 
            @click="activeTab = 'faq'"
          >
            FAQ
          </button>
        </div>

        <div v-if="activeTab === 'alur'" class="tab-panel active" v-motion :initial="{ opacity: 0, y: 15 }" :enter="{ opacity: 1, y: 0, transition: { duration: 400 } }">
          <h3 style="margin-bottom:20px;">Kuota Penerimaan Terisi</h3>
          <div class="progress" style="margin-bottom:8px;"><span style="width:86%;"></span></div>
          <p class="form-hint" style="margin-bottom:32px;">275 dari 320 kursi terisi (86%)</p>
          <div class="stepper">
            <div class="step done"><div class="dot">&#10003;</div><span class="label">Buat Akun</span></div>
            <div class="step done"><div class="dot">&#10003;</div><span class="label">Isi Formulir</span></div>
            <div class="step active"><div class="dot">3</div><span class="label">Unggah Dokumen</span></div>
            <div class="step"><div class="dot">4</div><span class="label">Pembayaran</span></div>
            <div class="step"><div class="dot">5</div><span class="label">Verifikasi</span></div>
          </div>
          <p>Setiap tahapan pendaftaran dapat dipantau secara langsung melalui portal PPDB. Notifikasi email dan SMS dikirim setiap kali status pendaftaran berubah.</p>
          <router-link class="btn btn-primary" to="/pendaftaran">Mulai Pendaftaran &rarr;</router-link>
        </div>

        <div v-if="activeTab === 'syarat'" class="tab-panel active" v-motion :initial="{ opacity: 0, y: 15 }" :enter="{ opacity: 1, y: 0, transition: { duration: 400 } }">
          <h3>Persyaratan Pendaftaran</h3>
          <ul style="display:grid; gap:12px; margin:20px 0; list-style:none; padding:0;">
            <li>&#9989; Fotokopi ijazah/SKL SMP atau sederajat</li>
            <li>&#9989; Fotokopi Kartu Keluarga dan Akta Kelahiran</li>
            <li>&#9989; Pas foto berwarna terbaru ukuran 3x4 (2 lembar)</li>
            <li>&#9989; Nilai rapor kelas VII&ndash;IX semester 1&ndash;5</li>
            <li>&#9989; Surat rekomendasi sekolah asal (opsional, untuk jalur prestasi)</li>
          </ul>
        </div>

        <div v-if="activeTab === 'biaya'" class="tab-panel active" v-motion :initial="{ opacity: 0, y: 15 }" :enter="{ opacity: 1, y: 0, transition: { duration: 400 } }">
          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Komponen</th>
                  <th>Jalur Reguler</th>
                  <th>Jalur Prestasi</th>
                </tr>
              </thead>
              <tbody>
                <tr><td>Biaya Pendaftaran</td><td>Rp 250.000</td><td>Gratis</td></tr>
                <tr><td>Uang Pangkal</td><td>Rp 8.500.000</td><td>Diskon 50%</td></tr>
                <tr><td>SPP Bulanan</td><td>Rp 950.000</td><td>Rp 950.000</td></tr>
                <tr><td>Batas Pendaftaran</td><td>30 September 2026</td><td>15 September 2026</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <div v-if="activeTab === 'faq'" class="tab-panel active" v-motion :initial="{ opacity: 0, y: 15 }" :enter="{ opacity: 1, y: 0, transition: { duration: 400 } }">
          <h3>Pertanyaan Umum</h3>
          <div class="card" style="margin-top:16px;">
            <h4 style="font-size:.98rem;">Apakah bisa mendaftar lebih dari satu jalur?</h4>
            <p>Setiap calon siswa hanya dapat memilih satu jalur pendaftaran pada satu periode gelombang.</p>
          </div>
          <div class="card" style="margin-top:16px;">
            <h4 style="font-size:.98rem;">Bagaimana jika dokumen belum lengkap?</h4>
            <p>Dokumen dapat menyusul maksimal 3 hari kerja setelah pengiriman formulir awal, tanpa kehilangan slot kuota.</p>
          </div>
        </div>

      </div>
    </section>

    <Footer />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Navbar from '@/components/Navbar.vue'
import Footer from '@/components/Footer.vue'

// State untuk mengatur tab yang aktif
const activeTab = ref('alur')
</script>

<style scoped>
/* Tambahan styling opsional untuk transisi tab aktif */
.tab-panel {
  display: none;
}
.tab-panel.active {
  display: block;
}
</style>