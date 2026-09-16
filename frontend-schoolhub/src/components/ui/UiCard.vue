<script setup>
defineProps({ title: String, subtitle: String, padded: { type: Boolean, default: true } })
</script>

<template>
  <section class="rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <header v-if="title || $slots.actions" class="flex flex-wrap items-center justify-between gap-3 px-6 pt-5 pb-4">
      <div>
        <h2 class="text-base font-semibold text-slate-800">{{ title }}</h2>
        <p v-if="subtitle" class="mt-0.5 text-sm text-slate-400">{{ subtitle }}</p>
      </div>
      <div class="flex items-center gap-2"><slot name="actions" /></div>
    </header>
    <div :class="padded ? 'px-6 pb-6' : ''"><slot /></div>
  </section>
</template>
