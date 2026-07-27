<template>
  <div class="input-wrapper" :style="{ width }">
    <label v-if="label" class="input-label">{{ label }}</label>

    <div
      class="input-group"
      :class="{
        'is-focused': isFocused,
        'is-disabled': disabled,
        'has-prefix': hasPrefix,
        'has-suffix': hasSuffix,
      }"
    >
      <span v-if="hasPrefix" class="input-slot input-prefix">
        <slot name="prefix" />
      </span>

      <input
        v-model="model"
        class="input-field"
        :class="{ 'is-small': small }"
        :type="type"
        :name="inputName"
        :placeholder="placeholder"
        :disabled="disabled"
        :autocomplete="autocompleteValue"
        v-bind="inputAttrs"
        @focus="isFocused = true"
        @blur="isFocused = false"
      />

      <span v-if="hasSuffix" class="input-slot input-suffix">
        <slot name="suffix" />
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed, useAttrs, useSlots, ref } from 'vue'

const props = defineProps({
  width: {
    type: String,
    default: '',
  },
  label: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: '',
  },
  type: {
    type: String,
    default: 'text',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  small: {
    type: Boolean,
    default: false,
  },
  name: {
    type: String,
    default: null,
  },
  autocomplete: {
    type: String,
    default: null,
  },
})

const model = defineModel({ default: '' })
const attrs = useAttrs()
const slots = useSlots()
const isFocused = ref(false)

const hasPrefix = computed(() => !!slots.prefix)
const hasSuffix = computed(() => !!slots.suffix)

const generatedName = 'inp_' + Math.random().toString(36).slice(2, 9)
const inputName = computed(() => props.name || generatedName)

const autocompleteValue = computed(() => {
  if (props.autocomplete) return props.autocomplete
  if (props.type === 'password') return 'new-password'
  return 'off'
})

const inputAttrs = computed(() => {
  const { class: _class, style: _style, ...rest } = attrs
  return rest
})
</script>

<style scoped lang="scss">
.input-wrapper {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.input-label {
  font-size: 13px;
  font-weight: 500;
  color: var(--text-secondary);
  display: block;
}

.input-group {
  position: relative;
  display: flex;
  align-items: center;
  border: 1.5px solid var(--border-color);
  border-radius: 8px;
  background: rgba(11, 15, 25, 0.5);
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.15s ease;
  overflow: hidden;

  &:hover:not(.is-disabled) {
    border-color: rgba(255, 77, 77, 0.35);
    background: rgba(11, 15, 25, 0.7);
  }

  &.is-focused {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(255, 77, 77, 0.12);
    background: rgba(11, 15, 25, 0.8);
  }

  &.is-disabled {
    background: rgba(11, 15, 25, 0.35);
    opacity: 0.65;
    cursor: not-allowed;
  }
}

.input-slot {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: var(--text-muted);
  font-size: 13px;
}

.input-prefix {
  padding: 0 0 0 12px;
}

.input-suffix {
  padding: 0 12px 0 0;
}

.input-field {
  flex: 1;
  width: 100%;
  min-width: 0;
  padding: 9px 12px;
  border: none;
  background: transparent;
  color: var(--text-primary);
  font-size: 14px;
  outline: none;
  transition: color 0.15s ease;
  font-family: inherit;

  &::placeholder {
    color: var(--text-muted);
    font-weight: 400;
  }

  &:disabled {
    cursor: not-allowed;
    color: var(--text-secondary);
  }

  &[type='color'] {
    padding: 0;
    height: 42px;
    cursor: pointer;
    border: none;
    background: transparent;
  }

  &[type='date'] {
    color-scheme: dark;
  }

  &.is-small {
    padding: 4px 10px;
    font-size: 13px;
  }
}

.has-prefix .input-field {
  padding-left: 6px;
}

.has-suffix .input-field {
  padding-right: 6px;
}
</style>
