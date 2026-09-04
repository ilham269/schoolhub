<template>
  <section class="login-shell">
    <div class="login-card">
      
      <!-- BRAND -->
      <div class="brand">
        <span class="brand-mark">HB</span>
        SMA Harapan Bangsa
      </div>

      <h2 style="margin-top: 1rem; margin-bottom: 0.5rem;">Pendaftaran Siswa Baru</h2>
      <p class="sub">
        Daftar sebagai calon siswa SMA Harapan Bangsa.
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
        
        <!-- NAMA LENGKAP -->
        <div class="form-group">
          <label for="nama">Nama Lengkap *</label>
          <input
            id="nama"
            v-model="form.nama"
            class="input"
            type="text"
            placeholder="Nama lengkap sesuai ijazah"
            required
          >
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
          >
        </div>

        <!-- NISN -->
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
          >
        </div>

        <!-- NO HP -->
        <div class="form-group">
          <label for="hp">No. HP/WhatsApp *</label>
          <input
            id="hp"
            v-model="form.no_hp"
            class="input"
            type="tel"
            placeholder="08xxxxxxxxxx"
            required
          >
        </div>

        <!-- ASAL SEKOLAH -->
        <div class="form-group">
          <label for="asal_sekolah">Asal Sekolah (SMP) *</label>
          <input
            id="asal_sekolah"
            v-model="form.asal_sekolah"
            class="input"
            type="text"
            placeholder="Nama SMP"
            required
          >
        </div>

        <!-- JURUSAN PILIHAN -->
        <div class="form-group">
          <label for="jurusan">Jurusan Pilihan *</label>
          <select
            id="jurusan"
            v-model="form.jurusan"
            class="input"
            required
          >
            <option value="">-- Pilih Jurusan --</option>
            <option value="RPL">Rekayasa Perangkat Lunak (RPL)</option>
            <option value="TKR">Teknik Kendaraan Ringan (TKR)</option>
            <option value="TSM">Teknik Sepeda Motor (TSM)</option>
          </select>
        </div>

        <!-- PASSWORD -->
        <div class="form-group">
          <label for="password">Password *</label>
          <input
            id="password"
            v-model="form.password"
            class="input"
            type="password"
            placeholder="Minimal 6 karakter"
            required
          >
        </div>

        <!-- CONFIRM PASSWORD -->
        <div class="form-group">
          <label for="password_confirm">Konfirmasi Password *</label>
          <input
            id="password_confirm"
            v-model="form.password_confirm"
            class="input"
            type="password"
            placeholder="Ketik ulang password"
            required
          >
        </div>

        <!-- SUBMIT BUTTON -->
        <button
          type="submit"
          class="btn btn-primary btn-block"
          :disabled="loading"
        >
          <span v-if="loading">Memproses...</span>
          <span v-else>Daftar Sekarang</span>
        </button>

      </form>

      <!-- DIVIDER -->
      <div class="divider-or">atau</div>

      <!-- BACK TO LOGIN -->
      <p style="text-align: center; font-size: 0.88rem;">
        Sudah punya akun?
        <router-link
          to="/login"
          style="color: var(--leaf-600); font-weight: 600;"
        >
          Login di sini
        </router-link>
      </p>

    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const form = ref({
  nama: '',
  email: '',
  nisn: '',
  no_hp: '',
  asal_sekolah: '',
  jurusan: '',
  password: '',
  password_confirm: ''
})

const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const handleSubmit = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  // Validasi
  if (form.value.password !== form.value.password_confirm) {
    errorMessage.value = 'Password dan konfirmasi password tidak cocok.'
    return
  }

  if (form.value.password.length < 6) {
    errorMessage.value = 'Password minimal 6 karakter.'
    return
  }

  if (form.value.nisn.length !== 10) {
    errorMessage.value = 'NISN harus 10 digit.'
    return
  }

  loading.value = true

  try {
    // TODO: Implement registration API call
    // const response = await api.post('/auth/register', form.value)

    // Simulate success
    await new Promise(resolve => setTimeout(resolve, 2000))
    
    successMessage.value = 'Pendaftaran berhasil! Akun Anda sedang diverifikasi. Silakan cek email untuk konfirmasi.'
    
    // Reset form
    form.value = {
      nama: '',
      email: '',
      nisn: '',
      no_hp: '',
      asal_sekolah: '',
      jurusan: '',
      password: '',
      password_confirm: ''
    }

    // Redirect to login after 3 seconds
    setTimeout(() => {
      router.push('/login')
    }, 3000)

  } catch (error) {
    console.error(error)
    errorMessage.value = 'Terjadi kesalahan saat pendaftaran. Silakan coba lagi.'
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
  padding: 40px 20px;
}

.login-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.15);
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
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
  font-size: 0.9rem;
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

select.input {
  cursor: pointer;
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
