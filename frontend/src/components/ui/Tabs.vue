<template>
  <div class="tabs">
    <div class="tabs__list" role="tablist">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        type="button"
        role="tab"
        class="tabs__trigger"
        :class="{ 'is-active': modelValue === tab.key }"
        :aria-selected="modelValue === tab.key"
        @click="$emit('update:modelValue', tab.key)"
      >
        <i v-if="tab.icon" :class="tab.icon" aria-hidden="true" />
        {{ tab.label }}
      </button>
    </div>

    <div class="tabs__panel" role="tabpanel">
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  tabs: { type: Array, required: true },
  modelValue: { type: String, required: true },
})

defineEmits(['update:modelValue'])
</script>

<style scoped lang="scss">
.tabs__list {
  display: flex;
  gap: 4px;
  border-bottom: 1px solid var(--color-border);
  margin-bottom: 20px;
  overflow-x: auto;
}

.tabs__trigger {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border: none;
  background: transparent;
  color: var(--color-text-muted);
  font-family: var(--font-sans);
  font-size: 0.875rem;
  font-weight: 600;
  white-space: nowrap;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: color 0.15s ease, border-color 0.15s ease;

  i {
    font-size: 0.9em;
  }

  &:hover {
    color: var(--color-text);
  }

  &.is-active {
    color: var(--color-primary);
    border-bottom-color: var(--color-primary);
  }
}
</style>
