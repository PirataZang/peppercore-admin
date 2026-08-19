<template>
  <div class="page fade-in">
    <PageHeader
      title="Configurações"
      subtitle="Gerencie integrações, preferências e permissões do sistema"
      icon="fa-solid fa-gear"
    />

    <Tabs v-model="activeTab" :tabs="tabs">
      <IntegrationsTab v-if="activeTab === 'integrations'" />
      <NotificationsTab v-else-if="activeTab === 'notifications'" />
      <PermissionsTab v-else-if="activeTab === 'permissions'" />
      <GeneralTab v-else-if="activeTab === 'general'" />
    </Tabs>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import Tabs from '@/components/ui/Tabs.vue'
import IntegrationsTab from './tabs/IntegrationsTab.vue'
import GeneralTab from './tabs/GeneralTab.vue'
import NotificationsTab from './tabs/NotificationsTab.vue'
import PermissionsTab from './tabs/PermissionsTab.vue'

const tabs = [
  { key: 'integrations', label: 'Integrações', icon: 'fa-solid fa-plug' },
  { key: 'general', label: 'Geral', icon: 'fa-solid fa-sliders' },
  { key: 'notifications', label: 'Notificações', icon: 'fa-solid fa-bell' },
  { key: 'permissions', label: 'Usuários / Permissões', icon: 'fa-solid fa-user-shield' },
]

const activeTab = ref('integrations')
</script>
