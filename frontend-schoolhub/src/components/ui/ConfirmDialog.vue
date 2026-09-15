<template>
  <Modal
    v-model="isOpen"
    :title="title"
    size="sm"
    :close-on-overlay="!loading"
  >
    <div class="confirm-content">
      <div class="confirm-icon" :class="`icon-${variant}`">
        <i :class="iconClass" />
      </div>
      <p class="confirm-message">{{ message }}</p>
      <p v-if="description" class="confirm-description">{{ description }}</p>
    </div>

    <template #footer>
      <Button
        variant="secondary"
        @click="handleCancel"
        :disabled="loading"
      >
        {{ cancelText }}
      </Button>
      <Button
        :variant="variant"
        @click="handleConfirm"
        :loading="loading"
      >
        {{ confirmText }}
      </Button>
    </template>
  </Modal>
</template>

<script setup>
import { computed, watch } from 'vue'
import Modal from './Modal.vue'
import Button from './Button.vue'

const props = defineProps({
  modelValue: Boolean,
  title: {
    type: String,
    default: 'Konfirmasi',
  },
  message: {
    type: String,
    required: true,
  },
  description: String,
  variant: {
    type: String,
    default: 'danger',
    validator: (value) => ['primary', 'danger', 'warning', 'info'].includes(value),
  },
  confirmText: {
    type: String,
    default: 'Ya, Lanjutkan',
  },
  cancelText: {
    type: String,
    default: 'Batal',
  },
  loading: Boolean,
})

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel'])

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
})

const iconClass = computed(() => {
  const icons = {
    primary: 'fas fa-info-circle',
    danger: 'fas fa-exclamation-triangle',
    warning: 'fas fa-exclamation-circle',
    info: 'fas fa-question-circle',
  }
  return icons[props.variant]
})

const handleConfirm = () => {
  emit('confirm')
}

const handleCancel = () => {
  if (!props.loading) {
    isOpen.value = false
    emit('cancel')
  }
}
</script>

<style scoped>
.confirm-content {
  text-align: center;
  padding: 20px 0;
}

.confirm-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 20px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
}

.icon-primary {
  background: #dbeafe;
  color: #3b82f6;
}

.icon-danger {
  background: #fee2e2;
  color: #ef4444;
}

.icon-warning {
  background: #fef3c7;
  color: #f59e0b;
}

.icon-info {
  background: #cffafe;
  color: #06b6d4;
}

.confirm-message {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 8px;
}

.confirm-description {
  font-size: 0.875rem;
  color: #6b7280;
  line-height: 1.5;
}
</style>
