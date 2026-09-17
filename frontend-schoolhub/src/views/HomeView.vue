<template>
  <div class="home-view">
    <Navbar />

    <!-- =========================
         HERO
    ========================== -->
    <section class="hero">
      <div class="container">
        <p
          class="eyebrow-dot"
          v-motion
          :initial="{ opacity: 0, y: -20 }"
          :enter="{
            opacity: 1,
            y: 0,
            transition: { duration: 600 }
          }"
        >
          Selamat Datang di SMK harapan Bangsa
        </p>

        <h1
          v-motion
          :initial="{ opacity: 0, y: 30 }"
          :enter="{
            opacity: 1,
            y: 0,
            transition: {
              duration: 600,
              delay: 150
            }
          }"
        >
          {{ home.school.hero_title }}
        </h1>

        <p
          v-motion
          :initial="{ opacity: 0, y: 30 }"
          :enter="{
            opacity: 1,
            y: 0,
            transition: {
              duration: 600,
              delay: 300
            }
          }"
        >
          {{ home.school.hero_description }}
        </p>

        <div
          class="hero-cta"
          v-motion
          :initial="{ opacity: 0, scale: 0.9 }"
          :enter="{
            opacity: 1,
            scale: 1,
            transition: {
              duration: 500,
              delay: 450
            }
          }"
        >
          <router-link
            class="btn btn-primary"
            to="/profile"
          >
            Selengkapnya &rarr;
          </router-link>

          <router-link
            class="btn btn-outline-light"
            to="/ppdb"
          >
            Info PPDB
          </router-link>
        </div>
      </div>
    </section>

    <!-- =========================
         FEATURE
    ========================== -->
    <div class="container">
      <div class="feature-row">
        <div
          class="feature-card hi"
          v-motion
          :initial="{ opacity: 0, y: 50 }"
          :visible-once="{
            opacity: 1,
            y: 0,
            transition: { duration: 500 }
          }"
        >
          <div class="feature-icon">&#128218;</div>

          <h3>
            Perpustakaan &amp; Buku
          </h3>

          <p>
            Koleksi lebih dari 12.000 judul buku fisik
            dan digital untuk menunjang riset siswa.
          </p>
        </div>

        <div
          class="feature-card"
          v-motion
          :initial="{ opacity: 0, y: 50 }"
          :visible-once="{
            opacity: 1,
            y: 0,
            transition: {
              duration: 500,
              delay: 100
            }
          }"
        >
          <div class="feature-icon">&#127891;</div>

          <h3>
            Pengajar Berpengalaman
          </h3>

          <p>
            Tenaga pendidik tersertifikasi dengan rata-rata
            10 tahun pengalaman mengajar.
          </p>
        </div>

        <div
          class="feature-card"
          v-motion
          :initial="{ opacity: 0, y: 50 }"
          :visible-once="{
            opacity: 1,
            y: 0,
            transition: {
              duration: 500,
              delay: 200
            }
          }"
        >
          <div class="feature-icon">&#127942;</div>

          <h3>
            Beasiswa Prestasi
          </h3>

          <p>
            Program beasiswa penuh dan sebagian bagi siswa
            berprestasi akademik maupun non-akademik.
          </p>
        </div>

        <div
          class="feature-card"
          v-motion
          :initial="{ opacity: 0, y: 50 }"
          :visible-once="{
            opacity: 1,
            y: 0,
            transition: {
              duration: 500,
              delay: 300
            }
          }"
        >
          <div class="feature-icon">&#128179;</div>

          <h3>
            Pembayaran Daring
          </h3>

          <p>
            Gerbang pembayaran SPP dan biaya sekolah yang
            aman, cepat, dan dapat dipantau orang tua.
          </p>
        </div>
      </div>
    </div>

    <!-- =========================
         ABOUT
    ========================== -->
    <section class="section">
      <div class="container split">
        <div
          v-motion
          :initial="{ opacity: 0, x: -50 }"
          :visible-once="{
            opacity: 1,
            x: 0,
            transition: { duration: 700 }
          }"
        >
          <img
            :src="home.school.about_image"
            :alt="`Siswa ${home.school.name}`"
          />
        </div>

        <div
          v-motion
          :initial="{ opacity: 0, x: 50 }"
          :visible-once="{
            opacity: 1,
            x: 0,
            transition: { duration: 700 }
          }"
        >
          <p class="eyebrow-dot dark">
            Tentang Sekolah Kami
          </p>

          <h2>
            {{ home.school.about_title }}
          </h2>

          <p>
            {{ home.school.about_description }}
          </p>

          <p v-if="home.school.about_description_2">
            {{ home.school.about_description_2 }}
          </p>

          <!-- Statistik dari backend -->
          <div class="stat-row">
            <div class="stat">
              <b>
                {{ formatNumber(home.statistics.students) }}+
              </b>

              <span>
                Siswa Aktif
              </span>
            </div>

            <div class="stat">
              <b>
                {{ formatNumber(home.statistics.teachers) }}
              </b>

              <span>
                Tenaga Pengajar
              </span>
            </div>

            <div class="stat">
              <b>
                {{ home.statistics.ptn_percentage }}%
              </b>

              <span>
                Kelulusan PTN
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- =========================
         GURU
    ========================== -->
    <section class="section section-dark">
      <div class="container">
        <p
          class="eyebrow-dot"
          style="text-align: center"
          v-motion
          :initial="{ opacity: 0 }"
          :visible-once="{
            opacity: 1,
            transition: { duration: 500 }
          }"
        >
          Guru Kami
        </p>

        <h2
          style="text-align: center; margin-bottom: 44px"
          v-motion
          :initial="{ opacity: 0, y: 20 }"
          :visible-once="{
            opacity: 1,
            y: 0,
            transition: {
              duration: 500,
              delay: 100
            }
          }"
        >
          Kenali Para Pengajar Terbaik Kami
        </h2>

        <!-- Loading -->
        <div
          v-if="loadingGuru"
          style="
            text-align: center;
            padding: 40px;
          "
        >
          <p>
            Memuat data guru...
          </p>
        </div>

        <!-- Error -->
        <div
          v-else-if="
            errorGuru &&
            daftarGuru.length === 0
          "
          style="
            text-align: center;
            padding: 40px;
          "
        >
          <p
            style="
              color: var(--slate-400);
            "
          >
            Data guru belum tersedia saat ini.
          </p>
        </div>

        <!-- Data guru -->
        <div
          v-else-if="daftarGuru.length > 0"
          class="people-grid"
        >
          <div
            class="person-card"
            v-for="(guru, index) in daftarGuru.slice(0, 4)"
            :key="guru.id"
            v-motion
            :initial="{
              opacity: 0,
              scale: 0.8
            }"
            :visible-once="{
              opacity: 1,
              scale: 1,
              transition: {
                duration: 500,
                delay: index * 150
              }
            }"
          >
            <div class="photo">
              <img
                :src="getGuruImage(guru)"
                :alt="
                  guru.nama_lengkap_guru ||
                  guru.nama ||
                  'Guru'
                "
                @error="handleImageError"
              />
            </div>

            <div class="info">
              <h4>
                {{
                  guru.nama_lengkap_guru ||
                  guru.nama ||
                  'Nama Guru'
                }}
              </h4>
            </div>
          </div>
        </div>

        <!-- Data kosong -->
        <div
          v-else
          style="
            text-align: center;
            padding: 40px;
          "
        >
          <p
            style="
              color: var(--slate-400);
            "
          >
            Data guru akan segera ditampilkan.
          </p>
        </div>

        <div
          style="
            text-align: center;
            margin-top: 36px;
          "
          v-motion
          :initial="{ opacity: 0 }"
          :visible-once="{
            opacity: 1,
            transition: {
              duration: 500,
              delay: 600
            }
          }"
        >
          <router-link
            class="btn btn-primary"
            to="/profil"
          >
            Lihat Semua Guru &rarr;
          </router-link>
        </div>
      </div>
    </section>

    <!-- =========================
         PROGRAM PEMINATAN (JURUSAN SMK)
    ========================== -->
    <section class="section">
      <div class="container">
        <div
          v-motion
          :initial="{
            opacity: 0,
            y: 30
          }"
          :visible-once="{
            opacity: 1,
            y: 0,
            transition: {
              duration: 600
            }
          }"
        >
          <p
            class="eyebrow-dot dark"
            style="text-align: center"
          >
            Temukan Sekolah Kami
          </p>

          <h2
            style="
              text-align: center;
              margin-bottom: 12px;
            "
          >
            Jenjang &amp; Program Keahlian
          </h2>

          <p
            style="
              text-align: center;
              max-width: 560px;
              margin-inline: auto 36px;
            "
          >
            Pilih jurusan yang paling sesuai
            dengan minat dan rencana masa depanmu.
          </p>
        </div>

        <!-- Filter -->
        <div
          class="filter-pills"
          style="
            justify-content: center;
            margin-top: 28px;
          "
          v-motion
          :initial="{ opacity: 0 }"
          :visible-once="{
            opacity: 1,
            transition: {
              duration: 600,
              delay: 200
            }
          }"
        >
          <button
            :class="{
              active: activeFilter === 'Semua'
            }"
            @click="setFilter('Semua')"
          >
            Semua
          </button>

          <button
            :class="{
              active: activeFilter === 'TKR'
            }"
            @click="setFilter('TKR')"
          >
            Teknik Kendaraan Ringan
          </button>

          <button
            :class="{
              active: activeFilter === 'RPL'
            }"
            @click="setFilter('RPL')"
          >
            Rekayasa Perangkat Lunak
          </button>

          <button
            :class="{
              active: activeFilter === 'TSM'
            }"
            @click="setFilter('TSM')"
          >
            Teknik Sepeda Motor
          </button>
        </div>

        <!-- Program dari backend -->
        <div
          v-if="loadingPrograms"
          style="
            text-align: center;
            padding: 40px;
          "
        >
          <p>
            Memuat program peminatan...
          </p>
        </div>

        <div
          v-else-if="filteredPrograms.length > 0"
          class="news-grid"
        >
          <div
            v-for="(program, index) in filteredPrograms"
            :key="program.id || index"
            class="news-card"
            v-motion
            :initial="{
              opacity: 0,
              y: 30
            }"
            :visible-once="{
              opacity: 1,
              y: 0,
              transition: {
                duration: 500,
                delay: (index + 1) * 100
              }
            }"
          >
            <img
              :src="
                program.gambar ||
                program.image ||
                defaultProgramImage
              "
              :alt="
                program.nama ||
                program.name ||
                'Program Peminatan'
              "
              @error="handleImageError"
            />

            <div class="body">
              <span
                class="badge"
                :class="
                  getProgramBadgeClass(program.kategori)
                "
              >
                {{
                  program.kategori ||
                  program.category ||
                  'Program'
                }}
              </span>

              <h3 style="font-size: 1.1rem">
                {{
                  program.nama ||
                  program.name ||
                  'Program Peminatan'
                }}
              </h3>

              <p>
                {{
                  program.deskripsi ||
                  program.description ||
                  'Informasi program belum tersedia.'
                }}
              </p>

              <router-link
                class="link-arrow"
                :to="
                  program.slug
                    ? `/program/${program.slug}`
                    : '/profile'
                "
              >
                Pelajari program &rarr;
              </router-link>
            </div>
          </div>
        </div>

        <!-- Fallback jika backend belum menyediakan program TKR/RPL/TSM -->
        <div
          v-else
          class="news-grid"
        >
          <!-- Teknik Kendaraan Ringan -->
          <div
            v-show="
              activeFilter === 'Semua' ||
              activeFilter === 'TKR'
            "
            class="news-card"
            v-motion
            :initial="{
              opacity: 0,
              y: 30
            }"
            :visible-once="{
              opacity: 1,
              y: 0,
              transition: {
                duration: 500,
                delay: 100
              }
            }"
          >
            <img
              src="https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?q=80&w=700&auto=format&fit=crop"
              alt="Teknik Kendaraan Ringan"
            />

            <div class="body">
              <span class="badge badge-green">
                TKR
              </span>

              <h3 style="font-size: 1.1rem">
                Teknik Kendaraan Ringan
              </h3>

              <p>
                Mempelajari perawatan, perbaikan, dan
                kelistrikan mobil sesuai standar industri
                otomotif terkini.
              </p>

              <router-link
                class="link-arrow"
                to="/profile"
              >
                Pelajari program &rarr;
              </router-link>
            </div>
          </div>

          <!-- Rekayasa Perangkat Lunak -->
          <div
            v-show="
              activeFilter === 'Semua' ||
              activeFilter === 'RPL'
            "
            class="news-card"
            v-motion
            :initial="{
              opacity: 0,
              y: 30
            }"
            :visible-once="{
              opacity: 1,
              y: 0,
              transition: {
                duration: 500,
                delay: 200
              }
            }"
          >
            <img
              src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=700&auto=format&fit=crop"
              alt="Rekayasa Perangkat Lunak"
            />

            <div class="body">
              <span class="badge badge-dark">
                RPL
              </span>

              <h3 style="font-size: 1.1rem">
                Rekayasa Perangkat Lunak
              </h3>

              <p>
                Fokus pada pemrograman, pengembangan
                aplikasi web/mobile, dan basis data untuk
                membekali siswa siap kerja di dunia IT.
              </p>

              <router-link
                class="link-arrow"
                to="/profile"
              >
                Pelajari program &rarr;
              </router-link>
            </div>
          </div>

          <!-- Teknik Sepeda Motor -->
          <div
            v-show="
              activeFilter === 'Semua' ||
              activeFilter === 'TSM'
            "
            class="news-card"
            v-motion
            :initial="{
              opacity: 0,
              y: 30
            }"
            :visible-once="{
              opacity: 1,
              y: 0,
              transition: {
                duration: 500,
                delay: 300
              }
            }"
          >
            <img
              src="https://images.unsplash.com/photo-1558981806-ec527fa84c39?q=80&w=700&auto=format&fit=crop"
              alt="Teknik Sepeda Motor"
            />

            <div class="body">
              <span class="badge badge-amber">
                TSM
              </span>

              <h3 style="font-size: 1.1rem">
                Teknik Sepeda Motor
              </h3>

              <p>
                Mempelajari mesin, sistem injeksi, dan
                perawatan sepeda motor konvensional maupun
                matic sesuai kebutuhan bengkel resmi.
              </p>

              <router-link
                class="link-arrow"
                to="/profile"
              >
                Pelajari program &rarr;
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- =========================
         PPDB CTA
    ========================== -->
    <section
      class="section-tight"
      style="background: var(--cream)"
    >
      <div
        class="container"
        style="text-align: center"
        v-motion
        :initial="{
          opacity: 0,
          scale: 0.9
        }"
        :visible-once="{
          opacity: 1,
          scale: 1,
          transition: {
            duration: 600
          }
        }"
      >
        <h2>
          {{
            home.ppdb.is_open
              ? 'Siap Bergabung dengan Kami?'
              : 'Informasi PPDB'
          }}
        </h2>

        <p
          style="
            max-width: 480px;
            margin-inline: auto 24px;
          "
        >
          <template v-if="home.ppdb.is_open">
            Pendaftaran siswa baru tahun ajaran
            {{ home.ppdb.year }} telah dibuka.
            Amankan kursimu sekarang.
          </template>

          <template v-else>
            Informasi pendaftaran siswa baru
            tahun ajaran {{ home.ppdb.year || 'mendatang' }}
            akan segera tersedia.
          </template>
        </p>

        <router-link
          class="btn btn-primary"
          to="/pendaftaran"
        >
          {{
            home.ppdb.is_open
              ? 'Daftar Sekarang'
              : 'Lihat Informasi'
          }}
          &rarr;
        </router-link>
      </div>
    </section>

    <Footer />
  </div>
</template>

<script setup>
import {
  ref,
  computed,
  onMounted
} from 'vue'

import Navbar from '@/components/Navbar.vue'
import Footer from '@/components/Footer.vue'
import api from '@/utils/api'

/*
|--------------------------------------------------------------------------
| Default Data
|--------------------------------------------------------------------------
| Digunakan supaya halaman tidak error ketika API belum mengembalikan
| semua field.
|--------------------------------------------------------------------------
*/

const defaultProgramImage =
  'https://images.unsplash.com/photo-1532094349884-543bc11b234d?q=80&w=700&auto=format&fit=crop'

const defaultGuruImage =
  'https://ui-avatars.com/api/?name=Guru&background=e2e8f0&color=475569'

const home = ref({
  school: {
    name: 'SMK Harapan Bangsa',

    hero_title:
      'Memimpin Jalan Menuju Pendidikan Tinggi Berkualitas',

    hero_description:
      'Tempat keunggulan akademik bertemu dengan pembentukan karakter, membekali setiap siswa untuk masa depan yang mereka pilih sendiri.',

    about_title:
      'Kami Akan Memberikan Masa Depan Untukmu',

    about_description:
      'Sudah menjadi kepercayaan luas bahwa pembaca akan teralihkan oleh isi bacaan yang mudah dibaca dan menarik, dibandingkan hanya melihat tata letaknya.',

    about_description_2:
      'Kami percaya setiap siswa memiliki potensi unik. Karena itu, kami menyediakan ruang eksplorasi minat lewat lebih dari 20 ekstrakurikuler dan kelas peminatan sejak kelas 10.',

    about_image:
      'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=900&auto=format&fit=crop'
  },

  statistics: {
    students: 0,
    teachers: 0,
    ptn_percentage: 0
  },

  teachers: [],

  programs: [],

  news: [],

  ppdb: {
    year: '2027/2028',
    is_open: true
  }
})

/*
|--------------------------------------------------------------------------
| Loading / Error
|--------------------------------------------------------------------------
*/

const loadingHome = ref(false)
const errorHome = ref(null)

const loadingGuru = ref(false)
const errorGuru = ref(null)

const loadingPrograms = ref(false)
const errorPrograms = ref(null)

/*
|--------------------------------------------------------------------------
| Guru
|--------------------------------------------------------------------------
*/

const daftarGuru = ref([])

/*
|--------------------------------------------------------------------------
| Filter Program
|--------------------------------------------------------------------------
*/

const activeFilter = ref('Semua')

const setFilter = (filterName) => {
  activeFilter.value = filterName
}

/*
|--------------------------------------------------------------------------
| Format Number
|--------------------------------------------------------------------------
*/

const formatNumber = (number) => {
  return new Intl.NumberFormat('id-ID').format(
    Number(number) || 0
  )
}

/*
|--------------------------------------------------------------------------
| Ambil URL gambar guru
|--------------------------------------------------------------------------
*/

const getGuruImage = (guru) => {
  return (
    guru.gambar_guru ||
    guru.foto ||
    guru.image ||
    guru.foto_guru ||
    defaultGuruImage
  )
}

/*
|--------------------------------------------------------------------------
| Filter Programs
|--------------------------------------------------------------------------
| Mendukung pencocokan lewat singkatan (TKR/RPL/TSM) maupun
| nama lengkap jurusan yang dikirim backend.
|--------------------------------------------------------------------------
*/

const jurusanKeywordMap = {
  TKR: ['tkr', 'kendaraan ringan'],
  RPL: ['rpl', 'perangkat lunak'],
  TSM: ['tsm', 'sepeda motor']
}

const filteredPrograms = computed(() => {
  if (!Array.isArray(home.value.programs)) {
    return []
  }

  // Hanya program yang kategorinya benar-benar cocok TKR/RPL/TSM yang
  // dipakai dari backend. Kalau backend mengirim data lain (mis. program
  // peminatan MIPA/IPS/Bahasa), data itu diabaikan di sini, dan halaman
  // otomatis jatuh ke kartu fallback TKR/RPL/TSM di bawah — sehingga tab
  // "Semua" pun tetap menampilkan tiga jurusan SMK yang benar, bukan
  // program yang tidak relevan dari backend.
  const allowedKeywords = Object.values(jurusanKeywordMap).flat()

  const getKategori = (program) =>
    String(
      program.kategori ||
        program.category ||
        program.jurusan ||
        program.nama_jurusan ||
        program.nama ||
        program.name ||
        ''
    ).toLowerCase()

  const relevantPrograms = home.value.programs.filter((program) =>
    allowedKeywords.some((keyword) => getKategori(program).includes(keyword))
  )

  if (activeFilter.value === 'Semua') {
    return relevantPrograms
  }

  const keywords =
    jurusanKeywordMap[activeFilter.value] || [
      activeFilter.value.toLowerCase()
    ]

  return relevantPrograms.filter((program) =>
    keywords.some((keyword) => getKategori(program).includes(keyword))
  )
})

/*
|--------------------------------------------------------------------------
| Badge Program
|--------------------------------------------------------------------------
*/

const getProgramBadgeClass = (kategori) => {
  const value = String(
    kategori || ''
  ).toLowerCase()

  if (
    value.includes('kendaraan ringan') ||
    value === 'tkr'
  ) {
    return 'badge-green'
  }

  if (
    value.includes('perangkat lunak') ||
    value === 'rpl'
  ) {
    return 'badge-dark'
  }

  if (
    value.includes('sepeda motor') ||
    value === 'tsm'
  ) {
    return 'badge-amber'
  }

  return 'badge-dark'
}

/*
|--------------------------------------------------------------------------
| Image Error Handler
|--------------------------------------------------------------------------
*/

const handleImageError = (event) => {
  if (
    event.target.src !== defaultGuruImage
  ) {
    event.target.src = defaultGuruImage
  }
}

/*
|--------------------------------------------------------------------------
| Fetch Homepage
|--------------------------------------------------------------------------
*/

const fetchHome = async () => {
  loadingHome.value = true
  errorHome.value = null

  try {
    const response =
      await api.get('/public/home')

    const result =
      response.data?.data ??
      response.data

    if (!result) {
      throw new Error(
        'Response homepage kosong.'
      )
    }

    /*
    |--------------------------------------------------------------------------
    | Merge dengan default object
    |--------------------------------------------------------------------------
    | Jadi kalau backend hanya mengirim sebagian field,
    | field lainnya tidak menjadi undefined.
    |--------------------------------------------------------------------------
    */

    home.value = {
      ...home.value,

      ...result,

      school: {
        ...home.value.school,
        ...(result.school || {})
      },

      statistics: {
        ...home.value.statistics,
        ...(result.statistics || {})
      },

      teachers:
        Array.isArray(result.teachers)
          ? result.teachers
          : home.value.teachers,

      programs:
        Array.isArray(result.programs)
          ? result.programs
          : home.value.programs,

      news:
        Array.isArray(result.news)
          ? result.news
          : home.value.news,

      ppdb: {
        ...home.value.ppdb,
        ...(result.ppdb || {})
      }
    }

    /*
    |--------------------------------------------------------------------------
    | Kalau endpoint home mengirim teachers,
    | langsung gunakan tanpa request kedua.
    |--------------------------------------------------------------------------
    */

    if (
      Array.isArray(result.teachers)
    ) {
      daftarGuru.value =
        result.teachers
    }
  } catch (error) {
    console.error(
      'Gagal mengambil data homepage:',
      error
    )

    errorHome.value =
      error.response?.data?.message ||
      error.message ||
      'Gagal mengambil data homepage.'
  } finally {
    loadingHome.value = false
  }
}

/*
|--------------------------------------------------------------------------
| Fetch Guru
|--------------------------------------------------------------------------
| Hanya dipanggil jika /public/home tidak mengirim teachers.
|--------------------------------------------------------------------------
*/

const fetchGuru = async () => {
  /*
  |--------------------------------------------------------------------------
  | Jangan request ulang kalau Home sudah memberikan data guru.
  |--------------------------------------------------------------------------
  */

  if (
    Array.isArray(home.value.teachers) &&
    home.value.teachers.length > 0
  ) {
    daftarGuru.value =
      home.value.teachers

    return
  }

  loadingGuru.value = true
  errorGuru.value = null

  try {
    const response =
      await api.get('/public/guru')

    const result =
      response.data?.data ??
      response.data

    if (Array.isArray(result)) {
      daftarGuru.value = result
    } else {
      daftarGuru.value = []
    }
  } catch (error) {
    console.error(
      'Gagal mengambil data guru:',
      error
    )

    errorGuru.value =
      error.response?.data?.message ||
      error.message ||
      'Gagal mengambil data guru.'

    daftarGuru.value = []
  } finally {
    loadingGuru.value = false
  }
}

/*
|--------------------------------------------------------------------------
| Fetch Program
|--------------------------------------------------------------------------
| Endpoint opsional.
|--------------------------------------------------------------------------
*/

const fetchPrograms = async () => {
  /*
  |--------------------------------------------------------------------------
  | Kalau /public/home sudah mengirim programs,
  | tidak perlu request tambahan.
  |--------------------------------------------------------------------------
  */

  if (
    Array.isArray(home.value.programs) &&
    home.value.programs.length > 0
  ) {
    return
  }

  loadingPrograms.value = true
  errorPrograms.value = null

  try {
    const response =
      await api.get('/public/programs')

    const result =
      response.data?.data ??
      response.data

    if (Array.isArray(result)) {
      home.value.programs =
        result
    }
  } catch (error) {
    /*
    |--------------------------------------------------------------------------
    | Program bersifat optional.
    |--------------------------------------------------------------------------
    | Jangan membuat seluruh Home error hanya karena endpoint program
    | belum tersedia.
    |--------------------------------------------------------------------------
    */

    console.warn(
      'Endpoint program belum tersedia:',
      error
    )

    errorPrograms.value =
      error.response?.data?.message ||
      error.message
  } finally {
    loadingPrograms.value = false
  }
}

/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(async () => {
  await fetchHome()

  /*
  |--------------------------------------------------------------------------
  | Ambil guru hanya jika belum ada dari endpoint Home.
  |--------------------------------------------------------------------------
  */

  await fetchGuru()

  /*
  |--------------------------------------------------------------------------
  | Ambil program jika belum ada dari endpoint Home.
  |--------------------------------------------------------------------------
  */

  await fetchPrograms()
})
</script>