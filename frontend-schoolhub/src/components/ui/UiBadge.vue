<script setup>
defineProps({ variant: { type: String, default: 'neutral' } })
const tones = {
  neutral: 'bg-slate-100 text-slate-600',
  success: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100',
  warning: 'bg-amber-50 text-amber-700 ring-1 ring-amber-100',
  danger: 'bg-rose-50 text-rose-600 ring-1 ring-rose-100',
  info: 'bg-sky-50 text-sky-700 ring-1 ring-sky-100',
  brand: 'bg-emerald-600/10 text-emerald-700',
}
</script>

<template>
  <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium" :class="tones[variant]">
    <slot />
  </span>
</template>
