<template>
  <DashboardLayout title="Portal Calon Siswa" role-label="Calon Siswa" :navigation="navigation"
    ><section class="hero">
      <div>
        <span>Status pendaftaran</span>
        <h2>{{ profile?.status || 'Memuat...' }}</h2>
        <p>{{ profile?.nama }} · {{ profile?.jurusan }}</p>
      </div>
      <i class="fas fa-user-graduate" />
    </section>
    <section class="card">
      <h2>Ujian tersedia</h2>
      <p v-if="!exams.length">Belum ada ujian yang tersedia saat ini.</p>
      <div v-for="exam in exams" :key="exam.id" class="exam">
        <div>
          <b>{{ exam.title }}</b>
          <p>{{ exam.description || 'Ujian seleksi siswa baru' }}</p>
          <small>{{ exam.duration_minutes }} menit · {{ exam.questions_count }} soal</small>
        </div>
        <router-link class="btn btn-primary" :to="`/dashboard/casis/ujian/${exam.id}`">{{
          exam.attempt?.status === 'submitted' ? 'Lihat hasil' : 'Mulai ujian'
        }}</router-link>
      </div>
    </section></DashboardLayout
  >
</template>
<script setup>
import { onMounted, ref } from 'vue'
import api from '../../utils/api'
import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'
const profile = ref(null),
  exams = ref([])
const navigation = [{ label: 'Dashboard', icon: 'fas fa-chart-pie', to: '/dashboard/casis' }]
onMounted(async () => {
  try {
    const [p, e] = await Promise.all([api.get('/ppdb/profile'), api.get('/ppdb/exams')])
    profile.value = p.data.data
    exams.value = e.data.data
  } catch {}
})
</script>
<style scoped>
.hero {
  background: linear-gradient(110deg, #06231a, #1c9c5f);
  color: #fff;
  border-radius: 18px;
  padding: 26px;
  display: flex;
  justify-content: space-between;
}
.hero span,
.hero p {
  color: #d9efe0;
}
.hero h2 {
  margin: 4px 0;
}
.hero i {
  font-size: 3rem;
  color: #8fd94a;
}
.card {
  margin-top: 22px;
  background: #fff;
  border: 1px solid var(--line);
  border-radius: 16px;
  padding: 22px;
}
.card h2 {
  font-size: 1.1rem;
}
.exam {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 18px;
  border-top: 1px solid var(--line);
  padding: 16px 0;
}
.exam p {
  margin: 5px 0;
  font-size: 0.88rem;
}
.exam small {
  color: #667085;
}
@media (max-width: 600px) {
  .exam {
    align-items: flex-start;
    flex-direction: column;
  }
}
</style>
