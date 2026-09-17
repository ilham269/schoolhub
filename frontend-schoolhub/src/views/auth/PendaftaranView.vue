<template>
  <section class="login-shell">
    <div class="login-card">
      <!-- BRAND -->
      <div class="brand">
        <span class="brand-mark">HB</span>
        <span class="brand-title">SMK Harapan Bangsa</span>
      </div>

      <div class="header-text">
        <h2>Pendaftaran Siswa Baru</h2>
        <p class="sub">Lengkapi data di bawah untuk mendaftar sebagai calon siswa.</p>
      </div>

      <!-- SUCCESS MESSAGE -->
      <transition name="fade">
        <div v-if="successMessage" class="alert alert-success">
          {{ successMessage }}
        </div>
      </transition>

      <!-- ERROR MESSAGE -->
      <transition name="fade">
        <div v-if="errorMessage" class="alert alert-error">
          {{ errorMessage }}
        </div>
      </transition>

      <!-- FORM -->
      <form @submit.prevent="handleSubmit" class="register-form">
        <!-- NAMA LENGKAP -->
        <div class="form-group">
          <label for="nama">Nama Lengkap *</label>
          <input
            id="nama"
            v-model="form.nama"
            class="input"
            type="text"
            placeholder="Sesuai ijazah SMP"
            required
          />
        </div>

        <!-- EMAIL -->
        <div class="form-group">
          <label for="email">Email *</label>
          <input
            id="email"
            v-model="form.email"
            class="input"
            type="email"
            placeholder="email@contoh.com"
            required
          />
        </div>

        <!-- GRID 2 KOLOM: NISN & NO HP -->
        <div class="form-grid">
          <div class="form-group">
            <label for="nisn">NISN *</label>
            <input
              id="nisn"
              v-model="form.nisn"
              class="input"
              type="text"
              placeholder="10 digit NISN"
              maxlength="10"
              required
            />
          </div>

          <div class="form-group">
            <label for="hp">No. HP/WhatsApp *</label>
            <input
              id="hp"
              v-model="form.no_hp"
              class="input"
              type="tel"
              placeholder="08xxxxxxxxxx"
              required
            />
          </div>
        </div>

        <!-- ASAL SEKOLAH -->
        <div class="form-group">
          <label for="asal_sekolah">Asal Sekolah (SMP) *</label>
          <input
            id="asal_sekolah"
            v-model="form.asal_sekolah"
            class="input"
            type="text"
            placeholder="Nama SMP asal"
            required
          />
        </div>

        <!-- JURUSAN PILIHAN -->
        <div class="form-group">
          <label for="jurusan">Jurusan Pilihan *</label>
          <select id="jurusan" v-model="form.jurusan" class="input select-input" required>
            <option value="" disabled selected>-- Pilih Jurusan --</option>
            <option value="RPL">Rekayasa Perangkat Lunak (RPL)</option>
            <option value="TKR">Teknik Kendaraan Ringan (TKR)</option>
            <option value="TSM">Teknik Sepeda Motor (TSM)</option>
          </select>
        </div>

        <!-- UPLOAD DOKUMEN -->
        <div class="form-group">
          <label for="documents">Dokumen Pendukung</label>
          <div class="file-upload-wrapper">
            <input
              id="documents"
              class="input-file"
              type="file"
              accept=".pdf,.jpg,.jpeg,.png"
              multiple
              @change="handleFileChange"
            />
          </div>
          <small class="help-text">PDF, JPG, atau PNG (Maksimal 5 MB per berkas).</small>
        </div>

        <div class="account-note">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
          <span>Setelah data diverifikasi, akun ujian seleksi akan dibuatkan oleh admin.</span>
        </div>

        <!-- SUBMIT BUTTON -->
        <button type="submit" class="btn btn-primary btn-block" :disabled="loading">
          <span v-if="loading" class="spinner-wrapper">
            <span class="spinner"></span> Memproses...
          </span>
          <span v-else>Daftar Sekarang</span>
        </button>
      </form>

      <!-- DIVIDER -->
      <div class="divider-or">atau</div>

      <!-- BACK TO LOGIN -->
      <p class="login-footer">
        Sudah punya akun?
        <router-link to="/login" class="link-login">
          Login di sini
        </router-link>
      </p>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../utils/api'

const router = useRouter()

const form = ref({
  nama: '',
  email: '',
  nisn: '',
  no_hp: '',
  asal_sekolah: '',
  jurusan: '',
  documents: [],
})

const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const handleFileChange = (event) => {
  form.value.documents = [...event.target.files]
}

const handleSubmit = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  if (form.value.nisn.length !== 10) {
    errorMessage.value = 'NISN harus terdiri dari 10 digit angka.'
    return
  }

  loading.value = true

  try {
    const payload = new FormData()
    for (const [key, value] of Object.entries(form.value)) {
      if (key !== 'documents') payload.append(key, value)
    }
    form.value.documents.forEach((file) => payload.append('documents[]', file))

    const response = await api.post('/public/ppdb/register', payload)
    successMessage.value = response.data.message || 'Pendaftaran berhasil!'

    // Reset Form
    form.value = {
      nama: '',
      email: '',
      nisn: '',
      no_hp: '',
      asal_sekolah: '',
      jurusan: '',
      documents: [],
    }

    setTimeout(() => router.push('/login'), 2500)
  } catch (error) {
    console.error(error)
    errorMessage.value =
      error.response?.data?.message ||
      Object.values(error.response?.data?.errors || {})[0]?.[0] ||
      'Terjadi kesalahan saat pendaftaran. Silakan coba lagi.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* CONTAINER UTAMA */
.login-shell {
  min-height: 100vh;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 30px 15px;

  background: 
    linear-gradient(rgba(15, 23, 42, 0.55), rgba(15, 23, 42, 0.55)),
    url('https://i.pinimg.com/736x/4e/6f/cf/4e6fcff0ea88fd7724700944a36c05fb.jpg') center/cover no-repeat fixed;
}

/* CARD FORM */
.login-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  padding: 2.5rem;
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 520px;
  max-height: 90vh;
  overflow-y: auto;
}

/* STYLING SCROLLBAR CARD */
.login-card::-webkit-scrollbar {
  width: 6px;
}
.login-card::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 4px;
}
.login-card::-webkit-scrollbar-track {
  background: transparent;
}

/* BRANDING (Disesuaikan persis seperti Login) */
.brand {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  margin-bottom: 1.25rem;
}

.brand-mark {
  width: 36px;
  height: 36px;
  background: #10b981;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-size: 0.9rem;
  font-weight: 700;
}

.brand-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
  line-height: 1.2;
}

/* HEADER */
.header-text {
  margin-bottom: 1.5rem;
  text-align: center;
}

.header-text h2 {
  font-size: 1.35rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 0.35rem 0;
}

.sub {
  color: #64748b;
  font-size: 0.88rem;
  margin: 0;
}

/* FORM ELEMENTS */
.register-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

@media (max-width: 480px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 0.4rem;
  font-weight: 600;
  color: #334155;
  font-size: 0.85rem;
}

.input {
  width: 100%;
  padding: 0.7rem 0.9rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.92rem;
  color: #0f172a;
  background-color: #ffffff;
  transition: all 0.2s ease;
  box-sizing: border-box;
}

.input:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
}

.select-input {
  cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%3C64748b' %3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 1rem;
  appearance: none;
  -webkit-appearance: none;
}

.input-file {
  font-size: 0.85rem;
  color: #475569;
}

.input-file::file-selector-button {
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  padding: 0.4rem 0.8rem;
  margin-right: 0.8rem;
  font-weight: 500;
  color: #334155;
  cursor: pointer;
  transition: background 0.2s;
}

.input-file::file-selector-button:hover {
  background: #e2e8f0;
}

.help-text {
  margin-top: 4px;
  color: #64748b;
  font-size: 0.76rem;
}

/* ALERT MESSAGES */
.alert {
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.88rem;
  margin-bottom: 1rem;
  line-height: 1.4;
}

.alert-error {
  background-color: #fef2f2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

.alert-success {
  background-color: #f0fdf4;
  color: #166534;
  border: 1px solid #bbf7d0;
}

.account-note {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  padding: 0.75rem;
  background: #f0f9ff;
  border: 1px solid #bae6fd;
  color: #0369a1;
  border-radius: 8px;
  font-size: 0.82rem;
  line-height: 1.35;
}

.account-note svg {
  flex-shrink: 0;
  margin-top: 2px;
}

/* BUTTONS */
.btn {
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 600;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-primary {
  background: #10b981;
  color: #ffffff;
  box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
}

.btn-primary:hover:not(:disabled) {
  background: #059669;
  transform: translateY(-1px);
}

.btn-primary:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.btn-block {
  width: 100%;
}

.spinner-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
}

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: #fff;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* DIVIDER & FOOTER */
.divider-or {
  text-align: center;
  margin: 1.25rem 0;
  color: #94a3b8;
  font-size: 0.82rem;
  position: relative;
}

.divider-or::before,
.divider-or::after {
  content: '';
  position: absolute;
  top: 50%;
  width: 38%;
  height: 1px;
  background: #e2e8f0;
}

.divider-or::before { left: 0; }
.divider-or::after { right: 0; }

.login-footer {
  text-align: center;
  font-size: 0.88rem;
  color: #475569;
  margin: 0;
}

.link-login {
  color: #10b981;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.2s;
}

.link-login:hover {
  text-decoration: underline;
  color: #059669;
}

/* TRANSITIONS */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>