<template>
  <DashboardLayout title="Jadwal" role-label="Murid" :navigation="navigation">
    <UiAlert v-if="error" type="danger" title="Gagal memuat jadwal" class="mb-4" :dismissible="false">
      {{ error }}
    </UiAlert>

    <UiCard :padded="false" class="overflow-hidden">
      <!-- Header kalender -->
      <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-4">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">{{ tahunLabel }}</p>
          <h2 class="text-lg font-semibold text-slate-800">{{ bulanLabel }}</h2>
        </div>
        <div class="flex items-center gap-2">
          <UiTabs v-model="viewMode" :tabs="viewTabs" />
          <div class="flex items-center gap-1">
            <UiButton variant="secondary" size="icon" @click="goToday" title="Hari ini">
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
                <circle cx="10" cy="10" r="7" /><path d="M10 6v4l2.5 1.5" stroke-linecap="round" />
              </svg>
            </UiButton>
            <UiButton variant="secondary" size="icon" @click="prevMonth" title="Bulan sebelumnya">
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                <path d="M12 5l-5 5 5 5" />
              </svg>
            </UiButton>
            <UiButton variant="secondary" size="icon" @click="nextMonth" title="Bulan berikutnya">
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                <path d="M8 5l5 5-5 5" />
              </svg>
            </UiButton>
          </div>
        </div>
      </div>

      <div v-if="loading" class="px-6 py-14 text-center text-slate-400">Memuat jadwal...</div>

      <!-- Tampilan bulan (kalender) -->
      <template v-else-if="viewMode === 'bulan'">
        <div class="grid grid-cols-7 border-b-2 border-slate-200 bg-slate-50/70 text-xs font-semibold uppercase tracking-wide text-slate-500">
          <div v-for="h in HARI_URUT" :key="h"
               class="border-r border-slate-200 px-3 py-3 text-center last:border-r-0 sm:px-4 sm:text-left"
               :class="h === 'Minggu' ? 'text-rose-500' : ''">{{ h.slice(0, 3) }}</div>
        </div>
        <div class="grid grid-cols-7 border-l border-t border-slate-200">
          <div v-for="(cell, i) in calendarCells" :key="i"
               class="min-h-[100px] border-b border-r border-slate-200 p-2 sm:min-h-[130px] sm:p-2.5"
               :class="!cell.inMonth ? 'bg-slate-50/70' : 'bg-white'">
            <div class="flex items-center justify-between">
              <span class="grid h-7 w-7 place-items-center rounded-full text-sm font-semibold"
                    :class="tanggalClass(cell)">
                {{ cell.date.getDate() }}
              </span>
              <span v-if="isLibur(cell.date) && !cell.isToday" class="mr-0.5 h-1.5 w-1.5 rounded-full bg-rose-400" title="Tanggal libur" />
            </div>
            <p v-if="liburLabel(cell.date)" class="mt-0.5 truncate text-[10px] font-medium text-rose-500">{{ liburLabel(cell.date) }}</p>
            <div class="mt-1.5 space-y-1">
              <button v-for="j in cell.items.slice(0, 2)" :key="j.id" @click="openDetailFor(cell, j)"
                      class="block w-full truncate rounded-md px-1.5 py-1 text-left text-[11px] font-medium transition hover:opacity-80"
                      :class="chipColor(j.mapel_id)">
                {{ formatJam(j.jam_mulai) }} {{ j.mapel_name ?? mapelFallback(j) }}
              </button>
              <button v-if="cell.items.length > 2" @click="openDayDetail(cell)"
                      class="block w-full truncate rounded-md px-1.5 py-1 text-left text-[11px] font-medium text-slate-500 hover:bg-slate-100">
                +{{ cell.items.length - 2 }} lagi
              </button>
            </div>
          </div>
        </div>
      </template>

      <!-- Tampilan minggu (list per hari, seperti sebelumnya) -->
      <template v-else>
        <div class="px-6 pb-5 pt-4">
          <UiTabs v-model="activeDay" :tabs="dayTabs" />
        </div>
        <div v-if="!itemsForActiveDay.length" class="px-6 py-14 text-center">
          <p class="font-medium text-slate-600">Tidak ada jadwal di hari ini</p>
          <p class="mt-1 text-sm text-slate-400">Nikmati waktu luangmu, atau pilih hari lain di atas.</p>
        </div>
        <ul v-else class="divide-y divide-slate-100">
          <li v-for="j in itemsForActiveDay" :key="j.id" class="flex items-center gap-4 px-6 py-4">
            <div class="w-24 shrink-0 text-sm text-slate-500">
              <p class="font-medium text-slate-700">{{ formatJam(j.jam_mulai) }}</p>
              <p class="text-xs text-slate-400">{{ formatJam(j.jam_selesai) }}</p>
            </div>
            <div class="h-10 w-1 shrink-0 rounded-full" :class="isBerlangsungSekarang(j) ? 'bg-emerald-500' : 'bg-slate-200'" />
            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center gap-2">
                <p class="font-medium text-slate-800">{{ j.mapel_name ?? mapelFallback(j) }}</p>
                <UiBadge v-if="isBerlangsungSekarang(j)" variant="success">Sedang berlangsung</UiBadge>
              </div>
              <p class="mt-0.5 text-sm text-slate-400">{{ j.guru_name ?? '—' }} • Ruang {{ j.ruang || '—' }}</p>
            </div>
          </li>
        </ul>
      </template>
    </UiCard>

    <!-- Modal detail hari / jadwal -->
    <UiModal v-model="detailOpen" :title="detailTitle" subtitle="Jadwal pelajaran">
      <ul class="divide-y divide-slate-100 -mx-1">
        <li v-for="j in detailItems" :key="j.id" class="flex items-center gap-4 px-1 py-3">
          <div class="w-20 shrink-0 text-sm text-slate-500">
            <p class="font-medium text-slate-700">{{ formatJam(j.jam_mulai) }}</p>
            <p class="text-xs text-slate-400">{{ formatJam(j.jam_selesai) }}</p>
          </div>
          <span class="h-8 w-1 shrink-0 rounded-full" :class="chipColor(j.mapel_id, true)" />
          <div class="min-w-0 flex-1">
            <p class="font-medium text-slate-800">{{ j.mapel_name ?? mapelFallback(j) }}</p>
            <p class="text-sm text-slate-400">{{ j.guru_name ?? '—' }} • Ruang {{ j.ruang || '—' }}</p>
          </div>
        </li>
      </ul>
      <template #footer>
        <UiButton variant="secondary" @click="detailOpen = false">Tutup</UiButton>
      </template>
    </UiModal>
  </DashboardLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'
import UiCard from '@/components/ui/UiCard.vue'
import UiButton from '@/components/ui/UiButton.vue'
import UiBadge from '@/components/ui/UiBadge.vue'
import UiAlert from '@/components/ui/UiAlert.vue'
import UiTabs from '@/components/ui/UiTabs.vue'
import UiModal from '@/components/ui/UiModal.vue'
import { muridJadwalApi } from '@/services/muridJadwalApi'
import { muridProfileApi } from '@/services/muridProfileApi'

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

const HARI_URUT = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']
const HARI_JS_INDEX = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
const hariIni = HARI_JS_INDEX[new Date().getDay()]

// Daftar tanggal libur nasional / cuti bersama.
// Format: 'YYYY-MM-DD'. Tambahkan sendiri sesuai kalender akademik sekolahmu,
// atau ganti dengan hasil fetch dari API kalender libur nasional kalau ada.
const HARI_LIBUR = {
  '2026-01-01': 'Tahun Baru Masehi',
  '2026-03-19': 'Hari Raya Nyepi',
  '2026-03-20': 'Isra Mikraj',
  '2026-04-03': 'Wafat Isa Almasih',
  '2026-05-01': 'Hari Buruh',
  '2026-05-14': 'Kenaikan Isa Almasih',
  '2026-06-01': 'Hari Lahir Pancasila',
  '2026-08-17': 'Hari Kemerdekaan RI',
  '2026-12-25': 'Hari Raya Natal',
}

const toDateKey = (date) => {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

const isLibur = (date) => date.getDay() === 0 || Boolean(HARI_LIBUR[toDateKey(date)])
const liburLabel = (date) => HARI_LIBUR[toDateKey(date)] ?? null

const tanggalClass = (cell) => {
  if (cell.isToday) return 'bg-emerald-600 text-white'
  if (!cell.inMonth) return 'text-slate-300'
  if (isLibur(cell.date)) return 'text-rose-500'
  return 'text-slate-700'
}

const items = ref([])
const loading = ref(false)
const error = ref('')

const fetchAll = async () => {
  loading.value = true
  error.value = ''
  try {
    const profilRes = await muridProfileApi.get()
    const profil = profilRes.data ?? profilRes
    const kelasId = profil.kelas_id ?? profil.kelas?.id

    if (!kelasId) {
      error.value = 'Kelas milikmu belum terdaftar, hubungi wali kelas.'
      return
    }

    const res = await muridJadwalApi.byKelas(kelasId)
    items.value = res.data ?? res
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Jadwal gagal dimuat. Periksa koneksi ke server.'
  } finally {
    loading.value = false
  }
}

const formatJam = (val) => (val ? val.slice(0, 5) : '—')
const mapelFallback = (j) => `Mapel #${j.mapel_id}`

const itemsByHari = (hari) =>
  items.value.filter((j) => j.hari === hari).sort((a, b) => a.jam_mulai.localeCompare(b.jam_mulai))

/* ---------- toggle tampilan ---------- */
const viewMode = ref('bulan') // 'bulan' | 'minggu'
const viewTabs = [
  { value: 'bulan', label: 'Bulan' },
  { value: 'minggu', label: 'Minggu' },
]

/* ---------- tampilan minggu (tab per hari) ---------- */
const activeDay = ref(hariIni)
const dayTabs = computed(() =>
  HARI_URUT.map((h) => ({
    value: h,
    label: h === hariIni ? `${h} (hari ini)` : h,
    count: itemsByHari(h).length,
  })),
)
const itemsForActiveDay = computed(() => itemsByHari(activeDay.value))

const isBerlangsungSekarang = (j) => {
  if (activeDay.value !== hariIni) return false
  const now = new Date().toTimeString().slice(0, 8)
  return now >= j.jam_mulai && now <= j.jam_selesai
}

/* ---------- tampilan bulan (kalender) ---------- */
const cursor = ref(new Date()) // tanggal acuan bulan yang ditampilkan
const today = new Date()

const bulanLabel = computed(() =>
  cursor.value.toLocaleDateString('id-ID', { month: 'long' }),
)
const tahunLabel = computed(() => cursor.value.getFullYear())

const prevMonth = () => { cursor.value = new Date(cursor.value.getFullYear(), cursor.value.getMonth() - 1, 1) }
const nextMonth = () => { cursor.value = new Date(cursor.value.getFullYear(), cursor.value.getMonth() + 1, 1) }
const goToday = () => { cursor.value = new Date() }

const isSameDay = (a, b) =>
  a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate()

// Senin sebagai awal minggu (0 = Senin ... 6 = Minggu)
const dayIndexMonStart = (jsDay) => (jsDay === 0 ? 6 : jsDay - 1)

const calendarCells = computed(() => {
  const year = cursor.value.getFullYear()
  const month = cursor.value.getMonth()
  const firstOfMonth = new Date(year, month, 1)
  const startOffset = dayIndexMonStart(firstOfMonth.getDay())
  const gridStart = new Date(year, month, 1 - startOffset)

  const cells = []
  for (let i = 0; i < 42; i++) {
    const date = new Date(gridStart.getFullYear(), gridStart.getMonth(), gridStart.getDate() + i)
    const hari = HARI_JS_INDEX[date.getDay()]
    cells.push({
      date,
      inMonth: date.getMonth() === month,
      isToday: isSameDay(date, today),
      hari,
      items: itemsByHari(hari),
    })
  }
  // Buang baris terakhir kalau seluruhnya di luar bulan (kalender jadi 5 baris rapi)
  if (cells.slice(35).every((c) => !c.inMonth)) return cells.slice(0, 35)
  return cells
})

/* ---------- warna chip per mapel ---------- */
const PALETTE = [
  'bg-emerald-100 text-emerald-700',
  'bg-sky-100 text-sky-700',
  'bg-amber-100 text-amber-700',
  'bg-rose-100 text-rose-700',
  'bg-violet-100 text-violet-700',
  'bg-cyan-100 text-cyan-700',
]
const PALETTE_SOLID = ['bg-emerald-400', 'bg-sky-400', 'bg-amber-400', 'bg-rose-400', 'bg-violet-400', 'bg-cyan-400']
const chipColor = (mapelId, solid = false) => {
  const idx = (Number(mapelId) || 0) % PALETTE.length
  return solid ? PALETTE_SOLID[idx] : PALETTE[idx]
}

/* ---------- modal detail hari ---------- */
const detailOpen = ref(false)
const detailItems = ref([])
const detailTitle = ref('')

const openDayDetail = (cell) => {
  detailItems.value = cell.items
  detailTitle.value = cell.date.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' })
  detailOpen.value = true
}
const openDetailFor = (cell, jadwal) => {
  detailItems.value = [jadwal]
  detailTitle.value = cell.date.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' })
  detailOpen.value = true
}

onMounted(fetchAll)
</script>