<template>
  <DashboardLayout title="Materi" role-label="Murid" :navigation="navigation">
    <UiAlert v-if="error" type="danger" :dismissible="false">{{ error }}</UiAlert>
    <UiCard class="mt-4" title="Daftar Materi" subtitle="Materi pembelajaran yang telah dipublikasikan untuk kelasmu.">
      <p v-if="loading" class="py-10 text-center text-slate-400">Memuat materi...</p>
      <p v-else-if="!items.length" class="py-10 text-center text-slate-500">Belum ada materi yang tersedia.</p>
      <div v-else class="space-y-4">
        <article v-for="materi in items" :key="materi.id" class="rounded-xl border border-slate-200 p-5">
          <h2 class="font-semibold text-slate-800">{{ materi.judul }}</h2>
          <p class="mt-1 text-sm text-slate-500">{{ materi.mapel?.nama_mapel || 'Mata pelajaran' }} · Guru: {{ materi.guru?.nama_lengkap_guru || materi.guru?.user?.name || '—' }}</p>
          <p v-if="materi.deskripsi" class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ materi.deskripsi }}</p>
          <div class="mt-4 flex flex-wrap gap-3 text-sm font-medium">
            <button v-if="materi.file" class="text-emerald-700 hover:underline" @click="download(materi)">Unduh materi</button>
            <a v-if="materi.link" :href="materi.link" target="_blank" rel="noopener noreferrer" class="text-emerald-700 hover:underline">Buka link</a>
          </div>
          <p class="mt-4 text-xs text-slate-400">Dipublikasikan: {{ formatDate(materi.published_at) }}</p>
        </article>
      </div>
    </UiCard>
  </DashboardLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import DashboardLayout from '@/components/dashboard/DashboardLayout.vue'
import UiAlert from '@/components/ui/UiAlert.vue'
import UiCard from '@/components/ui/UiCard.vue'
import { muridNavigation as navigation } from './muridNavigation'
import { muridMateriApi } from '@/services/muridMateriApi'

const items = ref([])
const loading = ref(true)
const error = ref('')
const formatDate = (value) => value ? new Date(value).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '—'
const messageFor = (status) => ({ 401: 'Sesi Anda telah berakhir. Silakan masuk kembali.', 403: 'Anda tidak memiliki akses ke materi ini.', 404: 'Materi tidak ditemukan.', 422: 'Data materi tidak valid.', 500: 'Server sedang bermasalah. Silakan coba lagi.' })[status]

const load = async () => {
  loading.value = true
  error.value = ''
  try { const response = await muridMateriApi.list(); items.value = response.data ?? response } catch (e) { error.value = messageFor(e.response?.status) ?? 'Materi gagal dimuat. Silakan coba lagi.' } finally { loading.value = false }
}
const download = async (materi) => {
  try {
    const response = await muridMateriApi.download(materi.id)
    const url = URL.createObjectURL(response.data)
    const link = Object.assign(document.createElement('a'), { href: url, download: '' })
    link.click(); URL.revokeObjectURL(url)
  } catch (e) { error.value = messageFor(e.response?.status) ?? 'File materi gagal diunduh. Silakan coba lagi.' }
}
onMounted(load)
</script>
