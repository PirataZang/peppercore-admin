<template>
  <div class="tab-panel">
    <div class="tab-panel__header">
      <h3>Geral</h3>
    </div>

    <p class="empty-note">Em breve.</p>
  </div>
</template>

<style scoped lang="scss">
.tab-panel {
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 16px;
  box-shadow: var(--shadow-sm);
  padding: 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.tab-panel__header {
  h3 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--color-text);
  }
}

.empty-note {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  color: var(--color-text-muted);
  background: var(--color-bg-muted);
  border-radius: 12px;
  font-size: 0.875rem;
}
</style>
