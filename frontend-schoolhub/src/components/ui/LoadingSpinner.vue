<template>
  <div class="loading-spinner" :class="sizeClass">
    <div class="spinner" :class="variantClass">
      <i class="fas fa-circle-notch fa-spin" />
    </div>
    <p v-if="text" class="loading-text">{{ text }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value),
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'white'].includes(value),
  },
  text: String,
})

const sizeClass = computed(() => `spinner-${props.size}`)
const variantClass = computed(() => `spinner-${props.variant}`)
</script>

<style scoped>
.loading-spinner {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
}

.spinner i {
  display: block;
}

.spinner-sm i {
  font-size: 1rem;
}

.spinner-md i {
  font-size: 1.5rem;
}

.spinner-lg i {
  font-size: 2rem;
}

.spinner-xl i {
  font-size: 3rem;
}

.spinner-primary {
  color: #3b82f6;
}

.spinner-secondary {
  color: #6b7280;
}

.spinner-white {
  color: white;
}

.loading-text {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}
</style>
