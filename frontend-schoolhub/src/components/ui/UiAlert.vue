<script setup>
defineProps({
  type: { type: String, default: 'info' }, // success | danger | warning | info
  title: String,
  dismissible: { type: Boolean, default: true },
})
defineEmits(['close'])
const tones = {
  success: 'border-emerald-200 bg-emerald-50 text-emerald-800',
  danger: 'border-rose-200 bg-rose-50 text-rose-800',
  warning: 'border-amber-200 bg-amber-50 text-amber-800',
  info: 'border-sky-200 bg-sky-50 text-sky-800',
}
</script>

<template>
  <div class="flex items-start gap-3 rounded-xl border px-4 py-3 text-sm" :class="tones[type]" role="status">
    <div class="flex-1">
      <p v-if="title" class="font-semibold">{{ title }}</p>
      <p class="opacity-90"><slot /></p>
    </div>
    <button v-if="dismissible" class="rounded-lg p-1 opacity-60 transition hover:opacity-100" @click="$emit('close')" aria-label="Tutup">
      <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
        <path d="m5 5 10 10M15 5 5 15" stroke-linecap="round" />
      </svg>
    </button>
  </div>
</template>
