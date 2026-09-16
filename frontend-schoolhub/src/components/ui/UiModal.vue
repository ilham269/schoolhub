<script setup>
import { watch, onUnmounted } from 'vue'
const props = defineProps({
  modelValue: Boolean,
  title: String,
  subtitle: String,
  size: { type: String, default: 'md' }, // sm | md | lg
})
const emit = defineEmits(['update:modelValue'])
const sizes = { sm: 'max-w-sm', md: 'max-w-lg', lg: 'max-w-3xl' }

const close = () => emit('update:modelValue', false)
const onKey = (e) => e.key === 'Escape' && close()

watch(() => props.modelValue, (open) => {
  document.body.style.overflow = open ? 'hidden' : ''
  open ? window.addEventListener('keydown', onKey) : window.removeEventListener('keydown', onKey)
})
onUnmounted(() => {
  document.body.style.overflow = ''
  window.removeEventListener('keydown', onKey)
})
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-150" enter-from-class="opacity-0"
      leave-active-class="transition duration-100" leave-to-class="opacity-0"
    >
      <div v-if="modelValue" class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/50 p-0 backdrop-blur-[2px] sm:items-center sm:p-6"
           @click.self="close">
        <div class="w-full rounded-t-2xl bg-white shadow-xl sm:rounded-2xl" :class="sizes[size]" role="dialog" aria-modal="true">
          <header class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-4">
            <div>
              <h3 class="text-base font-semibold text-slate-800">{{ title }}</h3>
              <p v-if="subtitle" class="mt-0.5 text-sm text-slate-400">{{ subtitle }}</p>
            </div>
            <button class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" @click="close" aria-label="Tutup">
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="m5 5 10 10M15 5 5 15" stroke-linecap="round" />
              </svg>
            </button>
          </header>
          <div class="px-6 py-5"><slot /></div>
          <footer v-if="$slots.footer" class="flex items-center justify-end gap-2 rounded-b-2xl bg-slate-50/70 px-6 py-4">
            <slot name="footer" />
          </footer>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
