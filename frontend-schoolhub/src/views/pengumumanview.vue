<template>
  <div class="pengumuman-view">
    <Navbar />

    <section class="breadcrumb-hero">
      <div class="container">
        <h1>Pengumuman</h1>
        <div class="breadcrumb">
          <router-link to="/">Beranda</router-link><span class="sep">/</span>
          <span class="current">Pengumuman</span>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <!-- Alerts -->
        <div
          v-for="alert in visibleAlerts"
          :key="alert.id"
          class="alert"
          :class="`alert-${alert.type}`"
        >
          <span>{{ alert.icon }}</span>
          <div><strong>{{ alert.title }}</strong> {{ alert.message }}</div>
          <button class="alert-close" aria-label="Tutup" @click="closeAlert(alert.id)">&times;</button>
        </div>

        <!-- Tabs -->
        <div class="tabs">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            class="tab-btn"
            :class="{ active: activeTab === tab.id }"
            @click="activeTab = tab.id"
          >
            {{ tab.label }}
          </button>
        </div>

        <div v-for="tab in tabs" :key="tab.id" class="tab-panel" :class="{ active: activeTab === tab.id }">
          <div v-if="activeTab === tab.id" class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>Judul Pengumuman</th>
                  <th v-if="tab.id === 'tab-semua'">Kategori</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, idx) in filteredItems(tab.id)" :key="idx">
                  <td>{{ item.tanggal }}</td>
                  <td>{{ item.judul }}</td>
                  <td v-if="tab.id === 'tab-semua'">{{ item.kategori }}</td>
                  <td><span class="badge" :class="badgeClass(item.status)">{{ item.status }}</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pagination -->
        <div class="pagination">
          <button class="prev" :disabled="currentPage === 1" @click="currentPage = Math.max(1, currentPage - 1)">&larr;</button>
          <button
            v-for="page in totalPages"
            :key="page"
            :class="{ active: currentPage === page }"
            @click="currentPage = page"
          >
            {{ page }}
          </button>
          <button class="next" @click="currentPage = Math.min(totalPages, currentPage + 1)">&rarr;</button>
        </div>
      </div>
    </section>

    <Footer />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import Navbar from '@/components/Navbar.vue'
import Footer from '@/components/Footer.vue'

// ===== Alerts =====
const alerts = ref([
  {
    id: 1,
    type: 'info',
    icon: '\u2139',
    title: 'Jadwal Ujian Tengah Semester',
    message: 'Pelaksanaan UTS ganjil dimulai 15 September 2026. Kartu ujian dapat diunduh melalui portal siswa.'
  },
  {
    id: 2,
    type: 'warning',
    icon: '\u26A0',
    title: 'Pembayaran SPP Bulan September',
    message: 'Batas akhir pembayaran adalah tanggal 10. Keterlambatan dikenai denda administrasi.'
  }
])

const closeAlert = (id) => {
  alerts.value = alerts.value.filter(a => a.id !== id)
}
const visibleAlerts = computed(() => alerts.value)

// ===== Tabs =====
const tabs = [
  { id: 'tab-semua', label: 'Semua' },
  { id: 'tab-akademik', label: 'Akademik' },
  { id: 'tab-umum', label: 'Umum' }
]
const activeTab = ref('tab-semua')

// ===== Data pengumuman =====
const pengumumanList = [
  { tanggal: '01 Sep 2026', judul: 'Libur Nasional Peringatan Hari Raya', kategori: 'Umum', status: 'Aktif' },
  { tanggal: '28 Agu 2026', judul: 'Jadwal Ujian Tengah Semester Ganjil', kategori: 'Akademik', status: 'Aktif' },
  { tanggal: '20 Agu 2026', judul: 'Pembayaran SPP Bulan September', kategori: 'Umum', status: 'Segera Berakhir' },
  { tanggal: '15 Agu 2026', judul: 'Pendaftaran Ekstrakurikuler Semester Baru', kategori: 'Umum', status: 'Berakhir' },
  { tanggal: '10 Agu 2026', judul: 'Pengumpulan Tugas Proyek Kelas XII', kategori: 'Akademik', status: 'Berakhir' }
]

const filteredItems = (tabId) => {
  if (tabId === 'tab-akademik') return pengumumanList.filter(i => i.kategori === 'Akademik')
  if (tabId === 'tab-umum') return pengumumanList.filter(i => i.kategori === 'Umum')
  return pengumumanList
}

const badgeClass = (status) => {
  if (status === 'Aktif') return 'badge-green'
  if (status === 'Segera Berakhir') return 'badge-amber'
  return 'badge-red'
}

// ===== Pagination (visual only, sesuai HTML asli) =====
const currentPage = ref(1)
const totalPages = 2
</script>

<style scoped>
</style>