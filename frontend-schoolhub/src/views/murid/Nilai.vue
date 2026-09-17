<template>
  <DashboardLayout title="Nilai Saya" role-label="Murid" :navigation="muridNavigation">
    <div v-if="loading" class="py-20 text-center text-slate-400">Memuat nilai...</div>
    <UiAlert v-else-if="error" type="danger" :dismissible="false"><div class="flex items-center justify-between gap-4"><span>{{ error }}</span><button class="shrink-0 rounded-lg border border-rose-300 px-3 py-1 text-sm font-medium text-rose-700 hover:bg-rose-50" @click="load">Coba lagi</button></div></UiAlert>
    <UiCard v-else-if="noKelas" class="mt-4"><div class="py-14 text-center"><p class="font-medium text-amber-700">Akun kamu belum terhubung ke kelas</p><p class="mt-1 text-sm text-slate-400">Hubungi admin sekolah untuk menghubungkan akunmu ke kelas.</p></div></UiCard>
    <template v-else>
      <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article v-for="card in cards" :key="card.label" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex items-start justify-between"><p class="text-sm text-slate-500">{{ card.label }}</p><i :class="[card.icon, card.color]"></i></div>
          <p class="mt-3 text-2xl font-bold text-slate-800">{{ card.value }}</p><p class="mt-1 text-xs text-slate-400">{{ card.note }}</p>
        </article>
      </section>

      <UiCard class="mt-6" title="Ringkasan ketuntasan" :subtitle="`Bobot nilai: tugas ${weights.tugas}% • ujian ${weights.ujian}%`">
        <div class="flex flex-wrap gap-2"><UiBadge variant="success">{{ summary.mapel_tuntas }} mapel di atas KKM</UiBadge><UiBadge v-if="summary.mapel_perlu_perhatian" variant="danger">{{ summary.mapel_perlu_perhatian }} mapel perlu perhatian</UiBadge></div>
      </UiCard>

      <UiCard class="mt-6" title="Nilai per mata pelajaran" subtitle="Nilai akhir memakai gabungan rata-rata tugas dan ujian.">
        <div v-if="!data.breakdown.length" class="empty">Belum ada nilai untuk semester ini.</div>
        <div v-else class="overflow-x-auto"><table class="w-full min-w-[680px] text-left text-sm"><thead><tr class="border-b border-slate-200 text-slate-400"><th>Mata pelajaran</th><th>Rata-rata tugas</th><th>Rata-rata ujian</th><th>Nilai akhir</th><th>KKM</th><th>Status</th></tr></thead><tbody><tr v-for="item in data.breakdown" :key="item.mapel_id" class="border-b border-slate-100 last:border-0"><td><b class="text-slate-700">{{ item.nama_mapel }}</b><small class="block text-slate-400">{{ item.kode_mapel }}</small></td><td>{{ score(item.rata_tugas) }}</td><td>{{ score(item.rata_ujian) }}</td><td class="font-semibold">{{ score(item.nilai_akhir) }}</td><td>{{ item.kkm }}</td><td><UiBadge v-if="item.nilai_akhir !== null" :variant="item.tuntas ? 'success' : 'danger'">{{ item.tuntas ? 'Tuntas' : 'Perlu perhatian' }}</UiBadge><span v-else class="text-slate-400">Belum ada nilai</span></td></tr></tbody></table></div>
      </UiCard>

      <section class="mt-6 grid gap-6 xl:grid-cols-2"><UiCard title="Tren nilai"><div v-if="data.trend.length" class="chart"><canvas ref="trendCanvas" /></div><p v-else class="empty">Belum ada data untuk grafik tren.</p></UiCard><UiCard title="Kekuatan per mata pelajaran"><div v-if="scoredBreakdown.length" class="chart"><canvas ref="mapelCanvas" /></div><p v-else class="empty">Belum ada data nilai per mata pelajaran.</p></UiCard></section>

      <section class="mt-6 grid gap-6 xl:grid-cols-2">
        <UiCard title="Tugas selesai" :subtitle="`${data.tugas_selesai.length} tugas sudah dikumpulkan`"><div v-if="data.tugas_selesai.length" class="space-y-4"><article v-for="item in data.tugas_selesai" :key="item.id" class="task"><div class="flex flex-wrap justify-between gap-2"><div><b>{{ item.judul }}</b><p>{{ item.mapel }} • {{ date(item.tanggal_pengumpulan) }}</p></div><div class="text-right"><b class="text-emerald-600">{{ score(item.nilai) }}</b><UiBadge :variant="item.terlambat ? 'warning' : 'success'" class="ml-2">{{ item.terlambat ? 'Terlambat' : 'Tepat waktu' }}</UiBadge></div></div><p v-if="item.feedback" class="feedback"><b>Feedback guru:</b> {{ item.feedback }}</p></article></div><p v-else class="empty">Belum ada tugas yang dikumpulkan.</p></UiCard>
        <UiCard title="Tugas belum dikumpulkan" :subtitle="`${data.tugas_belum_dikumpulkan.length} tugas menunggu`"><div v-if="data.tugas_belum_dikumpulkan.length" class="space-y-4"><article v-for="item in data.tugas_belum_dikumpulkan" :key="item.id" class="task"><div class="flex flex-wrap justify-between gap-2"><div><b>{{ item.judul }}</b><p>{{ item.mapel }} • Deadline {{ date(item.deadline) }}</p></div><UiBadge :variant="item.lewat_deadline ? 'danger' : 'neutral'">{{ item.lewat_deadline ? 'Lewat deadline' : 'Menunggu' }}</UiBadge></div></article></div><p v-else class="empty">Tidak ada tugas yang menunggu dikumpulkan.</p></UiCard>
      </section>

      <UiCard class="mt-6" title="Riwayat ujian"><div v-if="data.ujian.length" class="overflow-x-auto"><table class="w-full min-w-[620px] text-left text-sm"><thead><tr class="border-b border-slate-200 text-slate-400"><th>Ujian</th><th>Mapel</th><th>Tanggal</th><th>Skor</th><th>Nilai akhir</th><th>Status</th></tr></thead><tbody><tr v-for="item in data.ujian" :key="`${item.title}-${item.tanggal}`" class="border-b border-slate-100 last:border-0"><td><b>{{ item.title }}</b><small class="block text-slate-400">{{ item.type }}</small></td><td>{{ item.mapel }}</td><td>{{ date(item.tanggal) }}</td><td>{{ score(item.score) }}</td><td>{{ score(item.grade) }}</td><td><UiBadge :variant="item.status === 'Graded' ? 'success' : 'neutral'">{{ item.status }}</UiBadge></td></tr></tbody></table></div><p v-else class="empty">Belum ada riwayat ujian.</p></UiCard>
    </template>
  </DashboardLayout>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { Chart, BarController, BarElement, CategoryScale, Legend, LineController, LineElement, LinearScale, PointElement, Tooltip } from 'chart.js'
import DashboardLayout from '@/components/dashboard/DashboardLayout.vue'
import UiAlert from '@/components/ui/UiAlert.vue'
import UiBadge from '@/components/ui/UiBadge.vue'
import UiCard from '@/components/ui/UiCard.vue'
import { muridNavigation } from './muridNavigation'
import { muridNilaiApi } from '@/services/muridNilaiApi'

Chart.register(BarController, BarElement, CategoryScale, Legend, LineController, LineElement, LinearScale, PointElement, Tooltip)
const loading = ref(true); const error = ref(''); const noKelas = ref(false); const data = ref({ summary: {}, breakdown: [], tugas_selesai: [], tugas_belum_dikumpulkan: [], ujian: [], trend: [], weights: { tugas: .4, ujian: .6 } })
const trendCanvas = ref(null); const mapelCanvas = ref(null); let trendChart; let mapelChart
const summary = computed(() => data.value.summary); const weights = computed(() => ({ tugas: Math.round(data.value.weights.tugas * 100), ujian: Math.round(data.value.weights.ujian * 100) })); const scoredBreakdown = computed(() => data.value.breakdown.filter((item) => item.nilai_akhir !== null))
const cards = computed(() => [{ label: 'Rata-rata nilai', value: score(summary.value.rata_rata), note: 'Gabungan tugas dan ujian', icon: 'fas fa-chart-line', color: 'text-emerald-500' }, { label: 'Tugas selesai', value: `${summary.value.tugas_selesai ?? 0}/${summary.value.tugas_total ?? 0}`, note: 'Tugas aktif', icon: 'fas fa-book-check', color: 'text-blue-500' }, { label: 'Ujian diambil', value: summary.value.ujian_diambil ?? 0, note: 'Riwayat ujian', icon: 'fas fa-file-circle-check', color: 'text-amber-500' }, { label: 'Status KKM', value: `${summary.value.mapel_tuntas ?? 0} tuntas`, note: `${summary.value.mapel_perlu_perhatian ?? 0} perlu perhatian`, icon: 'fas fa-award', color: 'text-violet-500' }])
function score(value) { return value === null || value === undefined ? '—' : Number(value).toLocaleString('id-ID', { maximumFractionDigits: 2 }) }
function date(value) { return value ? new Date(value).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '—' }
function renderCharts() { trendChart?.destroy(); mapelChart?.destroy(); if (trendCanvas.value && data.value.trend.length) trendChart = new Chart(trendCanvas.value, { type: 'line', data: { labels: data.value.trend.map((item) => date(item.label)), datasets: [{ label: 'Nilai', data: data.value.trend.map((item) => item.nilai), borderColor: '#15935a', backgroundColor: 'rgba(21,147,90,.12)', fill: true, tension: .35 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { min: 0, max: 100 } } } }); if (mapelCanvas.value && scoredBreakdown.value.length) mapelChart = new Chart(mapelCanvas.value, { type: 'bar', data: { labels: scoredBreakdown.value.map((item) => item.nama_mapel), datasets: [{ label: 'Nilai akhir', data: scoredBreakdown.value.map((item) => item.nilai_akhir), backgroundColor: scoredBreakdown.value.map((item) => item.tuntas ? '#15935a' : '#dc4c4c'), borderRadius: 7 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { min: 0, max: 100 } }, plugins: { legend: { display: false } } } }) }
async function load() {
  loading.value = true
  error.value = ''
  noKelas.value = false

  try {
    const body = await muridNilaiApi.get()
    data.value = body ?? data.value
    await nextTick()
    renderCharts()
  } catch (e) {
    const status = e.response?.status
    const message = e.response?.data?.message

    if (status === 422 && message?.includes('belum terhubung ke kelas')) {
      noKelas.value = true
      return
    }

    const errorMap = {
      401: 'Sesi Anda telah berakhir. Silakan masuk kembali.',
      403: 'Anda tidak memiliki akses ke nilai ini.',
      404: 'Profil murid tidak ditemukan.',
      500: 'Server sedang bermasalah. Silakan coba lagi.',
    }

    error.value = errorMap[status] ?? message ?? 'Nilai gagal dimuat. Silakan coba lagi.'
  } finally {
    loading.value = false
  }
}
onMounted(load); onBeforeUnmount(() => { trendChart?.destroy(); mapelChart?.destroy() })
</script>

<style scoped>
th, td { padding: .85rem .6rem; } th:first-child, td:first-child { padding-left: 0; } th:last-child, td:last-child { padding-right: 0; } .chart { height: 280px; } .empty { padding: 2rem 0; text-align: center; color: #94a3b8; font-size: .9rem; } .task { border: 1px solid #e2e8f0; border-radius: .8rem; padding: 1rem; } .task p { margin: .25rem 0 0; color: #64748b; font-size: .82rem; } .feedback { white-space: pre-wrap; border-top: 1px solid #f1f5f9; padding-top: .75rem; margin-top: .75rem !important; color: #334155 !important; }
</style>
