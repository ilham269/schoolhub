<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../utils/api'

const router = useRouter()

// =========================
// TAB LOGIN
// =========================
const activeTab = ref('login-siswa')

// =========================
// FORM SISWA
// =========================
const siswaForm = ref({
  email: '',
  password: '',
  remember: false,
})

// =========================
// FORM STAF
// =========================
const stafForm = ref({
  email: '',
  password: '',
})

// =========================
// STATE
// =========================
const loading = ref(false)
const errorMessage = ref('')

const changeTab = (tab) => {
  activeTab.value = tab
  errorMessage.value = ''
}

// =========================
// LOGIN
// =========================
const login = async (type) => {
  errorMessage.value = ''

  const form = type === 'siswa' ? siswaForm.value : stafForm.value

  if (!form.email || !form.password) {
    errorMessage.value = 'Email/NISN dan kata sandi wajib diisi.'

    return
  }

  loading.value = true

  try {
    const response = await api.post('/auth/login', {
      email: form.email,
      password: form.password,
    })

    const result = response.data

    // Check if response is successful
    if (!result.success) {
      errorMessage.value = result.message || 'Login gagal.'
      return
    }

    // Extract data from response
    const { user, token } = result.data

    // Log untuk debugging
    console.log('Login successful!')
    console.log('Token received:', token ? 'YES' : 'NO')
    console.log('User:', user?.name, 'Role:', user?.role)

    // Token hanya hidup selama browser session; jangan simpan di persistent storage.
    if (token) {
      sessionStorage.setItem('token', token)
      console.log('✅ Token saved to sessionStorage')
    } else {
      console.error('❌ No token received from server!')
    }

    // Data UI mengikuti masa hidup token.
    if (user) {
      sessionStorage.setItem('user', JSON.stringify(user))
      console.log('✅ User saved to sessionStorage')
    }

    // Bersihkan token lama yang mungkin tersisa dari versi sebelumnya.
    localStorage.removeItem('token')
    localStorage.removeItem('user')

    // Redirect berdasarkan role
    const role = user?.role?.toLowerCase()

    // Cek apakah ada intended URL dari sebelumnya
    const intendedUrl = sessionStorage.getItem('intendedUrl')
    if (intendedUrl) {
      sessionStorage.removeItem('intendedUrl')
      console.log('Redirecting to intended URL:', intendedUrl)
      router.push(intendedUrl)
      return
    }

    // Default redirect berdasarkan role
    console.log('Redirecting based on role:', role)
    switch (role) {
      case 'admin':
        router.push('/dashboard/admin')
        break

      case 'guru':
        router.push('/dashboard/guru')
        break

      case 'murid':
        router.push('/ujian-ppdb')
        break

      case 'karyawan':
        router.push('/dashboard/karyawan')
        break

      default:
        router.push('/dashboard')
    }
  } catch (error) {
    if (error.response?.status === 422) {
      errorMessage.value = error.response.data.message || 'Data login tidak valid.'
    } else if (error.response?.status === 401) {
      errorMessage.value = 'Email/NISN atau kata sandi salah.'
    } else {
      errorMessage.value = 'Terjadi kesalahan saat login. Silakan coba lagi.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <!-- ========================= -->
  <!-- LOGIN -->
  <!-- ========================= -->

  <section class="login-shell">
    <div class="login-card">
      <!-- BRAND -->
      <div class="brand">
        <span class="brand-mark">HB</span>
        SMK Harapan Bangsa
      </div>

      <p class="sub">Masuk ke portal siswa, orang tua, atau staf sekolah.</p>

      <!-- ========================= -->
      <!-- TABS -->
      <!-- ========================= -->

      <div class="tabs" style="justify-content: center">
        <button
          class="tab-btn"
          :class="{
            active: activeTab === 'login-siswa',
          }"
          type="button"
          @click="changeTab('login-siswa')"
        >
          Siswa / Orang Tua
        </button>

        <button
          class="tab-btn"
          :class="{
            active: activeTab === 'login-staf',
          }"
          type="button"
          @click="changeTab('login-staf')"
        >
          Staf / Guru
        </button>
      </div>

      <!-- ========================= -->
      <!-- ERROR -->
      <!-- ========================= -->

      <div v-if="errorMessage" class="form-feedback" style="margin-bottom: 14px">
        {{ errorMessage }}
      </div>

      <!-- ========================= -->
      <!-- LOGIN SISWA -->
      <!-- ========================= -->

      <div v-if="activeTab === 'login-siswa'" id="login-siswa" class="tab-panel active">
        <form @submit.prevent="login('siswa')">
          <!-- EMAIL / NISN -->
          <div class="form-group">
            <label for="email1"> Email atau NISN * </label>

            <input
              id="email1"
              v-model="siswaForm.email"
              class="input"
              type="text"
              placeholder="nama@email.com"
              required
            />
          </div>

          <!-- PASSWORD -->
          <div class="form-group">
            <label for="pass1"> Kata Sandi * </label>

            <input
              id="pass1"
              v-model="siswaForm.password"
              class="input"
              type="password"
              placeholder="••••••••"
              required
            />
          </div>

          <!-- REMEMBER -->
          <div class="checkbox-row" style="justify-content: space-between; margin-bottom: 18px">
            <label style="display: flex; gap: 8px; align-items: center; font-weight: 400">
              <input v-model="siswaForm.remember" type="checkbox" />

              Ingat saya
            </label>

            <router-link
              to="/forgot-password"
              style="color: var(--leaf-600); font-weight: 600; font-size: 0.85rem"
            >
              Lupa sandi?
            </router-link>
          </div>

          <!-- LOGIN -->
          <button type="submit" class="btn btn-primary btn-block" :disabled="loading">
            <span v-if="loading"> Memproses... </span>

            <span v-else> Masuk </span>
          </button>
        </form>
      </div>

      <!-- ========================= -->
      <!-- LOGIN STAF -->
      <!-- ========================= -->

      <div v-if="activeTab === 'login-staf'" id="login-staf" class="tab-panel active">
        <form @submit.prevent="login('staf')">
          <!-- EMAIL DINAS -->
          <div class="form-group">
            <label for="email2"> Email Dinas * </label>

            <input
              id="email2"
              v-model="stafForm.email"
              class="input"
              type="text"
              placeholder="nama@harapanbangsa.sch.id"
              required
            />
          </div>

          <!-- PASSWORD -->
          <div class="form-group">
            <label for="pass2"> Kata Sandi * </label>

            <input
              id="pass2"
              v-model="stafForm.password"
              class="input"
              type="password"
              placeholder="••••••••"
              required
            />
          </div>

          <!-- LOGIN -->
          <button type="submit" class="btn btn-primary btn-block" :disabled="loading">
            <span v-if="loading"> Memproses... </span>

            <span v-else> Masuk sebagai Staf </span>
          </button>
        </form>
      </div>

      <!-- ========================= -->
      <!-- DIVIDER -->
      <!-- ========================= -->

      <div class="divider-or">atau</div>

      <!-- REGISTER -->
      <p style="text-align: center; font-size: 0.88rem">
        Belum punya akun?

        <router-link to="/pendaftaran" style="color: var(--leaf-600); font-weight: 600">
          Daftar sebagai siswa baru
        </router-link>
      </p>
    </div>
  </section>
</template>
<style scoped>
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

/* Memastikan card login berada di atas background dengan tampilan kontras */
.login-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(8px); /* Efek kaca halus */
  border-radius: 16px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
}
</style>