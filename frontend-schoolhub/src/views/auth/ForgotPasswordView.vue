<template>
  <section class="login-shell">
    <div class="login-card">
      <!-- BRAND -->
      <div class="brand">
        <span class="brand-mark">HB</span>
        <span class="brand-title">SMK Harapan Bangsa</span>
      </div>

      <div class="header-text">
        <h2>Lupa Kata Sandi?</h2>
        <p class="sub">Masukkan email Anda dan kami akan mengirimkan link untuk reset password.</p>
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
      <form @submit.prevent="handleSubmit" class="forgot-form">
        <!-- EMAIL -->
        <div class="form-group">
          <label for="email">Email *</label>
          <input
            id="email"
            v-model="email"
            class="input"
            type="email"
            placeholder="nama@email.com"
            required
          />
        </div>

        <!-- SUBMIT BUTTON -->
        <button type="submit" class="btn btn-primary btn-block" :disabled="loading">
          <span v-if="loading" class="spinner-wrapper">
            <span class="spinner"></span> Mengirim...
          </span>
          <span v-else>Kirim Link Reset Password</span>
        </button>
      </form>

      <!-- DIVIDER -->
      <div class="divider-or">atau</div>

      <!-- BACK TO LOGIN -->
      <p class="login-footer">
        Sudah ingat password?
        <router-link to="/login" class="link-login">
          Kembali ke Login
        </router-link>
      </p>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'

const email = ref('')
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const handleSubmit = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  if (!email.value) {
    errorMessage.value = 'Email wajib diisi.'
    return
  }

  loading.value = true

  try {
    // TODO: Implement forgot password API call
    // const response = await api.post('/auth/forgot-password', { email: email.value })

    // Simulasi delay request
    await new Promise((resolve) => setTimeout(resolve, 1500))

    successMessage.value =
      'Link reset password telah dikirim ke email Anda. Silakan cek inbox atau spam folder.'
    email.value = ''
  } catch (error) {
    console.error(error)
    errorMessage.value = 'Terjadi kesalahan. Email tidak ditemukan atau server sedang bermasalah.'
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
  padding: 20px;

  /* Background Gambar dengan Dark Overlay */
  background: 
    linear-gradient(rgba(15, 23, 42, 0.55), rgba(15, 23, 42, 0.55)),
    url('https://i.pinimg.com/736x/4e/6f/cf/4e6fcff0ea88fd7724700944a36c05fb.jpg') center/cover no-repeat;
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
  max-width: 450px;
}

/* BRANDING */
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
.forgot-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
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
  to {
    transform: rotate(360deg);
  }
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

.divider-or::before {
  left: 0;
}

.divider-or::after {
  right: 0;
}

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