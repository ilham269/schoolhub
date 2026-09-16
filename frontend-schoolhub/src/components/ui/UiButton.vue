<script setup>
const props = defineProps({
  variant: { type: String, default: 'primary' }, // primary | secondary | ghost | danger | soft
  size: { type: String, default: 'md' },         // sm | md | icon
  type: { type: String, default: 'button' },
  loading: Boolean,
  disabled: Boolean,
})

const variants = {
  primary: 'bg-emerald-600 text-white hover:bg-emerald-700 focus-visible:outline-emerald-600 shadow-sm',
  secondary: 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 focus-visible:outline-slate-400',
  soft: 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 focus-visible:outline-emerald-600',
  ghost: 'text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-slate-400',
  danger: 'bg-rose-600 text-white hover:bg-rose-700 focus-visible:outline-rose-600 shadow-sm',
}
const sizes = {
  sm: 'h-8 px-3 text-xs gap-1.5',
  md: 'h-10 px-4 text-sm gap-2',
  icon: 'h-9 w-9 text-sm',
}
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    class="inline-flex items-center justify-center rounded-xl font-medium transition-colors
           focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2
           disabled:opacity-50 disabled:pointer-events-none"
    :class="[variants[variant], sizes[size]]"
  >
    <svg v-if="loading" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
      <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v3a5 5 0 00-5 5H4z" />
    </svg>
    <slot />
  </button>
</template>
