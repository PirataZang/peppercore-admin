<template>
  <span class="status-badge" :class="`status-badge--${variant}`">
    <span class="status-badge__dot" aria-hidden="true" />
    {{ label }}
  </span>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
  /** success | warning | danger | info | neutral */
  variant: { type: String, default: 'neutral' },
})
</script>

<style scoped lang="scss">
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: var(--radius-full, 9999px);
  font-size: 0.75rem;
  font-weight: 600;
  white-space: nowrap;
  line-height: 1.2;
}

.status-badge__dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}

.status-badge--success {
  background: var(--color-success-soft);
  color: var(--color-success);
  .status-badge__dot { background: var(--color-success); }
}

.status-badge--warning {
  background: var(--color-warning-soft);
  color: var(--color-warning);
  .status-badge__dot { background: var(--color-warning); }
}

.status-badge--danger {
  background: var(--color-danger-soft);
  color: var(--color-danger);
  .status-badge__dot { background: var(--color-danger); }
}

.status-badge--info {
  background: var(--color-info-soft);
  color: var(--color-info);
  .status-badge__dot { background: var(--color-info); }
}

.status-badge--neutral {
  background: var(--color-bg-muted);
  color: var(--color-text-muted);
  .status-badge__dot { background: var(--color-text-faint); }
}
</style>
