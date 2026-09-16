<script setup>
import UiBreadcrumb from '../ui/UiBreadcrumb.vue'
defineProps({ title: String, breadcrumb: { type: Array, default: () => [] } })
defineEmits(['toggle-sidebar'])
</script>

<template>
  <header class="sticky top-0 z-20 flex items-center justify-between gap-4 border-b border-slate-200/70 bg-white/85 px-6 py-4 backdrop-blur">
    <div class="flex items-center gap-3">
      <button class="grid h-9 w-9 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 lg:hidden"
              @click="$emit('toggle-sidebar')" aria-label="Buka menu">
        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
          <path d="M3 6h14M3 10h14M3 14h14" />
        </svg>
      </button>
      <div>
        <UiBreadcrumb :items="breadcrumb" />
        <h1 class="text-xl font-semibold text-slate-800">{{ title }}</h1>
      </div>
    </div>

    <div class="flex items-center gap-3">
      <button class="relative grid h-9 w-9 place-items-center rounded-full border border-slate-200 text-slate-500 transition hover:bg-slate-50" aria-label="Notifikasi">
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
          <path d="M6 8a4 4 0 1 1 8 0c0 3 1 4 1 4H5s1-1 1-4M8.5 15a1.5 1.5 0 0 0 3 0" />
        </svg>
        <span class="absolute right-2 top-2 h-1.5 w-1.5 rounded-full bg-emerald-500" />
      </button>
      <span class="grid h-9 w-9 place-items-center rounded-full bg-emerald-600 text-sm font-semibold text-white">A</span>
    </div>
  </header>
</template>
