<template>
  <main class="shell">
    <header>
      <router-link to="/dashboard/casis">← Kembali</router-link>
      <b>{{ exam?.title }}</b>
      <span v-if="seconds >= 0">{{ clock }}</span>
    </header>

    <section v-if="result" class="result">
      <h1>Ujian selesai</h1>
      <p v-if="result.show_result">
        Nilai Anda: <b>{{ result.score }}</b>
      </p>
      <p v-else>Jawaban Anda sudah diterima. Hasil akan ditampilkan ketika diizinkan admin.</p>
    </section>
    <section v-else-if="error" class="result error">
      <h1>Ujian tidak dapat dimuat</h1>
      <p>{{ error }}</p>
      <button class="btn btn-primary" @click="loadExam">Coba lagi</button>
    </section>
    <section v-else-if="exam" class="paper">
      <h1>{{ exam.title }}</h1>
      <p>{{ exam.description }}</p>
      <p v-if="questions.length === 0" class="empty">Belum ada soal untuk ujian ini.</p>
      <form v-else @submit.prevent="submit">
        <article v-for="(q, index) in questions" :key="q.id">
          <b>{{ index + 1 }}. {{ q.question }}</b>
          <label v-for="o in q.options" :key="o.id">
            <input
              v-model="answers[q.id].option_id"
              type="radio"
              :name="`q${q.id}`"
              :value="o.id"
            />
            {{ o.option_text }}
          </label>
          <textarea
            v-if="q.type === 'essay'"
            v-model="answers[q.id].answer_text"
            placeholder="Tulis jawaban Anda"
          />
        </article>
        <button class="btn btn-primary" :disabled="saving">
          {{ saving ? 'Mengirim...' : 'Submit jawaban' }}
        </button>
      </form>
    </section>
    <p v-else>Memuat ujian...</p>
  </main>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../utils/api'

const route = useRoute()
const exam = ref(null)
const session = ref(null)
const questions = ref([])
const answers = ref({})
const saving = ref(false)
const result = ref(null)
const error = ref('')
const seconds = ref(-1)
let timer = null

const clock = computed(() => {
  const minutes = String(Math.max(0, Math.floor(seconds.value / 60))).padStart(2, '0')
  const remainingSeconds = String(Math.max(0, seconds.value % 60)).padStart(2, '0')
  return `${minutes}:${remainingSeconds}`
})

function startTimer() {
  clearInterval(timer)
  timer = setInterval(() => {
    if (seconds.value > 0) seconds.value--
    else if (seconds.value === 0) {
      clearInterval(timer)
      submit()
    }
  }, 1000)
}

async function loadExam() {
  error.value = ''
  result.value = null
  try {
    const response = await api.post(`/ppdb/exams/${route.params.id}/start`)
    const data = response.data.data
    exam.value = data.exam
    session.value = data.session
    questions.value = data.questions || []
    answers.value = {}

    questions.value.forEach((question) => {
      const savedAnswer = data.saved_answers?.[question.id]
      answers.value[question.id] = {
        question_id: question.id,
        option_id: savedAnswer?.option_id ?? null,
        answer_text: savedAnswer?.answer_text ?? '',
      }
    })

    seconds.value = data.session.remaining_seconds
    startTimer()
  } catch (requestError) {
    error.value = requestError.response?.data?.message || 'Gagal memuat ujian. Silakan coba lagi.'
  }
}

async function submit() {
  if (saving.value || !session.value || result.value) return
  saving.value = true
  try {
    const response = await api.post(`/ppdb/exams/${route.params.id}/submit`, {
      session_id: session.value.id,
      answers: Object.values(answers.value),
    })
    result.value = response.data.data
    clearInterval(timer)
  } catch (requestError) {
    error.value =
      requestError.response?.data?.message || 'Gagal mengirim jawaban. Silakan coba lagi.'
  } finally {
    saving.value = false
  }
}

onMounted(loadExam)
onUnmounted(() => clearInterval(timer))
</script>

<style scoped>
.shell {
  max-width: 820px;
  margin: auto;
  padding: 28px 18px;
}
.shell header {
  display: flex;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 20px;
}
.shell header span {
  font-weight: bold;
  color: #1c9c5f;
}
.paper,
.result {
  background: white;
  border: 1px solid #e4e7ec;
  border-radius: 15px;
  padding: 28px;
}
.paper h1 {
  font-size: 1.35rem;
}
.paper article {
  padding: 20px 0;
  border-top: 1px solid #e4e7ec;
  display: grid;
  gap: 11px;
}
.paper label {
  padding: 10px;
  border: 1px solid #e4e7ec;
  border-radius: 8px;
}
.paper textarea {
  min-height: 100px;
  padding: 10px;
}
.result {
  text-align: center;
  margin-top: 60px;
}
.error {
  color: #b42318;
}
.empty {
  color: #667085;
}
</style>
