<template>
  <div :class="cardClasses">
    <div v-if="$slots.header || title" class="card-header">
      <slot name="header">
        <h3 class="card-title">{{ title }}</h3>
      </slot>
    </div>

    <div class="card-body" :class="{ 'no-padding': noPadding }">
      <slot />
    </div>

    <div v-if="$slots.footer" class="card-footer">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: String,
  noPadding: Boolean,
  hover: Boolean,
  bordered: Boolean,
})

const cardClasses = computed(() => [
  'card',
  {
    'card-hover': props.hover,
    'card-bordered': props.bordered,
  },
])
</script>

<style scoped>
.card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: all 0.2s;
}

.card-bordered {
  border: 1px solid #e5e7eb;
  box-shadow: none;
}

.card-hover:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

.card-header {
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
}

.card-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
  margin: 0;
}

.card-body {
  padding: 24px;
}

.card-body.no-padding {
  padding: 0;
}

.card-footer {
  padding: 16px 24px;
  border-top: 1px solid #e5e7eb;
  background: #f9fafb;
}
</style>
