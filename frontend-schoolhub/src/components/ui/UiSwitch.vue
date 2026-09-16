<script setup>
defineProps({
  modelValue: Boolean,
  label: String,
  hint: String,
})
defineEmits(['update:modelValue'])
</script>

<template>
  <div class="flex items-start justify-between gap-4">
    <span v-if="label || hint">
      <span v-if="label" class="block text-sm font-medium text-slate-700">{{ label }}</span>
      <span v-if="hint" class="mt-0.5 block text-xs text-slate-400">{{ hint }}</span>
    </span>
    <button
      type="button"
      role="switch"
      :aria-checked="modelValue"
      class="relative h-6 w-11 shrink-0 rounded-full transition"
      :class="modelValue ? 'bg-emerald-600' : 'bg-slate-200'"
      @click="$emit('update:modelValue', !modelValue)"
    >
      <span class="absolute top-0.5 h-5 w-5 rounded-full bg-white shadow transition-all"
            :class="modelValue ? 'left-[22px]' : 'left-0.5'" />
    </button>
  </div>
</template>
