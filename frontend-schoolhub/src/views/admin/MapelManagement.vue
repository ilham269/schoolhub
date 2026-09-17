<template>
  <DashboardLayout title="Kelola Mata Pelajaran" role-label="Admin" :navigation="navigation">
    <UiAlert v-if="alert.show" class="mb-4" :type="alert.type" @close="alert.show = false">
      {{ alert.message }}
    </UiAlert>

    <UiAlert v-if="error" class="mb-4" type="danger" title="Gagal memuat mata pelajaran" :dismissible="false">
      <div class="flex items-center justify-between gap-4">
        <span>{{ error }}</span>
        <button class="shrink-0 rounded-lg border border-rose-300 px-3 py-1 text-sm font-medium text-rose-700 hover:bg-rose-50" @click="load">
          Coba lagi
        </button>
      </div>
    </UiAlert>

    <UiCard title="Mata Pelajaran" subtitle="Atur kode, KKM, jumlah jam, dan status mata pelajaran." :padded="false">
      <template #actions>
        <UiButton @click="openCreate">Tambah mata pelajaran</UiButton>
      </template>

      <div class="border-y border-slate-100 px-6 py-4">
        <UiInput v-model="search" class="max-w-md" placeholder="Cari kode atau nama mata pelajaran...">
          <template #icon>
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="9" cy="9" r="5" /><path d="m13 13 4 4" stroke-linecap="round" /></svg>
          </template>
        </UiInput>
      </div>

      <UiTable :columns="columns" :rows="filtered" :loading="loading">
        <template #empty>
          <p class="font-medium text-slate-600">{{ search ? 'Mata pelajaran tidak ditemukan' : 'Belum ada mata pelajaran' }}</p>
          <p class="mt-1 text-sm text-slate-400">{{ search ? 'Coba ubah kata kunci pencarian.' : 'Tambahkan mata pelajaran pertama untuk mulai mengatur kegiatan akademik.' }}</p>
        </template>
        <template #row="{ row }">
          <td class="px-6 py-4"><p class="font-medium text-slate-800">{{ row.kode_mapel }}</p></td>
          <td class="px-6 py-4"><p class="font-medium text-slate-800">{{ row.nama_mapel }}</p><p v-if="row.deskripsi" class="mt-0.5 max-w-md truncate text-xs text-slate-400">{{ row.deskripsi }}</p></td>
          <td class="px-6 py-4 text-slate-600">{{ row.jumlah_jam }} jam</td>
          <td class="px-6 py-4 text-slate-600">{{ row.kkm }}</td>
          <td class="px-6 py-4"><UiBadge :variant="row.is_active ? 'success' : 'neutral'">{{ row.is_active ? 'Aktif' : 'Nonaktif' }}</UiBadge></td>
          <td class="px-6 py-4"><div class="flex justify-end gap-2"><UiButton size="sm" variant="ghost" @click="openEdit(row)">Edit</UiButton><UiButton size="sm" variant="ghost" class="text-rose-600 hover:bg-rose-50 hover:text-rose-700" @click="confirmDelete(row)">Hapus</UiButton></div></td>
        </template>
      </UiTable>
    </UiCard>

    <UiModal v-model="formOpen" :title="editing ? 'Edit mata pelajaran' : 'Tambah mata pelajaran'" subtitle="Lengkapi informasi mata pelajaran.">
      <form id="mapel-form" class="grid gap-4 sm:grid-cols-2" @submit.prevent="save">
        <UiInput v-model="form.kode_mapel" label="Kode mata pelajaran" placeholder="Contoh: PWB01" required :error="fieldError('kode_mapel')" />
        <UiInput v-model="form.nama_mapel" label="Nama mata pelajaran" placeholder="Contoh: Pemrograman Web" required :error="fieldError('nama_mapel')" />
        <UiInput v-model.number="form.jumlah_jam" type="number" label="Jumlah jam" hint="Jumlah jam pelajaran per minggu." :error="fieldError('jumlah_jam')" />
        <UiInput v-model.number="form.kkm" type="number" label="KKM" hint="Nilai ketuntasan minimal, 0–100." :error="fieldError('kkm')" />
        <UiSelect v-model="form.is_active" class="sm:col-span-2" label="Status" :options="statusOptions" :error="fieldError('is_active')" />
        <label class="block sm:col-span-2"><span class="mb-1.5 block text-sm font-medium text-slate-700">Deskripsi <span class="text-slate-400">(opsional)</span></span><textarea v-model="form.deskripsi" rows="4" class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100" placeholder="Ringkasan materi atau cakupan pembelajaran." /><span v-if="fieldError('deskripsi')" class="mt-1.5 block text-xs text-rose-600">{{ fieldError('deskripsi') }}</span></label>
      </form>
      <template #footer><UiButton variant="secondary" :disabled="saving" @click="formOpen = false">Batal</UiButton><UiButton type="submit" form="mapel-form" :loading="saving">{{ editing ? 'Simpan perubahan' : 'Tambah mata pelajaran' }}</UiButton></template>
    </UiModal>

    <UiModal v-model="deleteOpen" title="Hapus mata pelajaran" subtitle="Tindakan ini tidak dapat dibatalkan.">
      <p class="text-sm text-slate-600">Hapus <span class="font-semibold text-slate-800">{{ selected?.nama_mapel }}</span> dari daftar mata pelajaran?</p>
      <template #footer><UiButton variant="secondary" :disabled="deleting" @click="deleteOpen = false">Batal</UiButton><UiButton variant="danger" :loading="deleting" @click="remove">Ya, hapus</UiButton></template>
    </UiModal>
  </DashboardLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import DashboardLayout from '@/components/dashboard/DashboardLayout.vue'
import { adminNavigation } from '@/views/admin/adminNavigation'
import UiAlert from '@/components/ui/UiAlert.vue'
import UiBadge from '@/components/ui/UiBadge.vue'
import UiButton from '@/components/ui/UiButton.vue'
import UiCard from '@/components/ui/UiCard.vue'
import UiInput from '@/components/ui/UiInput.vue'
import UiModal from '@/components/ui/UiModal.vue'
import UiSelect from '@/components/ui/UiSelect.vue'
import UiTable from '@/components/ui/UiTable.vue'
import { mapelApi } from '@/services/mapelApi'

const navigation = adminNavigation
const columns = [{ key: 'kode', label: 'Kode' }, { key: 'nama', label: 'Mata pelajaran' }, { key: 'jam', label: 'Jam' }, { key: 'kkm', label: 'KKM' }, { key: 'status', label: 'Status' }, { key: 'aksi', label: '', class: 'text-right' }]
const statusOptions = [{ value: 'true', label: 'Aktif' }, { value: 'false', label: 'Nonaktif' }]
const items = ref([]); const loading = ref(true); const error = ref(''); const search = ref(''); const formOpen = ref(false); const deleteOpen = ref(false); const editing = ref(false); const saving = ref(false); const deleting = ref(false); const selected = ref(null); const errors = ref({})
const alert = reactive({ show: false, type: 'success', message: '' })
const form = reactive({ kode_mapel: '', nama_mapel: '', deskripsi: '', jumlah_jam: 2, kkm: 75, is_active: 'true' })
const filtered = computed(() => { const q = search.value.trim().toLowerCase(); return !q ? items.value : items.value.filter((item) => `${item.kode_mapel} ${item.nama_mapel}`.toLowerCase().includes(q)) })
const fieldError = (name) => errors.value[name]?.[0] ?? ''
const messageFor = (e, fallback) => ({ 401: 'Sesi Anda telah berakhir. Silakan masuk kembali.', 403: 'Anda tidak memiliki akses ke mata pelajaran.', 404: 'Mata pelajaran tidak ditemukan.', 500: 'Server sedang bermasalah. Silakan coba lagi.' })[e.response?.status] ?? e.response?.data?.message ?? fallback
const notify = (type, message) => { Object.assign(alert, { show: true, type, message }); setTimeout(() => { alert.show = false }, 4000) }
async function load() { loading.value = true; error.value = ''; try { const body = await mapelApi.list(); items.value = body?.data ?? [] } catch (e) { error.value = messageFor(e, 'Mata pelajaran gagal dimuat. Silakan coba lagi.') } finally { loading.value = false } }
function resetForm() { Object.assign(form, { kode_mapel: '', nama_mapel: '', deskripsi: '', jumlah_jam: 2, kkm: 75, is_active: 'true' }); errors.value = {} }
function openCreate() { editing.value = false; selected.value = null; resetForm(); formOpen.value = true }
function openEdit(item) { editing.value = true; selected.value = item; Object.assign(form, { kode_mapel: item.kode_mapel, nama_mapel: item.nama_mapel, deskripsi: item.deskripsi ?? '', jumlah_jam: item.jumlah_jam, kkm: item.kkm, is_active: String(item.is_active) }); errors.value = {}; formOpen.value = true }
async function save() { saving.value = true; errors.value = {}; const payload = { ...form, jumlah_jam: Number(form.jumlah_jam), kkm: Number(form.kkm), is_active: form.is_active === 'true' }; try { const body = editing.value ? await mapelApi.update(selected.value.id, payload) : await mapelApi.create(payload); const record = body.data; if (editing.value) items.value = items.value.map((item) => item.id === record.id ? record : item); else items.value.unshift(record); formOpen.value = false; notify('success', body.message ?? 'Mata pelajaran berhasil disimpan.') } catch (e) { if (e.response?.status === 422) errors.value = e.response.data.errors ?? {}; else notify('danger', messageFor(e, 'Mata pelajaran gagal disimpan.')) } finally { saving.value = false } }
function confirmDelete(item) { selected.value = item; deleteOpen.value = true }
async function remove() { deleting.value = true; try { const body = await mapelApi.remove(selected.value.id); items.value = items.value.filter((item) => item.id !== selected.value.id); deleteOpen.value = false; notify('success', body.message ?? 'Mata pelajaran berhasil dihapus.') } catch (e) { notify('danger', messageFor(e, 'Mata pelajaran gagal dihapus.')) } finally { deleting.value = false } }
onMounted(load)
</script>
