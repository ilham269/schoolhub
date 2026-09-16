<template>
  <DashboardLayout title="Materi" role-label="Guru" :navigation="navigation">
    <UiAlert v-if="notice" :type="notice.type" class="mb-4" @close="notice = null">{{ notice.text }}</UiAlert>
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_380px]">
      <UiCard title="Daftar Materi" subtitle="Kelola materi yang Anda buat.">
        <p v-if="loading" class="py-10 text-center text-slate-400">Memuat materi...</p>
        <p v-else-if="!items.length" class="py-10 text-center text-slate-500">Belum ada materi yang tersedia.</p>
        <div v-else class="space-y-3">
          <article v-for="materi in items" :key="materi.id" class="rounded-xl border border-slate-200 p-4">
            <div class="flex items-start justify-between gap-4"><div><h2 class="font-semibold">{{ materi.judul }}</h2><p class="mt-1 text-sm text-slate-500">{{ materi.kelas?.name || 'Kelas' }} · {{ materi.mapel?.nama_mapel || 'Mapel' }}</p></div><span class="rounded-full px-2.5 py-1 text-xs" :class="materi.published_at ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'">{{ materi.published_at ? 'Published' : 'Draft' }}</span></div>
            <p class="mt-2 text-xs text-slate-400">{{ materi.published_at ? `Dipublikasikan ${formatDate(materi.published_at)}` : 'Belum dipublikasikan' }}</p>
            <div class="mt-3 flex gap-3"><button class="text-sm font-medium text-emerald-700 hover:underline" @click="edit(materi)">Edit</button><button class="text-sm font-medium text-rose-600 hover:underline" @click="remove(materi)">Hapus</button></div>
          </article>
        </div>
      </UiCard>
      <UiCard :title="editing ? 'Edit Materi' : 'Tambah Materi'" subtitle="File atau link wajib diisi.">
        <form class="space-y-4" @submit.prevent="save">
          <label class="block text-sm font-medium">Kelas<select v-model="form.kelas_id" required class="mt-1 w-full rounded-lg border border-slate-300 p-2"><option value="">Pilih kelas</option><option v-for="kelas in classes" :key="kelas.id" :value="kelas.id">{{ kelas.name }}</option></select></label>
          <label class="block text-sm font-medium">Mata Pelajaran<select v-model="form.mapel_id" required class="mt-1 w-full rounded-lg border border-slate-300 p-2"><option value="">Pilih mata pelajaran</option><option v-for="mapel in subjects" :key="mapel.id" :value="mapel.id">{{ mapel.nama_mapel }}</option></select></label>
          <label class="block text-sm font-medium">Judul<input v-model="form.judul" required class="mt-1 w-full rounded-lg border border-slate-300 p-2" /></label>
          <label class="block text-sm font-medium">Deskripsi<textarea v-model="form.deskripsi" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 p-2"></textarea></label>
          <label class="block text-sm font-medium">File<input type="file" class="mt-1 block w-full text-sm" @change="form.file = $event.target.files[0]" /></label>
          <label class="block text-sm font-medium">Link<input v-model="form.link" type="url" class="mt-1 w-full rounded-lg border border-slate-300 p-2" placeholder="https://..." /></label>
          <label class="flex items-center gap-2 text-sm"><input v-model="form.publish" type="checkbox" /> Publikasikan sekarang</label>
          <div class="flex gap-2"><UiButton type="submit" :loading="saving">{{ editing ? 'Simpan perubahan' : 'Simpan materi' }}</UiButton><UiButton v-if="editing" type="button" variant="secondary" @click="reset">Batal</UiButton></div>
        </form>
      </UiCard>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import DashboardLayout from '@/components/dashboard/DashboardLayout.vue'
import UiAlert from '@/components/ui/UiAlert.vue'
import UiButton from '@/components/ui/UiButton.vue'
import UiCard from '@/components/ui/UiCard.vue'
import { materiApi } from '@/services/materiApi'
import { kelasApi } from '@/services/kelasApi'
import http from '@/services/kelasApi'

const navigation = [{ label: 'Dashboard', icon: 'fas fa-chart-pie', to: '/dashboard/guru' }, { label: 'Data Kelas', icon: 'fas fa-users', to: '/dashboard/guru/kelas' }, { label: 'Materi', icon: 'fas fa-book-open', to: '/dashboard/guru/materi' }, { label: 'Tugas', icon: 'fas fa-clipboard-list', to: '/dashboard/guru/tugas' }, { label: 'Ujian PPDB & Soal', icon: 'fas fa-file-circle-check', to: '/dashboard/guru/ujian-ppdb' }]
const items = ref([]), classes = ref([]), subjects = ref([]), loading = ref(true), saving = ref(false), editing = ref(null), notice = ref(null)
const form = reactive({ kelas_id: '', mapel_id: '', judul: '', deskripsi: '', link: '', file: null, publish: false })
const formatDate = (value) => new Date(value).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
const message = (e, fallback) => e.response?.data?.message || ({ 401: 'Sesi Anda telah berakhir. Silakan masuk kembali.', 403: 'Anda tidak memiliki akses untuk melakukan tindakan ini.', 404: 'Materi tidak ditemukan.', 422: 'Periksa kembali data materi Anda.', 500: 'Server sedang bermasalah. Silakan coba lagi.' })[e.response?.status] || fallback
const reset = () => { editing.value = null; Object.assign(form, { kelas_id: '', mapel_id: '', judul: '', deskripsi: '', link: '', file: null, publish: false }) }
const load = async () => { loading.value = true; try { const [materi, kelas, mapel] = await Promise.all([materiApi.list(), kelasApi.list(), http.get('/mapel?per_page=100').then(r => r.data)]); items.value = materi.data ?? materi; classes.value = kelas.data ?? kelas; subjects.value = mapel.data?.data ?? mapel.data ?? [] } catch (e) { notice.value = { type: 'danger', text: message(e, 'Materi gagal dimuat. Silakan coba lagi.') } } finally { loading.value = false } }
const edit = (materi) => { editing.value = materi; Object.assign(form, { kelas_id: materi.kelas_id, mapel_id: materi.mapel_id, judul: materi.judul, deskripsi: materi.deskripsi || '', link: materi.link || '', file: null, publish: Boolean(materi.published_at) }); window.scrollTo({ top: 0, behavior: 'smooth' }) }
const save = async () => { saving.value = true; try { const payload = new FormData(); for (const key of ['kelas_id', 'mapel_id', 'judul', 'deskripsi', 'link']) payload.append(key, form[key] || ''); if (form.file) payload.append('file', form.file); if (form.publish) payload.append('published_at', new Date().toISOString()); else payload.append('published_at', ''); if (editing.value) await materiApi.update(editing.value.id, payload); else await materiApi.create(payload); notice.value = { type: 'success', text: 'Materi berhasil disimpan.' }; reset(); await load() } catch (e) { notice.value = { type: 'danger', text: message(e, 'Materi gagal disimpan.') } } finally { saving.value = false } }
const remove = async (materi) => { if (!window.confirm(`Hapus materi "${materi.judul}"?`)) return; try { await materiApi.remove(materi.id); notice.value = { type: 'success', text: 'Materi berhasil dihapus.' }; await load() } catch (e) { notice.value = { type: 'danger', text: message(e, 'Materi gagal dihapus.') } } }
onMounted(load)
</script>
