<template>
  <div class="form-group" :class="{ 'has-error': error }">
    <label v-if="label" :for="inputId" class="form-label">
      {{ label }}
      <span v-if="required" class="required">*</span>
    </label>

    <div class="input-wrapper">
      <i v-if="icon" :class="`fas fa-${icon}`" class="input-icon" />

      <input
        v-if="type !== 'textarea' && type !== 'select'"
        :id="inputId"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :class="['form-input', { 'has-icon': icon }]"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur')"
      />

      <textarea
        v-else-if="type === 'textarea'"
        :id="inputId"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :rows="rows"
        class="form-textarea"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur')"
      />

      <select
        v-else-if="type === 'select'"
        :id="inputId"
        :value="modelValue"
        :disabled="disabled"
        :required="required"
        class="form-select"
        @change="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur')"
      >
        <option value="" disabled>{{ placeholder || 'Pilih...' }}</option>
        <option v-for="option in options" :key="option.value" :value="option.value">
          {{ option.label }}
        </option>
      </select>
    </div>

    <p v-if="error" class="error-message">
      <i class="fas fa-exclamation-circle" />
      {{ error }}
    </p>

    <p v-else-if="hint" class="hint-message">{{ hint }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: [String, Number],
  label: String,
  type: {
    type: String,
    default: 'text',
  },
  placeholder: String,
  icon: String,
  error: String,
  hint: String,
  disabled: Boolean,
  required: Boolean,
  rows: {
    type: Number,
    default: 4,
  },
  options: {
    type: Array,
    default: () => [],
  },
})

defineEmits(['update:modelValue', 'blur'])

const inputId = computed(() => `input-${Math.random().toString(36).substr(2, 9)}`)
</script>

<style scoped>
.form-group {
  margin-bottom: 20px;
}

.form-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 6px;
}

.required {
  color: #ef4444;
  margin-left: 2px;
}

.input-wrapper {
  position: relative;
}

.input-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  font-size: 0.875rem;
  pointer-events: none;
}

.form-input,
.form-textarea,
.form-select {
  width: 100%;
  padding: 10px 12px;
  font-size: 0.875rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  transition: all 0.2s;
  font-family: inherit;
}

.form-input.has-icon {
  padding-left: 38px;
}

.form-input:focus,
.form-textarea:focus,
.form-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-input:disabled,
.form-textarea:disabled,
.form-select:disabled {
  background: #f9fafb;
  cursor: not-allowed;
  color: #9ca3af;
}

.form-textarea {
  resize: vertical;
  min-height: 80px;
}

.has-error .form-input,
.has-error .form-textarea,
.has-error .form-select {
  border-color: #ef4444;
}

.has-error .form-input:focus,
.has-error .form-textarea:focus,
.has-error .form-select:focus {
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.error-message {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 6px;
  font-size: 0.75rem;
  color: #ef4444;
}

.hint-message {
  margin-top: 6px;
  font-size: 0.75rem;
  color: #6b7280;
}

.form-select {
  cursor: pointer;
  background: white url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e")
    no-repeat right 8px center/20px 20px;
  padding-right: 36px;
  appearance: none;
}
</style>
