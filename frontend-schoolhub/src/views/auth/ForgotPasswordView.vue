<template>
  <section class="login-shell">
    <div class="login-card">
      
      <!-- BRAND -->
      <div class="brand">
        <span class="brand-mark">HB</span>
        SMA Harapan Bangsa
      </div>

      <h2 style="margin-top: 1rem; margin-bottom: 0.5rem;">Lupa Kata Sandi?</h2>
      <p class="sub">
        Masukkan email Anda dan kami akan mengirimkan link untuk reset password.
      </p>

      <!-- SUCCESS MESSAGE -->
      <div
        v-if="successMessage"
        class="form-success"
        style="margin-bottom: 14px;"
      >
        {{ successMessage }}
      </div>

      <!-- ERROR MESSAGE -->
      <div
        v-if="errorMessage"
        class="form-feedback"
        style="margin-bottom: 14px;"
      >
        {{ errorMessage }}
      </div>

      <!-- FORM -->
      <form @submit.prevent="handleSubmit">
        
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
          >
        </div>

        <!-- SUBMIT BUTTON -->
        <button
          type="submit"
          class="btn btn-primary btn-block"
          :disabled="loading"
        >
          <span v-if="loading">Mengirim...</span>
          <span v-else>Kirim Link Reset Password</span>
        </button>

      </form>

      <!-- DIVIDER -->
      <div class="divider-or">atau</div>

      <!-- BACK TO LOGIN -->
      <p style="text-align: center; font-size: 0.88rem;">
        Sudah ingat password?
        <router-link
          to="/login"
          style="color: var(--leaf-600); font-weight: 600;"
        >
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
    // const response = await api.post('/auth/forgot-password', {
    //   email: email.value
    // })

    // Simulate success
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    successMessage.value = 'Link reset password telah dikirim ke email Anda. Silakan cek inbox atau spam folder.'
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
.login-shell {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.login-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.15);
  width: 100%;
  max-width: 450px;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--leaf-700, #2d5f3f);
  margin-bottom: 0.5rem;
}

.brand-mark {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, var(--leaf-600, #3d7a50), var(--leaf-700, #2d5f3f));
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  font-size: 1.25rem;
  font-weight: bold;
}

.sub {
  color: var(--slate-600);
  font-size: 0.9rem;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: var(--slate-700);
}

.input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--slate-300, #ddd);
  border-radius: 6px;
  font-size: 1rem;
  transition: border-color 0.2s;
}

.input:focus {
  outline: none;
  border-color: var(--leaf-600, #3d7a50);
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-primary {
  background: var(--leaf-600, #3d7a50);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: var(--leaf-700, #2d5f3f);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-block {
  width: 100%;
}

.divider-or {
  text-align: center;
  margin: 1.5rem 0;
  color: var(--slate-400);
  position: relative;
}

.divider-or::before,
.divider-or::after {
  content: '';
  position: absolute;
  top: 50%;
  width: 40%;
  height: 1px;
  background: var(--slate-300);
}

.divider-or::before {
  left: 0;
}

.divider-or::after {
  right: 0;
}

.form-feedback {
  background: #fee;
  color: #c33;
  padding: 0.75rem;
  border-radius: 6px;
  font-size: 0.9rem;
}

.form-success {
  background: #efe;
  color: #3c3;
  padding: 0.75rem;
  border-radius: 6px;
  font-size: 0.9rem;
}
</style>
