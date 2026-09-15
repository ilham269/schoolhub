<template>
  <span :class="badgeClasses">
    <i v-if="icon" :class="`fas fa-${icon}`" />
    <slot />
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (value) =>
      [
        'default',
        'primary',
        'success',
        'danger',
        'warning',
        'info',
        'secondary',
      ].includes(value),
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
  icon: String,
  rounded: Boolean,
  outlined: Boolean,
})

const badgeClasses = computed(() => [
  'badge',
  `badge-${props.variant}`,
  `badge-${props.size}`,
  {
    'badge-rounded': props.rounded,
    'badge-outlined': props.outlined,
  },
])
</script>

<style scoped>
.badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  font-size: 0.75rem;
  font-weight: 500;
  border-radius: 6px;
  line-height: 1;
  white-space: nowrap;
  border: 1px solid transparent;
}

.badge i {
  font-size: 0.7rem;
}

/* Sizes */
.badge-sm {
  padding: 2px 8px;
  font-size: 0.7rem;
}

.badge-md {
  padding: 4px 10px;
  font-size: 0.75rem;
}

.badge-lg {
  padding: 6px 12px;
  font-size: 0.875rem;
}

/* Variants - Solid */
.badge-default {
  background: #f3f4f6;
  color: #6b7280;
}

.badge-primary {
  background: #dbeafe;
  color: #1d4ed8;
}

.badge-success {
  background: #d1fae5;
  color: #065f46;
}

.badge-danger {
  background: #fee2e2;
  color: #991b1b;
}

.badge-warning {
  background: #fef3c7;
  color: #92400e;
}

.badge-info {
  background: #cffafe;
  color: #155e75;
}

.badge-secondary {
  background: #f9fafb;
  color: #374151;
}

/* Outlined Variants */
.badge-outlined {
  background: transparent;
  border-width: 1px;
  border-style: solid;
}

.badge-outlined.badge-default {
  border-color: #d1d5db;
  color: #6b7280;
}

.badge-outlined.badge-primary {
  border-color: #3b82f6;
  color: #1d4ed8;
}

.badge-outlined.badge-success {
  border-color: #10b981;
  color: #065f46;
}

.badge-outlined.badge-danger {
  border-color: #ef4444;
  color: #991b1b;
}

.badge-outlined.badge-warning {
  border-color: #f59e0b;
  color: #92400e;
}

.badge-outlined.badge-info {
  border-color: #06b6d4;
  color: #155e75;
}

.badge-outlined.badge-secondary {
  border-color: #9ca3af;
  color: #374151;
}

/* Rounded */
.badge-rounded {
  border-radius: 9999px;
}
</style>
