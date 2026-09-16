```vue
<script setup>
import { watch, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: Boolean,

  title: String,

  subtitle: String,

  size: {
    type: String,
    default: 'md',
  },
})

const emit = defineEmits([
  'update:modelValue',
])

const sizes = {
  sm: 'max-w-sm',
  md: 'max-w-lg',
  lg: 'max-w-3xl',
}

const close = () => {
  emit('update:modelValue', false)
}

const onKey = (e) => {
  if (e.key === 'Escape') {
    close()
  }
}

watch(
  () => props.modelValue,
  (open) => {
    document.body.style.overflow = open
      ? 'hidden'
      : ''

    if (open) {
      window.addEventListener(
        'keydown',
        onKey,
      )
    } else {
      window.removeEventListener(
        'keydown',
        onKey,
      )
    }
  },
)

onUnmounted(() => {
  document.body.style.overflow = ''

  window.removeEventListener(
    'keydown',
    onKey,
  )
})
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-150"
      enter-from-class="opacity-0"
      leave-active-class="transition duration-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 flex items-end justify-center
               bg-slate-900/50 p-0 backdrop-blur-[2px]
               sm:items-center sm:p-6"
        @click.self="close"
      >
        <div
          class="flex max-h-[90vh] w-full flex-col
                 overflow-hidden rounded-t-2xl bg-white shadow-xl
                 sm:rounded-2xl"
          :class="sizes[size]"
          role="dialog"
          aria-modal="true"
        >
          <!-- =================================================
               HEADER
          ================================================== -->
          <header
            class="flex shrink-0 items-start justify-between gap-4
                   border-b border-slate-100 px-6 py-4"
          >
            <div class="min-w-0">
              <h3
                class="text-base font-semibold text-slate-800"
              >
                {{ title }}
              </h3>

              <p
                v-if="subtitle"
                class="mt-0.5 text-sm text-slate-400"
              >
                {{ subtitle }}
              </p>
            </div>

            <button
              type="button"
              class="shrink-0 rounded-lg p-1.5
                     text-slate-400 transition
                     hover:bg-slate-100
                     hover:text-slate-600"
              aria-label="Tutup"
              @click="close"
            >
              <svg
                class="h-4 w-4"
                viewBox="0 0 20 20"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
              >
                <path
                  d="m5 5 10 10M15 5 5 15"
                  stroke-linecap="round"
                />
              </svg>
            </button>
          </header>

          <!-- =================================================
               CONTENT / SCROLL AREA
          ================================================== -->
          <div
            class="min-h-0 flex-1 overflow-y-auto px-6 py-5
                   overscroll-contain"
          >
            <slot />
          </div>

          <!-- =================================================
               FOOTER
          ================================================== -->
          <footer
            v-if="$slots.footer"
            class="shrink-0 flex items-center justify-end
                   gap-2 border-t border-slate-100
                   bg-slate-50/70 px-6 py-4"
          >
            <slot name="footer" />
          </footer>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
```
