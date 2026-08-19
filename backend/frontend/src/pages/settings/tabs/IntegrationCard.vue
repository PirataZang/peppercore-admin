<template>
  <div class="integration-card">
    <div class="integration-card__top">
      <div class="integration-card__logo" :style="{ background: logoBg }">
        <slot name="logo" />
      </div>
      <Switch :model-value="isActive" :disabled="switchDisabled" @update:model-value="$emit('toggle', $event)" />
    </div>

    <div class="integration-card__body">
      <h4>{{ title }}</h4>
      <p>{{ description }}</p>
    </div>

    <div class="integration-card__footer">
      <span class="integration-card__status" :class="{ 'is-active': isActive }">
        <i class="fa-solid fa-circle" aria-hidden="true" />
        {{ isActive ? 'Ativo' : (hasCredentials ? 'Inativo' : 'Não configurado') }}
      </span>
      <Button variant="secondary" size="sm" label="Configurar" icon="fa-solid fa-gear" @click="$emit('configure')" />
    </div>
  </div>
</template>

<script setup>
import Switch from '@/components/utils/Switch.vue'
import Button from '@/components/utils/Button.vue'

defineProps({
  title: { type: String, required: true },
  description: { type: String, default: '' },
  isActive: { type: Boolean, default: false },
  hasCredentials: { type: Boolean, default: false },
  switchDisabled: { type: Boolean, default: false },
  logoBg: { type: String, default: '#f1f5f9' },
})

defineEmits(['configure', 'toggle'])
</script>

<style scoped lang="scss">
.integration-card {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 20px;
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 16px;
  box-shadow: var(--shadow-sm);
  transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;

  &:hover {
    border-color: var(--color-border-strong);
    box-shadow: var(--shadow-md);
    transform: translateY(-1px);
  }
}

.integration-card__top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}

.integration-card__logo {
  width: 48px;
  height: 48px;
  display: grid;
  place-items: center;
  border-radius: 12px;
  overflow: hidden;

  :deep(svg),
  :deep(img) {
    width: 28px;
    height: 28px;
  }
}

.integration-card__body {
  display: flex;
  flex-direction: column;
  gap: 4px;

  h4 {
    font-size: 0.9375rem;
    font-weight: 700;
    color: var(--color-text);
  }

  p {
    font-size: 0.8125rem;
    color: var(--color-text-muted);
    line-height: 1.5;
  }
}

.integration-card__footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding-top: 4px;
  border-top: 1px solid var(--color-border);
  margin-top: 2px;
  padding-top: 14px;
}

.integration-card__status {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-text-muted);

  i {
    font-size: 0.5rem;
    color: var(--color-text-faint);
  }

  &.is-active {
    color: var(--color-success);

    i {
      color: var(--color-success);
    }
  }
}
</style>
