<template>
  <DashboardLayout title="Keuangan" role-label="Murid" :navigation="navigation">
    <section class="welcome">
      <div>
        <span class="eyebrow-dot dark">Administrasi Keuangan</span>
        <h2>Tagihan & Pembayaran SPP</h2>
        <p>Pantau status tagihan SPP dan riwayat pembayaran kamu.</p>
      </div>
      <i class="fas fa-wallet"></i>
    </section>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <i class="fas fa-spinner fa-spin"></i>
      <p>Memuat data keuangan...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="error-state">
      <i class="fas fa-exclamation-circle"></i>
      <p>{{ error }}</p>
      <button class="btn-retry" @click="loadKeuangan">
        <i class="fas fa-redo"></i> Coba Lagi
      </button>
    </div>

    <template v-else>
      <!-- Ringkasan -->
      <section class="summary-row">
        <div class="summary-card total">
          <i class="fas fa-file-invoice-dollar"></i>
          <div>
            <b>{{ formatRupiah(summary.totalTagihan) }}</b>
            <span>Total Tagihan Tahun Ini</span>
          </div>
        </div>
        <div class="summary-card lunas">
          <i class="fas fa-circle-check"></i>
          <div>
            <b>{{ formatRupiah(summary.sudahDibayar) }}</b>
            <span>Sudah Dibayar</span>
          </div>
        </div>
        <div class="summary-card belum">
          <i class="fas fa-hourglass-half"></i>
          <div>
            <b>{{ formatRupiah(summary.belumDibayar) }}</b>
            <span>Belum Dibayar</span>
          </div>
        </div>
        <div class="summary-card tunggak">
          <i class="fas fa-triangle-exclamation"></i>
          <div>
            <b>{{ summary.tunggakan }} bulan</b>
            <span>Tunggakan</span>
          </div>
        </div>
      </section>

      <!-- Tabel Tagihan Bulanan -->
      <section class="panel">
        <h3>Status Tagihan SPP per Bulan</h3>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Bulan</th>
                <th>Nominal</th>
                <th>Jatuh Tempo</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in tagihanList" :key="t.id">
                <td>{{ t.bulan }}</td>
                <td>{{ formatRupiah(t.nominal) }}</td>
                <td>{{ formatTanggal(t.jatuh_tempo) }}</td>
                <td>
                  <span class="status-badge" :class="t.status">{{ statusLabel(t.status) }}</span>
                </td>
                <td>
                  <button v-if="t.status !== 'lunas'" class="btn-bayar" @click="bayarSekarang(t)">
                    <i class="fas fa-credit-card"></i> Bayar
                  </button>
                  <span v-else class="paid-note">
                    <i class="fas fa-check"></i> Lunas
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Riwayat Pembayaran -->
      <section class="panel">
        <h3>Riwayat Pembayaran</h3>
        <div class="table-wrap">
          <table class="data-table" v-if="riwayatList.length">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Metode</th>
                <th>Nominal</th>
                <th>No. Referensi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in riwayatList" :key="r.id">
                <td>{{ formatTanggal(r.tanggal) }}</td>
                <td>{{ r.keterangan }}</td>
                <td>{{ r.metode }}</td>
                <td>{{ formatRupiah(r.nominal) }}</td>
                <td class="ref">{{ r.referensi }}</td>
              </tr>
            </tbody>
          </table>
          <div v-else class="empty-state">
            <i class="fas fa-receipt"></i>
            <p>Belum ada riwayat pembayaran.</p>
          </div>
        </div>
      </section>
    </template>

    <!-- Modal info bayar (placeholder, belum terhubung payment gateway) -->
    <div v-if="showBayarModal" class="modal-overlay" @click.self="showBayarModal = false">
      <div class="modal-box">
        <h3>Bayar Tagihan</h3>
        <p class="modal-desc">
          Tagihan SPP <strong>{{ selectedTagihan?.bulan }}</strong> sebesar
          <strong>{{ formatRupiah(selectedTagihan?.nominal) }}</strong>.
        </p>
        <p class="modal-note">
          <i class="fas fa-circle-info"></i>
          Fitur pembayaran online belum aktif. Silakan lakukan pembayaran melalui bagian tata usaha sekolah.
        </p>
        <div class="modal-actions">
          <button class="btn-secondary" @click="showBayarModal = false">Tutup</button>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'
// import api from '../../utils/api' // aktifkan lagi setelah backend keuangan murid siap

const muridItems = [
  ['Profil', 'fas fa-id-badge', 'profil'],
  ['Tugas', 'fas fa-list-check', 'tugas'],
  ['Nilai', 'fas fa-star', 'nilai'],
  ['Jadwal', 'fas fa-calendar-days', 'jadwal'],
  ['Ujian', 'fas fa-file-pen', 'ujian'],
  ['Administrasi', 'fas fa-clipboard-check', 'administrasi'],
  ['Keuangan', 'fas fa-wallet', 'keuangan'],
]

const navigation = [
  { label: 'Dashboard', icon: 'fas fa-chart-pie', to: '/dashboard/murid' },
  ...muridItems.map(([label, icon, slug]) => ({ label, icon, to: `/dashboard/murid/${slug}` })),
]

const loading = ref(true)
const error = ref(null)

const tagihanList = ref([])
const riwayatList = ref([])

const showBayarModal = ref(false)
const selectedTagihan = ref(null)

const statusLabel = (status) => {
  const map = { lunas: 'Lunas', belum_bayar: 'Belum Bayar', terlambat: 'Terlambat' }
  return map[status] || status || '-'
}

const formatRupiah = (angka) => {
  const n = Number(angka) || 0
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n)
}

const formatTanggal = (tgl) => {
  if (!tgl) return '-'
  const date = new Date(tgl)
  if (isNaN(date.getTime())) return tgl
  return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
}

const summary = computed(() => {
  const totalTagihan = tagihanList.value.reduce((sum, t) => sum + Number(t.nominal || 0), 0)
  const sudahDibayar = tagihanList.value
    .filter((t) => t.status === 'lunas')
    .reduce((sum, t) => sum + Number(t.nominal || 0), 0)
  const belumDibayar = totalTagihan - sudahDibayar
  const tunggakan = tagihanList.value.filter((t) => t.status === 'terlambat').length
  return { totalTagihan, sudahDibayar, belumDibayar, tunggakan }
})

const bayarSekarang = (tagihan) => {
  selectedTagihan.value = tagihan
  showBayarModal.value = true
}

const loadKeuangan = async () => {
  loading.value = true
  error.value = null

  // --- DATA CONTOH (sementara, sebelum backend keuangan murid dibuat) ---
  // Setelah backend siap, ganti isi fungsi ini menjadi pemanggilan API, misal:
  // const [tagihanRes, riwayatRes] = await Promise.all([
  //   api.get('/murid/keuangan/tagihan'),
  //   api.get('/murid/keuangan/riwayat'),
  // ])
  // tagihanList.value = tagihanRes.data?.data ?? []
  // riwayatList.value = riwayatRes.data?.data ?? []
  await new Promise((resolve) => setTimeout(resolve, 300))

  tagihanList.value = [
    { id: 1, bulan: 'Juli 2026', nominal: 350000, jatuh_tempo: '2026-07-10', status: 'lunas' },
    { id: 2, bulan: 'Agustus 2026', nominal: 350000, jatuh_tempo: '2026-08-10', status: 'lunas' },
    { id: 3, bulan: 'September 2026', nominal: 350000, jatuh_tempo: '2026-09-10', status: 'terlambat' },
    { id: 4, bulan: 'Oktober 2026', nominal: 350000, jatuh_tempo: '2026-10-10', status: 'belum_bayar' },
  ]

  riwayatList.value = [
    { id: 1, tanggal: '2026-07-08', keterangan: 'SPP Juli 2026', metode: 'Transfer Bank', nominal: 350000, referensi: 'TRX-20260708-001' },
    { id: 2, tanggal: '2026-08-09', keterangan: 'SPP Agustus 2026', metode: 'Virtual Account', nominal: 350000, referensi: 'TRX-20260809-014' },
  ]

  loading.value = false
}

onMounted(() => {
  loadKeuangan()
})
</script>

<style scoped>
.welcome {
  background: linear-gradient(110deg, #06231a, #1c9c5f);
  padding: 27px 30px;
  border-radius: 18px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}
.welcome h2 {
  color: #fff;
  font-size: 1.35rem;
  margin: 4px 0;
}
.welcome p {
  color: #d9efe0;
  margin: 0;
}
.welcome > i {
  font-size: 3rem;
  color: #8fd94a;
}

.summary-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
  margin-bottom: 24px;
}
@media (max-width: 900px) {
  .summary-row {
    grid-template-columns: repeat(2, 1fr);
  }
}
.summary-card {
  background: #fff;
  border: 1px solid #eef2f0;
  border-radius: 14px;
  padding: 18px;
  display: flex;
  align-items: center;
  gap: 14px;
}
.summary-card i {
  font-size: 1.3rem;
  width: 42px;
  height: 42px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex: none;
}
.summary-card.total i { background: #eaf1fd; color: #2563eb; }
.summary-card.lunas i { background: #e3f6ea; color: #1c9c5f; }
.summary-card.belum i { background: #fdf1dc; color: #a9711f; }
.summary-card.tunggak i { background: #fbe4e4; color: #d64545; }
.summary-card b {
  display: block;
  font-size: 1.05rem;
  color: #06231a;
}
.summary-card span {
  font-size: 0.78rem;
  color: #64748b;
}

.panel {
  margin-bottom: 28px;
}
.panel h3 {
  font-size: 1rem;
  color: #06231a;
  margin: 0 0 12px;
}

.table-wrap {
  background: #fff;
  border-radius: 14px;
  overflow-x: auto;
  border: 1px solid #eef2f0;
}
.data-table {
  width: 100%;
  border-collapse: collapse;
}
.data-table th,
.data-table td {
  padding: 13px 16px;
  text-align: left;
  font-size: 0.86rem;
  border-bottom: 1px solid #f1f5f4;
  white-space: nowrap;
}
.data-table th {
  background: #f7faf8;
  color: #475569;
  font-weight: 600;
}
.data-table td.ref {
  font-family: monospace;
  font-size: 0.8rem;
  color: #64748b;
}

.status-badge {
  display: inline-flex;
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 600;
}
.status-badge.lunas { background: #e3f6ea; color: #1c9c5f; }
.status-badge.belum_bayar { background: #f1f5f9; color: #475569; }
.status-badge.terlambat { background: #fbe4e4; color: #d64545; }

.btn-bayar {
  background: #1c9c5f;
  color: #fff;
  border: none;
  padding: 7px 14px;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.btn-bayar:hover {
  filter: brightness(1.08);
}
.paid-note {
  color: #1c9c5f;
  font-size: 0.82rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.loading-state,
.error-state,
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #64748b;
}
.loading-state i,
.empty-state i {
  font-size: 2.5rem;
  margin-bottom: 16px;
  color: #3b82f6;
}
.empty-state i {
  color: #94a3b8;
}
.error-state i {
  font-size: 2.5rem;
  margin-bottom: 16px;
  color: #ef4444;
}
.btn-retry {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 8px;
  cursor: pointer;
  margin-top: 12px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(6, 35, 26, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  padding: 20px;
}
.modal-box {
  background: #fff;
  border-radius: 16px;
  padding: 26px 28px;
  width: 100%;
  max-width: 420px;
}
.modal-box h3 {
  margin: 0 0 14px;
  font-size: 1.1rem;
  color: #06231a;
}
.modal-desc {
  color: #334155;
  font-size: 0.92rem;
}
.modal-note {
  background: #eaf1fd;
  color: #1e40af;
  border-radius: 10px;
  padding: 12px 14px;
  font-size: 0.82rem;
  display: flex;
  align-items: flex-start;
  gap: 8px;
}
.modal-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 18px;
}
.btn-secondary {
  background: #f1f5f4;
  color: #334155;
  border: none;
  padding: 10px 18px;
  border-radius: 8px;
  cursor: pointer;
}
</style>