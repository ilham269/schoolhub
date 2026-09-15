<template>
  <div class="exam-container" @contextmenu.prevent @copy.prevent @paste.prevent>
    <!-- Header -->
    <header class="exam-header">
      <div class="brand">
        <span class="brand-mark">HB</span>
        <span>Ujian PPDB - SMA Harapan Bangsa</span>
      </div>
      <button @click="logout" class="btn-logout">Logout</button>
    </header>

    <!-- Security Warnings -->
    <div v-if="showWarnings && activeSession" class="security-warnings">
      <div v-if="warnings.fullscreen" class="warning warning-danger">
        <i class="fas fa-exclamation-triangle"></i>
        Mohon aktifkan mode fullscreen! Tekan F11 atau klik tombol fullscreen.
      </div>
      <div v-if="warnings.tabSwitch" class="warning warning-warning">
        <i class="fas fa-eye"></i>
        Aktivitas berpindah tab terdeteksi. Fokus pada ujian!
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Memuat data ujian...</p>
    </div>

    <!-- Exam Taking View -->
    <div v-else-if="activeSession" class="exam-taking">
      <div class="exam-header-info">
        <h2>{{ examData.title }}</h2>
        <div
          class="timer"
          :class="{ warning: remainingSeconds < 300, danger: remainingSeconds < 60 }"
        >
          <i class="fas fa-clock"></i>
          {{ formatTime(remainingSeconds) }}
        </div>
      </div>

      <!-- Connection Status -->
      <div v-if="!isOnline" class="connection-warning">
        <i class="fas fa-wifi-slash"></i>
        Koneksi internet terputus. Mencoba menyambung kembali...
      </div>

      <div class="questions-container">
        <div v-for="(question, index) in questions" :key="question.id" class="question-card">
          <div class="question-number">Soal {{ index + 1 }}</div>
          <div class="question-text">{{ question.question }}</div>
          <div class="question-score">Nilai: {{ question.score }} poin</div>

          <!-- Multiple Choice -->
          <div v-if="question.type === 'multiple_choice'" class="options">
            <label v-for="option in question.options" :key="option.id" class="option-label">
              <input
                type="radio"
                :name="`question-${question.id}`"
                :value="option.id"
                v-model="answers[question.id]"
                @change="onAnswerChange(question.id)"
              />
              <span>{{ option.option_text }}</span>
            </label>
          </div>

          <!-- Essay -->
          <div v-else class="essay-answer">
            <textarea
              v-model="essayAnswers[question.id]"
              @input="onAnswerChange(question.id)"
              placeholder="Tulis jawaban Anda di sini..."
              rows="5"
            ></textarea>
          </div>
        </div>
      </div>

      <div class="exam-actions">
        <button @click="confirmSubmit" class="btn btn-success btn-large">
          <i class="fas fa-paper-plane"></i> Kirim Jawaban
        </button>
      </div>
    </div>

    <!-- No Exam State -->
    <div v-else class="empty-state">
      <i class="fas fa-inbox"></i>
      <p>Belum ada ujian yang tersedia atau ujian sudah selesai.</p>
    </div>

    <!-- Confirmation Modal -->
    <div v-if="showConfirmModal" class="modal-overlay" @click="showConfirmModal = false">
      <div class="modal-content" @click.stop>
        <h3>Konfirmasi Pengiriman</h3>
        <p>
          Apakah Anda yakin ingin mengirim jawaban? Setelah dikirim, Anda tidak bisa mengubah
          jawaban.
        </p>
        <div class="modal-actions">
          <button @click="showConfirmModal = false" class="btn btn-secondary">Batal</button>
          <button @click="submitExam" class="btn btn-success">Ya, Kirim</button>
        </div>
      </div>
    </div>

    <!-- Fullscreen Button -->
    <button v-if="activeSession && !isFullscreen" @click="enterFullscreen" class="btn-fullscreen">
      <i class="fas fa-expand"></i> Fullscreen
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../utils/api'

const router = useRouter()

const loading = ref(true)
const activeSession = ref(null)
const examData = ref(null)
const questions = ref([])
const answers = ref({}) // { questionId: optionId }
const essayAnswers = ref({}) // { questionId: answerText }
const remainingSeconds = ref(0)
const timer = ref(null)
const showConfirmModal = ref(false)
const isFullscreen = ref(false)
const isOnline = ref(true)
const showWarnings = ref(true)

const warnings = ref({
  fullscreen: false,
  tabSwitch: false,
})

// Auto-save
const autoSaveInterval = ref(null)
const pendingSaves = ref(new Set())

// Activity tracking
const activityCooldowns = ref({
  tabSwitch: false,
  fullscreenExit: false,
})

// Load and start first available exam
const loadAndStartExam = async () => {
  try {
    loading.value = true
    const response = await api.get('/ppdb/exams')
    const exams = response.data.data

    console.log('📚 Loaded exams:', exams)

    if (exams.length === 0) {
      loading.value = false
      return
    }

    const firstExam = exams[0]

    // If already completed, show message
    if (firstExam.attempt?.status === 'submitted') {
      alert('Ujian sudah selesai dikerjakan!')
      loading.value = false
      return
    }

    // Start the exam
    await startExam(firstExam)
  } catch (error) {
    console.error('Error loading exams:', error)
    alert('Gagal memuat ujian')
  } finally {
    loading.value = false
  }
}

// Start exam
const startExam = async (exam) => {
  try {
    loading.value = true
    const response = await api.post(`/ppdb/exams/${exam.id}/start`)
    const data = response.data.data

    activeSession.value = data.session
    examData.value = data.exam
    questions.value = data.questions

    // Load saved answers
    if (data.saved_answers) {
      for (const [questionId, answer] of Object.entries(data.saved_answers)) {
        if (answer.option_id) {
          answers.value[questionId] = answer.option_id
        }
        if (answer.answer_text) {
          essayAnswers.value[questionId] = answer.answer_text
        }
      }
    }

    // Set timer from server
    remainingSeconds.value = data.session.remaining_seconds
    startTimer()

    // Start auto-save
    startAutoSave()

    // Setup security monitoring
    setupSecurityMonitoring()

    // Try to enter fullscreen
    setTimeout(() => {
      enterFullscreen()
    }, 500)

    console.log('▶️ Exam started:', exam.title)
  } catch (error) {
    console.error('Error starting exam:', error)
    alert(error.response?.data?.message || 'Gagal memulai ujian')
    loading.value = false
  }
}

// Timer functions
const startTimer = () => {
  // Sync with server every 30 seconds
  let syncCounter = 0

  timer.value = setInterval(async () => {
    if (remainingSeconds.value > 0) {
      remainingSeconds.value--
      syncCounter++

      // Sync with server every 30 seconds
      if (syncCounter >= 30) {
        syncCounter = 0
        await syncTimeWithServer()
      }
    } else {
      // Time's up, auto submit
      await autoSubmitExam()
    }
  }, 1000)
}

const stopTimer = () => {
  if (timer.value) {
    clearInterval(timer.value)
    timer.value = null
  }
}

const syncTimeWithServer = async () => {
  if (!activeSession.value) return

  try {
    const response = await api.get(`/ppdb/session/${activeSession.value.id}/status`)
    const data = response.data.data

    // Update remaining time from server
    remainingSeconds.value = data.remaining_seconds

    // Check if expired
    if (data.is_expired || data.status !== 'active') {
      await autoSubmitExam()
    }
  } catch (error) {
    console.error('Error syncing time:', error)
  }
}

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
}

// Auto-save functionality
const startAutoSave = () => {
  // Save every 10 seconds
  autoSaveInterval.value = setInterval(() => {
    saveAllPendingAnswers()
  }, 10000)
}

const stopAutoSave = () => {
  if (autoSaveInterval.value) {
    clearInterval(autoSaveInterval.value)
    autoSaveInterval.value = null
  }
}

const onAnswerChange = (questionId) => {
  pendingSaves.value.add(questionId)
}

const saveAllPendingAnswers = async () => {
  if (pendingSaves.value.size === 0) return

  const saves = Array.from(pendingSaves.value)
  pendingSaves.value.clear()

  for (const questionId of saves) {
    await saveAnswer(questionId)
  }
}

const saveAnswer = async (questionId) => {
  if (!activeSession.value) return

  try {
    const payload = {
      session_id: activeSession.value.id,
      question_id: questionId,
      option_id: answers.value[questionId] || null,
      answer_text: essayAnswers.value[questionId] || null,
    }

    const response = await api.post('/ppdb/auto-save', payload)

    if (response.data.expired) {
      await autoSubmitExam()
    }
  } catch (error) {
    console.error('Error saving answer:', error)
    // Retry later
    pendingSaves.value.add(questionId)
  }
}

// Security monitoring
const setupSecurityMonitoring = () => {
  // Fullscreen change detection
  document.addEventListener('fullscreenchange', onFullscreenChange)
  document.addEventListener('webkitfullscreenchange', onFullscreenChange)
  document.addEventListener('mozfullscreenchange', onFullscreenChange)
  document.addEventListener('MSFullscreenChange', onFullscreenChange)

  // Visibility change (tab switching)
  document.addEventListener('visibilitychange', onVisibilityChange)

  // Blur event (window lost focus)
  window.addEventListener('blur', onWindowBlur)

  // Online/offline detection
  window.addEventListener('online', onOnline)
  window.addEventListener('offline', onOffline)

  // Prevent keyboard shortcuts
  document.addEventListener('keydown', onKeyDown)
}

const cleanupSecurityMonitoring = () => {
  document.removeEventListener('fullscreenchange', onFullscreenChange)
  document.removeEventListener('webkitfullscreenchange', onFullscreenChange)
  document.removeEventListener('mozfullscreenchange', onFullscreenChange)
  document.removeEventListener('MSFullscreenChange', onFullscreenChange)
  document.removeEventListener('visibilitychange', onVisibilityChange)
  window.removeEventListener('blur', onWindowBlur)
  window.removeEventListener('online', onOnline)
  window.removeEventListener('offline', onOffline)
  document.removeEventListener('keydown', onKeyDown)
}

const onFullscreenChange = () => {
  const isNowFullscreen = !!(
    document.fullscreenElement ||
    document.webkitFullscreenElement ||
    document.mozFullScreenElement ||
    document.msFullscreenElement
  )

  isFullscreen.value = isNowFullscreen
  warnings.value.fullscreen = !isNowFullscreen

  if (!isNowFullscreen && activeSession.value && !activityCooldowns.value.fullscreenExit) {
    activityCooldowns.value.fullscreenExit = true
    logActivity('fullscreen_exit')

    setTimeout(() => {
      activityCooldowns.value.fullscreenExit = false
    }, 2000)
  }
}

const onVisibilityChange = () => {
  if (document.hidden && activeSession.value) {
    if (!activityCooldowns.value.tabSwitch) {
      activityCooldowns.value.tabSwitch = true
      logActivity('tab_switch')
      warnings.value.tabSwitch = true

      setTimeout(() => {
        warnings.value.tabSwitch = false
        activityCooldowns.value.tabSwitch = false
      }, 5000)
    }

    logActivity('page_hidden')
  } else {
    logActivity('page_visible')
  }
}

const onWindowBlur = () => {
  if (activeSession.value && !activityCooldowns.value.tabSwitch) {
    activityCooldowns.value.tabSwitch = true
    logActivity('tab_switch')

    setTimeout(() => {
      activityCooldowns.value.tabSwitch = false
    }, 2000)
  }
}

const onOnline = () => {
  isOnline.value = true
  if (activeSession.value) {
    syncTimeWithServer()
  }
}

const onOffline = () => {
  isOnline.value = false
  if (activeSession.value) {
    logActivity('network_disconnect')
  }
}

const onKeyDown = (e) => {
  // Prevent common cheating shortcuts
  if (
    (e.ctrlKey &&
      (e.key === 'c' || e.key === 'v' || e.key === 'x' || e.key === 'a' || e.key === 'f')) ||
    e.key === 'F12' ||
    (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C'))
  ) {
    e.preventDefault()

    if (e.key === 'c' || e.key === 'x') {
      logActivity('copy_attempt')
    } else if (e.key === 'v') {
      logActivity('paste_attempt')
    }

    return false
  }
}

const enterFullscreen = async () => {
  const elem = document.documentElement

  try {
    if (elem.requestFullscreen) {
      await elem.requestFullscreen()
    } else if (elem.webkitRequestFullscreen) {
      await elem.webkitRequestFullscreen()
    } else if (elem.mozRequestFullScreen) {
      await elem.mozRequestFullScreen()
    } else if (elem.msRequestFullscreen) {
      await elem.msRequestFullscreen()
    }

    isFullscreen.value = true
    warnings.value.fullscreen = false
  } catch (error) {
    console.error('Error entering fullscreen:', error)
  }
}

const logActivity = async (event, metadata = {}) => {
  if (!activeSession.value) return

  try {
    await api.post('/ppdb/log-activity', {
      session_id: activeSession.value.id,
      event: event,
      metadata: metadata,
    })
  } catch (error) {
    console.error('Error logging activity:', error)
  }
}

// Submit exam
const confirmSubmit = async () => {
  // Save all pending answers first
  await saveAllPendingAnswers()
  showConfirmModal.value = true
}

const submitExam = async () => {
  showConfirmModal.value = false
  stopTimer()
  stopAutoSave()

  try {
    loading.value = true

    // Prepare answers
    const answersData = []

    // Multiple choice answers
    for (const [questionId, optionId] of Object.entries(answers.value)) {
      answersData.push({
        question_id: parseInt(questionId),
        option_id: parseInt(optionId),
      })
    }

    // Essay answers
    for (const [questionId, answerText] of Object.entries(essayAnswers.value)) {
      if (answerText && answerText.trim()) {
        answersData.push({
          question_id: parseInt(questionId),
          answer_text: answerText,
        })
      }
    }

    const response = await api.post(`/ppdb/exams/${examData.value.id}/submit`, {
      session_id: activeSession.value.id,
      answers: answersData,
    })

    const result = response.data.data

    // Show result
    if (result.show_result) {
      alert(`Ujian selesai!\n\nNilai Anda: ${result.score}`)
    } else {
      alert('Jawaban berhasil dikirim! Tunggu hasil dari admin.')
    }

    // Cleanup and logout
    cleanupSecurityMonitoring()
    logout()
  } catch (error) {
    console.error('Error submitting exam:', error)
    alert(error.response?.data?.message || 'Gagal mengirim jawaban')
    loading.value = false
  }
}

const autoSubmitExam = async () => {
  stopTimer()
  stopAutoSave()

  alert('Waktu ujian habis! Jawaban akan dikirim otomatis.')

  await submitExam()
}

// Logout
const logout = () => {
  stopTimer()
  stopAutoSave()
  cleanupSecurityMonitoring()

  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}

// Lifecycle
onMounted(() => {
  loadAndStartExam()
})

onUnmounted(() => {
  stopTimer()
  stopAutoSave()
  cleanupSecurityMonitoring()
})

// Prevent accidental page close
window.addEventListener('beforeunload', (e) => {
  if (activeSession.value && activeSession.value.status === 'active') {
    e.preventDefault()
    e.returnValue = ''
  }
})
</script>

<style scoped>
.exam-container {
  min-height: 100vh;
  background: #f5f7fa;
  user-select: none; /* Prevent text selection */
}

.exam-header {
  background: white;
  padding: 1rem 2rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: sticky;
  top: 0;
  z-index: 100;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 600;
  font-size: 1.1rem;
}

.brand-mark {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #3d7a50, #2d5f3f);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  font-weight: bold;
}

.btn-logout {
  background: #dc3545;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.btn-logout:hover {
  background: #c82333;
}

.security-warnings {
  position: fixed;
  top: 70px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 999;
  width: 90%;
  max-width: 600px;
}

.warning {
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-weight: 500;
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    transform: translateY(-20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.warning-danger {
  background: #fee;
  color: #c00;
  border: 2px solid #c00;
}

.warning-warning {
  background: #fffbea;
  color: #856404;
  border: 2px solid #ffc107;
}

.connection-warning {
  background: #fff3cd;
  color: #856404;
  padding: 1rem;
  text-align: center;
  border-bottom: 2px solid #ffc107;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  font-weight: 500;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  gap: 1rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e5e7eb;
  border-top-color: #3d7a50;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  color: #6b7280;
}

.empty-state i {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.exam-taking {
  max-width: 900px;
  margin: 2rem auto;
  padding: 0 2rem;
}

.exam-header-info {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.exam-header-info h2 {
  margin: 0;
}

.timer {
  font-size: 1.5rem;
  font-weight: bold;
  color: #3d7a50;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.timer.warning {
  color: #f59e0b;
}

.timer.danger {
  color: #dc2626;
  animation: pulse 1s infinite;
}

@keyframes pulse {
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.questions-container {
  display: grid;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.question-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.question-number {
  display: inline-block;
  background: #3d7a50;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.question-text {
  font-size: 1.1rem;
  color: #1f2937;
  margin-bottom: 0.5rem;
  line-height: 1.6;
}

.question-score {
  color: #6b7280;
  font-size: 0.9rem;
  margin-bottom: 1rem;
}

.options {
  display: grid;
  gap: 0.75rem;
  margin-top: 1rem;
}

.option-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.option-label:hover {
  border-color: #3d7a50;
  background: #f0fdf4;
}

.option-label input[type='radio'] {
  margin: 0;
}

.option-label input[type='radio']:checked + span {
  font-weight: 600;
  color: #3d7a50;
}

.essay-answer textarea {
  width: 100%;
  padding: 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-family: inherit;
  font-size: 1rem;
  resize: vertical;
}

.essay-answer textarea:focus {
  outline: none;
  border-color: #3d7a50;
}

.exam-actions {
  text-align: center;
  padding: 2rem 0;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover {
  background: #059669;
}

.btn-secondary {
  background: #e5e7eb;
  color: #6b7280;
}

.btn-large {
  padding: 1rem 2rem;
  font-size: 1.1rem;
}

.btn-fullscreen {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  background: #3d7a50;
  color: white;
  border: none;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  z-index: 100;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-fullscreen:hover {
  background: #2d5f3f;
  transform: translateY(-2px);
  box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
}

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
}

.modal-content {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  max-width: 400px;
  width: 90%;
}

.modal-content h3 {
  margin-top: 0;
  margin-bottom: 1rem;
}

.modal-content p {
  color: #6b7280;
  margin-bottom: 1.5rem;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}
</style>
