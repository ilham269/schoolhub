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

  const form =
    type === 'siswa'
      ? siswaForm.value
      : stafForm.value

  if (!form.email || !form.password) {
    errorMessage.value =
      'Email/NISN dan kata sandi wajib diisi.'

    return
  }

  loading.value = true

  try {
    console.log('🔐 Attempting login with:', form.email)
    
    const response = await api.post('/auth/login', {
      email: form.email,
      password: form.password,
    })

    console.log('📦 Full response:', response)
    console.log('📦 Response data:', response.data)

    const result = response.data

    // Check if response is successful
    if (!result.success) {
      errorMessage.value = result.message || 'Login gagal.'
      console.error('❌ Login failed:', result.message)
      return
    }

    // Extract data from response
    const { user, token } = result.data

    console.log('✅ Login success!')
    console.log('👤 User:', user)
    console.log('🔑 Token:', token)

    // Simpan token
    if (token) {
      localStorage.setItem('token', token)
      console.log('💾 Token saved to localStorage')
    }

    // Simpan user
    if (user) {
      localStorage.setItem('user', JSON.stringify(user))
      console.log('💾 User saved to localStorage')
    }

    // Redirect berdasarkan role
    const role = user?.role?.toLowerCase()
    console.log('🎭 User role:', role)

    switch (role) {
      case 'admin':
        console.log('🚀 Redirecting to /dashboard/admin')
        router.push('/dashboard/admin')
        break

      case 'guru':
        console.log('🚀 Redirecting to /dashboard/guru')
        router.push('/dashboard/guru')
        break

      case 'murid':
        console.log('🚀 Redirecting to /dashboard/murid')
        router.push('/dashboard/murid')
        break

      case 'karyawan':
        console.log('🚀 Redirecting to /dashboard/karyawan')
        router.push('/dashboard/karyawan')
        break

      default:
        console.log('🚀 Redirecting to /dashboard (default)')
        router.push('/dashboard')
    }

  } catch (error) {
    console.error('❌ Login error:', error)

    if (error.response?.status === 422) {
      errorMessage.value =
        error.response.data.message ||
        'Data login tidak valid.'
    } else if (error.response?.status === 401) {
      errorMessage.value =
        'Email/NISN atau kata sandi salah.'
    } else {
      errorMessage.value =
        'Terjadi kesalahan saat login. Silakan coba lagi.'
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
        SMA Harapan Bangsa
      </div>

      <p class="sub">
        Masuk ke portal siswa, orang tua, atau staf sekolah.
      </p>


      <!-- ========================= -->
      <!-- TABS -->
      <!-- ========================= -->

      <div
        class="tabs"
        style="justify-content:center;"
      >

        <button
          class="tab-btn"
          :class="{
            active: activeTab === 'login-siswa'
          }"
          type="button"
          @click="changeTab('login-siswa')"
        >
          Siswa / Orang Tua
        </button>

        <button
          class="tab-btn"
          :class="{
            active: activeTab === 'login-staf'
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

      <div
        v-if="errorMessage"
        class="form-feedback"
        style="margin-bottom:14px;"
      >
        {{ errorMessage }}
      </div>


      <!-- ========================= -->
      <!-- LOGIN SISWA -->
      <!-- ========================= -->

      <div
        v-if="activeTab === 'login-siswa'"
        id="login-siswa"
        class="tab-panel active"
      >

        <form
          @submit.prevent="login('siswa')"
        >

          <!-- EMAIL / NISN -->
          <div class="form-group">

            <label for="email1">
              Email atau NISN *
            </label>

            <input
              id="email1"
              v-model="siswaForm.email"
              class="input"
              type="text"
              placeholder="nama@email.com"
              required
            >

          </div>


          <!-- PASSWORD -->
          <div class="form-group">

            <label for="pass1">
              Kata Sandi *
            </label>

            <input
              id="pass1"
              v-model="siswaForm.password"
              class="input"
              type="password"
              placeholder="••••••••"
              required
            >

          </div>


          <!-- REMEMBER -->
          <div
            class="checkbox-row"
            style="
              justify-content:space-between;
              margin-bottom:18px;
            "
          >

            <label
              style="
                display:flex;
                gap:8px;
                align-items:center;
                font-weight:400;
              "
            >

              <input
                v-model="siswaForm.remember"
                type="checkbox"
              >

              Ingat saya

            </label>


            <router-link
              to="/forgot-password"
              style="
                color:var(--leaf-600);
                font-weight:600;
                font-size:.85rem;
              "
            >
              Lupa sandi?
            </router-link>

          </div>


          <!-- LOGIN -->
          <button
            type="submit"
            class="btn btn-primary btn-block"
            :disabled="loading"
          >

            <span v-if="loading">
              Memproses...
            </span>

            <span v-else>
              Masuk
            </span>

          </button>

        </form>

      </div>


      <!-- ========================= -->
      <!-- LOGIN STAF -->
      <!-- ========================= -->

      <div
        v-if="activeTab === 'login-staf'"
        id="login-staf"
        class="tab-panel active"
      >

        <form
          @submit.prevent="login('staf')"
        >

          <!-- EMAIL DINAS -->
          <div class="form-group">

            <label for="email2">
              Email Dinas *
            </label>

            <input
              id="email2"
              v-model="stafForm.email"
              class="input"
              type="text"
              placeholder="nama@harapanbangsa.sch.id"
              required
            >

          </div>


          <!-- PASSWORD -->
          <div class="form-group">

            <label for="pass2">
              Kata Sandi *
            </label>

            <input
              id="pass2"
              v-model="stafForm.password"
              class="input"
              type="password"
              placeholder="••••••••"
              required
            >

          </div>


          <!-- LOGIN -->
          <button
            type="submit"
            class="btn btn-primary btn-block"
            :disabled="loading"
          >

            <span v-if="loading">
              Memproses...
            </span>

            <span v-else>
              Masuk sebagai Staf
            </span>

          </button>

        </form>

      </div>


      <!-- ========================= -->
      <!-- DIVIDER -->
      <!-- ========================= -->

      <div class="divider-or">
        atau
      </div>


      <!-- REGISTER -->
      <p
        style="
          text-align:center;
          font-size:.88rem;
        "
      >

        Belum punya akun?

        <router-link
          to="/pendaftaran"
          style="
            color:var(--leaf-600);
            font-weight:600;
          "
        >
          Daftar sebagai siswa baru
        </router-link>

      </p>

    </div>

  </section>

</template>