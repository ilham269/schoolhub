<template>
  <DashboardLayout title="Materi" role-label="Murid" :navigation="navigation">
    <!-- Error state -->
    <UiAlert v-if="error" type="danger" class="mb-4" :dismissible="false">
      <div class="flex items-center justify-between gap-4">
        <span>{{ error }}</span>
        <button
          class="shrink-0 rounded-lg border border-rose-300 px-3 py-1 text-sm font-medium text-rose-700 hover:bg-rose-50"
          @click="load"
        >
          Coba lagi
        </button>
      </div>
    </UiAlert>

    <UiCard
      title="Materi Pembelajaran"
      subtitle="Materi yang telah dipublikasikan oleh guru untuk kelasmu."
      class="mt-4"
    >
      <!-- Loading -->
      <div v-if="loading" class="py-14 text-center text-slate-400">
        <svg class="mx-auto mb-3 h-8 w-8 animate-spin text-emerald-500" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
        </svg>
        Memuat materi...
      </div>

      <!-- No kelas warning -->
      <div v-else-if="noKelas" class="px-6 py-14 text-center">
        <p class="font-medium text-amber-700">Akun kamu belum terhubung ke kelas</p>
        <p class="mt-1 text-sm text-slate-400">Hubungi admin sekolah untuk menghubungkan akunmu ke kelas.</p>
      </div>

      <!-- Empty state -->
      <div v-else-if="!items.length" class="py-14 text-center">
        <p class="font-medium text-slate-600">Belum ada materi yang tersedia</p>
        <p class="mt-1 text-sm text-slate-400">Guru belum mempublikasikan materi untuk kelasmu saat ini.</p>
      </div>

      <!-- Materi list -->
      <div v-else class="divide-y divide-slate-100">
        <article
          v-for="materi in items"
          :key="materi.id"
          class="flex flex-col gap-3 px-6 py-5 sm:flex-row sm:items-start sm:justify-between"
        >
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <h2 class="font-semibold text-slate-800">{{ materi.judul }}</h2>
              <UiBadge variant="neutral" class="text-xs">
                {{ materi.mapel?.nama_mapel || '—' }}
              </UiBadge>
            </div>
            <p class="mt-0.5 text-sm text-slate-500">
              Guru: {{ materi.guru?.nama_lengkap_guru || materi.guru?.user?.name || '—' }}
              <span class="mx-1">·</span>
              Diupload: {{ formatDate(materi.tanggal_upload) }}
            </p>
            <p v-if="materi.deskripsi" class="mt-2 text-sm leading-relaxed text-slate-700 whitespace-pre-line">
              {{ materi.deskripsi }}
            </p>
          </div>

          <!-- Actions -->
          <div class="flex shrink-0 flex-wrap gap-2">
            <a
              v-if="materi.link"
              :href="materi.link"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50"
            >
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M11 5H7a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-4M13 3h4m0 0v4m0-4L9 11" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              Buka link
            </a>
            <button
              v-if="materi.file_path"
              :disabled="downloading === materi.id"
              class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-60"
              @click="download(materi)"
            >
              <svg v-if="downloading === materi.id" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
              </svg>
              <svg v-else class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M10 3v10m0 0-3-3m3 3 3-3M4 15v1a2 2 0 002 2h8a2 2 0 002-2v-1" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              {{ downloading === materi.id ? 'Mengunduh...' : 'Unduh materi' }}
            </button>
          </div>
        </article>
      </div>
    </UiCard>
  </DashboardLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import DashboardLayout from '@/components/dashboard/DashboardLayout.vue'
import UiAlert from '@/components/ui/UiAlert.vue'
import UiBadge from '@/components/ui/UiBadge.vue'
import UiCard from '@/components/ui/UiCard.vue'
import { muridNavigation as navigation } from './muridNavigation'
import { muridMateriApi } from '@/services/muridMateriApi'

const items     = ref([])
const loading   = ref(true)
const error     = ref('')
const noKelas   = ref(false)
const downloading = ref(null) // id materi yang sedang didownload

const formatDate = (value) =>
  value
    ? new Date(value).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
    : '—'

const load = async () => {
  loading.value = true
  error.value   = ''
  noKelas.value = false
  try {
    // muridMateriApi.list() mengembalikan response.data (body JSON dari Laravel)
    // Struktur: { data: [...] } atau { message: '...', data: [] } (kalau 422)
    const body = await muridMateriApi.list()

    if (body?.message?.includes('belum terhubung ke kelas')) {
      noKelas.value = true
      items.value   = []
      return
    }

    items.value = body?.data ?? []
  } catch (e) {
    const status = e.response?.status
    const msg    = e.response?.data?.message

    if (status === 422 && msg?.includes('belum terhubung ke kelas')) {
      noKelas.value = true
      items.value   = []
      return
    }

    const errorMap = {
      401: 'Sesi Anda telah berakhir. Silakan masuk kembali.',
      403: 'Anda tidak memiliki akses ke materi ini.',
      404: 'Materi tidak ditemukan.',
      500: 'Server sedang bermasalah. Silakan coba lagi.',
    }
    error.value = errorMap[status] ?? msg ?? 'Materi gagal dimuat. Silakan coba lagi.'
  } finally {
    loading.value = false
  }
}

const download = async (materi) => {
  if (downloading.value === materi.id) return
  downloading.value = materi.id
  error.value       = ''

  try {
    // muridMateriApi.download() mengembalikan axios response penuh (responseType: 'blob')
    // karena kita butuh headers dan blob — berbeda dengan list()/show() yang return body JSON
    const response = await muridMateriApi.download(materi.id)

    // Parse nama file dari Content-Disposition, fallback ke judul + ekstensi
    const disposition = response.headers?.['content-disposition'] ?? ''
    const match       = disposition.match(/filename[^;=\n]*=(['"]?)([^'";\n]+)\1/)
    const filename    = match?.[2]?.trim() || `${materi.judul}.pdf`

    const url  = URL.createObjectURL(response.data)
    const link = document.createElement('a')
    link.href  = url
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)

    // Cabut object URL setelah browser sempat memproses download
    setTimeout(() => URL.revokeObjectURL(url), 100)
  } catch (e) {
    const status = e.response?.status
    error.value  = status === 404
      ? 'File materi tidak ditemukan di server. Hubungi guru untuk mengunggah ulang.'
      : 'File gagal diunduh. Periksa koneksi dan coba lagi.'
  } finally {
    downloading.value = null
  }
}

onMounted(load)
</script>
