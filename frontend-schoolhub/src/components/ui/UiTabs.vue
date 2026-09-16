<script setup>
defineProps({
  modelValue: [String, Number],
  tabs: { type: Array, default: () => [] }, // [{ value, label, count }]
})
defineEmits(['update:modelValue'])
</script>

<template>
  <div class="flex flex-wrap items-center gap-1 rounded-xl bg-slate-100/80 p-1">
    <button
      v-for="tab in tabs" :key="tab.value"
      class="inline-flex items-center gap-2 rounded-lg px-3.5 py-2 text-sm font-medium transition"
      :class="modelValue === tab.value
        ? 'bg-white text-emerald-700 shadow-sm'
        : 'text-slate-500 hover:text-slate-700'"
      @click="$emit('update:modelValue', tab.value)"
    >
      {{ tab.label }}
      <span v-if="tab.count !== undefined"
            class="rounded-full px-1.5 py-0.5 text-[11px]"
            :class="modelValue === tab.value ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-200/70 text-slate-500'">
        {{ tab.count }}
      </span>
    </button>
  </div>
</template>
