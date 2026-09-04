<template>
  <div class="feature-grid">
    <router-link v-for="feature in features" :key="feature.title" class="feature-card" :to="feature.to || '#'">
      <span class="feature-icon"><i :class="feature.icon"></i></span>
      <div><h3>{{ feature.title }}</h3><p>{{ feature.description }}</p></div>
      <i class="fas fa-arrow-right arrow"></i>
    </router-link>
  </div>
</template>

<script setup>
defineProps({ features: { type: Array, required: true } })
</script>

<style scoped>
.feature-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.feature-card{position:relative;background:#fff;border:1px solid var(--line);border-radius:14px;padding:20px;min-height:130px;transition:transform .15s,box-shadow .15s;overflow:hidden}.feature-card:hover{transform:translateY(-3px);box-shadow:var(--shadow-card);border-color:#b9ddc4}.feature-icon{display:grid;place-items:center;width:39px;height:39px;border-radius:10px;background:#eaf7ee;color:var(--leaf-600);margin-bottom:13px}.feature-card h3{font-size:.95rem;margin-bottom:5px}.feature-card p{font-size:.79rem;margin:0;line-height:1.45}.arrow{position:absolute;right:18px;bottom:18px;color:#9ab3a2;font-size:.78rem}@media(max-width:1050px){.feature-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:560px){.feature-grid{grid-template-columns:1fr}}
</style>
