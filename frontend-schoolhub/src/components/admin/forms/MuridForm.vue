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

      <!-- Data Murid -->
      <div class="form-section">
        <h4 class="section-title">Data Siswa</h4>

        <FormInput
          v-model="localData.nis"
          label="NIS"
          type="text"
          icon="id-card"
          placeholder="Nomor Induk Siswa"
          :error="errors.nis"
          hint="Masukkan NIS yang unik"
          required
        />

        <FormInput
          v-model="localData.nama_lengkap_murid"
          label="Nama Lengkap"
          type="text"
          icon="user"
          placeholder="Nama lengkap siswa"
          :error="errors.nama_lengkap_murid"
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
          v-model="localData.kelas_id"
          label="Kelas"
          type="select"
          :options="kelasOptions"
          :error="errors.kelas_id"
          hint="Pilih kelas untuk siswa"
          required
        />

        <FormInput
          v-model="localData.tanggal_lahir"
          label="Tanggal Lahir"
          type="date"
          :error="errors.tanggal_lahir"
          required
        />

        <FormInput
          v-model="localData.tempat_lahir"
          label="Tempat Lahir"
          type="text"
          icon="map-marker-alt"
          placeholder="Kota tempat lahir"
          :error="errors.tempat_lahir"
        />

        <FormInput
          v-model="localData.alamat"
          label="Alamat Lengkap"
          type="textarea"
          placeholder="Alamat lengkap siswa"
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
          hint="Nomor telepon siswa atau orang tua"
          required
        />
      </div>

      <!-- Data Orang Tua/Wali -->
      <div class="form-section">
        <h4 class="section-title">Data Orang Tua/Wali</h4>

        <FormInput
          v-model="localData.nama_ayah"
          label="Nama Ayah"
          type="text"
          icon="user"
          placeholder="Nama lengkap ayah"
          :error="errors.nama_ayah"
        />

        <FormInput
          v-model="localData.nama_ibu"
          label="Nama Ibu"
          type="text"
          icon="user"
          placeholder="Nama lengkap ibu"
          :error="errors.nama_ibu"
        />

        <FormInput
          v-model="localData.pekerjaan_ayah"
          label="Pekerjaan Ayah"
          type="text"
          icon="briefcase"
          placeholder="Pekerjaan ayah"
          :error="errors.pekerjaan_ayah"
        />

        <FormInput
          v-model="localData.pekerjaan_ibu"
          label="Pekerjaan Ibu"
          type="text"
          icon="briefcase"
          placeholder="Pekerjaan ibu"
          :error="errors.pekerjaan_ibu"
        />

        <FormInput
          v-model="localData.nomor_telepon_ortu"
          label="Nomor Telepon Orang Tua"
          type="text"
          icon="phone"
          placeholder="08xxxxxxxxxx"
          :error="errors.nomor_telepon_ortu"
          hint="Nomor yang bisa dihubungi"
        />
      </div>

      <!-- Informasi Tambahan -->
      <div class="form-section">
        <h4 class="section-title">Informasi Tambahan</h4>

        <FormInput
          v-model="localData.agama"
          label="Agama"
          type="select"
          :options="agamaOptions"
          :error="errors.agama"
        />

        <FormInput
          v-model="localData.anak_ke"
          label="Anak Ke-"
          type="number"
          placeholder="Contoh: 1"
          :error="errors.anak_ke"
          hint="Urutan anak dalam keluarga"
        />

        <FormInput
          v-model="localData.jumlah_saudara"
          label="Jumlah Saudara"
          type="number"
          placeholder="Contoh: 2"
          :error="errors.jumlah_saudara"
        />

        <FormInput
          v-model="localData.hobi"
          label="Hobi"
          type="text"
          icon="heart"
          placeholder="Hobi siswa"
          :error="errors.hobi"
        />

        <FormInput
          v-model="localData.cita_cita"
          label="Cita-cita"
          type="text"
          icon="star"
          placeholder="Cita-cita siswa"
          :error="errors.cita_cita"
        />
      </div>
    </div>
  </form>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { FormInput } from '@/components/ui'
import api from '@/utils/api'

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
const kelasOptions = ref([])

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

// Fetch kelas options
const fetchKelas = async () => {
  try {
    const response = await api.get('/kelas')
    kelasOptions.value = response.data.data.map((k) => ({
      value: k.id,
      label: `${k.kelas} ${k.nama_kelas} - ${k.jurusan}`,
    }))
  } catch (error) {
    console.error('Error fetching kelas:', error)
    // Fallback jika error
    kelasOptions.value = []
  }
}

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

onMounted(() => {
  fetchKelas()
})
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
