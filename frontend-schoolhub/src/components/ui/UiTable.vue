<script setup>
defineProps({
  columns: { type: Array, default: () => [] }, // [{ key, label, sortable, class }]
  rows: { type: Array, default: () => [] },
  sortKey: String,
  sortDir: { type: String, default: 'asc' },
  loading: Boolean,
})
defineEmits(['sort'])
</script>

<template>
  <div class="overflow-x-auto">
    <table class="w-full min-w-[720px] text-left text-sm">
      <thead>
        <tr class="border-y border-slate-100 bg-slate-50/60 text-xs font-semibold tracking-wide text-slate-500">
          <th v-for="col in columns" :key="col.key" class="px-6 py-3.5" :class="col.class">
            <button v-if="col.sortable" class="inline-flex items-center gap-1 transition hover:text-emerald-700"
                    @click="$emit('sort', col.key)">
              {{ col.label }}
              <span class="text-[10px]" :class="sortKey === col.key ? 'text-emerald-600' : 'text-slate-300'">
                {{ sortKey === col.key && sortDir === 'desc' ? '▼' : '▲' }}
              </span>
            </button>
            <span v-else>{{ col.label }}</span>
          </th>
        </tr>
      </thead>

      <tbody class="divide-y divide-slate-100">
        <tr v-if="loading">
          <td :colspan="columns.length" class="px-6 py-14 text-center text-slate-400">Memuat data kelas…</td>
        </tr>
        <tr v-else-if="!rows.length">
          <td :colspan="columns.length" class="px-6 py-14 text-center">
            <slot name="empty">
              <p class="font-medium text-slate-600">Belum ada kelas</p>
              <p class="mt-1 text-sm text-slate-400">Tambah kelas pertama untuk mulai mengatur rombongan belajar.</p>
            </slot>
          </td>
        </tr>
        <tr v-for="(row, i) in rows" :key="row.id ?? i" class="transition hover:bg-emerald-50/40">
          <slot name="row" :row="row" :index="i" />
        </tr>
      </tbody>
    </table>
  </div>
</template>
