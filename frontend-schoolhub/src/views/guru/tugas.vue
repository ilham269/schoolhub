```vue
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
        <div class="flex flex-col gap-3 sm:flex-row">
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

          <!-- Filter kelas -->
          <div class="sm:w-52">
            <UiSelect
              v-model="kelasFilter"
              :options="kelasOptions"
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
        </div>
      </div>

      <!-- =======================================================
           TABLE
      ======================================================== -->
      <UiTable
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

      <!-- Pagination -->
      <UiPagination
        v-model:page="page"
        v-model:perPage="perPage"
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

import DashboardLayout from '../../components/dashboard/DashboardLayout.vue'

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
    label: 'Tugas',
    icon: 'fas fa-book-open',
    to: '/dashboard/guru/tugas',
  },
  {
    label: 'Nilai',
    icon: 'fas fa-chart-bar',
    to: '/dashboard/guru/nilai',
  },
  {
    label: 'Ujian PPDB & Soal',
    icon: 'fas fa-file-circle-check',
    to: '/dashboard/guru/ujian-ppdb',
  },
]

/* =============================================================
   COMPOSABLE
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
   REFERENCE DATA
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
const kelasFilter = ref('')
const mapelFilter = ref('')
const activeTab = ref('semua')

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
============================================================= */

const page = ref(1)
const perPage = ref(10)

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

watch(
  [
    search,
    kelasFilter,
    mapelFilter,
    activeTab,
  ],
  () => {
    page.value = 1
  },
)

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
```
