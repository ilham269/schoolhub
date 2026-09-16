<script setup>
defineProps({
  modelValue: String,
  label: String,
  placeholder: String,
  rows: { type: Number, default: 4 },
  error: String,
  hint: String,
  required: Boolean,
})
defineEmits(['update:modelValue'])
</script>

<template>
  <label class="block">
    <span v-if="label" class="mb-1.5 block text-sm font-medium text-slate-700">
      {{ label }}<span v-if="required" class="text-rose-500"> *</span>
    </span>
    <textarea
      :value="modelValue"
      :placeholder="placeholder"
      :rows="rows"
      class="w-full resize-y rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder:text-slate-400
             transition focus:outline-none focus:ring-4"
      :class="error
        ? 'border-rose-300 focus:border-rose-400 focus:ring-rose-100'
        : 'border-slate-200 focus:border-emerald-500 focus:ring-emerald-100'"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <span v-if="error" class="mt-1.5 block text-xs text-rose-600">{{ error }}</span>
    <span v-else-if="hint" class="mt-1.5 block text-xs text-slate-400">{{ hint }}</span>
  </label>
</template>
