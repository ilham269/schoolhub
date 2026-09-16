<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
defineProps({ align: { type: String, default: 'right' } })
const open = ref(false)
const root = ref(null)
const onClickOutside = (e) => { if (root.value && !root.value.contains(e.target)) open.value = false }
onMounted(() => document.addEventListener('click', onClickOutside))
onUnmounted(() => document.removeEventListener('click', onClickOutside))
</script>

<template>
  <div ref="root" class="relative">
    <div @click="open = !open"><slot name="trigger" :open="open" /></div>
    <Transition
      enter-active-class="transition duration-100" enter-from-class="opacity-0 scale-95"
      leave-active-class="transition duration-75" leave-to-class="opacity-0 scale-95"
    >
      <div v-if="open"
           class="absolute z-30 mt-2 min-w-[11rem] origin-top rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg"
           :class="align === 'right' ? 'right-0' : 'left-0'"
           @click="open = false">
        <slot />
      </div>
    </Transition>
  </div>
</template>
