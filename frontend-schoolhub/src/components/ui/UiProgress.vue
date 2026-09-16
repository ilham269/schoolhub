<script setup>
defineProps({
  value: { type: Number, default: 0 },
  max: { type: Number, default: 100 },
  label: String,
})
</script>

<template>
  <div>
    <div v-if="label" class="mb-2 flex items-center justify-between text-sm">
      <span class="text-slate-600">{{ label }}</span>
      <span class="font-semibold text-slate-700">{{ Math.round((value / max) * 100) }}%</span>
    </div>
    <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
      <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-emerald-400 transition-[width] duration-500"
           :style="{ width: Math.min(100, (value / max) * 100) + '%' }" />
    </div>
  </div>
</template>
