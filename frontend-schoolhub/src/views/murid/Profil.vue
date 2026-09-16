<template>
  <DashboardLayout title="Profil Murid" role-label="Murid" :navigation="navigation">
    <UiAlert v-if="alert.show" :type="alert.type" class="mb-4" @close="alert.show = false">
      {{ alert.message }}
    </UiAlert>
    <UiAlert v-if="error" type="danger" title="Gagal memuat profil" class="mb-4" :dismissible="false">
      {{ error }}
    </UiAlert>

    <div v-if="loading" class="flex justify-center py-20 text-slate-400">Memuat profil...</div>

    <template v-else-if="profil">
      <!-- Header profil -->
      <UiCard class="mb-6">
        <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-start">
          <div class="relative shrink-0">
            <img v-if="profil.gambar_murid" :src="profil.gambar_murid" alt="Foto profil"
                 class="h-24 w-24 rounded-2xl object-cover ring-4 ring-emerald-50" />
            <span v-else class="grid h-24 w-24 place-items-center rounded-2xl bg-emerald-50 text-3xl font-semibold text-emerald-600 ring-4 ring-emerald-50">
              {{ inisial }}
            </span>
          </div>
          <div class="flex-1 text-center sm:text-left">
            <h2 class="text-xl font-semibold text-slate-800">{{ profil.nama_lengkap_murid }}</h2>
            <p class="mt-0.5 text-sm text-slate-400">NIS {{ profil.nis }} • {{ kelasLabel }}</p>
            <div class="mt-3 flex flex-wrap justify-center gap-2 sm:justify-start">
              <UiBadge variant="brand">{{ profil.gender === 'P' ? 'Perempuan' : 'Laki-laki' }}</UiBadge>
              <UiBadge v-if="profil.agama" variant="neutral">{{ profil.agama }}</UiBadge>
              <UiBadge v-if="profil.hobi" variant="info">Hobi: {{ profil.hobi }}</UiBadge>
            </div>
          </div>
          <UiButton variant="secondary" @click="openEdit">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round">
              <path d="M14 3l3 3-9 9H5v-3z" />
            </svg>
            Ubah profil
          </UiButton>
        </div>
      </UiCard>

      <div class="grid gap-6 lg:grid-cols-2">
        <!-- Data pribadi -->
        <UiCard title="Data Pribadi">
          <dl class="divide-y divide-slate-100 text-sm">
            <div class="flex justify-between py-2.5"><dt class="text-slate-400">Tempat, tanggal lahir</dt><dd class="font-medium text-slate-700">{{ ttlLabel }}</dd></div>
            <div class="flex justify-between py-2.5"><dt class="text-slate-400">Nomor telepon</dt><dd class="font-medium text-slate-700">{{ profil.nomor_telepon || '—' }}</dd></div>
            <div class="flex justify-between py-2.5"><dt class="text-slate-400">Anak ke</dt><dd class="font-medium text-slate-700">{{ profil.anak_ke || '—' }}</dd></div>
            <div class="flex justify-between py-2.5"><dt class="text-slate-400">Jumlah saudara</dt><dd class="font-medium text-slate-700">{{ profil.jumlah_saudara ?? '—' }}</dd></div>
            <div class="flex justify-between py-2.5"><dt class="text-slate-400">Cita-cita</dt><dd class="font-medium text-slate-700">{{ profil.cita_cita || '—' }}</dd></div>
            <div class="py-2.5">
              <dt class="mb-1 text-slate-400">Alamat</dt>
              <dd class="font-medium text-slate-700">{{ profil.alamat || '—' }}</dd>
            </div>
          </dl>
        </UiCard>

        <!-- Data orang tua -->
        <UiCard title="Data Orang Tua / Wali">
          <dl class="divide-y divide-slate-100 text-sm">
            <div class="flex justify-between py-2.5"><dt class="text-slate-400">Nama orang tua</dt><dd class="font-medium text-slate-700">{{ profil.nama_orangtua || '—' }}</dd></div>
            <div class="flex justify-between py-2.5"><dt class="text-slate-400">Nama ayah</dt><dd class="font-medium text-slate-700">{{ profil.nama_ayah || '—' }}</dd></div>
            <div class="flex justify-between py-2.5"><dt class="text-slate-400">Pekerjaan ayah</dt><dd class="font-medium text-slate-700">{{ profil.pekerjaan_ayah || '—' }}</dd></div>
            <div class="flex justify-between py-2.5"><dt class="text-slate-400">Nama ibu</dt><dd class="font-medium text-slate-700">{{ profil.nama_ibu || '—' }}</dd></div>
            <div class="flex justify-between py-2.5"><dt class="text-slate-400">Pekerjaan ibu</dt><dd class="font-medium text-slate-700">{{ profil.pekerjaan_ibu || '—' }}</dd></div>
            <div class="flex justify-between py-2.5"><dt class="text-slate-400">Telepon orang tua</dt><dd class="font-medium text-slate-700">{{ profil.nomor_telepon_ortu || '—' }}</dd></div>
          </dl>
        </UiCard>
      </div>
    </template>

    <!-- Modal ubah profil -->
    <UiModal v-model="formOpen" size="lg" title="Ubah profil" subtitle="Data NIS dan kelas tidak bisa diubah sendiri.">
      <form class="space-y-6" @submit.prevent="submit">
        <div>
          <p class="mb-3 text-sm font-semibold text-slate-700">Data pribadi</p>
          <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
              <UiInput v-model="form.nama_lengkap_murid" label="Nama lengkap" :error="errors.nama_lengkap_murid" required />
            </div>
            <UiSelect v-model="form.gender" label="Jenis kelamin" :options="genderOptions" :error="errors.gender" required />
            <UiSelect v-model="form.agama" label="Agama" :options="agamaOptions" placeholder="Pilih agama" />
            <UiInput v-model="form.tempat_lahir" label="Tempat lahir" />
            <UiInput v-model="form.tanggal_lahir" label="Tanggal lahir" type="date" />
            <UiInput v-model="form.nomor_telepon" label="Nomor telepon" placeholder="08xxxxxxxxxx" />
            <UiInput v-model="form.hobi" label="Hobi" />
            <UiInput v-model="form.cita_cita" label="Cita-cita" />
            <UiInput v-model="form.anak_ke" label="Anak ke" type="number" />
            <UiInput v-model="form.jumlah_saudara" label="Jumlah saudara" type="number" />
            <div class="sm:col-span-2">
              <UiTextarea v-model="form.alamat" label="Alamat" :rows="3" />
            </div>
          </div>
        </div>

        <div>
          <p class="mb-3 text-sm font-semibold text-slate-700">Data orang tua / wali</p>
          <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
              <UiInput v-model="form.nama_orangtua" label="Nama orang tua / wali" />
            </div>
            <UiInput v-model="form.nama_ayah" label="Nama ayah" />
            <UiInput v-model="form.pekerjaan_ayah" label="Pekerjaan ayah" />
            <UiInput v-model="form.nama_ibu" label="Nama ibu" />
            <UiInput v-model="form.pekerjaan_ibu" label="Pekerjaan ibu" />
            <div class="sm:col-span-2">
              <UiInput v-model="form.nomor_telepon_ortu" label="Nomor telepon orang tua" placeholder="08xxxxxxxxxx" />
            </div>
          </div>
        </div>
      </form>

      <template #footer>
        <UiButton variant="secondary" @click="formOpen = false">Batal</UiButton>
        <UiButton :loading="saving" @click="submit">Simpan perubahan</UiButton>
      </template>
    </UiModal>
  </DashboardLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'
import UiCard from '@/components/ui/UiCard.vue'
import UiButton from '@/components/ui/UiButton.vue'
import UiInput from '@/components/ui/UiInput.vue'
import UiSelect from '@/components/ui/UiSelect.vue'
import UiTextarea from '@/components/ui/UiTextarea.vue'
import UiModal from '@/components/ui/UiModal.vue'
import UiBadge from '@/components/ui/UiBadge.vue'
import UiAlert from '@/components/ui/UiAlert.vue'
import { muridProfileApi } from '@/services/muridProfileApi'

const navigation = [
  { label: 'Dashboard', icon: 'fas fa-chart-pie', to: '/dashboard/murid' },
  { label: 'Profil', icon: 'fas fa-user', to: '/dashboard/murid/profil' },
  { label: 'Tugas', icon: 'fas fa-book-open', to: '/dashboard/murid/tugas' },
  { label: 'Nilai', icon: 'fas fa-chart-bar', to: '/dashboard/murid/nilai' },
  { label: 'Jadwal', icon: 'fas fa-calendar', to: '/dashboard/murid/jadwal' },
  { label: 'Ujian', icon: 'fas fa-laptop', to: '/dashboard/murid/ujian' },
  { label: 'Administrasi', icon: 'fas fa-folder', to: '/dashboard/murid/administrasi' },
  { label: 'Keuangan', icon: 'fas fa-file-invoice', to: '/dashboard/murid/keuangan' },
]

const genderOptions = [
  { value: 'L', label: 'Laki-laki' },
  { value: 'P', label: 'Perempuan' },
]
const agamaOptions = [
  { value: 'Islam', label: 'Islam' },
  { value: 'Kristen', label: 'Kristen' },
  { value: 'Katolik', label: 'Katolik' },
  { value: 'Hindu', label: 'Hindu' },
  { value: 'Buddha', label: 'Buddha' },
  { value: 'Konghucu', label: 'Konghucu' },
]

const profil = ref(null)
const loading = ref(false)
const error = ref('')

const fetchProfil = async () => {
  loading.value = true
  error.value = ''
  try {
    const res = await muridProfileApi.get()
    profil.value = res.data ?? res
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Profil gagal dimuat. Periksa koneksi ke server.'
  } finally {
    loading.value = false
  }
}

const inisial = computed(() =>
  (profil.value?.nama_lengkap_murid ?? '')
    .split(' ')
    .map((w) => w[0])
    .slice(0, 2)
    .join('')
    .toUpperCase(),
)

const kelasLabel = computed(() => profil.value?.kelas?.name ?? profil.value?.kelas_nama ?? '—')

const ttlLabel = computed(() => {
  if (!profil.value) return '—'
  const tempat = profil.value.tempat_lahir
  const tanggal = profil.value.tanggal_lahir
    ? new Date(profil.value.tanggal_lahir).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
    : null
  if (tempat && tanggal) return `${tempat}, ${tanggal}`
  return tempat || tanggal || '—'
})

/* ---------- form ubah profil ---------- */
const formOpen = ref(false)
const saving = ref(false)
const form = reactive({
  nama_lengkap_murid: '', gender: '', agama: '', tempat_lahir: '', tanggal_lahir: '',
  nomor_telepon: '', hobi: '', cita_cita: '', anak_ke: '', jumlah_saudara: '', alamat: '',
  nama_orangtua: '', nama_ayah: '', pekerjaan_ayah: '', nama_ibu: '', pekerjaan_ibu: '', nomor_telepon_ortu: '',
})
const errors = reactive({})

const openEdit = () => {
  Object.keys(errors).forEach((k) => delete errors[k])
  Object.assign(form, {
    ...profil.value,
    tanggal_lahir: profil.value.tanggal_lahir?.slice(0, 10) ?? '',
  })
  formOpen.value = true
}

const validate = () => {
  Object.keys(errors).forEach((k) => delete errors[k])
  if (!form.nama_lengkap_murid.trim()) errors.nama_lengkap_murid = 'Nama lengkap wajib diisi.'
  if (!form.gender) errors.gender = 'Pilih jenis kelamin.'
  return Object.keys(errors).length === 0
}

const submit = async () => {
  if (!validate()) return
  saving.value = true
  try {
    const updated = await muridProfileApi.update(form)
    profil.value = updated.data ?? updated
    notify('success', 'Profil berhasil diperbarui.')
    formOpen.value = false
  } catch (e) {
    notify('danger', e.response?.data?.message ?? 'Profil gagal disimpan. Coba lagi.')
  } finally {
    saving.value = false
  }
}

/* ---------- notifikasi ---------- */
const alert = reactive({ show: false, type: 'success', message: '' })
let alertTimer
const notify = (type, message) => {
  Object.assign(alert, { show: true, type, message })
  clearTimeout(alertTimer)
  alertTimer = setTimeout(() => (alert.show = false), 4000)
}

onMounted(fetchProfil)
</script>
