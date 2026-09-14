<template>
  <div class="monitoring-container">
    <div class="page-header">
      <h1><i class="fas fa-shield-alt"></i> Monitoring Keamanan Ujian PPDB</h1>
      <p>Pantau aktivitas siswa selama ujian berlangsung</p>
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filter-group">
        <label>Filter Ujian:</label>
        <select v-model="selectedExamId" @change="loadData" class="form-select">
          <option :value="null">Semua Ujian</option>
          <option v-for="exam in exams" :key="exam.id" :value="exam.id">
            {{ exam.title }}
          </option>
        </select>
      </div>

      <div class="filter-group">
        <label>Filter Status:</label>
        <select v-model="statusFilter" class="form-select">
          <option value="all">Semua</option>
          <option value="active">Sedang Ujian</option>
          <option value="submitted">Selesai</option>
          <option value="suspicious">Mencurigakan</option>
          <option value="review">Perlu Review</option>
        </select>
      </div>

      <div class="filter-actions">
        <button @click="loadData" class="btn btn-primary">
          <i class="fas fa-sync"></i> Refresh
        </button>
        <span class="auto-refresh">
          <input type="checkbox" v-model="autoRefresh" id="autoRefresh" />
          <label for="autoRefresh">Auto-refresh (30s)</label>
        </span>
      </div>
    </div>

    <!-- Stats Overview -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon active">
          <i class="fas fa-user-clock"></i>
        </div>
        <div class="stat-content">
          <h3>{{ stats.active }}</h3>
          <p>Sedang Ujian</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon completed">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
          <h3>{{ stats.completed }}</h3>
          <p>Selesai</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon warning">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-content">
          <h3>{{ stats.suspicious }}</h3>
          <p>Mencurigakan</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon review">
          <i class="fas fa-eye"></i>
        </div>
        <div class="stat-content">
          <h3>{{ stats.needReview }}</h3>
          <p>Perlu Review</p>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Memuat data...</p>
    </div>

    <!-- Sessions Table -->
    <div v-else class="table-card">
      <h2>Daftar Sesi Ujian</h2>

      <div v-if="filteredSessions.length === 0" class="empty-state">
        <i class="fas fa-inbox"></i>
        <p>Tidak ada data sesi ujian</p>
      </div>

      <div v-else class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Siswa</th>
              <th>Ujian</th>
              <th>Status</th>
              <th>Waktu</th>
              <th>Skor</th>
              <th>Suspicion</th>
              <th>Aktivitas</th>
              <th>IP Address</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="session in filteredSessions"
              :key="session.id"
              :class="getSuspicionClass(session.suspicion_score)"
            >
              <td>
                <div class="student-info">
                  <strong>{{ session.candidate.nama }}</strong>
                  <small>{{ session.candidate.email }}</small>
                </div>
              </td>
              <td>{{ session.exam.title }}</td>
              <td>
                <span :class="'badge badge-' + getStatusColor(session.status)">
                  {{ getStatusLabel(session.status) }}
                </span>
              </td>
              <td>
                <div class="time-info">
                  <div><strong>Mulai:</strong> {{ formatDateTime(session.started_at) }}</div>
                  <div v-if="session.submitted_at">
                    <strong>Selesai:</strong> {{ formatDateTime(session.submitted_at) }}
                  </div>
                  <div v-else-if="session.status === 'active'" class="remaining-time">
                    <i class="fas fa-clock"></i> {{ formatSeconds(session.remaining_seconds) }}
                  </div>
                </div>
              </td>
              <td>
                <span
                  v-if="session.score !== null && session.score !== undefined"
                  class="score-badge"
                >
                  {{ session.score }}
                </span>
                <span v-else class="text-muted">-</span>
              </td>
              <td>
                <div class="suspicion-score">
                  <div class="score-value" :class="getSuspicionScoreClass(session.suspicion_score)">
                    {{ session.suspicion_score }}
                  </div>
                  <div class="score-label">{{ getSuspicionLabel(session.suspicion_score) }}</div>
                </div>
              </td>
              <td>
                <div class="activity-summary">
                  <span v-if="session.activities.tab_switches > 0" class="activity-badge warning">
                    <i class="fas fa-window-restore"></i> {{ session.activities.tab_switches }}
                  </span>
                  <span
                    v-if="session.activities.fullscreen_exits > 0"
                    class="activity-badge danger"
                  >
                    <i class="fas fa-expand"></i> {{ session.activities.fullscreen_exits }}
                  </span>
                  <span v-if="session.activities.copy_attempts > 0" class="activity-badge warning">
                    <i class="fas fa-copy"></i> {{ session.activities.copy_attempts }}
                  </span>
                  <span v-if="session.activities.disconnect_count > 0" class="activity-badge info">
                    <i class="fas fa-wifi-slash"></i> {{ session.activities.disconnect_count }}
                  </span>
                </div>
              </td>
              <td>
                <code class="ip-address">{{ session.ip_address }}</code>
              </td>
              <td>
                <div class="action-buttons">
                  <button
                    @click="viewDetails(session)"
                    class="btn btn-sm btn-info"
                    title="Lihat Detail"
                  >
                    <i class="fas fa-eye"></i>
                  </button>
                  <button
                    v-if="session.status === 'active' && isAdmin"
                    @click="suspendSession(session)"
                    class="btn btn-sm btn-danger"
                    title="Suspend Sesi"
                  >
                    <i class="fas fa-ban"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="showDetailModal" class="modal-overlay" @click="closeModal">
      <div class="modal-content large" @click.stop>
        <div class="modal-header">
          <h2><i class="fas fa-chart-line"></i> Detail Aktivitas Sesi</h2>
          <button @click="closeModal" class="btn-close">&times;</button>
        </div>

        <div class="modal-body">
          <!-- Session Info -->
          <div class="session-info-card">
            <h3>Informasi Sesi</h3>
            <div class="info-grid">
              <div class="info-item">
                <label>Siswa:</label>
                <span>{{ selectedSession.session.candidate.nama }}</span>
              </div>
              <div class="info-item">
                <label>Ujian:</label>
                <span>{{ selectedSession.session.exam.title }}</span>
              </div>
              <div class="info-item">
                <label>Status:</label>
                <span :class="'badge badge-' + getStatusColor(selectedSession.session.status)">
                  {{ getStatusLabel(selectedSession.session.status) }}
                </span>
              </div>
              <div class="info-item">
                <label>Skor Suspicion:</label>
                <span
                  class="suspicion-large"
                  :class="getSuspicionScoreClass(selectedSession.session.suspicion_score)"
                >
                  {{ selectedSession.session.suspicion_score }} / 100
                </span>
              </div>
              <div class="info-item">
                <label>Mulai:</label>
                <span>{{ formatDateTime(selectedSession.session.started_at) }}</span>
              </div>
              <div class="info-item">
                <label>Selesai:</label>
                <span>{{ formatDateTime(selectedSession.session.submitted_at) || '-' }}</span>
              </div>
            </div>
          </div>

          <!-- Activity Timeline -->
          <div class="timeline-card">
            <h3>Timeline Aktivitas</h3>
            <div v-if="loadingLogs" class="loading-state">
              <div class="spinner-small"></div>
              <p>Memuat log aktivitas...</p>
            </div>
            <div v-else-if="selectedSession.logs.length === 0" class="empty-state">
              <p>Tidak ada log aktivitas</p>
            </div>
            <div v-else class="timeline">
              <div
                v-for="log in selectedSession.logs"
                :key="log.id"
                class="timeline-item"
                :class="getEventClass(log.event)"
              >
                <div class="timeline-time">
                  {{ formatTime(log.created_at) }}
                </div>
                <div class="timeline-icon">
                  <i :class="getEventIcon(log.event)"></i>
                </div>
                <div class="timeline-content">
                  <strong>{{ getEventLabel(log.event) }}</strong>
                  <p>{{ log.description }}</p>
                  <div v-if="log.metadata && Object.keys(log.metadata).length > 0" class="metadata">
                    <code>{{ JSON.stringify(log.metadata, null, 2) }}</code>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button @click="closeModal" class="btn btn-secondary">Tutup</button>
        </div>
      </div>
    </div>

    <!-- Suspend Confirmation Modal -->
    <div v-if="showSuspendModal" class="modal-overlay" @click="showSuspendModal = false">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Suspend Sesi Ujian</h3>
          <button @click="showSuspendModal = false" class="btn-close">&times;</button>
        </div>

        <div class="modal-body">
          <p>
            Anda yakin ingin menghentikan sesi ujian untuk siswa
            <strong>{{ sessionToSuspend?.candidate?.nama }}</strong
            >?
          </p>
          <p class="text-danger">
            Tindakan ini akan menghentikan ujian secara paksa dan tidak dapat dibatalkan.
          </p>

          <div class="form-group">
            <label>Alasan Suspend:</label>
            <textarea
              v-model="suspendReason"
              class="form-control"
              rows="3"
              placeholder="Masukkan alasan suspend (wajib diisi)"
            ></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button @click="showSuspendModal = false" class="btn btn-secondary">Batal</button>
          <button
            @click="confirmSuspend"
            class="btn btn-danger"
            :disabled="!suspendReason || suspendReason.trim() === ''"
          >
            Suspend Sesi
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../utils/api'

const router = useRouter()

const loading = ref(true)
const loadingLogs = ref(false)
const sessions = ref([])
const exams = ref([])
const selectedExamId = ref(null)
const statusFilter = ref('all')
const autoRefresh = ref(true)
const refreshInterval = ref(null)

const showDetailModal = ref(false)
const selectedSession = ref(null)

const showSuspendModal = ref(false)
const sessionToSuspend = ref(null)
const suspendReason = ref('')

// Get user from localStorage
const user = JSON.parse(localStorage.getItem('user') || '{}')
const isAdmin = computed(() => user.role?.toLowerCase() === 'admin')

// Stats
const stats = computed(() => {
  return {
    active: sessions.value.filter((s) => s.status === 'active').length,
    completed: sessions.value.filter((s) => s.status === 'submitted').length,
    suspicious: sessions.value.filter((s) => s.suspicion_score >= 60).length,
    needReview: sessions.value.filter((s) => s.suspicion_score >= 30 && s.suspicion_score < 60)
      .length,
  }
})

// Filtered sessions
const filteredSessions = computed(() => {
  let filtered = sessions.value

  // Filter by status
  if (statusFilter.value !== 'all') {
    if (statusFilter.value === 'suspicious') {
      filtered = filtered.filter((s) => s.suspicion_score >= 60)
    } else if (statusFilter.value === 'review') {
      filtered = filtered.filter((s) => s.suspicion_score >= 30 && s.suspicion_score < 60)
    } else {
      filtered = filtered.filter((s) => s.status === statusFilter.value)
    }
  }

  return filtered
})

// Load data
const loadData = async () => {
  try {
    loading.value = true

    // Load exams first
    const examsResponse = await api.get('/ppdb/manage/exams')
    exams.value = examsResponse.data.data

    // Load sessions
    const params = selectedExamId.value ? { exam_id: selectedExamId.value } : {}
    const response = await api.get('/ppdb/manage/security-dashboard', { params })
    sessions.value = response.data.data

    console.log('📊 Loaded sessions:', sessions.value.length)
  } catch (error) {
    console.error('Error loading data:', error)
    alert('Gagal memuat data monitoring')
  } finally {
    loading.value = false
  }
}

// View session details
const viewDetails = async (session) => {
  try {
    loadingLogs.value = true
    showDetailModal.value = true

    const response = await api.get(`/ppdb/manage/sessions/${session.id}/logs`)
    selectedSession.value = response.data.data

    console.log('📋 Loaded session logs:', selectedSession.value.logs.length)
  } catch (error) {
    console.error('Error loading session details:', error)
    alert('Gagal memuat detail sesi')
    showDetailModal.value = false
  } finally {
    loadingLogs.value = false
  }
}

// Close modal
const closeModal = () => {
  showDetailModal.value = false
  selectedSession.value = null
}

// Suspend session
const suspendSession = (session) => {
  sessionToSuspend.value = session
  suspendReason.value = ''
  showSuspendModal.value = true
}

const confirmSuspend = async () => {
  if (!suspendReason.value || suspendReason.value.trim() === '') {
    alert('Alasan suspend wajib diisi')
    return
  }

  try {
    await api.post(`/ppdb/manage/sessions/${sessionToSuspend.value.id}/suspend`, {
      reason: suspendReason.value,
    })

    alert('Sesi berhasil disuspend')
    showSuspendModal.value = false
    sessionToSuspend.value = null
    suspendReason.value = ''

    // Reload data
    loadData()
  } catch (error) {
    console.error('Error suspending session:', error)
    alert(error.response?.data?.message || 'Gagal suspend sesi')
  }
}

// Helper functions
const getStatusLabel = (status) => {
  const labels = {
    active: 'Sedang Ujian',
    submitted: 'Selesai',
    expired: 'Waktu Habis',
    suspended: 'Ditangguhkan',
  }
  return labels[status] || status
}

const getStatusColor = (status) => {
  const colors = {
    active: 'primary',
    submitted: 'success',
    expired: 'warning',
    suspended: 'danger',
  }
  return colors[status] || 'secondary'
}

const getSuspicionLabel = (score) => {
  if (score < 30) return 'Normal'
  if (score < 60) return 'Perlu Perhatian'
  if (score < 90) return 'Mencurigakan'
  return 'Sangat Mencurigakan'
}

const getSuspicionClass = (score) => {
  if (score < 30) return 'suspicion-normal'
  if (score < 60) return 'suspicion-attention'
  if (score < 90) return 'suspicion-suspicious'
  return 'suspicion-danger'
}

const getSuspicionScoreClass = (score) => {
  if (score < 30) return 'score-normal'
  if (score < 60) return 'score-attention'
  if (score < 90) return 'score-suspicious'
  return 'score-danger'
}

const getEventLabel = (event) => {
  const labels = {
    session_started: 'Sesi Dimulai',
    session_ended: 'Sesi Berakhir',
    tab_switch: 'Pindah Tab',
    fullscreen_exit: 'Keluar Fullscreen',
    copy_attempt: 'Percobaan Copy',
    paste_attempt: 'Percobaan Paste',
    right_click: 'Klik Kanan',
    page_hidden: 'Halaman Tersembunyi',
    page_visible: 'Halaman Terlihat',
    network_disconnect: 'Koneksi Terputus',
    network_reconnect: 'Koneksi Kembali',
    auto_save: 'Jawaban Tersimpan',
    session_suspended: 'Sesi Ditangguhkan',
  }
  return labels[event] || event
}

const getEventIcon = (event) => {
  const icons = {
    session_started: 'fas fa-play-circle',
    session_ended: 'fas fa-stop-circle',
    tab_switch: 'fas fa-window-restore',
    fullscreen_exit: 'fas fa-compress',
    copy_attempt: 'fas fa-copy',
    paste_attempt: 'fas fa-paste',
    right_click: 'fas fa-mouse-pointer',
    page_hidden: 'fas fa-eye-slash',
    page_visible: 'fas fa-eye',
    network_disconnect: 'fas fa-wifi-slash',
    network_reconnect: 'fas fa-wifi',
    auto_save: 'fas fa-save',
    session_suspended: 'fas fa-ban',
  }
  return icons[event] || 'fas fa-circle'
}

const getEventClass = (event) => {
  const dangerEvents = ['fullscreen_exit', 'copy_attempt', 'paste_attempt', 'session_suspended']
  const warningEvents = ['tab_switch', 'page_hidden', 'network_disconnect']

  if (dangerEvents.includes(event)) return 'event-danger'
  if (warningEvents.includes(event)) return 'event-warning'
  return 'event-info'
}

const formatDateTime = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleString('id-ID', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatTime = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  })
}

const formatSeconds = (seconds) => {
  if (!seconds || seconds < 0) return '00:00'
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
}

// Auto-refresh
const startAutoRefresh = () => {
  refreshInterval.value = setInterval(() => {
    if (autoRefresh.value && !showDetailModal.value) {
      loadData()
    }
  }, 30000) // 30 seconds
}

const stopAutoRefresh = () => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value)
    refreshInterval.value = null
  }
}

// Lifecycle
onMounted(() => {
  loadData()
  startAutoRefresh()
})

onUnmounted(() => {
  stopAutoRefresh()
})
</script>

<style scoped>
.monitoring-container {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2rem;
  color: #1f2937;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.page-header p {
  color: #6b7280;
}

.filters-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  align-items: flex-end;
}

.filter-group {
  flex: 1;
  min-width: 200px;
}

.filter-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #374151;
}

.form-select {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.9rem;
}

.filter-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.auto-refresh {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  display: flex;
  gap: 1rem;
  align-items: center;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.stat-icon.active {
  background: #3b82f6;
}
.stat-icon.completed {
  background: #10b981;
}
.stat-icon.warning {
  background: #f59e0b;
}
.stat-icon.review {
  background: #8b5cf6;
}

.stat-content h3 {
  font-size: 2rem;
  margin: 0;
  color: #1f2937;
}

.stat-content p {
  margin: 0;
  color: #6b7280;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  gap: 1rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.spinner-small {
  width: 24px;
  height: 24px;
  border: 3px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.table-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.table-card h2 {
  margin-top: 0;
  margin-bottom: 1.5rem;
  color: #1f2937;
}

.table-responsive {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  background: #f9fafb;
  padding: 0.75rem;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
}

.data-table td {
  padding: 1rem 0.75rem;
  border-bottom: 1px solid #e5e7eb;
}

.data-table tr.suspicion-danger {
  background: #fef2f2;
}

.data-table tr.suspicion-suspicious {
  background: #fffbeb;
}

.data-table tr.suspicion-attention {
  background: #fef3c7;
}

.student-info strong {
  display: block;
  color: #1f2937;
}

.student-info small {
  color: #6b7280;
}

.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-primary {
  background: #dbeafe;
  color: #1e40af;
}
.badge-success {
  background: #d1fae5;
  color: #065f46;
}
.badge-warning {
  background: #fef3c7;
  color: #92400e;
}
.badge-danger {
  background: #fee2e2;
  color: #991b1b;
}

.time-info {
  font-size: 0.85rem;
}

.remaining-time {
  color: #3b82f6;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.score-badge {
  display: inline-block;
  background: #3b82f6;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-weight: 600;
}

.suspicion-score {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.score-value {
  font-size: 1.25rem;
  font-weight: bold;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  text-align: center;
}

.score-normal {
  background: #d1fae5;
  color: #065f46;
}
.score-attention {
  background: #fef3c7;
  color: #92400e;
}
.score-suspicious {
  background: #fed7aa;
  color: #9a3412;
}
.score-danger {
  background: #fecaca;
  color: #991b1b;
}

.score-label {
  font-size: 0.7rem;
  text-align: center;
  color: #6b7280;
}

.activity-summary {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
}

.activity-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.activity-badge.warning {
  background: #fef3c7;
  color: #92400e;
}
.activity-badge.danger {
  background: #fee2e2;
  color: #991b1b;
}
.activity-badge.info {
  background: #dbeafe;
  color: #1e40af;
}

.ip-address {
  background: #f3f4f6;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.85rem;
  font-family: monospace;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
}

.btn-sm {
  padding: 0.35rem 0.75rem;
  font-size: 0.85rem;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}
.btn-primary:hover {
  background: #2563eb;
}

.btn-info {
  background: #0ea5e9;
  color: white;
}
.btn-info:hover {
  background: #0284c7;
}

.btn-danger {
  background: #ef4444;
  color: white;
}
.btn-danger:hover {
  background: #dc2626;
}

.btn-secondary {
  background: #e5e7eb;
  color: #374151;
}
.btn-secondary:hover {
  background: #d1d5db;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  color: #6b7280;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 2rem;
}

.modal-content {
  background: white;
  border-radius: 12px;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-content.large {
  max-width: 900px;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2,
.modal-header h3 {
  margin: 0;
  color: #1f2937;
}

.btn-close {
  background: none;
  border: none;
  font-size: 2rem;
  color: #6b7280;
  cursor: pointer;
  line-height: 1;
  padding: 0;
  width: 2rem;
  height: 2rem;
}

.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
}

.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.session-info-card {
  background: #f9fafb;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
}

.session-info-card h3 {
  margin-top: 0;
  margin-bottom: 1rem;
  color: #1f2937;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.info-item label {
  font-size: 0.85rem;
  color: #6b7280;
  font-weight: 600;
}

.suspicion-large {
  font-size: 1.25rem;
  font-weight: bold;
  padding: 0.5rem;
  border-radius: 6px;
  display: inline-block;
}

.timeline-card {
  background: #f9fafb;
  padding: 1.5rem;
  border-radius: 8px;
}

.timeline-card h3 {
  margin-top: 0;
  margin-bottom: 1rem;
  color: #1f2937;
}

.timeline {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.timeline-item {
  display: grid;
  grid-template-columns: 80px 40px 1fr;
  gap: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 8px;
  border-left: 4px solid #e5e7eb;
}

.timeline-item.event-danger {
  border-left-color: #ef4444;
}

.timeline-item.event-warning {
  border-left-color: #f59e0b;
}

.timeline-item.event-info {
  border-left-color: #3b82f6;
}

.timeline-time {
  font-size: 0.85rem;
  color: #6b7280;
  font-weight: 600;
}

.timeline-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
}

.timeline-content strong {
  display: block;
  margin-bottom: 0.25rem;
  color: #1f2937;
}

.timeline-content p {
  margin: 0;
  color: #6b7280;
  font-size: 0.9rem;
}

.metadata {
  margin-top: 0.5rem;
  background: #f3f4f6;
  padding: 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
}

.metadata code {
  color: #374151;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #374151;
}

.form-control {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-family: inherit;
  font-size: 1rem;
}

.form-control:focus {
  outline: none;
  border-color: #3b82f6;
}

.text-danger {
  color: #ef4444;
}

.text-muted {
  color: #9ca3af;
}
</style>
