<template>
  <DashboardLayout title="Tugas" role-label="Murid" :navigation="navigation">
    <UiAlert v-if="alert.show" :type="alert.type" class="mb-4" @close="alert.show = false">
      {{ alert.message }}
    </UiAlert>
    <UiAlert v-if="error" type="danger" title="Gagal memuat tugas" class="mb-4" :dismissible="false">
      {{ error }}
    </UiAlert>

    <!-- Ringkasan -->
    <div class="grid gap-4 sm:grid-cols-3 mb-6">
      <UiCard>
        <p class="text-sm text-slate-400">Total tugas</p>
        <p class="mt-1 text-3xl font-semibold text-slate-800">{{ items.length }}</p>
      </UiCard>
      <UiCard>
        <p class="text-sm text-slate-400">Belum dikumpulkan</p>
        <p class="mt-1 text-3xl font-semibold text-amber-600">{{ belumCount }}</p>
      </UiCard>
      <UiCard>
        <p class="text-sm text-slate-400">Sudah dikumpulkan</p>
        <p class="mt-1 text-3xl font-semibold text-emerald-600">{{ sudahCount }}</p>
      </UiCard>
    </div>

    <UiCard title="Daftar Tugas" subtitle="Tugas dari semua mata pelajaran di kelasmu" :padded="false">
      <div class="space-y-4 px-6 pb-5">
        <UiTabs v-model="activeTab" :tabs="tabs" />
        <div class="sm:max-w-xs">
          <UiInput v-model="search" placeholder="Cari judul tugas...">
            <template #icon>
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
                <circle cx="9" cy="9" r="5" /><path d="m13 13 4 4" stroke-linecap="round" />
              </svg>
            </template>
          </UiInput>
        </div>
      </div>

      <div v-if="loading" class="px-6 py-14 text-center text-slate-400">Memuat tugas...</div>
      <div v-else-if="!filtered.length" class="px-6 py-14 text-center">
        <p class="font-medium text-slate-600">Tidak ada tugas yang cocok</p>
        <p class="mt-1 text-sm text-slate-400">Coba ubah kata kunci atau tab filter.</p>
      </div>

      <ul v-else class="divide-y divide-slate-100">
        <li v-for="t in filtered" :key="t.id" class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <p class="font-medium text-slate-800">{{ t.judul }}</p>
              <UiBadge :variant="statusTone(t)">{{ statusLabel(t) }}</UiBadge>
            </div>
            <p class="mt-0.5 text-sm text-slate-400">{{ t.mapel_nama || mapelName(t.mapel_id) }} • Tenggat {{ formatTanggal(t.deadline) }}</p>
            <p v-if="t.pengumpulan?.nilai != null" class="mt-1 text-sm font-medium text-emerald-700">
              Nilai: {{ t.pengumpulan.nilai }} / {{ t.nilai_maksimal }}
            </p>
          </div>
          <div class="flex shrink-0 gap-2">
            <UiButton variant="ghost" size="sm" @click="openDetail(t)">Lihat detail</UiButton>
            <UiButton size="sm" :variant="t.pengumpulan ? 'secondary' : 'primary'" @click="openSubmit(t)">
              {{ t.pengumpulan ? 'Kumpul ulang' : 'Kumpulkan' }}
            </UiButton>
          </div>
        </li>
      </ul>
    </UiCard>

    <!-- Modal detail tugas -->
    <UiModal v-model="detailOpen" :title="detail?.judul" subtitle="Detail tugas">
      <div v-if="detail" class="space-y-4">
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div><p class="text-slate-400">Mata pelajaran</p><p class="mt-0.5 font-medium">{{ detail.mapel_nama || mapelName(detail.mapel_id) }}</p></div>
          <div><p class="text-slate-400">Tenggat</p><p class="mt-0.5 font-medium">{{ formatTanggal(detail.deadline) }}</p></div>
          <div><p class="text-slate-400">Nilai maksimal</p><p class="mt-0.5 font-medium">{{ detail.nilai_maksimal }}</p></div>
          <div><p class="text-slate-400">Status</p><UiBadge class="mt-1" :variant="statusTone(detail)">{{ statusLabel(detail) }}</UiBadge></div>
        </div>
        <div>
          <p class="text-sm text-slate-400">Deskripsi</p>
          <p class="mt-1 text-sm text-slate-700">{{ detail.deskripsi || '—' }}</p>
        </div>
        <div>
          <p class="text-sm text-slate-400">Instruksi pengumpulan</p>
          <p class="mt-1 text-sm text-slate-700">{{ detail.instruksi || '—' }}</p>
        </div>
        <a v-if="detail.file_path" :href="detail.file_path" target="_blank" rel="noopener"
           class="inline-flex items-center gap-2 text-sm font-medium text-emerald-600 hover:underline">
          <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M10 3v10m0 0-3-3m3 3 3-3M4 15v1a2 2 0 002 2h8a2 2 0 002-2v-1" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          Unduh lampiran soal
        </a>
        <div v-if="detail.pengumpulan" class="rounded-xl bg-emerald-50 p-4 text-sm">
          <p class="font-medium text-emerald-800">Jawabanmu sudah dikumpulkan</p>
          <p class="mt-1 text-emerald-700">Dikumpulkan pada {{ formatTanggal(detail.pengumpulan.dikumpulkan_at) }}</p>
          <p v-if="detail.pengumpulan.nilai != null" class="mt-1 text-emerald-700">Nilai: {{ detail.pengumpulan.nilai }} / {{ detail.nilai_maksimal }}</p>
          <p v-if="detail.pengumpulan.catatan_guru" class="mt-1 text-emerald-700">Catatan guru: {{ detail.pengumpulan.catatan_guru }}</p>
        </div>
      </div>
      <template #footer>
        <UiButton variant="secondary" @click="detailOpen = false">Tutup</UiButton>
        <UiButton @click="detailOpen = false; openSubmit(detail)">
          {{ detail?.pengumpulan ? 'Kumpul ulang' : 'Kumpulkan tugas' }}
        </UiButton>
      </template>
    </UiModal>

    <!-- Modal kumpulkan tugas -->
    <UiModal v-model="submitOpen" :title="`Kumpulkan: ${submitTarget?.judul ?? ''}`" subtitle="Unggah jawabanmu sebelum tenggat waktu.">
      <div class="space-y-4">
        <UiAlert v-if="submitTarget && isLewat(submitTarget)" type="warning" :dismissible="false">
          Tenggat tugas ini sudah lewat. Kamu mungkin masih bisa mengumpulkan, tapi hubungi gurumu kalau ada kebijakan keterlambatan.
        </UiAlert>
        <div>
          <span class="mb-1.5 block text-sm font-medium text-slate-700">File jawaban</span>
          <input type="file" class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0
                                     file:bg-emerald-50 file:px-3.5 file:py-2 file:text-sm file:font-medium file:text-emerald-700
                                     hover:file:bg-emerald-100"
                 @change="submitForm.file = $event.target.files[0]" />
          <p v-if="errors.file" class="mt-1.5 text-xs text-rose-600">{{ errors.file }}</p>
        </div>
        <UiTextarea v-model="submitForm.catatan" label="Catatan untuk guru (opsional)" :rows="3" placeholder="Contoh: maaf terlambat karena..." />
      </div>
      <template #footer>
        <UiButton variant="secondary" @click="submitOpen = false">Batal</UiButton>
        <UiButton :loading="submitting" @click="submitTugas">Kirim jawaban</UiButton>
      </template>
    </UiModal>
  </DashboardLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'
import UiCard from '@/components/ui/UiCard.vue'
import UiButton from '@/components/ui/UiButton.vue'
import UiInput from '@/components/ui/UiInput.vue'
import UiTextarea from '@/components/ui/UiTextarea.vue'
import UiModal from '@/components/ui/UiModal.vue'
import UiBadge from '@/components/ui/UiBadge.vue'
import UiAlert from '@/components/ui/UiAlert.vue'
import UiTabs from '@/components/ui/UiTabs.vue'
import { muridTugasApi } from '@/services/muridTugasApi'

const navigation = [
  { label: 'Dashboard', icon: 'fas fa-chart-pie', to: '/dashboard/murid' },
  { label: 'Profil', icon: 'fas fa-user', to: '/dashboard/murid/profil' },
  { label: 'Tugas', icon: 'fas fa-book-open', to: '/dashboard/murid/tugas' },
  { label: 'Nilai', icon: 'fas fa-chart-bar', to: '/dashboard/murid/nilai' },
  { label: 'Jadwal', icon: 'fas fa-calendar', to: '/dashboard/murid/jadwal' },
  { label: 'Ujian', icon: 'fas fa-laptop', to: '/dashboard/murid/ujian' },
  { label: 'Administrasi', icon: 'fas fa-folder', to: '/dashboard/murid/administrasi' },
  { label: 'Keuangan', icon: 'fas fa-file-invoice', to: '/dashboard/murid/keuangan' },
]

const items = ref([])
const loading = ref(false)
const error = ref('')

const fetchAll = async () => {
  loading.value = true
  error.value = ''
  try {
    const res = await muridTugasApi.list()
    items.value = res.data ?? res
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Tugas gagal dimuat. Periksa koneksi ke server.'
  } finally {
    loading.value = false
  }
}

const mapelName = (id) => `Mapel #${id}`
const formatTanggal = (val) =>
  val ? new Date(val).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—'

const isLewat = (t) => new Date(t.deadline) < new Date()

const statusInfo = (t) => {
  if (t.pengumpulan?.nilai != null) return { label: 'Sudah dinilai', tone: 'success' }
  if (t.pengumpulan) return { label: 'Sudah dikumpulkan', tone: 'info' }
  if (isLewat(t)) return { label: 'Terlambat', tone: 'danger' }
  return { label: 'Belum dikumpulkan', tone: 'warning' }
}
const statusLabel = (t) => statusInfo(t).label
const statusTone = (t) => statusInfo(t).tone

/* ---------- filter ---------- */
const search = ref('')
const activeTab = ref('semua')
const tabs = computed(() => [
  { value: 'semua', label: 'Semua', count: items.value.length },
  { value: 'belum', label: 'Belum dikumpulkan', count: items.value.filter((t) => statusLabel(t) === 'Belum dikumpulkan').length },
  { value: 'sudah', label: 'Sudah dikumpulkan', count: items.value.filter((t) => t.pengumpulan).length },
  { value: 'terlambat', label: 'Terlambat', count: items.value.filter((t) => statusLabel(t) === 'Terlambat').length },
])

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  return items.value.filter((t) => {
    const cocokTab =
      activeTab.value === 'semua' ||
      (activeTab.value === 'belum' && statusLabel(t) === 'Belum dikumpulkan') ||
      (activeTab.value === 'sudah' && t.pengumpulan) ||
      (activeTab.value === 'terlambat' && statusLabel(t) === 'Terlambat')
    const cocokCari = !q || t.judul.toLowerCase().includes(q)
    return cocokTab && cocokCari
  })
})

const belumCount = computed(() => items.value.filter((t) => statusLabel(t) === 'Belum dikumpulkan').length)
const sudahCount = computed(() => items.value.filter((t) => t.pengumpulan).length)

/* ---------- detail ---------- */
const detail = ref(null)
const detailOpen = ref(false)
const openDetail = (t) => { detail.value = t; detailOpen.value = true }

/* ---------- kumpulkan tugas ---------- */
const submitOpen = ref(false)
const submitting = ref(false)
const submitTarget = ref(null)
const submitForm = reactive({ file: null, catatan: '' })
const errors = reactive({})

const openSubmit = (t) => {
  submitTarget.value = t
  Object.assign(submitForm, { file: null, catatan: '' })
  Object.keys(errors).forEach((k) => delete errors[k])
  submitOpen.value = true
}

const submitTugas = async () => {
  Object.keys(errors).forEach((k) => delete errors[k])
  if (!submitForm.file) {
    errors.file = 'Pilih file jawaban terlebih dahulu.'
    return
  }
  submitting.value = true
  try {
    const fd = new FormData()
    fd.append('file', submitForm.file)
    fd.append('catatan', submitForm.catatan)
    const res = await muridTugasApi.submit(submitTarget.value.id, fd)
    const updatedPengumpulan = res.data ?? res

    const i = items.value.findIndex((t) => t.id === submitTarget.value.id)
    if (i !== -1) items.value[i] = { ...items.value[i], pengumpulan: updatedPengumpulan }

    notify('success', 'Jawaban berhasil dikumpulkan.')
    submitOpen.value = false
  } catch (e) {
    notify('danger', e.response?.data?.message ?? 'Jawaban gagal dikirim. Coba lagi.')
  } finally {
    submitting.value = false
  }
}

/* ---------- notifikasi ---------- */
const alert = reactive({ show: false, type: 'success', message: '' })
let alertTimer
const notify = (type, message) => {
  Object.assign(alert, { show: true, type, message })
  clearTimeout(alertTimer)
  alertTimer = setTimeout(() => (alert.show = false), 4000)
}

onMounted(fetchAll)
</script>