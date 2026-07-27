<template>
  <button
    v-if="type === 'button'"
    :type="nativeType"
    class="button-style"
    :class="{ disabled }"
    :style="buttonStyle"
    :disabled="disabled"
    @click="handleClick"
  >
    <slot>{{ label }}</slot>
  </button>

  <a
    v-else-if="type === 'link'"
    :href="href || '#'"
    class="button-style"
    :class="{ disabled }"
    :style="buttonStyle"
    @click="handleClick"
  >
    <slot>{{ label }}</slot>
  </a>

  <router-link
    v-else
    :to="href || '/'"
    class="button-style"
    :class="{ disabled }"
    :style="buttonStyle"
    @click="handleClick"
  >
    <slot>{{ label }}</slot>
  </router-link>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: {
    type: String,
    default: '',
  },
  type: {
    type: String,
    default: 'button',
  },
  nativeType: {
    type: String,
    default: 'button',
  },
  href: {
    type: String,
    default: '',
  },
  color: {
    type: String,
    default: '#ff4d4d',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})

const buttonStyle = computed(() => ({
  '--btn-color': props.color,
}))

const handleClick = (event) => {
  if (props.disabled) {
    event.preventDefault()
    event.stopPropagation()
  }
}
</script>

<style scoped lang="scss">
.button-style {
  --btn-color: #ff4d4d;

  padding: 10px 18px;
  margin: 4px;
  background: var(--btn-color);
  color: #fff;
  border: none;
  border-radius: 10px;
  cursor: pointer;

  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;

  font-size: 14px;
  font-weight: 500;
  text-decoration: none;
  font-family: var(--font-sans);

  box-shadow: 0 6px 14px rgba(0, 0, 0, 0.18);
  transition:
    background 0.25s ease,
    transform 0.12s ease,
    box-shadow 0.12s ease,
    opacity 0.2s ease;

  &:hover:not(.disabled):not(:disabled) {
    filter: brightness(1.08);
    transform: translateY(-1px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.22);
  }

  &:active:not(.disabled):not(:disabled) {
    transform: translateY(0);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
  }

  &:focus-visible {
    outline: 3px solid rgba(255, 77, 77, 0.4);
    outline-offset: 2px;
  }

  &.disabled,
  &:disabled {
    filter: grayscale(25%);
    cursor: not-allowed;
    opacity: 0.7;
    box-shadow: none;
    transform: none;
    pointer-events: none;
  }
}
</style>
