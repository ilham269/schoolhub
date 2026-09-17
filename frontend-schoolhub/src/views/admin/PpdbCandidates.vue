<template>
  <DashboardLayout title="Pendaftaran PPDB" role-label="Admin" :navigation="navigation"
    ><section class="card">
      <h2>Calon siswa</h2>
      <p>Verifikasi data lalu buat akun ujian untuk calon siswa.</p>
      <div v-for="c in candidates" :key="c.id" class="row">
        <div>
          <b>{{ c.nama }}</b
          ><small>{{ c.email }} · NISN {{ c.nisn }} · {{ c.asal_sekolah }}</small
          ><small>Status: {{ c.status }} {{ c.user ? '· Akun dibuat' : ' ' }}</small>
        </div>
        <div>
          <button @click="status(c, 'Terverifikasi')">Verifikasi</button
          ><button v-if="!c.user" class="btn btn-primary" @click="account(c)">Buat akun</button>
        </div>
      </div>
    </section></DashboardLayout
  >
</template>
<script setup>
import { onMounted, ref } from 'vue'
import api from '../../utils/api'
import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'
import { adminNavigation } from '@/views/admin/adminNavigation'

const candidates = ref([])
const navigation = adminNavigation

async function load() {
  candidates.value = (await api.get('/ppdb/manage/candidates')).data.data
}
onMounted(load)
async function status(c, s) {
  await api.patch(`/ppdb/manage/candidates/${c.id}`, { status: s })
  load()
}
async function account(c) {
  const password = window.prompt(`Password awal untuk ${c.nama} (minimal 8 karakter):`)
  if (password?.length >= 8) {
    await api.post(`/ppdb/manage/candidates/${c.id}/account`, { password })
    load()
  }
}
</script>
<style scoped>
.card {
  background: #fff;
  border: 1px solid var(--line);
  border-radius: 16px;
  padding: 22px;
}
.row {
  display: flex;
  justify-content: space-between;
  gap: 15px;
  padding: 16px 0;
  border-top: 1px solid var(--line);
}
small {
  display: block;
  color: #667085;
  margin-top: 4px;
}
button {
  padding: 8px 10px;
  margin-left: 7px;
  border: 1px solid var(--line);
  border-radius: 7px;
  background: white;
  cursor: pointer;
}
@media (max-width: 600px) {
  .row {
    flex-direction: column;
  }
}
</style>
