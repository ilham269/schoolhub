<template>
  <div class="empty-state">
    <div class="empty-icon">
      <i :class="`fas fa-${icon}`" />
    </div>
    <h3 class="empty-title">{{ title }}</h3>
    <p v-if="description" class="empty-description">{{ description }}</p>
    <div v-if="$slots.action" class="empty-action">
      <slot name="action" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  icon: {
    type: String,
    default: 'inbox',
  },
  title: {
    type: String,
    default: 'Tidak ada data',
  },
  description: String,
})
</script>

<style scoped>
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 48px 24px;
  text-align: center;
}

.empty-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
}

.empty-icon i {
  font-size: 2.5rem;
  color: #9ca3af;
}

.empty-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #374151;
  margin: 0 0 8px 0;
}

.empty-description {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0 0 20px 0;
  max-width: 400px;
  line-height: 1.5;
}

.empty-action {
  margin-top: 12px;
}
</style>
