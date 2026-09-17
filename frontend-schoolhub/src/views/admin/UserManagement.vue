<template>
  <DashboardLayout title="Kelola User" role-label="Admin" :navigation="navigation">
    <UiAlert v-if="error" type="danger" class="mb-4" :dismissible="false">{{ error }}</UiAlert>

    <UiCard title="User" subtitle="Kelola akun dan role pengguna." :padded="false">
      <template #actions>
        <UiButton @click="open()">Tambah user</UiButton>
      </template>

      <div class="border-y border-slate-100 px-6 py-4">
        <div class="flex flex-col gap-3 md:flex-row md:items-end">
          <UiInput v-model="search" class="max-w-md" placeholder="Cari nama, email, atau role..." />
          <UiSelect v-model="roleFilter" label="Role" :options="roleOptions" class="max-w-[200px]" />
          <UiSelect v-model="statusFilter" label="Status" :options="statusOptions" class="max-w-[180px]" />
        </div>
      </div>

      <UiTable :columns="cols" :rows="filteredItems" :loading="loading">
        <template #empty>
          <p class="font-medium text-slate-600">{{ search || roleFilter !== 'all' || statusFilter !== 'all' ? 'User tidak ditemukan' : 'Belum ada user' }}</p>
        </template>
        <template #row="{ row }">
          <td class="px-6 py-4 font-medium">{{ row.name }}</td>
          <td class="px-6 py-4">{{ row.email }}</td>
          <td class="px-6 py-4"><UiBadge variant="neutral">{{ row.role }}</UiBadge></td>
          <td class="px-6 py-4"><UiBadge :variant="row.is_active ? 'success' : 'danger'">{{ row.is_active ? 'Aktif' : 'Nonaktif' }}</UiBadge></td>
          <td class="px-6 py-4 text-right">
            <UiButton size="sm" variant="ghost" @click="open(row)">Edit</UiButton>
          </td>
        </template>
      </UiTable>
    </UiCard>

    <UiModal v-model="show" :title="edit ? 'Edit user' : 'Tambah user'">
      <form id="user-form" class="grid gap-4 sm:grid-cols-2" @submit.prevent="save">
        <UiInput v-model="form.name" label="Nama" required :error="err('name')" />
        <UiInput v-model="form.email" label="Email" type="email" required :error="err('email')" />
        <UiInput v-model="form.password" class="sm:col-span-2" label="Password" type="password" :required="!edit" hint="Minimal 8 karakter; kosongkan saat edit bila tidak diubah." :error="err('password')" />
        <UiSelect v-model="form.role" label="Role" :options="roles" required :error="err('role')" />
        <UiSelect v-model="form.is_active" label="Status" :options="states" />
        <UiInput v-if="form.role === 'Guru'" v-model="form.nip" label="NIP" required :error="err('nip')" />
        <UiInput v-if="form.role === 'Murid'" v-model="form.nis" label="NIS" required :error="err('nis')" />
        <UiSelect v-if="form.role === 'Murid'" v-model="form.gender" label="Jenis kelamin" :options="gender" :error="err('gender')" />
        <UiInput v-if="form.role === 'Murid'" v-model="form.tanggal_lahir" label="Tanggal lahir" type="date" :error="err('tanggal_lahir')" />
        <UiInput v-if="form.role === 'Karyawan'" v-model="form.bagian" label="Bagian" required :error="err('bagian')" />
      </form>
      <template #footer>
        <UiButton variant="secondary" @click="show = false">Batal</UiButton>
        <UiButton type="submit" form="user-form" :loading="saving">Simpan</UiButton>
      </template>
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
import { adminUserApi as api } from '@/services/adminUserApi'

const navigation = adminNavigation
const cols = [
  { key: 'name', label: 'Nama' },
  { key: 'email', label: 'Email' },
  { key: 'role', label: 'Role' },
  { key: 'status', label: 'Status' },
  { key: 'action', label: '', class: 'text-right' },
]
const roles = ['Admin', 'Guru', 'Murid', 'Karyawan'].map((value) => ({ value, label: value }))
const roleOptions = [{ value: 'all', label: 'Semua role' }, ...roles]
const states = [{ value: 'true', label: 'Aktif' }, { value: 'false', label: 'Nonaktif' }]
const statusOptions = [{ value: 'all', label: 'Semua status' }, ...states]
const gender = [{ value: 'L', label: 'Laki-laki' }, { value: 'P', label: 'Perempuan' }]

const items = ref([])
const loading = ref(true)
const error = ref('')
const show = ref(false)
const edit = ref(false)
const saving = ref(false)
const current = ref()
const errors = ref({})
const form = reactive({})
const search = ref('')
const roleFilter = ref('all')
const statusFilter = ref('all')

const filteredItems = computed(() => {
  const query = search.value.trim().toLowerCase()

  return items.value.filter((user) => {
    const matchesQuery =
      !query ||
      [user.name, user.email, user.role].some((value) =>
        String(value ?? '').toLowerCase().includes(query),
      )
    const matchesRole = roleFilter.value === 'all' || user.role === roleFilter.value
    const matchesStatus =
      statusFilter.value === 'all' || String(user.is_active) === statusFilter.value

    return matchesQuery && matchesRole && matchesStatus
  })
})

const err = (key) => errors.value[key]?.[0] ?? ''

async function load() {
  loading.value = true
  try {
    items.value = (await api.list()).data ?? []
  } catch (e) {
    error.value = e.response?.data?.message ?? 'User gagal dimuat.'
  } finally {
    loading.value = false
  }
}

function open(u) {
  edit.value = !!u
  current.value = u
  Object.assign(form, {
    name: u?.name ?? '',
    email: u?.email ?? '',
    password: '',
    role: u?.role ?? 'Admin',
    is_active: String(u?.is_active ?? true),
    nip: '',
    nis: '',
    gender: '',
    tanggal_lahir: '',
    bagian: '',
  })
  errors.value = {}
  show.value = true
}

async function save() {
  saving.value = true
  errors.value = {}

  const payload = { ...form, is_active: form.is_active === 'true' }
  if (!payload.password) delete payload.password

  try {
    const response = edit.value
      ? await api.update(current.value.id, payload)
      : await api.create(payload)

    const nextUser = response.data
    if (edit.value) {
      items.value = items.value.map((user) => (user.id === nextUser.id ? nextUser : user))
    } else {
      items.value.unshift(nextUser)
    }

    show.value = false
    error.value = ''
  } catch (e) {
    errors.value = e.response?.data?.errors ?? {}
    error.value = e.response?.data?.message ?? 'User gagal disimpan.'
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>
