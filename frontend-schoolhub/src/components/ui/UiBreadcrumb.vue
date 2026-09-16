<script setup>
defineProps({ items: { type: Array, default: () => [] } }) // [{ label, to }]
</script>

<template>
  <nav aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center gap-1.5 text-sm text-slate-400">
      <li v-for="(item, i) in items" :key="item.label" class="flex items-center gap-1.5">
        <component :is="item.to ? 'a' : 'span'" :href="item.to"
                   :class="i === items.length - 1 ? 'text-slate-600 font-medium' : 'hover:text-emerald-600 transition'">
          {{ item.label }}
        </component>
        <span v-if="i < items.length - 1" class="text-slate-300">/</span>
      </li>
    </ol>
  </nav>
</template>
