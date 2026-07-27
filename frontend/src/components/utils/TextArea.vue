<template>
  <div class="inputWrapper" :style="{ width }">
    <label v-if="label" class="inputLabel">{{ label }}</label>

    <div class="inputGroup" :class="{ isDisabled: disabled }">
      <textarea
        v-model="model"
        class="inputField"
        :class="{ isSmall: small }"
        :name="inputName"
        :placeholder="placeholder"
        :disabled="disabled"
        :rows="rows"
        v-bind="inputAttrs"
      />
    </div>
  </div>
</template>

<script setup>
import { computed, useAttrs } from 'vue'

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
  rows: {
    type: Number,
    default: 4,
  },
})

const model = defineModel({ default: '' })
const attrs = useAttrs()

const generatedName = 'txt_' + Math.random().toString(36).slice(2, 9)
const inputName = computed(() => props.name || generatedName)

const inputAttrs = computed(() => {
  const { class: _class, ...rest } = attrs
  return rest
})
</script>

<style scoped lang="scss">
.inputWrapper {
  display: flex;
  flex-direction: column;
  gap: 2px;

  .inputLabel {
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 4px;
  }

  .inputGroup {
    position: relative;
    display: flex;
    align-items: center;
    transition: all 0.2s ease;

    &.isDisabled {
      opacity: 0.7;
      cursor: not-allowed;
    }

    &:not(.isDisabled) {
      cursor: text;
    }

    .inputField {
      width: 100%;
      padding: 0.6rem 0.85rem;
      border: 1px solid var(--border-color);
      border-radius: 12px;
      background-color: rgba(11, 15, 25, 0.5);
      color: var(--text-primary);
      outline: none;
      transition: all 0.2s ease;
      font-size: 14px;
      resize: vertical;
      font-family: inherit;

      &::placeholder {
        color: var(--text-muted);
      }

      &:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(255, 77, 77, 0.12);
      }

      &:disabled {
        background-color: rgba(11, 15, 25, 0.35);
        cursor: not-allowed;
      }
    }

    .isSmall {
      padding: 1px;
      padding-left: 5px;
    }
  }
}
</style>
