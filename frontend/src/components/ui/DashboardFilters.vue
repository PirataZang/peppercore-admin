<template>
  <div class="dashboard-filters glass-panel">
    <div class="filter-field">
      <Input v-model="dateFrom" type="date" label="Início" />
    </div>
    <div class="filter-field">
      <Input v-model="dateTo" type="date" label="Fim" />
    </div>
    <div class="filter-field">
      <Select v-model="status" label="Status" placeholder="Todos os status" :options="PAYMENT_STATUS_OPTIONS" />
    </div>
    <div class="filter-field">
      <ClientSelect v-model="clientId" />
    </div>
    <div class="filter-field">
      <ProjectSelect v-model="projectId" />
    </div>
  </div>
</template>

<script setup>
// Bloco de filtros do dashboard: período (padrão = mês atual), status de pagamento,
// cliente e projeto. Emite "change" com o filtro completo sempre que algo muda, para
// a página recarregar o resumo — não guarda nem busca dado nenhum sozinho.
import { ref, watch } from 'vue'
import Input from '@/components/utils/Input.vue'
import Select from '@/components/utils/Select.vue'
import ClientSelect from '@/components/utils/ClientSelect.vue'
import ProjectSelect from '@/components/utils/ProjectSelect.vue'
import { PAYMENT_STATUS_OPTIONS } from '@/config/projectStatus'

const emit = defineEmits(['change'])

const toIsoDate = (date) => date.toISOString().slice(0, 10)
const now = new Date()
const firstDayOfMonth = new Date(now.getFullYear(), now.getMonth(), 1)
const lastDayOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0)

const dateFrom = ref(toIsoDate(firstDayOfMonth))
const dateTo = ref(toIsoDate(lastDayOfMonth))
const status = ref(null)
const clientId = ref(null)
const projectId = ref(null)

watch([dateFrom, dateTo, status, clientId, projectId], () => {
  emit('change', {
    date_from: dateFrom.value || null,
    date_to: dateTo.value || null,
    status: status.value,
    client_id: clientId.value,
    project_id: projectId.value,
  })
}, { immediate: true })
</script>

<style scoped>
.dashboard-filters {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  padding: 18px 20px;
  border-radius: 16px;
}

.filter-field {
  flex: 1 1 180px;
  min-width: 160px;
}
</style>
