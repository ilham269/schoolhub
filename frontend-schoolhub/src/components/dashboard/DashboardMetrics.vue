<template>
  <div class="metrics-grid">
    <article v-for="metric in items" :key="metric.label" class="metric-card">
      <span class="metric-icon" :class="metric.color"><i :class="metric.icon"></i></span>
      <div><p>{{ metric.label }}</p><strong>{{ metric.value }}</strong><small v-if="metric.note">{{ metric.note }}</small></div>
    </article>
  </div>
</template>

<script setup>
defineProps({ items: { type: Array, required: true } })
</script>

<style scoped>
.metrics-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}.metric-card{background:#fff;border:1px solid var(--line);border-radius:14px;padding:18px;display:flex;gap:13px;align-items:center;box-shadow:0 6px 20px -16px rgba(6,35,26,.4)}.metric-icon{width:43px;height:43px;display:grid;place-items:center;border-radius:12px;flex:none}.metric-icon.green{background:#e3f6ea;color:#16804a}.metric-icon.amber{background:#fdf1dc;color:#b27114}.metric-icon.blue{background:#e8f0ff;color:#3767b7}.metric-icon.red{background:#fbe4e4;color:#bd3b3b}.metric-card p,.metric-card small{margin:0;display:block;font-size:.76rem;color:var(--muted)}.metric-card strong{font:700 1.35rem var(--font-head);display:block;color:var(--forest-950)}@media(max-width:1050px){.metrics-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:500px){.metrics-grid{grid-template-columns:1fr}}
</style>
