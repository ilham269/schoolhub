<template>
  <DashboardLayout
    title="Tugas"
    role-label="Guru"
    :navigation="navigation"
  >
    <!-- =========================================================
         ALERT / NOTIFICATION
    ========================================================== -->
    <UiAlert
      v-if="alert.show"
      :type="alert.type"
      class="mb-4"
      @close="alert.show = false"
    >
      {{ alert.message }}
    </UiAlert>

    <UiAlert
      v-if="error"
      type="danger"
      title="Gagal memuat data"
      class="mb-4"
      :dismissible="false"
    >
      {{ error }}
    </UiAlert>

    <!-- =========================================================
         RINGKASAN
    ========================================================== -->
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <!-- Total tugas -->
      <UiCard>
        <div class="space-y-1">
          <p class="text-sm text-slate-400">
            Total tugas
          </p>

          <p class="text-3xl font-semibold text-slate-800">
            {{ items.length }}
          </p>

          <p class="text-xs text-slate-400">
            Semua kelas yang diampu
          </p>
        </div>
      </UiCard>

      <!-- Tugas aktif -->
      <UiCard>
        <div class="space-y-1">
          <p class="text-sm text-slate-400">
            Tugas aktif
          </p>

          <p class="text-3xl font-semibold text-slate-800">
            {{ aktifCount }}
          </p>

          <p class="text-xs text-slate-400">
            Masih bisa dikumpulkan siswa
          </p>
        </div>
      </UiCard>

      <!-- Mendekati tenggat -->
      <UiCard>
        <div class="space-y-1">
          <p class="text-sm text-slate-400">
            Mendekati tenggat
          </p>

          <p class="text-3xl font-semibold text-slate-800">
            {{ segeraCount }}
          </p>

          <p class="text-xs text-slate-400">
            Deadline dalam 3 hari
          </p>
        </div>
      </UiCard>

      <!-- Progress penilaian -->
      <UiCard>
        <div>
          <p class="text-sm text-slate-400">
            Tugas dinilai
          </p>

          <p class="mt-1 mb-3 text-3xl font-semibold text-slate-800">
            {{ Math.round(rataProgress) }}%
          </p>

          <UiProgress :value="rataProgress" />
        </div>
      </UiCard>
    </div>

    <!-- =========================================================
         DAFTAR TUGAS
    ========================================================== -->
    <UiCard
      title="Daftar Tugas"
      subtitle="Kelola tugas untuk setiap kelas yang Anda ampu"
      :padded="false"
    >
      <!-- Header actions -->
      <template #actions>
        <div class="flex items-center gap-2">
          <UiDropdown>
            <template #trigger>
              <UiButton variant="secondary">
                Aksi lain

                <svg
                  class="h-3.5 w-3.5"
                  viewBox="0 0 20 20"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.6"
                  stroke-linecap="round"
                >
                  <path d="M6 8l4 4 4-4" />
                </svg>
              </UiButton>
            </template>

            <UiDropdownItem @click="fetchAll">
              Muat ulang data
            </UiDropdownItem>
          </UiDropdown>

          <UiButton @click="openCreate">
            <svg
              class="h-4 w-4"
              viewBox="0 0 20 20"
              fill="none"
              stroke="currentColor"
              stroke-width="1.8"
              stroke-linecap="round"
            >
              <path d="M10 5v10M5 10h10" />
            </svg>

            Buat tugas
          </UiButton>
        </div>
      </template>

      <!-- =======================================================
           FILTER
      ======================================================== -->
      <div class="space-y-4 px-6 pb-5">
        <!-- Tabs -->
        <UiTabs
          v-model="activeTab"
          :tabs="tabs"
        />

        <!-- Search + Filter -->
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
          <!-- Search -->
          <div class="sm:max-w-xs sm:flex-1">
            <UiInput
              v-model="search"
              placeholder="Cari judul tugas..."
            >
              <template #icon>
                <svg
                  class="h-4 w-4"
                  viewBox="0 0 20 20"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.6"
                >
                  <circle
                    cx="9"
                    cy="9"
                    r="5"
                  />

                  <path
                    d="m13 13 4 4"
                    stroke-linecap="round"
                  />
                </svg>
              </template>
            </UiInput>
          </div>

          <!-- Filter jurusan -->
          <div class="sm:w-56">
            <UiSelect
              v-model="jurusanFilter"
              :options="jurusanOptions"
              placeholder="Semua jurusan"
            />
          </div>

          <!-- Filter kelas (mengikuti jurusan yang dipilih) -->
          <div class="sm:w-52">
            <UiSelect
              v-model="kelasFilter"
              :options="kelasOptionsFiltered"
              placeholder="Semua kelas"
            />
          </div>

          <!-- Filter mapel -->
          <div class="sm:w-52">
            <UiSelect
              v-model="mapelFilter"
              :options="mapelOptions"
              placeholder="Semua mapel"
            />
          </div>

          <!-- Jumlah data per halaman (5-25) -->
          <div class="sm:w-44">
            <UiSelect
              v-model.number="perPage"
              :options="perPageOptions"
              placeholder="Tampilkan"
            />
          </div>
        </div>

        <!-- Toggle tampilan: Tabel / Kelompok -->
        <div class="flex flex-wrap items-center gap-2">
          <span class="text-xs font-medium text-slate-500">
            Tampilan:
          </span>

          <UiButton
            size="sm"
            :variant="viewMode === 'tabel' ? 'primary' : 'secondary'"
            @click="viewMode = 'tabel'"
          >
            Tabel
          </UiButton>

          <UiButton
            size="sm"
            :variant="viewMode === 'kelompok' ? 'primary' : 'secondary'"
            @click="viewMode = 'kelompok'"
          >
            Kelompok
          </UiButton>

          <template v-if="viewMode === 'kelompok'">
            <span class="ml-2 text-xs font-medium text-slate-500">
              Kelompokkan per:
            </span>

            <UiSelect
              v-model="groupBy"
              class="w-40"
              :options="groupByOptions"
            />
          </template>
        </div>
      </div>

      <!-- =======================================================
           TABLE VIEW
      ======================================================== -->
      <UiTable
        v-if="viewMode === 'tabel'"
        :columns="columns"
        :rows="paged"
        :sort-key="sortKey"
        :sort-dir="sortDir"
        :loading="loading"
        @sort="toggleSort"
      >
        <!-- Empty state -->
        <template #empty>
          <div class="py-6 text-center">
            <p class="font-medium text-slate-600">
              Belum ada tugas yang cocok
            </p>

            <p class="mt-1 text-sm text-slate-400">
              Ubah kata kunci atau filter, atau buat tugas baru.
            </p>

            <UiButton
              class="mt-4"
              variant="soft"
              size="sm"
              @click="openCreate"
            >
              Buat tugas
            </UiButton>
          </div>
        </template>

        <!-- Table row -->
        <template #row="{ row }">
          <!-- Tugas -->
          <td class="max-w-xs px-6 py-4">
            <p class="truncate font-medium text-slate-800">
              {{ row.judul }}
            </p>

            <p class="text-xs text-slate-400">
              {{ mapelName(row.mapel_id) }}
            </p>
          </td>

          <!-- Jurusan -->
          <td class="px-6 py-4">
            <UiBadge variant="neutral">
              {{ jurusanName(getJurusanIdFromKelas(row.kelas_id)) }}
            </UiBadge>
          </td>

          <!-- Kelas -->
          <td class="px-6 py-4 text-slate-600">
            {{ kelasName(row.kelas_id) }}
          </td>

          <!-- Deadline -->
          <td class="px-6 py-4">
            <p class="text-slate-700">
              {{ formatTanggal(row.deadline) }}
            </p>

            <UiBadge
              :variant="statusTone(row)"
              class="mt-1"
            >
              {{ statusLabel(row) }}
            </UiBadge>
          </td>

          <!-- Pengumpulan -->
          <td class="px-6 py-4">
            <div class="w-32">
              <p class="mb-1.5 text-xs text-slate-500">
                {{ row.jumlah_pengumpulan ?? 0 }}
                pengumpulan
              </p>

              <UiProgress
                :value="row.jumlah_pengumpulan ?? 0"
                :max="40"
              />
            </div>
          </td>

          <!-- Nilai -->
          <td class="px-6 py-4 text-slate-600">
            {{ row.nilai_maksimal }}
          </td>

          <!-- Status aktif -->
          <td class="px-6 py-4">
            <UiButton
              variant="ghost"
              size="sm"
              :title="row.is_active ? 'Nonaktifkan tugas' : 'Aktifkan tugas'"
              @click="toggleTaskActive(row)"
            >
              <UiBadge
                :variant="row.is_active ? 'success' : 'neutral'"
              >
                {{ row.is_active ? 'Aktif' : 'Nonaktif' }}
              </UiBadge>
            </UiButton>
          </td>

          <!-- Actions -->
          <td class="px-6 py-4">
            <div class="flex items-center justify-end gap-1">
              <!-- Detail -->
              <UiButton
                variant="ghost"
                size="icon"
                title="Lihat detail"
                @click="openDetail(row)"
              >
                <svg
                  class="h-4 w-4"
                  viewBox="0 0 20 20"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.5"
                >
                  <path
                    d="M2 10s3-5 8-5 8 5 8 5-3 5-8 5-8-5-8-5Z"
                  />

                  <circle
                    cx="10"
                    cy="10"
                    r="2"
                  />
                </svg>
              </UiButton>

              <!-- Edit -->
              <UiButton
                variant="ghost"
                size="icon"
                title="Ubah tugas"
                @click="openEdit(row)"
              >
                <svg
                  class="h-4 w-4"
                  viewBox="0 0 20 20"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.5"
                  stroke-linejoin="round"
                >
                  <path d="M14 3l3 3-9 9H5v-3z" />
                </svg>
              </UiButton>

              <!-- Delete -->
              <UiButton
                variant="ghost"
                size="icon"
                title="Hapus tugas"
                class="text-rose-500 hover:bg-rose-50 hover:text-rose-600"
                @click="askDelete(row)"
              >
                <svg
                  class="h-4 w-4"
                  viewBox="0 0 20 20"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.5"
                  stroke-linecap="round"
                >
                  <path d="M4 6h12M8 6V4h4v2M6 6l1 10h6l1-10" />
                </svg>
              </UiButton>
            </div>
          </td>
        </template>
      </UiTable>

      <!-- =======================================================
           GROUPED VIEW (per Kelas / per Jurusan)
      ======================================================== -->
      <div
        v-else
        class="space-y-4 px-6 pb-6"
      >
        <div
          v-if="loading"
          class="py-10 text-center text-sm text-slate-400"
        >
          Memuat data tugas...
        </div>

        <div
          v-else-if="!groupedSections.length"
          class="py-10 text-center"
        >
          <p class="font-medium text-slate-600">
            Belum ada tugas yang cocok
          </p>

          <p class="mt-1 text-sm text-slate-400">
            Ubah kata kunci atau filter, atau buat tugas baru.
          </p>

          <UiButton
            class="mt-4"
            variant="soft"
            size="sm"
            @click="openCreate"
          >
            Buat tugas
          </UiButton>
        </div>

        <div
          v-for="group in groupedSections"
          :key="group.key"
          class="overflow-hidden rounded-xl border border-slate-200"
        >
          <div class="flex items-center justify-between bg-slate-50 px-4 py-2.5">
            <p class="text-sm font-semibold text-slate-700">
              {{ group.label }}
            </p>

            <UiBadge variant="neutral">
              {{ group.rows.length }} tugas
            </UiBadge>
          </div>

          <div class="divide-y divide-slate-100">
            <div
              v-for="row in group.rows"
              :key="row.id"
              class="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
            >
              <div class="min-w-0">
                <p class="truncate font-medium text-slate-800">
                  {{ row.judul }}
                </p>

                <p class="text-xs text-slate-400">
                  {{ kelasName(row.kelas_id) }}
                  &middot;
                  {{ mapelName(row.mapel_id) }}
                  &middot;
                  {{ formatTanggal(row.deadline) }}
                </p>
              </div>

              <div class="flex items-center gap-2">
                <UiBadge :variant="statusTone(row)">
                  {{ statusLabel(row) }}
                </UiBadge>

                <UiButton
                  variant="ghost"
                  size="icon"
                  title="Lihat detail"
                  @click="openDetail(row)"
                >
                  <svg
                    class="h-4 w-4"
                    viewBox="0 0 20 20"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                  >
                    <path
                      d="M2 10s3-5 8-5 8 5 8 5-3 5-8 5-8-5-8-5Z"
                    />

                    <circle
                      cx="10"
                      cy="10"
                      r="2"
                    />
                  </svg>
                </UiButton>

                <UiButton
                  variant="ghost"
                  size="icon"
                  title="Ubah tugas"
                  @click="openEdit(row)"
                >
                  <svg
                    class="h-4 w-4"
                    viewBox="0 0 20 20"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linejoin="round"
                  >
                    <path d="M14 3l3 3-9 9H5v-3z" />
                  </svg>
                </UiButton>

                <UiButton
                  variant="ghost"
                  size="icon"
                  title="Hapus tugas"
                  class="text-rose-500 hover:bg-rose-50 hover:text-rose-600"
                  @click="askDelete(row)"
                >
                  <svg
                    class="h-4 w-4"
                    viewBox="0 0 20 20"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linecap="round"
                  >
                    <path d="M4 6h12M8 6V4h4v2M6 6l1 10h6l1-10" />
                  </svg>
                </UiButton>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <UiPagination
        v-model:page="page"
        :per-page="perPage"
        :total="sorted.length"
      />
    </UiCard>

    <!-- =========================================================
         MODAL CREATE / EDIT
    ========================================================== -->
    <UiModal
      v-model="formOpen"
      size="lg"
      :title="editingId ? 'Ubah tugas' : 'Buat tugas'"
      subtitle="Lengkapi detail tugas untuk kelas yang dituju."
    >
      <form
        class="grid gap-4 sm:grid-cols-2"
        @submit.prevent="submit"
      >
        <!-- Kelas -->
        <UiSelect
          v-model="form.kelas_id"
          label="Kelas"
          :options="kelasOptions"
          :error="errors.kelas_id"
          required
          placeholder="Pilih kelas"
        />

        <!-- Jurusan (otomatis mengikuti kelas) -->
        <UiInput
          :model-value="jurusanName(getJurusanIdFromKelas(form.kelas_id))"
          label="Jurusan"
          disabled
          placeholder="Otomatis dari kelas"
        />

        <!-- Mata pelajaran -->
        <UiSelect
          v-model="form.mapel_id"
          label="Mata pelajaran"
          :options="mapelOptions"
          :error="errors.mapel_id"
          required
          placeholder="Pilih mapel"
        />

        <!-- Materi -->
        <div class="sm:col-span-2">
          <UiSelect
            v-model="form.materi_id"
            label="Materi terkait"
            :options="materiOptionsForMapel"
            placeholder="Tanpa materi tertentu"
          />
        </div>

        <!-- Judul -->
        <div class="sm:col-span-2">
          <UiInput
            v-model="form.judul"
            label="Judul tugas"
            placeholder="Contoh: Membuat Halaman Profil HTML"
            :error="errors.judul"
            required
          />
        </div>

        <!-- Deskripsi -->
        <div class="sm:col-span-2">
          <UiTextarea
            v-model="form.deskripsi"
            label="Deskripsi"
            placeholder="Jelaskan tugas secara singkat"
            :rows="3"
            :error="errors.deskripsi"
          />
        </div>

        <!-- Instruksi -->
        <div class="sm:col-span-2">
          <UiTextarea
            v-model="form.instruksi"
            label="Instruksi pengumpulan"
            placeholder="Contoh: kumpulkan dalam format .zip"
            :rows="3"
          />
        </div>

        <!-- Deadline -->
        <UiInput
          v-model="form.deadline"
          label="Tenggat waktu"
          type="datetime-local"
          :error="errors.deadline"
          required
        />

        <!-- Nilai -->
        <UiInput
          v-model="form.nilai_maksimal"
          label="Nilai maksimal"
          type="number"
          :error="errors.nilai_maksimal"
        />

        <!-- File -->
        <div class="sm:col-span-2">
          <span class="mb-1.5 block text-sm font-medium text-slate-700">
            Lampiran (opsional)
          </span>

          <input
            type="file"
            class="block w-full text-sm text-slate-600
                   file:mr-3 file:rounded-lg file:border-0
                   file:bg-emerald-50 file:px-3.5 file:py-2
                   file:text-sm file:font-medium file:text-emerald-700
                   hover:file:bg-emerald-100"
            @change="handleFileChange"
          />

          <p
            v-if="editingId && form.file_path && !form.file"
            class="mt-1.5 text-xs text-slate-400"
          >
            File saat ini:
            {{ form.file_path }}
          </p>

          <p
            v-if="form.file"
            class="mt-1.5 text-xs text-slate-500"
          >
            File baru:
            {{ form.file.name }}
          </p>
        </div>

        <!-- Active -->
        <div class="sm:col-span-2">
          <UiSwitch
            v-model="form.is_active"
            label="Tugas aktif"
            hint="Siswa hanya bisa mengumpulkan saat tugas aktif"
          />
        </div>
      </form>

      <!-- Modal footer -->
      <template #footer>
        <UiButton
          variant="secondary"
          @click="formOpen = false"
        >
          Batal
        </UiButton>

        <UiButton
          :loading="saving"
          @click="submit"
        >
          {{ editingId ? 'Simpan perubahan' : 'Simpan tugas' }}
        </UiButton>
      </template>
    </UiModal>

    <!-- =========================================================
         MODAL DETAIL
    ========================================================== -->
    <UiModal
      v-model="detailOpen"
      :title="detail?.judul"
      subtitle="Detail tugas"
    >
      <div
        v-if="detail"
        class="space-y-5"
      >
        <!-- Metadata -->
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <p class="text-slate-400">
              Jurusan
            </p>

            <p class="mt-0.5 font-medium text-slate-800">
              {{ jurusanName(getJurusanIdFromKelas(detail.kelas_id)) }}
            </p>
          </div>

          <div>
            <p class="text-slate-400">
              Kelas
            </p>

            <p class="mt-0.5 font-medium text-slate-800">
              {{ kelasName(detail.kelas_id) }}
            </p>
          </div>

          <div>
            <p class="text-slate-400">
              Mapel
            </p>

            <p class="mt-0.5 font-medium text-slate-800">
              {{ mapelName(detail.mapel_id) }}
            </p>
          </div>

          <div>
            <p class="text-slate-400">
              Tenggat
            </p>

            <p class="mt-0.5 font-medium text-slate-800">
              {{ formatTanggal(detail.deadline) }}
            </p>
          </div>

          <div>
            <p class="text-slate-400">
              Nilai maksimal
            </p>

            <p class="mt-0.5 font-medium text-slate-800">
              {{ detail.nilai_maksimal }}
            </p>
          </div>
        </div>

        <!-- Status -->
        <div>
          <p class="mb-1 text-sm text-slate-400">
            Status
          </p>

          <UiBadge :variant="statusTone(detail)">
            {{ statusLabel(detail) }}
          </UiBadge>
        </div>

        <!-- Deskripsi -->
        <div>
          <p class="text-sm text-slate-400">
            Deskripsi
          </p>

          <p class="mt-1 text-sm leading-6 text-slate-700">
            {{ detail.deskripsi || '—' }}
          </p>
        </div>

        <!-- Instruksi -->
        <div>
          <p class="text-sm text-slate-400">
            Instruksi pengumpulan
          </p>

          <p class="mt-1 text-sm leading-6 text-slate-700">
            {{ detail.instruksi || '—' }}
          </p>
        </div>

        <!-- Pengumpulan -->
        <div>
          <UiProgress
            :value="detail.jumlah_pengumpulan ?? 0"
            :max="40"
            :label="`${detail.jumlah_pengumpulan ?? 0} siswa sudah mengumpulkan`"
          />
        </div>

        <!-- Attachment -->
        <div v-if="detail.file_path">
          <p class="text-sm text-slate-400">
            Lampiran
          </p>

          <p class="mt-1 text-sm text-slate-700">
            {{ detail.file_path }}
          </p>
        </div>
      </div>

      <template #footer>
        <UiButton
          variant="secondary"
          @click="detailOpen = false"
        >
          Tutup
        </UiButton>

        <UiButton @click="editFromDetail">
          Ubah tugas
        </UiButton>
      </template>
    </UiModal>

    <!-- =========================================================
         MODAL DELETE
    ========================================================== -->
    <UiModal
      v-model="deleteOpen"
      size="sm"
      title="Hapus tugas?"
    >
      <div class="space-y-3">
        <p class="text-sm leading-6 text-slate-600">
          Tugas
          <span class="font-semibold text-slate-800">
            {{ deleteTarget?.judul }}
          </span>
          akan dihapus permanen, termasuk riwayat pengumpulan
          yang terkait.
        </p>

        <UiAlert
          type="warning"
          :dismissible="false"
        >
          Pastikan tugas memang sudah tidak diperlukan sebelum
          menghapusnya.
        </UiAlert>
      </div>

      <template #footer>
        <UiButton
          variant="secondary"
          @click="deleteOpen = false"
        >
          Batal
        </UiButton>

        <UiButton
          variant="danger"
          :loading="deleting"
          @click="confirmDelete"
        >
          Hapus tugas
        </UiButton>
      </template>
    </UiModal>
  </DashboardLayout>
</template>

<script setup>
import {
  computed,
  onMounted,
  reactive,
  ref,
  watch,
} from 'vue'
import { useRouter } from 'vue-router'

import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'

// Check auth before anything
const router = useRouter()
onMounted(() => {
  const token = sessionStorage.getItem('token')
  const user = sessionStorage.getItem('user')
  
  if (!token || !user) {
    console.error('❌ No token or user found, redirecting to login...')
    sessionStorage.clear()
    router.push('/login')
    return
  }
  
  // Check if user is guru
  try {
    const userData = JSON.parse(user)
    if (userData.role !== 'guru' && userData.role !== 'Guru') {
      console.error('❌ User is not a guru, redirecting...')
      router.push('/dashboard')
      return
    }
    console.log('✅ Auth OK: User is guru')
  } catch (e) {
    console.error('❌ Failed to parse user data:', e)
    sessionStorage.clear()
    router.push('/login')
  }
})

import UiCard from '@/components/ui/UiCard.vue'
import UiButton from '@/components/ui/UiButton.vue'
import UiInput from '@/components/ui/UiInput.vue'
import UiSelect from '@/components/ui/UiSelect.vue'
import UiTextarea from '@/components/ui/UiTextarea.vue'
import UiSwitch from '@/components/ui/UiSwitch.vue'
import UiModal from '@/components/ui/UiModal.vue'
import UiTable from '@/components/ui/UiTable.vue'
import UiBadge from '@/components/ui/UiBadge.vue'
import UiAlert from '@/components/ui/UiAlert.vue'
import UiTabs from '@/components/ui/UiTabs.vue'
import UiPagination from '@/components/ui/UiPagination.vue'
import UiProgress from '@/components/ui/UiProgress.vue'
import UiDropdown from '@/components/ui/UiDropdown.vue'
import UiDropdownItem from '@/components/ui/UiDropdownItem.vue'

import {
  useTugas,
  KELAS_REF,
  MAPEL_REF,
  MATERI_REF,
} from '@/composables/useTugas'

/* =============================================================
   NAVIGATION
============================================================= */

const navigation = [
  {
    label: 'Dashboard',
    icon: 'fas fa-chart-pie',
    to: '/dashboard/guru',
  },
  {
    label: 'Data Kelas',
    icon: 'fas fa-users',
    to: '/dashboard/guru/kelas',
  },
  {
    label: 'Materi',
    icon: 'fas fa-book-open',
    to: '/dashboard/guru/materi',
  },
  {
    label: 'Tugas',
    icon: 'fas fa-book-open',
    to: '/dashboard/guru/tugas',
  },
  {
    label: 'Ujian PPDB & Soal',
    icon: 'fas fa-file-circle-check',
    to: '/dashboard/guru/ujian-ppdb',
  },
]

/* =============================================================
   COMPOSABLE
   Catatan: fetchAll() tetap mengambil SEMUA data tugas dari
   backend sekaligus (tidak ada server-side pagination). Yang
   dibatasi hanya jumlah baris yang ditampilkan di halaman
   (lihat bagian PAGINATION di bawah).
============================================================= */

const {
  items,
  loading,
  error,
  fetchAll,
  store,
  update,
  destroy,
  toggleActive,
} = useTugas()

/* =============================================================
   REFERENCE DATA - KELAS & MAPEL
============================================================= */

const kelasOptions = KELAS_REF.map((kelas) => ({
  value: kelas.id,
  label: kelas.name,
}))

const mapelOptions = MAPEL_REF.map((mapel) => ({
  value: mapel.id,
  label: mapel.name,
}))

const kelasName = (id) => {
  return (
    KELAS_REF.find(
      (kelas) => kelas.id === Number(id),
    )?.name ?? '—'
  )
}

const mapelName = (id) => {
  return (
    MAPEL_REF.find(
      (mapel) => mapel.id === Number(id),
    )?.name ?? '—'
  )
}

/* =============================================================
   REFERENCE DATA - JURUSAN
   Jurusan disimpulkan dari nama kelas (mis. "X TKR 1" -> TKR).
   Kalau nanti KELAS_REF sudah punya field jurusan_id sendiri
   dari backend, tinggal ganti getJurusanIdFromKelas untuk
   membaca field itu langsung.
============================================================= */

const JURUSAN_REF = [
  {
    id: 'TKR',
    name: 'Teknik Kendaraan Ringan',
    keywords: ['tkr', 'kendaraan ringan'],
  },
  {
    id: 'RPL',
    name: 'Rekayasa Perangkat Lunak',
    keywords: ['rpl', 'perangkat lunak'],
  },
  {
    id: 'TSM',
    name: 'Teknik Sepeda Motor',
    keywords: ['tsm', 'sepeda motor'],
  },
]

const jurusanOptions = JURUSAN_REF.map((jurusan) => ({
  value: jurusan.id,
  label: jurusan.name,
}))

const getJurusanIdFromKelas = (kelasId) => {
  const kelas = KELAS_REF.find(
    (item) => item.id === Number(kelasId),
  )

  if (!kelas) {
    return null
  }

  const namaKelas = String(
    kelas.name || '',
  ).toLowerCase()

  const found = JURUSAN_REF.find((jurusan) =>
    jurusan.keywords.some((keyword) =>
      namaKelas.includes(keyword),
    ),
  )

  return found ? found.id : null
}

const jurusanName = (id) => {
  return (
    JURUSAN_REF.find(
      (jurusan) => jurusan.id === id,
    )?.name ?? 'Lainnya'
  )
}

/* =============================================================
   FORM
============================================================= */

const form = reactive({
  kelas_id: '',
  mapel_id: '',
  materi_id: '',
  judul: '',
  deskripsi: '',
  instruksi: '',
  deadline: '',
  nilai_maksimal: 100,
  is_active: true,
  file: null,
  file_path: null,
})

const materiOptionsForMapel = computed(() => {
  return MATERI_REF
    .filter(
      (materi) =>
        materi.mapel_id === Number(form.mapel_id),
    )
    .map((materi) => ({
      value: materi.id,
      label: materi.name,
    }))
})

/* =============================================================
   STATUS
============================================================= */

const statusInfo = (row) => {
  if (!row?.is_active) {
    return {
      label: 'Nonaktif',
      tone: 'neutral',
    }
  }

  const deadline = new Date(row.deadline)
  const now = new Date()

  const diffHari =
    (deadline - now) /
    (1000 * 60 * 60 * 24)

  if (diffHari < 0) {
    return {
      label: 'Lewat tenggat',
      tone: 'danger',
    }
  }

  if (diffHari <= 3) {
    return {
      label: 'Segera tenggat',
      tone: 'warning',
    }
  }

  return {
    label: 'Berlangsung',
    tone: 'success',
  }
}

const statusLabel = (row) => {
  return statusInfo(row).label
}

const statusTone = (row) => {
  return statusInfo(row).tone
}

const formatTanggal = (value) => {
  if (!value) {
    return '—'
  }

  return new Date(value).toLocaleString(
    'id-ID',
    {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    },
  )
}

/* =============================================================
   FILTER
============================================================= */

const search = ref('')
const jurusanFilter = ref('')
const kelasFilter = ref('')
const mapelFilter = ref('')
const activeTab = ref('semua')

/*
|--------------------------------------------------------------------------
| Opsi kelas mengikuti jurusan yang dipilih (cascading).
| Kalau jurusan dikosongkan, semua kelas ditampilkan lagi.
|--------------------------------------------------------------------------
*/

const kelasOptionsFiltered = computed(() => {
  if (!jurusanFilter.value) {
    return kelasOptions
  }

  return KELAS_REF.filter(
    (kelas) =>
      getJurusanIdFromKelas(kelas.id) ===
      jurusanFilter.value,
  ).map((kelas) => ({
    value: kelas.id,
    label: kelas.name,
  }))
})

/*
|--------------------------------------------------------------------------
| Reset pilihan kelas kalau jurusan diganti, supaya tidak
| "nyangkut" ke kelas yang sudah tidak relevan.
|--------------------------------------------------------------------------
*/

watch(jurusanFilter, () => {
  kelasFilter.value = ''
})

const filtered = computed(() => {
  const keyword = search.value
    .trim()
    .toLowerCase()

  return items.value.filter((task) => {
    const cocokTab =
      activeTab.value === 'semua' ||
      (
        activeTab.value === 'aktif' &&
        task.is_active
      ) ||
      (
        activeTab.value === 'segera' &&
        statusLabel(task) === 'Segera tenggat'
      ) ||
      (
        activeTab.value === 'lewat' &&
        statusLabel(task) === 'Lewat tenggat'
      )

    const cocokJurusan =
      !jurusanFilter.value ||
      getJurusanIdFromKelas(task.kelas_id) ===
        jurusanFilter.value

    const cocokKelas =
      !kelasFilter.value ||
      Number(task.kelas_id) ===
        Number(kelasFilter.value)

    const cocokMapel =
      !mapelFilter.value ||
      Number(task.mapel_id) ===
        Number(mapelFilter.value)

    const cocokCari =
      !keyword ||
      String(task.judul ?? '')
        .toLowerCase()
        .includes(keyword)

    return (
      cocokTab &&
      cocokJurusan &&
      cocokKelas &&
      cocokMapel &&
      cocokCari
    )
  })
})

/* =============================================================
   TABS
============================================================= */

const tabs = computed(() => [
  {
    value: 'semua',
    label: 'Semua tugas',
    count: items.value.length,
  },
  {
    value: 'aktif',
    label: 'Aktif',
    count: items.value.filter(
      (task) => task.is_active,
    ).length,
  },
  {
    value: 'segera',
    label: 'Segera tenggat',
    count: items.value.filter(
      (task) =>
        statusLabel(task) ===
        'Segera tenggat',
    ).length,
  },
  {
    value: 'lewat',
    label: 'Lewat tenggat',
    count: items.value.filter(
      (task) =>
        statusLabel(task) ===
        'Lewat tenggat',
    ).length,
  },
])

/* =============================================================
   SORTING
============================================================= */

const sortKey = ref('deadline')
const sortDir = ref('asc')

const sorted = computed(() => {
  return [...filtered.value].sort(
    (first, second) => {
      const direction =
        sortDir.value === 'asc'
          ? 1
          : -1

      const x =
        first[sortKey.value] ?? ''

      const y =
        second[sortKey.value] ?? ''

      if (
        typeof x === 'number' &&
        typeof y === 'number'
      ) {
        return (
          (x - y) * direction
        )
      }

      return (
        String(x).localeCompare(
          String(y),
          'id',
        ) * direction
      )
    },
  )
})

const toggleSort = (key) => {
  if (sortKey.value === key) {
    sortDir.value =
      sortDir.value === 'asc'
        ? 'desc'
        : 'asc'

    return
  }

  sortKey.value = key
  sortDir.value = 'asc'
}

/* =============================================================
   PAGINATION
   Semua data tugas sudah ada di memori (items/sorted), di sini
   kita cuma slice untuk ditampilkan. perPage dibatasi 5-25 lewat
   perPageOptions di bawah.
============================================================= */

const page = ref(1)
const perPage = ref(10)

const perPageOptions = [5, 10, 15, 20, 25].map(
  (n) => ({
    value: n,
    label: `${n} / halaman`,
  }),
)

const paged = computed(() => {
  const start =
    (page.value - 1) *
    perPage.value

  const end =
    page.value *
    perPage.value

  return sorted.value.slice(
    start,
    end,
  )
})

watch(perPage, (value) => {
  const num = Number(value)

  if (Number.isNaN(num)) {
    perPage.value = 10

    return
  }

  if (num < 5) {
    perPage.value = 5
  } else if (num > 25) {
    perPage.value = 25
  }
})

watch(
  [
    search,
    jurusanFilter,
    kelasFilter,
    mapelFilter,
    activeTab,
    perPage,
  ],
  () => {
    page.value = 1
  },
)

/* =============================================================
   VIEW MODE (Tabel / Kelompok) & GROUPING
============================================================= */

const viewMode = ref('tabel')
const groupBy = ref('jurusan')

const groupByOptions = [
  {
    value: 'jurusan',
    label: 'Jurusan',
  },
  {
    value: 'kelas',
    label: 'Kelas',
  },
]

/*
|--------------------------------------------------------------------------
| Grouping diterapkan pada data yang sedang tampil di halaman
| aktif (paged), supaya tetap konsisten dengan pengaturan
| jumlah data per halaman.
|--------------------------------------------------------------------------
*/

const groupedSections = computed(() => {
  const groups = new Map()

  paged.value.forEach((task) => {
    const jurusanId = getJurusanIdFromKelas(
      task.kelas_id,
    )

    const key =
      groupBy.value === 'jurusan'
        ? jurusanId ?? 'lainnya'
        : String(task.kelas_id)

    const label =
      groupBy.value === 'jurusan'
        ? jurusanName(jurusanId)
        : kelasName(task.kelas_id)

    if (!groups.has(key)) {
      groups.set(key, {
        key,
        label,
        rows: [],
      })
    }

    groups.get(key).rows.push(task)
  })

  return Array.from(groups.values()).sort(
    (a, b) =>
      a.label.localeCompare(b.label, 'id'),
  )
})

/* =============================================================
   TABLE COLUMNS
============================================================= */

const columns = [
  {
    key: 'judul',
    label: 'Tugas',
    sortable: true,
  },
  {
    key: 'jurusan',
    label: 'Jurusan',
  },
  {
    key: 'kelas_id',
    label: 'Kelas',
    sortable: true,
  },
  {
    key: 'deadline',
    label: 'Tenggat',
    sortable: true,
  },
  {
    key: 'pengumpulan',
    label: 'Pengumpulan',
  },
  {
    key: 'nilai_maksimal',
    label: 'Nilai maks.',
    sortable: true,
  },
  {
    key: 'is_active',
    label: 'Status',
  },
  {
    key: 'aksi',
    label: 'Aksi',
    class: 'text-right',
  },
]

/* =============================================================
   SUMMARY
============================================================= */

const aktifCount = computed(() => {
  return items.value.filter(
    (task) => task.is_active,
  ).length
})

const segeraCount = computed(() => {
  return items.value.filter(
    (task) =>
      statusLabel(task) ===
      'Segera tenggat',
  ).length
})

const rataProgress = computed(() => {
  if (!items.value.length) {
    return 0
  }

  const totalPengumpulan =
    items.value.reduce(
      (total, task) =>
        total +
        Math.min(
          task.jumlah_pengumpulan ?? 0,
          40,
        ),
      0,
    )

  return (
    (totalPengumpulan /
      (items.value.length * 40)) *
    100
  )
})

/* =============================================================
   FORM STATE
============================================================= */

const formOpen = ref(false)
const saving = ref(false)
const editingId = ref(null)

const errors = reactive({})

const resetForm = () => {
  Object.assign(form, {
    kelas_id: '',
    mapel_id: '',
    materi_id: '',
    judul: '',
    deskripsi: '',
    instruksi: '',
    deadline: '',
    nilai_maksimal: 100,
    is_active: true,
    file: null,
    file_path: null,
  })

  Object.keys(errors).forEach(
    (key) => {
      delete errors[key]
    },
  )
}

const openCreate = () => {
  editingId.value = null

  resetForm()

  formOpen.value = true
}

const openEdit = (row) => {
  editingId.value = row.id

  resetForm()

  Object.assign(form, {
    ...row,
    deadline: row.deadline
      ? row.deadline.slice(0, 16)
      : '',
    file: null,
  })

  formOpen.value = true
}

const editFromDetail = () => {
  if (!detail.value) {
    return
  }

  const selected = detail.value

  detailOpen.value = false

  openEdit(selected)
}

/* =============================================================
   FILE
============================================================= */

const handleFileChange = (event) => {
  form.file =
    event.target.files?.[0] ?? null
}

/* =============================================================
   VALIDATION
============================================================= */

const validate = () => {
  Object.keys(errors).forEach(
    (key) => {
      delete errors[key]
    },
  )

  if (!form.kelas_id) {
    errors.kelas_id =
      'Pilih kelas tujuan.'
  }

  if (!form.mapel_id) {
    errors.mapel_id =
      'Pilih mata pelajaran.'
  }

  if (!form.judul?.trim()) {
    errors.judul =
      'Judul tugas wajib diisi.'
  }

  if (!form.deadline) {
    errors.deadline =
      'Tenggat waktu wajib diisi.'
  }

  if (
    Number(form.nilai_maksimal) < 1
  ) {
    errors.nilai_maksimal =
      'Nilai maksimal minimal 1.'
  }

  return (
    Object.keys(errors).length === 0
  )
}

/* =============================================================
   SUBMIT
============================================================= */

const submit = async () => {
  if (!validate()) {
    return
  }

  saving.value = true

  try {
    const payload = {
      kelas_id: Number(
        form.kelas_id,
      ),

      mapel_id: Number(
        form.mapel_id,
      ),

      materi_id: form.materi_id
        ? Number(form.materi_id)
        : null,

      judul:
        form.judul.trim(),

      deskripsi:
        form.deskripsi?.trim() ?? '',

      instruksi:
        form.instruksi?.trim() ?? '',

      deadline: form.deadline,

      nilai_maksimal: Number(
        form.nilai_maksimal,
      ),

      is_active:
        form.is_active,

      file: form.file,

      tanggal_dibuat:
        editingId.value
          ? undefined
          : new Date()
              .toISOString()
              .slice(0, 10),
    }

    if (editingId.value) {
      await update(
        editingId.value,
        payload,
      )

      notify(
        'success',
        `Tugas "${payload.judul}" diperbarui.`,
      )
    } else {
      await store(payload)

      notify(
        'success',
        `Tugas "${payload.judul}" dibuat.`,
      )
    }

    formOpen.value = false

    await fetchAll()
  } catch (e) {
    notify(
      'danger',
      e.response?.data?.message ??
        'Tugas gagal disimpan. Coba lagi.',
    )
  } finally {
    saving.value = false
  }
}

/* =============================================================
   DETAIL
============================================================= */

const detail = ref(null)
const detailOpen = ref(false)

const openDetail = (row) => {
  detail.value = row
  detailOpen.value = true
}

/* =============================================================
   DELETE
============================================================= */

const deleteTarget = ref(null)
const deleteOpen = ref(false)
const deleting = ref(false)

const askDelete = (row) => {
  deleteTarget.value = row
  deleteOpen.value = true
}

const confirmDelete = async () => {
  if (!deleteTarget.value) {
    return
  }

  deleting.value = true

  try {
    await destroy(
      deleteTarget.value.id,
    )

    notify(
      'success',
      `Tugas "${deleteTarget.value.judul}" dihapus.`,
    )

    deleteOpen.value = false

    deleteTarget.value = null

    if (
      page.value > 1 &&
      !paged.value.length
    ) {
      page.value--
    }

    await fetchAll()
  } catch (e) {
    notify(
      'danger',
      e.response?.data?.message ??
        'Tugas gagal dihapus. Coba lagi.',
    )
  } finally {
    deleting.value = false
  }
}

/* =============================================================
   TOGGLE ACTIVE
============================================================= */

const toggleTaskActive = async (row) => {
  try {
    await toggleActive(row)

    notify(
      'success',
      `Tugas "${row.judul}" ${
        row.is_active
          ? 'diaktifkan'
          : 'dinonaktifkan'
      }.`,
    )

    await fetchAll()
  } catch (e) {
    notify(
      'danger',
      e.response?.data?.message ??
        'Status tugas gagal diperbarui.',
    )
  }
}

/* =============================================================
   NOTIFICATION
============================================================= */

const alert = reactive({
  show: false,
  type: 'success',
  message: '',
})

let alertTimer = null

const notify = (
  type,
  message,
) => {
  Object.assign(alert, {
    show: true,
    type,
    message,
  })

  clearTimeout(alertTimer)

  alertTimer = setTimeout(() => {
    alert.show = false
  }, 4000)
}

/* =============================================================
   INITIAL LOAD
============================================================= */

onMounted(async () => {
  await fetchAll()
})
</script>