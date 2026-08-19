<template>
  <div class="tab-panel">
    <div class="tab-panel__header">
      <h3>Notificações</h3>
      <div class="tab-panel__actions">
        <Button variant="ghost" size="sm" icon="fa-solid fa-rotate" label="Atualizar" @click="loadNotifications" />
        <Button
          v-if="unreadCount"
          variant="secondary"
          size="sm"
          icon="fa-solid fa-check-double"
          label="Marcar todas como lidas"
          @click="markAllAsRead"
        />
      </div>
    </div>

    <div v-if="loading" class="tab-loading">
      <i class="fa-solid fa-spinner fa-spin" /> Carregando notificações...
    </div>

    <template v-else>
      <p v-if="!notifications.length" class="empty-note">Nenhuma notificação por enquanto.</p>

      <ul v-else class="notif-list">
        <li
          v-for="n in notifications"
          :key="n.id"
          class="notif-item"
          :class="{ 'is-unread': !n.read_at }"
        >
          <span class="notif-dot" aria-hidden="true" />
          <div class="notif-body">
            <p class="notif-message">{{ n.data?.message || n.data?.title || 'Notificação' }}</p>
            <span class="notif-date">{{ formatDate(n.created_at) }}</span>
          </div>
          <Button
            v-if="!n.read_at"
            variant="ghost"
            size="sm"
            icon="fa-solid fa-check"
            @click="markAsRead(n)"
          />
        </li>
      </ul>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { apiFetch } from '@/services/api'
import Button from '@/components/utils/Button.vue'
import { swal } from '@/plugins/swal'

const notifications = ref([])
const loading = ref(false)

const unreadCount = computed(() => notifications.value.filter((n) => !n.read_at).length)

const formatDate = (value) => {
  if (!value) return '—'
  return new Date(value).toLocaleString('pt-BR')
}

const loadNotifications = async () => {
  loading.value = true
  try {
    const response = await apiFetch('/api/notifications')
    notifications.value = await response.json()
  } finally {
    loading.value = false
  }
}

const markAsRead = async (n) => {
  try {
    await apiFetch(`/api/notifications/${n.id}/read`, { method: 'POST' })
    n.read_at = new Date().toISOString()
  } catch (err) {
    swal.toastError('Falha ao marcar notificação como lida.')
  }
}

const markAllAsRead = async () => {
  try {
    await apiFetch('/api/notifications/read-all', { method: 'POST' })
    notifications.value.forEach((n) => { n.read_at = n.read_at || new Date().toISOString() })
    swal.toastSuccess('Notificações marcadas como lidas!')
  } catch (err) {
    swal.toastError('Falha ao marcar notificações como lidas.')
  }
}

onMounted(loadNotifications)
</script>

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
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 10px;

  h3 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--color-text);
  }
}

.tab-panel__actions {
  display: flex;
  gap: 8px;
}

.tab-loading,
.empty-note {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 24px;
  color: var(--color-text-muted);
  background: var(--color-bg-muted);
  border-radius: 12px;
  font-size: 0.875rem;
}

.notif-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.notif-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px;
  border-radius: 12px;

  &.is-unread {
    background: var(--color-bg-muted);
  }
}

.notif-dot {
  flex: 0 0 8px;
  width: 8px;
  height: 8px;
  margin-top: 6px;
  border-radius: 50%;
  background: transparent;

  .is-unread & {
    background: var(--color-primary);
  }
}

.notif-body {
  flex: 1 1 auto;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.notif-message {
  font-size: 0.875rem;
  color: var(--color-text);
}

.notif-date {
  font-size: 0.75rem;
  color: var(--color-text-muted);
}
</style>
