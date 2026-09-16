<script setup>
defineProps({
  modelValue: [String, Number],
  label: String,
  placeholder: String,
  type: { type: String, default: 'text' },
  error: String,
  hint: String,
  required: Boolean,
  disabled: Boolean,
})
defineEmits(['update:modelValue'])
</script>

<template>
  <label class="block">
    <span v-if="label" class="mb-1.5 block text-sm font-medium text-slate-700">
      {{ label }}<span v-if="required" class="text-rose-500"> *</span>
    </span>
    <span class="relative block">
      <span v-if="$slots.icon" class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
        <slot name="icon" />
      </span>
      <input
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        class="w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder:text-slate-400
               transition focus:outline-none focus:ring-4 disabled:bg-slate-50"
        :class="[
          $slots.icon ? 'pl-10' : '',
          error
            ? 'border-rose-300 focus:border-rose-400 focus:ring-rose-100'
            : 'border-slate-200 focus:border-emerald-500 focus:ring-emerald-100',
        ]"
        @input="$emit('update:modelValue', $event.target.value)"
      />
    </span>
    <span v-if="error" class="mt-1.5 block text-xs text-rose-600">{{ error }}</span>
    <span v-else-if="hint" class="mt-1.5 block text-xs text-slate-400">{{ hint }}</span>
  </label>
</template>
