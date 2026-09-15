<template>
  <form @submit.prevent="$emit('submit')">
    <div class="form-grid">
      <!-- Data Akun -->
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

      <!-- Data Karyawan -->
      <div class="form-section">
        <h4 class="section-title">Data Karyawan</h4>

        <FormInput
          v-model="localData.nip"
          label="NIP"
          type="text"
          icon="id-card"
          placeholder="Nomor Induk Pegawai"
          :error="errors.nip"
          hint="Masukkan NIP yang unik"
          required
        />

        <FormInput
          v-model="localData.nama_lengkap_karyawan"
          label="Nama Lengkap"
          type="text"
          icon="user"
          placeholder="Nama lengkap karyawan"
          :error="errors.nama_lengkap_karyawan"
          required
        />

        <FormInput
          v-model="localData.bagian"
          label="Bagian/Departemen"
          type="select"
          :options="bagianOptions"
          :error="errors.bagian"
          hint="Pilih bagian/departemen karyawan"
          required
        />

        <FormInput
          v-model="localData.nomor_telepon"
          label="Nomor Telepon"
          type="text"
          icon="phone"
          placeholder="08xxxxxxxxxx"
          :error="errors.nomor_telepon"
          hint="Nomor telepon yang bisa dihubungi"
          required
        />

        <FormInput
          v-model="localData.alamat"
          label="Alamat Lengkap"
          type="textarea"
          placeholder="Alamat lengkap karyawan"
          :rows="3"
          :error="errors.alamat"
          required
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

const bagianOptions = [
  { value: 'Tata Usaha', label: 'Tata Usaha' },
  { value: 'Administrasi', label: 'Administrasi' },
  { value: 'Keuangan', label: 'Keuangan' },
  { value: 'IT', label: 'IT / Teknologi Informasi' },
  { value: 'Kebersihan', label: 'Kebersihan' },
  { value: 'Keamanan', label: 'Keamanan' },
  { value: 'Perpustakaan', label: 'Perpustakaan' },
  { value: 'Laboratorium', label: 'Laboratorium' },
  { value: 'BK', label: 'Bimbingan Konseling' },
  { value: 'Humas', label: 'Hubungan Masyarakat' },
  { value: 'Lainnya', label: 'Lainnya' },
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
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
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
