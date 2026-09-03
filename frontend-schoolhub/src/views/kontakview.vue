<template>
  <div class="kontak-view">
    <Navbar />

    <section class="breadcrumb-hero">
      <div class="container">
        <h1>Kontak Kami</h1>
        <div class="breadcrumb">
          <router-link to="/">Beranda</router-link><span class="sep">/</span>
          <span class="current">Kontak Kami</span>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container split" style="align-items:flex-start;">
        <div class="contact-info-card">
          <p class="eyebrow-dot">Hubungi Kami</p>
          <h3>Ada yang bisa kami bantu?</h3>
          <p style="color:#b7c9be;">Tim kami siap membantu pertanyaan seputar akademik, pendaftaran, maupun administrasi sekolah.</p>
          <ul class="contact-info-list">
            <li><span class="ic">&#128205;</span><div><strong style="display:block; color:#fff;">Alamat</strong>Jl. Pendidikan No. 45, Bandung, Jawa Barat</div></li>
            <li><span class="ic">&#128222;</span><div><strong style="display:block; color:#fff;">Telepon</strong>+62 812-3456-7890</div></li>
            <li><span class="ic">&#9993;</span><div><strong style="display:block; color:#fff;">Email</strong>info@harapanbangsa.sch.id</div></li>
            <li><span class="ic">&#128337;</span><div><strong style="display:block; color:#fff;">Jam Layanan</strong>Senin&ndash;Jumat, 07.00&ndash;15.00 WIB</div></li>
          </ul>
        </div>

        <div class="card">
          <h3>Kirim Pesan</h3>
          <p>Isi formulir di bawah ini, kami akan membalas dalam 1&ndash;2 hari kerja.</p>
          <form @submit.prevent="handleSubmit">
            <div class="form-row">
              <div class="form-group">
                <label for="cnama">Nama Lengkap *</label>
                <input
                  class="input"
                  id="cnama"
                  type="text"
                  placeholder="Nama Anda"
                  v-model="form.nama"
                  :style="{ borderColor: errors.nama ? 'var(--red)' : 'var(--line)' }"
                >
              </div>
              <div class="form-group">
                <label for="cemail">Email *</label>
                <input
                  class="input"
                  id="cemail"
                  type="email"
                  placeholder="nama@email.com"
                  v-model="form.email"
                  :style="{ borderColor: errors.email ? 'var(--red)' : 'var(--line)' }"
                >
              </div>
            </div>
            <div class="form-group">
              <label for="ctopik">Topik *</label>
              <select
                class="select"
                id="ctopik"
                v-model="form.topik"
                :style="{ borderColor: errors.topik ? 'var(--red)' : 'var(--line)' }"
              >
                <option value="">Pilih topik pesan</option>
                <option>Informasi PPDB</option>
                <option>Administrasi &amp; Pembayaran</option>
                <option>Akademik</option>
                <option>Lainnya</option>
              </select>
            </div>
            <div class="form-group">
              <label for="cpesan">Pesan *</label>
              <textarea
                class="input"
                id="cpesan"
                rows="5"
                placeholder="Tuliskan pertanyaan atau pesan Anda"
                v-model="form.pesan"
                :style="{ borderColor: errors.pesan ? 'var(--red)' : 'var(--line)' }"
              ></textarea>
            </div>
            <div
              v-if="feedback.show"
              class="alert form-feedback"
              :class="feedback.success ? 'alert-info' : 'alert-danger'"
              style="margin-bottom:14px;"
            >
              {{ feedback.message }}
            </div>
            <button type="submit" class="btn btn-primary">Kirim Pesan &rarr;</button>
          </form>
        </div>
      </div>
    </section>

    <section class="section-tight">
      <div class="container">
        <div class="map-frame">
          <iframe src="https://www.google.com/maps?q=Bandung,Jawa%20Barat&output=embed" title="Lokasi Sekolah" loading="lazy"></iframe>
        </div>
      </div>
    </section>

    <Footer />
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import Navbar from '@/components/Navbar.vue'
import Footer from '@/components/Footer.vue'

const form = reactive({
  nama: '',
  email: '',
  topik: '',
  pesan: ''
})

const errors = reactive({
  nama: false,
  email: false,
  topik: false,
  pesan: false
})

const feedback = reactive({
  show: false,
  success: false,
  message: ''
})

const handleSubmit = () => {
  errors.nama = !form.nama.trim()
  errors.email = !form.email.trim()
  errors.topik = !form.topik.trim()
  errors.pesan = !form.pesan.trim()

  const valid = !errors.nama && !errors.email && !errors.topik && !errors.pesan

  feedback.show = true
  feedback.success = valid
  feedback.message = valid
    ? 'Berhasil dikirim. Tim kami akan segera menghubungi Anda.'
    : 'Mohon lengkapi semua kolom bertanda wajib.'

  if (valid) {
    form.nama = ''
    form.email = ''
    form.topik = ''
    form.pesan = ''
  }
}
</script>

<style scoped>
</style>