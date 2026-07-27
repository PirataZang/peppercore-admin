<template>
  <div class="color-field-wrapper" :style="{ width }">
    <label v-if="label" class="color-label">{{ label }}</label>

    <div class="color-input-container">
      <div class="color-preview" :style="{ backgroundColor: modelValue || '#000000' }">
        <input
          type="color"
          class="native-color-input"
          :value="modelValue || '#000000'"
          @input="updateColor"
        />
      </div>
      <span class="color-value">{{ modelValue || '#000000' }}</span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: String,
    default: '#4f46e5',
  },
  label: {
    type: String,
    default: '',
  },
  width: {
    type: String,
    default: '100%',
  },
})

const emit = defineEmits(['update:modelValue', 'change'])

const updateColor = (event) => {
  const value = event.target.value
  emit('update:modelValue', value)
  emit('change', value)
}
</script>

<style scoped lang="scss">
.color-field-wrapper {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.color-label {
  font-size: 13px;
  font-weight: 500;
  color: var(--text-secondary);
}

.color-input-container {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 12px;
  background: rgba(11, 15, 25, 0.5);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  transition: all 0.2s ease;
  cursor: pointer;

  &:hover {
    border-color: rgba(255, 77, 77, 0.35);
  }

  &:focus-within {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(255, 77, 77, 0.12);
  }
}

.color-preview {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  position: relative;
  overflow: hidden;
  flex-shrink: 0;
}

.native-color-input {
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  cursor: pointer;
  opacity: 0;
}

.color-value {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-primary);
  text-transform: uppercase;
  font-family: monospace;
}
</style>
