<template>
  <DashboardLayout title="Ujian PPDB & Soal" role-label="Guru" :navigation="navigation">
    <div class="container">
      <!-- Section: Create Exam -->
      <section class="card">
        <h2><i class="fas fa-plus-circle"></i> Buat Ujian Baru</h2>
        <form @submit.prevent="createExam" class="form">
          <div class="form-group">
            <label>Nama Ujian *</label>
            <input
              v-model="form.title"
              class="input"
              placeholder="Contoh: Ujian Masuk PPDB 2027"
              required
            />
          </div>

          <div class="form-group">
            <label>Deskripsi</label>
            <textarea
              v-model="form.description"
              class="input"
              placeholder="Keterangan ujian (opsional)"
              rows="3"
            ></textarea>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Durasi (menit) *</label>
              <input
                v-model.number="form.duration_minutes"
                type="number"
                class="input"
                min="1"
                required
              />
            </div>

            <div class="form-group">
              <label>Passing Score (%)</label>
              <input
                v-model.number="form.passing_score"
                type="number"
                class="input"
                min="0"
                max="100"
                placeholder="Contoh: 70"
              />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Mulai Pada</label>
              <input v-model="form.start_at" type="datetime-local" class="input" />
            </div>

            <div class="form-group">
              <label>Berakhir Pada</label>
              <input v-model="form.end_at" type="datetime-local" class="input" />
            </div>
          </div>

          <div class="checkbox-group">
            <label class="checkbox-label">
              <input v-model="form.is_published" type="checkbox" />
              <span>Publikasikan ujian (siswa dapat melihat)</span>
            </label>

            <label class="checkbox-label">
              <input v-model="form.show_result" type="checkbox" />
              <span>Tampilkan hasil ke siswa setelah selesai</span>
            </label>
          </div>

          <button type="submit" class="btn btn-primary" :disabled="saving">
            <i class="fas fa-save"></i>
            {{ saving ? 'Menyimpan...' : 'Simpan Ujian' }}
          </button>
        </form>
      </section>

      <!-- Section: Exam List -->
      <section class="card">
        <h2><i class="fas fa-list"></i> Daftar Ujian</h2>

        <div v-if="exams.length === 0" class="empty-state">
          <i class="fas fa-inbox"></i>
          <p>Belum ada ujian. Buat ujian baru di atas.</p>
        </div>

        <div v-else class="exam-list">
          <button
            v-for="exam in exams"
            :key="exam.id"
            class="exam-item"
            :class="{ active: selected?.id === exam.id }"
            @click="selectExam(exam)"
          >
            <div class="exam-item-content">
              <strong>{{ exam.title }}</strong>
              <div class="exam-item-meta">
                <span>
                  <i class="fas fa-question-circle"></i>
                  {{ exam.questions?.length || 0 }} soal
                </span>
                <span>
                  <i class="fas fa-clock"></i>
                  {{ exam.duration_minutes }} menit
                </span>
                <span class="badge" :class="exam.is_published ? 'badge-success' : 'badge-warning'">
                  {{ exam.is_published ? 'Terbit' : 'Draft' }}
                </span>
              </div>
            </div>
            <div class="exam-actions">
              <button
                @click.stop="togglePublish(exam)"
                class="btn-icon"
                :class="exam.is_published ? 'btn-success' : 'btn-secondary'"
                :title="exam.is_published ? 'Unpublish ujian' : 'Publish ujian'"
              >
                <i :class="exam.is_published ? 'fas fa-eye' : 'fas fa-eye-slash'"></i>
              </button>
              <i class="fas fa-chevron-right"></i>
            </div>
          </button>
        </div>
      </section>

      <!-- Section: Questions Management -->
      <section v-if="selected" class="card questions-section">
        <div class="section-header">
          <h2>
            <i class="fas fa-question"></i>
            Soal — {{ selected.title }}
          </h2>
          <button @click="selected = null" class="btn btn-secondary btn-sm">
            <i class="fas fa-times"></i> Tutup
          </button>
        </div>

        <!-- Add Question Form -->
        <div class="question-form">
          <h3>Tambah Soal Baru</h3>
          <form @submit.prevent="createQuestion">
            <div class="form-group">
              <label>Pertanyaan *</label>
              <textarea
                v-model="question.question"
                class="input"
                placeholder="Tulis pertanyaan di sini..."
                rows="4"
                required
              ></textarea>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Tipe Soal *</label>
                <select v-model="question.type" class="input" required>
                  <option value="multiple_choice">Pilihan Ganda</option>
                  <option value="essay">Essay</option>
                </select>
              </div>

              <div class="form-group">
                <label>Nilai *</label>
                <input
                  v-model.number="question.score"
                  type="number"
                  class="input"
                  min="0"
                  required
                />
              </div>
            </div>

            <!-- Options for Multiple Choice -->
            <div v-if="question.type === 'multiple_choice'" class="options-section">
              <label>Pilihan Jawaban *</label>
              <div v-for="(option, index) in question.options" :key="index" class="option-row">
                <input
                  v-model="option.option_text"
                  class="input"
                  :placeholder="`Pilihan ${String.fromCharCode(65 + index)}`"
                  required
                />
                <label class="radio-label">
                  <input
                    type="radio"
                    :name="`correct-option`"
                    :checked="option.is_correct"
                    @change="setCorrectOption(index)"
                  />
                  <span>Jawaban Benar</span>
                </label>
                <button
                  v-if="question.options.length > 2"
                  type="button"
                  class="btn-icon btn-danger"
                  @click="removeOption(index)"
                  title="Hapus pilihan"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>

              <button type="button" class="btn btn-secondary btn-sm" @click="addOption">
                <i class="fas fa-plus"></i> Tambah Pilihan
              </button>
            </div>

            <button type="submit" class="btn btn-primary" :disabled="saving">
              <i class="fas fa-plus"></i>
              {{ saving ? 'Menyimpan...' : 'Tambah Soal' }}
            </button>
          </form>
        </div>

        <!-- Questions List -->
        <div class="questions-list">
          <h3>Daftar Soal ({{ selected.questions?.length || 0 }})</h3>

          <div v-if="!selected.questions || selected.questions.length === 0" class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>Belum ada soal. Tambahkan soal di atas.</p>
          </div>

          <ol v-else class="numbered-list">
            <li v-for="q in selected.questions" :key="q.id" class="question-item">
              <div class="question-content">
                <strong>{{ q.question }}</strong>
                <div class="question-meta">
                  <span class="badge badge-info">
                    {{ q.type === 'multiple_choice' ? 'Pilihan Ganda' : 'Essay' }}
                  </span>
                  <span>{{ q.score }} poin</span>
                </div>

                <!-- Show options for multiple choice -->
                <div v-if="q.type === 'multiple_choice' && q.options" class="options-preview">
                  <div
                    v-for="opt in q.options"
                    :key="opt.id"
                    class="option-preview"
                    :class="{ correct: opt.is_correct }"
                  >
                    <i :class="opt.is_correct ? 'fas fa-check-circle' : 'far fa-circle'"></i>
                    {{ opt.option_text }}
                  </div>
                </div>
              </div>
            </li>
          </ol>
        </div>
      </section>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../../utils/api'
import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'

// Menu sama persis seperti dashboard_guru.vue, supaya sidebar konsisten
// di semua halaman portal guru.
const navigation = [
  { label: 'Dashboard', icon: 'fas fa-chart-pie', to: '/dashboard/guru' },
  { label: 'Data Kelas', icon: 'fas fa-users', to: '/dashboard/guru/kelas' },
  { label: 'Materi', icon: 'fas fa-book-open', to: '/dashboard/guru/materi' },
  { label: 'Tugas', icon: 'fas fa-book-open', to: '/dashboard/guru/tugas' },
  {
    label: 'Ujian PPDB & Soal',
    icon: 'fas fa-file-circle-check',
    to: '/dashboard/guru/ujian-ppdb',
  },
]

// State
const exams = ref([])
const selected = ref(null)
const saving = ref(false)

// Form for creating exam
const form = ref({
  title: '',
  description: '',
  start_at: null,
  end_at: null,
  duration_minutes: 60,
  passing_score: null,
  is_published: false,
  show_result: false,
})

// Form for creating question
const question = ref({
  question: '',
  type: 'multiple_choice',
  score: 1,
  options: [
    { option_text: '', is_correct: false },
    { option_text: '', is_correct: false },
  ],
})

// Load exams
async function loadExams() {
  try {
    const response = await api.get('/ppdb/manage/exams')
    exams.value = response.data.data

    // Refresh selected exam if exists
    if (selected.value) {
      selected.value = exams.value.find((e) => e.id === selected.value.id)
    }

    console.log('📚 Loaded exams:', exams.value)
  } catch (error) {
    console.error('Error loading exams:', error)
    alert('Gagal memuat daftar ujian')
  }
}

// Create exam
async function createExam() {
  try {
    saving.value = true
    await api.post('/ppdb/manage/exams', form.value)

    // Reset form
    form.value = {
      title: '',
      description: '',
      start_at: null,
      end_at: null,
      duration_minutes: 60,
      passing_score: null,
      is_published: false,
      show_result: false,
    }

    await loadExams()
    alert('Ujian berhasil dibuat!')
  } catch (error) {
    console.error('Error creating exam:', error)
    alert(error.response?.data?.message || 'Gagal membuat ujian')
  } finally {
    saving.value = false
  }
}

// Select exam
function selectExam(exam) {
  selected.value = exam

  // Scroll to questions section
  setTimeout(() => {
    document.querySelector('.questions-section')?.scrollIntoView({ behavior: 'smooth' })
  }, 100)
}

// Toggle publish status
async function togglePublish(exam) {
  try {
    const newStatus = !exam.is_published
    const confirmMsg = newStatus
      ? 'Publish ujian ini? Siswa akan dapat melihat dan mengerjakan ujian.'
      : 'Unpublish ujian ini? Siswa tidak akan dapat melihat ujian.'

    if (!confirm(confirmMsg)) {
      return
    }

    await api.patch(`/ppdb/manage/exams/${exam.id}`, {
      is_published: newStatus,
    })

    await loadExams()
    alert(newStatus ? 'Ujian berhasil dipublish!' : 'Ujian berhasil di-unpublish!')
  } catch (error) {
    console.error('Error toggling publish:', error)
    alert('Gagal mengubah status publish')
  }
}

// Add option
function addOption() {
  question.value.options.push({
    option_text: '',
    is_correct: false,
  })
}

// Remove option
function removeOption(index) {
  question.value.options.splice(index, 1)
}

// Set correct option
function setCorrectOption(index) {
  question.value.options.forEach((opt, i) => {
    opt.is_correct = i === index
  })
}

// Create question
async function createQuestion() {
  // Validation for multiple choice
  if (question.value.type === 'multiple_choice') {
    const hasCorrect = question.value.options.some((opt) => opt.is_correct)
    if (!hasCorrect) {
      alert('Pilih satu jawaban yang benar!')
      return
    }

    const hasEmptyOption = question.value.options.some((opt) => !opt.option_text.trim())
    if (hasEmptyOption) {
      alert('Semua pilihan harus diisi!')
      return
    }
  }

  try {
    saving.value = true

    // Prepare data
    const payload = {
      question: question.value.question,
      type: question.value.type,
      score: question.value.score,
    }

    // Only include options for multiple choice
    if (question.value.type === 'multiple_choice') {
      payload.options = question.value.options
    }

    await api.post(`/ppdb/manage/exams/${selected.value.id}/questions`, payload)

    // Reset question form
    question.value = {
      question: '',
      type: 'multiple_choice',
      score: 1,
      options: [
        { option_text: '', is_correct: false },
        { option_text: '', is_correct: false },
      ],
    }

    await loadExams()
    alert('Soal berhasil ditambahkan!')
  } catch (error) {
    console.error('Error creating question:', error)
    const errorMsg = error.response?.data?.message || 'Gagal menambahkan soal'
    alert(errorMsg)
  } finally {
    saving.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadExams()
})
</script>

<style scoped>
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  display: grid;
  gap: 2rem;
}

.card {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.card h2 {
  font-size: 1.25rem;
  margin: 0 0 1.5rem 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.card h3 {
  font-size: 1.1rem;
  margin-bottom: 1rem;
}

.form {
  display: grid;
  gap: 1rem;
}

.form-group {
  display: grid;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 500;
  color: #374151;
  font-size: 0.9rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.input {
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font: inherit;
  transition: border-color 0.2s;
}

.input:focus {
  outline: none;
  border-color: #3d7a50;
}

textarea.input {
  resize: vertical;
  min-height: 80px;
  font-family: inherit;
}

.checkbox-group {
  display: grid;
  gap: 0.75rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
}

.checkbox-label input[type='checkbox'] {
  width: 18px;
  height: 18px;
  cursor: pointer;
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

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary {
  background: #3d7a50;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #2d5f3f;
}

.btn-secondary {
  background: #e5e7eb;
  color: #374151;
}

.btn-secondary:hover {
  background: #d1d5db;
}

.btn-danger {
  background: #dc2626;
  color: white;
}

.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}

.btn-icon {
  padding: 0.5rem;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-icon:hover {
  transform: scale(1.1);
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover {
  background: #059669;
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  color: #9ca3af;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.exam-list {
  display: grid;
  gap: 0.5rem;
}

.exam-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  background: none;
  cursor: pointer;
  text-align: left;
  transition: all 0.2s;
}

.exam-item:hover {
  border-color: #3d7a50;
  background: #f0fdf4;
}

.exam-item.active {
  border-color: #3d7a50;
  background: #f0fdf4;
}

.exam-item-content {
  flex: 1;
  display: grid;
  gap: 0.5rem;
}

.exam-item-content strong {
  color: #1f2937;
}

.exam-item-meta {
  display: flex;
  gap: 1rem;
  font-size: 0.875rem;
  color: #6b7280;
}

.exam-item-meta i {
  margin-right: 0.25rem;
}

.exam-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.badge {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-success {
  background: #d1fae5;
  color: #065f46;
}

.badge-warning {
  background: #fef3c7;
  color: #92400e;
}

.badge-info {
  background: #dbeafe;
  color: #1e40af;
}

.questions-section {
  grid-column: 1 / -1;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.section-header h2 {
  margin: 0;
}

.question-form {
  background: #f9fafb;
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 2rem;
}

.options-section {
  display: grid;
  gap: 0.75rem;
}

.option-row {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.option-row .input {
  flex: 1;
}

.radio-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  white-space: nowrap;
  cursor: pointer;
}

.questions-list {
  margin-top: 2rem;
}

.numbered-list {
  counter-reset: question-counter;
  list-style: none;
  padding: 0;
  display: grid;
  gap: 1rem;
}

.numbered-list li {
  counter-increment: question-counter;
  position: relative;
  padding-left: 3rem;
}

.numbered-list li::before {
  content: counter(question-counter);
  position: absolute;
  left: 0;
  top: 0;
  width: 2rem;
  height: 2rem;
  background: #3d7a50;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.question-item {
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  padding: 1rem;
}

.question-content {
  display: grid;
  gap: 0.75rem;
}

.question-meta {
  display: flex;
  gap: 1rem;
  align-items: center;
  font-size: 0.875rem;
  color: #6b7280;
}

.options-preview {
  display: grid;
  gap: 0.5rem;
  margin-top: 0.5rem;
  font-size: 0.9rem;
}

.option-preview {
  padding: 0.5rem;
  background: #f9fafb;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.option-preview.correct {
  background: #d1fae5;
  color: #065f46;
  font-weight: 500;
}

.option-preview i {
  width: 20px;
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }

  .container {
    padding: 1rem;
  }

  .card {
    padding: 1rem;
  }

  .option-row {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>