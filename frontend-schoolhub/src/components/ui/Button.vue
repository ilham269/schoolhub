<template>
  <component
    :is="tag"
    :type="tag === 'button' ? type : undefined"
    :to="tag === 'router-link' ? to : undefined"
    :href="tag === 'a' ? href : undefined"
    :disabled="disabled || loading"
    :class="buttonClasses"
    @click="handleClick"
  >
    <i v-if="loading" class="fas fa-spinner fa-spin" />
    <i v-else-if="icon" :class="`fas fa-${icon}`" />
    <span v-if="slots.default" class="button-text">
      <slot />
    </span>
  </component>
</template>

<script setup>
import { computed, useSlots } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) =>
      ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'ghost'].includes(value),
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
  outlined: Boolean,
  icon: String,
  loading: Boolean,
  disabled: Boolean,
  block: Boolean,
  type: {
    type: String,
    default: 'button',
  },
  to: [String, Object],
  href: String,
})

const emit = defineEmits(['click'])
const slots = useSlots()

const tag = computed(() => {
  if (props.to) return 'router-link'
  if (props.href) return 'a'
  return 'button'
})

const buttonClasses = computed(() => [
  'btn',
  `btn-${props.variant}`,
  `btn-${props.size}`,
  {
    'btn-outlined': props.outlined,
    'btn-block': props.block,
    'btn-loading': props.loading,
    'btn-disabled': props.disabled,
    'btn-icon-only': props.icon && !slots.default,
  },
])

const handleClick = (event) => {
  if (!props.disabled && !props.loading) {
    emit('click', event)
  }
}
</script>

<style scoped>
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-weight: 500;
  border: 1px solid transparent;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
  line-height: 1;
  white-space: nowrap;
}

.btn:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn:hover:not(.btn-disabled):not(.btn-loading) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.btn:active:not(.btn-disabled):not(.btn-loading) {
  transform: translateY(0);
}

/* Sizes */
.btn-sm {
  padding: 6px 12px;
  font-size: 0.75rem;
}

.btn-md {
  padding: 10px 16px;
  font-size: 0.875rem;
}

.btn-lg {
  padding: 12px 20px;
  font-size: 1rem;
}

.btn-icon-only {
  padding: 10px;
}

.btn-icon-only.btn-sm {
  padding: 6px;
}

.btn-icon-only.btn-lg {
  padding: 12px;
}

/* Variants - Solid */
.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover:not(.btn-disabled):not(.btn-loading) {
  background: #2563eb;
}

.btn-secondary {
  background: #6b7280;
  color: white;
}

.btn-secondary:hover:not(.btn-disabled):not(.btn-loading) {
  background: #4b5563;
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover:not(.btn-disabled):not(.btn-loading) {
  background: #059669;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover:not(.btn-disabled):not(.btn-loading) {
  background: #dc2626;
}

.btn-warning {
  background: #f59e0b;
  color: white;
}

.btn-warning:hover:not(.btn-disabled):not(.btn-loading) {
  background: #d97706;
}

.btn-info {
  background: #06b6d4;
  color: white;
}

.btn-info:hover:not(.btn-disabled):not(.btn-loading) {
  background: #0891b2;
}

.btn-ghost {
  background: transparent;
  color: #6b7280;
}

.btn-ghost:hover:not(.btn-disabled):not(.btn-loading) {
  background: #f3f4f6;
  color: #374151;
}

/* Outlined Variants */
.btn-outlined {
  background: transparent;
  border-width: 1px;
  border-style: solid;
}

.btn-outlined.btn-primary {
  border-color: #3b82f6;
  color: #3b82f6;
}

.btn-outlined.btn-primary:hover:not(.btn-disabled):not(.btn-loading) {
  background: #eff6ff;
}

.btn-outlined.btn-secondary {
  border-color: #6b7280;
  color: #6b7280;
}

.btn-outlined.btn-secondary:hover:not(.btn-disabled):not(.btn-loading) {
  background: #f9fafb;
}

.btn-outlined.btn-success {
  border-color: #10b981;
  color: #10b981;
}

.btn-outlined.btn-success:hover:not(.btn-disabled):not(.btn-loading) {
  background: #d1fae5;
}

.btn-outlined.btn-danger {
  border-color: #ef4444;
  color: #ef4444;
}

.btn-outlined.btn-danger:hover:not(.btn-disabled):not(.btn-loading) {
  background: #fee2e2;
}

.btn-outlined.btn-warning {
  border-color: #f59e0b;
  color: #f59e0b;
}

.btn-outlined.btn-warning:hover:not(.btn-disabled):not(.btn-loading) {
  background: #fef3c7;
}

.btn-outlined.btn-info {
  border-color: #06b6d4;
  color: #06b6d4;
}

.btn-outlined.btn-info:hover:not(.btn-disabled):not(.btn-loading) {
  background: #cffafe;
}

/* States */
.btn-disabled,
.btn-loading {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
  box-shadow: none !important;
}

.btn-block {
  width: 100%;
}

.button-text {
  display: inline-block;
}
</style>
