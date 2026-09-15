<template>
  <form @submit.prevent="$emit('submit')">
    <div class="form-grid">
      <!-- Data User -->
      <div class="form-section">
        <h4 class="section-title">Data Akun</h4>

        <FormInput
          v-model="localData.email"
          label="Email"
          type="email"
          icon="envelope"
          placeholder="contoh@email.com"
          :error="errors.email"
          required
        />

        <FormInput
          v-if="!isEdit"
          v-model="localData.password"
          label="Password"
          type="password"
          icon="lock"
          placeholder="Minimal 8 karakter"
          :error="errors.password"
          :hint="isEdit ? 'Kosongkan jika tidak ingin mengubah password' : undefined"
          :required="!isEdit"
        />
      </div>

      <!-- Data Guru -->
      <div class="form-section">
        <h4 class="section-title">Data Guru</h4>

        <FormInput
          v-model="localData.nip"
          label="NIP"
          type="text"
          icon="id-card"
          placeholder="Nomor Induk Pegawai"
          :error="errors.nip"
          required
        />

        <FormInput
          v-model="localData.nama_lengkap_guru"
          label="Nama Lengkap"
          type="text"
          icon="user"
          placeholder="Nama lengkap guru"
          :error="errors.nama_lengkap_guru"
          required
        />

        <FormInput
          v-model="localData.gender"
          label="Jenis Kelamin"
          type="select"
          :options="genderOptions"
          :error="errors.gender"
          required
        />

        <FormInput
          v-model="localData.alamat"
          label="Alamat"
          type="textarea"
          placeholder="Alamat lengkap"
          :rows="3"
          :error="errors.alamat"
          required
        />

        <FormInput
          v-model="localData.nomor_telepon"
          label="Nomor Telepon"
          type="text"
          icon="phone"
          placeholder="08xxxxxxxxxx"
          :error="errors.nomor_telepon"
          required
        />

        <FormInput
          v-model="localData.tanggal_lahir"
          label="Tanggal Lahir"
          type="date"
          :error="errors.tanggal_lahir"
        />

        <FormInput
          v-model="localData.pendidikan_terakhir"
          label="Pendidikan Terakhir"
          type="select"
          :options="pendidikanOptions"
          :error="errors.pendidikan_terakhir"
        />

        <FormInput
          v-model="localData.mata_pelajaran"
          label="Mata Pelajaran"
          type="text"
          icon="book"
          placeholder="Contoh: Matematika, Bahasa Indonesia"
          :error="errors.mata_pelajaran"
        />
      </div>
    </div>
  </form>
</template>

<script setup>
import { ref, watch } from 'vue'
import { FormInput } from '@/components/ui'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({}),
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
  isEdit: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue', 'submit'])

const localData = ref({ ...props.modelValue })

const genderOptions = [
  { value: 'L', label: 'Laki-laki' },
  { value: 'P', label: 'Perempuan' },
]

const pendidikanOptions = [
  { value: 'S3', label: 'S3 (Doktor)' },
  { value: 'S2', label: 'S2 (Magister)' },
  { value: 'S1', label: 'S1 (Sarjana)' },
  { value: 'D4', label: 'D4 (Sarjana Terapan)' },
  { value: 'D3', label: 'D3 (Ahli Madya)' },
]

// Watch for changes and emit to parent
watch(
  localData,
  (newValue) => {
    emit('update:modelValue', newValue)
  },
  { deep: true },
)

// Watch for external changes
watch(
  () => props.modelValue,
  (newValue) => {
    localData.value = { ...newValue }
  },
  { deep: true },
)
</script>

<style scoped>
.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
}

.form-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #374151;
  margin: 0 0 8px 0;
  padding-bottom: 8px;
  border-bottom: 2px solid #e5e7eb;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
