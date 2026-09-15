<template>
  <Transition name="alert">
    <div v-if="modelValue" :class="alertClasses">
      <div class="alert-icon">
        <i :class="iconClass" />
      </div>

      <div class="alert-content">
        <h4 v-if="title" class="alert-title">{{ title }}</h4>
        <p class="alert-message">
          <slot>{{ message }}</slot>
        </p>
      </div>

      <button
        v-if="closable"
        @click="close"
        class="alert-close"
        type="button"
      >
        <i class="fas fa-times" />
      </button>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: Boolean,
  title: String,
  message: String,
  variant: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'info', 'warning', 'danger'].includes(value),
  },
  closable: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['update:modelValue', 'close'])

const alertClasses = computed(() => ['alert', `alert-${props.variant}`])

const iconClass = computed(() => {
  const icons = {
    success: 'fas fa-check-circle',
    info: 'fas fa-info-circle',
    warning: 'fas fa-exclamation-triangle',
    danger: 'fas fa-times-circle',
  }
  return icons[props.variant]
})

const close = () => {
  emit('update:modelValue', false)
  emit('close')
}
</script>

<style scoped>
.alert {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 16px;
  border-radius: 8px;
  border: 1px solid;
  position: relative;
}

.alert-icon {
  flex-shrink: 0;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.alert-content {
  flex: 1;
  min-width: 0;
}

.alert-title {
  font-size: 0.875rem;
  font-weight: 600;
  margin: 0 0 4px 0;
}

.alert-message {
  font-size: 0.875rem;
  margin: 0;
  line-height: 1.5;
}

.alert-close {
  flex-shrink: 0;
  background: none;
  border: none;
  padding: 4px;
  cursor: pointer;
  border-radius: 4px;
  transition: background 0.2s;
  opacity: 0.7;
}

.alert-close:hover {
  opacity: 1;
}

/* Variants */
.alert-success {
  background: #d1fae5;
  border-color: #10b981;
  color: #065f46;
}

.alert-success .alert-icon {
  color: #10b981;
}

.alert-success .alert-close:hover {
  background: rgba(16, 185, 129, 0.1);
}

.alert-info {
  background: #dbeafe;
  border-color: #3b82f6;
  color: #1e40af;
}

.alert-info .alert-icon {
  color: #3b82f6;
}

.alert-info .alert-close:hover {
  background: rgba(59, 130, 246, 0.1);
}

.alert-warning {
  background: #fef3c7;
  border-color: #f59e0b;
  color: #92400e;
}

.alert-warning .alert-icon {
  color: #f59e0b;
}

.alert-warning .alert-close:hover {
  background: rgba(245, 158, 11, 0.1);
}

.alert-danger {
  background: #fee2e2;
  border-color: #ef4444;
  color: #991b1b;
}

.alert-danger .alert-icon {
  color: #ef4444;
}

.alert-danger .alert-close:hover {
  background: rgba(239, 68, 68, 0.1);
}

/* Transition */
.alert-enter-active,
.alert-leave-active {
  transition: all 0.3s;
}

.alert-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.alert-leave-to {
  opacity: 0;
  transform: translateX(20px);
}
</style>
