<template>
  <div class="berita-view">
    <Navbar />

    <section class="breadcrumb-hero">
      <div class="container">
        <h1
          v-motion
          :initial="{ opacity: 0, y: -20 }"
          :enter="{ opacity: 1, y: 0, transition: { duration: 600 } }"
        >
          Berita Sekolah
        </h1>
        <div 
          class="breadcrumb"
          v-motion
          :initial="{ opacity: 0 }"
          :enter="{ opacity: 1, transition: { duration: 600, delay: 200 } }"
        >
          <router-link to="/">Beranda</router-link>
          <span class="sep">/</span>
          <span class="current">Berita Sekolah</span>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container layout-with-sidebar sidebar-right">
        
        <div>
          
          <div 
            class="filter-pills"
            v-motion
            :initial="{ opacity: 0, y: 20 }"
            :enter="{ opacity: 1, y: 0, transition: { duration: 500, delay: 300 } }"
          >
            <button 
              :class="{ active: activeFilter === 'Semua' }" 
              @click="setFilter('Semua')"
            >
              Semua Berita
            </button>
            <button 
              :class="{ active: activeFilter === 'Akademik' }" 
              @click="setFilter('Akademik')"
            >
              Akademik
            </button>
            <button 
              :class="{ active: activeFilter === 'Kegiatan' }" 
              @click="setFilter('Kegiatan')"
            >
              Kegiatan
            </button>
            <button 
              :class="{ active: activeFilter === 'Prestasi' }" 
              @click="setFilter('Prestasi')"
            >
              Prestasi
            </button>
          </div>

          <div v-if="loading" style="text-align: center; padding: 40px;">
            <p>Memuat berita...</p>
          </div>

          <div v-else-if="error" style="text-align: center; padding: 40px;">
            <p style="color: var(--slate-500);">{{ error }}</p>
          </div>

          <div v-else class="news-grid">
            <div 
              v-for="(berita, index) in filteredBerita" 
              :key="berita.id" 
              class="news-card"
              v-motion
              :initial="{ opacity: 0, y: 40 }"
              :visible-once="{ opacity: 1, y: 0, transition: { duration: 500, delay: index * 100 } }"
            >
              <img 
                :src="berita.gambar || berita.image || 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=700&auto=format&fit=crop'" 
                :alt="berita.judul || berita.title"
              />
              <div class="body">
                <h3 style="font-size: 1.05rem; margin-top: 0;">
                  {{ berita.judul || berita.title }}
                </h3>
                <p>
                  {{ truncateText(berita.excerpt || berita.konten || berita.content, 120) }}
                </p>
                <div class="news-meta">
                  <span>&#128197; {{ formatDate(berita.tanggal || berita.published_at) }}</span>
                </div>
                <router-link 
                  :to="`/berita/${berita.slug || berita.id}`" 
                  class="link-arrow"
                >
                  Baca selengkapnya &rarr;
                </router-link>
              </div>
            </div>

            <div v-if="filteredBerita.length === 0" style="text-align: center; padding: 40px; grid-column: 1 / -1;">
              <p style="color: var(--slate-500);">Tidak ada berita untuk kategori ini.</p>
            </div>
          </div>

          <div v-if="totalPages > 1" class="pagination">
            <button 
              class="prev" 
              :disabled="currentPage === 1"
              @click="changePage(currentPage - 1)"
            >
              &larr;
            </button>
            
            <button 
              v-for="page in visiblePages" 
              :key="page"
              :class="{ active: currentPage === page }"
              @click="changePage(page)"
            >
              {{ page }}
            </button>
            
            <span v-if="totalPages > 5" class="dots">&hellip;</span>
            
            <button 
              v-if="totalPages > 5"
              @click="changePage(totalPages)"
            >
              {{ totalPages }}
            </button>
            
            <button 
              class="next"
              :disabled="currentPage === totalPages"
              @click="changePage(currentPage + 1)"
            >
              &rarr;
            </button>
          </div>

        </div>

        <aside 
          class="sidebar"
          v-motion
          :initial="{ opacity: 0, x: 30 }"
          :visible-once="{ opacity: 1, x: 0, transition: { duration: 600, delay: 200 } }"
        >
          
          <h4>Cari Berita</h4>
          <input 
            v-model="searchQuery"
            class="input" 
            type="text" 
            placeholder="Kata kunci..."
            @input="handleSearch"
          />

          <div class="sidebar-divider"></div>

          <h4>Berita Terbaru</h4>
          <ul>
            <li v-for="berita in recentNews" :key="berita.id">
              <router-link :to="`/berita/${berita.slug || berita.id}`">
                {{ berita.title || berita.judul }}
              </router-link>
            </li>
          </ul>

          <div class="sidebar-divider"></div>

          <h4>Tag Populer</h4>
          <div class="tag-cloud">
            <a href="#" @click.prevent="searchQuery = 'PPDB'">PPDB</a>
            <a href="#" @click.prevent="searchQuery = 'OSN'">OSN</a>
            <a href="#" @click.prevent="searchQuery = 'Ekskul'">Ekskul</a>
            <a href="#" @click.prevent="searchQuery = 'Beasiswa'">Beasiswa</a>
            <a href="#" @click.prevent="searchQuery = 'Ujian'">Ujian</a>
          </div>

        </aside>

      </div>
    </section>

    <Footer />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Navbar from '@/components/Navbar.vue'
import Footer from '@/components/Footer.vue'
import api from '@/utils/api'

// State
const daftarBerita = ref([])
const loading = ref(false)
const error = ref(null)
const activeFilter = ref('Semua')
const searchQuery = ref('')
const currentPage = ref(1)
const itemsPerPage = 6

// Recent news for sidebar (Otomatis ambil 5 teratas karena data udah disortir)
const recentNews = computed(() => {
  return daftarBerita.value.slice(0, 5)
})

// Fetch berita dari API
const fetchBerita = async () => {
  loading.value = true
  error.value = null
  
  try {
    // Try public endpoint first
    const response = await api.get('/public/berita')
    const result = response.data
    
    console.log('Hasil API Berita:', result)
    
    let rawData = []
    
    // Nampung data dari response API
    if (result.data) {
      rawData = result.data
    } else if (Array.isArray(result)) {
      rawData = result
    }
    
    // URUTKAN BERDASARKAN TANGGAL TERBARU (DESCENDING)
    rawData.sort((a, b) => {
      // Pastikan ngecek field tanggal, published_at, atau created_at
      const dateA = new Date(a.tanggal || a.published_at || a.created_at || 0)
      const dateB = new Date(b.tanggal || b.published_at || b.created_at || 0)
      
      return dateB - dateA
    })

    // Masukin data yang udah berurut ke state utama
    daftarBerita.value = rawData

  } catch (err) {
    console.error('Gagal mengambil data berita:', err)
    error.value = 'Gagal memuat berita dari database.'
    daftarBerita.value = []
  } finally {
    loading.value = false
  }
}

// Filter berita berdasarkan kategori dan search
const filteredBerita = computed(() => {
  let filtered = daftarBerita.value
  
  // Filter by category
  if (activeFilter.value !== 'Semua') {
    filtered = filtered.filter(berita => 
      (berita.kategori || berita.category) === activeFilter.value
    )
  }
  
  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(berita => {
      const judul = (berita.judul || berita.title || '').toLowerCase()
      const konten = (berita.konten || berita.content || '').toLowerCase()
      return judul.includes(query) || konten.includes(query)
    })
  }
  
  // Pagination
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  
  return filtered.slice(start, end)
})

// Total pages
const totalPages = computed(() => {
  let filtered = daftarBerita.value
  
  if (activeFilter.value !== 'Semua') {
    filtered = filtered.filter(berita => 
      (berita.kategori || berita.category) === activeFilter.value
    )
  }
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(berita => {
      const judul = (berita.judul || berita.title || '').toLowerCase()
      const konten = (berita.konten || berita.content || '').toLowerCase()
      return judul.includes(query) || konten.includes(query)
    })
  }
  
  return Math.ceil(filtered.length / itemsPerPage)
})

// Visible pages for pagination
const visiblePages = computed(() => {
  const pages = []
  const total = totalPages.value
  const current = currentPage.value
  
  if (total <= 5) {
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    if (current <= 3) {
      pages.push(1, 2, 3, 4)
    } else if (current >= total - 2) {
      pages.push(total - 3, total - 2, total - 1, total)
    } else {
      pages.push(current - 1, current, current + 1)
    }
  }
  
  return pages
})

// Methods
const setFilter = (filter) => {
  activeFilter.value = filter
  currentPage.value = 1 // Reset to first page
}

const handleSearch = () => {
  currentPage.value = 1 // Reset to first page on search
}

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const countByCategory = (category) => {
  return daftarBerita.value.filter(berita => 
    (berita.kategori || berita.category) === category
  ).length
}

const getBadgeClass = (category) => {
  const categoryMap = {
    'Akademik': 'badge-green',
    'Kegiatan': 'badge-amber',
    'Prestasi': 'badge-dark',
    'Pengumuman': 'badge-blue'
  }
  return categoryMap[category] || 'badge-green'
}

const truncateText = (text, maxLength) => {
  if (!text) return ''
  return text.length > maxLength 
    ? text.substring(0, maxLength) + '...' 
    : text
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  
  const date = new Date(dateString)
  const options = { day: 'numeric', month: 'short', year: 'numeric' }
  return date.toLocaleDateString('id-ID', options)
}

// Lifecycle
onMounted(() => {
  fetchBerita()
})
</script>

<style scoped>
/* Component specific styles if needed */
</style>