<script setup>
import { computed } from 'vue'
const props = defineProps({
  page: { type: Number, default: 1 },
  perPage: { type: Number, default: 10 },
  total: { type: Number, default: 0 },
  perPageOptions: { type: Array, default: () => [10, 25, 50] },
})
const emit = defineEmits(['update:page', 'update:perPage'])

const lastPage = computed(() => Math.max(1, Math.ceil(props.total / props.perPage)))
const from = computed(() => (props.total === 0 ? 0 : (props.page - 1) * props.perPage + 1))
const to = computed(() => Math.min(props.total, props.page * props.perPage))

const pages = computed(() => {
  const out = []
  const last = lastPage.value
  for (let i = 1; i <= last; i++) {
    if (i === 1 || i === last || Math.abs(i - props.page) <= 1) out.push(i)
    else if (out[out.length - 1] !== '...') out.push('...')
  }
  return out
})

const go = (p) => { if (p !== '...' && p >= 1 && p <= lastPage.value) emit('update:page', p) }
</script>

<template>
  <div class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 px-6 py-4 text-sm">
    <p class="text-slate-500">Menampilkan {{ from }}–{{ to }} dari {{ total }} data</p>

    <div class="flex items-center gap-4">
      <div class="flex items-center gap-2 text-slate-500">
        <span>Per halaman</span>
        <select :value="perPage"
                class="rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm focus:border-emerald-500 focus:outline-none"
                @change="$emit('update:perPage', Number($event.target.value)); $emit('update:page', 1)">
          <option v-for="n in perPageOptions" :key="n" :value="n">{{ n }}</option>
        </select>
      </div>

      <div class="flex items-center gap-1">
        <button class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 disabled:opacity-40"
                :disabled="page === 1" @click="go(page - 1)" aria-label="Sebelumnya">‹</button>
        <button v-for="(p, i) in pages" :key="i"
                class="h-8 min-w-8 rounded-lg px-2 text-sm transition"
                :class="p === page ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100'"
                :disabled="p === '...'" @click="go(p)">{{ p }}</button>
        <button class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 disabled:opacity-40"
                :disabled="page === lastPage" @click="go(page + 1)" aria-label="Berikutnya">›</button>
      </div>
    </div>
  </div>
</template>
