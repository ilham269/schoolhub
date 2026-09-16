<script setup>
defineProps({
  modelValue: [String, Number],
  label: String,
  options: { type: Array, default: () => [] }, // [{ value, label }]
  placeholder: { type: String, default: 'Pilih...' },
  error: String,
  required: Boolean,
})
defineEmits(['update:modelValue'])
</script>

<template>
  <label class="block">
    <span v-if="label" class="mb-1.5 block text-sm font-medium text-slate-700">
      {{ label }}<span v-if="required" class="text-rose-500"> *</span>
    </span>
    <span class="relative block">
      <select
        :value="modelValue"
        class="w-full appearance-none rounded-xl border bg-white px-3.5 py-2.5 pr-10 text-sm text-slate-800
               transition focus:outline-none focus:ring-4"
        :class="error
          ? 'border-rose-300 focus:border-rose-400 focus:ring-rose-100'
          : 'border-slate-200 focus:border-emerald-500 focus:ring-emerald-100'"
        @change="$emit('update:modelValue', $event.target.value)"
      >
        <option value="">{{ placeholder }}</option>
        <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
      </select>
      <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
           viewBox="0 0 20 20" fill="currentColor">
        <path d="M5.5 7.5 10 12l4.5-4.5" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" />
      </svg>
    </span>
    <span v-if="error" class="mt-1.5 block text-xs text-rose-600">{{ error }}</span>
  </label>
</template>
