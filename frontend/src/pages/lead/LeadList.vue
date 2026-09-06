<template>
  <div class="page fade-in">
    <PageHeader
      title="Leads Potenciais"
      subtitle="Empresas encontradas na web por região, ainda sem site ou sistema."
      icon="fa-solid fa-magnifying-glass-location"
    />

    <div class="list-toolbar">
      <Input
        class="search-input"
        type="search"
        v-model="searchQuery"
        @input="debounceSearch"
        placeholder="Buscar leads por nome, telefone ou endereço..."
      >
        <template #prefix>
          <i class="fa-solid fa-magnifying-glass" aria-hidden="true" />
        </template>
      </Input>

      <LeadSearchRequestSelect v-model="requestFilter" @update:modelValue="applyRequestFilter" />

      <div class="list-toolbar__actions">
        <Button
          variant="create"
          icon="fa-solid fa-map-location-dot"
          label="Nova busca"
          @click="showSearchModal = true"
        />
        <Button
          variant="edit"
          icon="fa-solid fa-check"
          :label="`Marcar verificado (${selectedLeads.length})`"
          :disabled="selectedLeads.length === 0"
          @click="markSelectedAsChecked"
        />
        <Button
          variant="danger"
          icon="fa-solid fa-trash-can"
          :label="selectedLeads.length > 0 ? `Excluir (${selectedLeads.length})` : 'Excluir'"
          :disabled="selectedLeads.length === 0"
          @click="deleteSelectedLeads"
        />
      </div>
    </div>

    <div class="grid-wrap">
      <AgGrid
        :rowData="leadsData"
        :columnDefs="columnDefs"
        :currentPage="currentPage"
        :pageSize="pageSize"
        :totalRows="totalRows"
        :selectable="true"
        @update:page="handlePageChange"
        @update:pageSize="handlePageSizeChange"
        @update:selection="handleSelectionChange"
      />
    </div>

    <LeadSearchModal v-model="showSearchModal" @created="fetchLeads" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AgGrid from '@/components/utils/AgGrid.vue'
import Button from '@/components/utils/Button.vue'
import Input from '@/components/utils/Input.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import LeadSearchRequestSelect from '@/components/utils/LeadSearchRequestSelect.vue'
import LeadSearchModal from './LeadSearchModal.vue'
import { apiFetch } from '@/services/api'
import { swal } from '@/plugins/swal'

const leadsData = ref([])
const totalRows = ref(0)
const currentPage = ref(1)
const pageSize = ref(10)
const searchQuery = ref('')
const requestFilter = ref(null)
const selectedLeads = ref([])
const showSearchModal = ref(false)
let searchTimeout = null

const columnDefs = ref([
  { field: 'name', headerName: 'Nome', flex: 1, sortable: true, filter: true },
  { field: 'phone', headerName: 'Telefone', width: 160, sortable: true, filter: true },
  { field: 'address', headerName: 'Endereço', flex: 1, sortable: true, filter: true },
  { field: 'check', headerName: 'Verificado', type: 'boolean', width: 130, sortable: true },
])

const fetchLeads = async () => {
  try {
    const params = new URLSearchParams({
      page: currentPage.value,
      per_page: pageSize.value,
      search: searchQuery.value,
    })
    if (requestFilter.value) params.set('lead_search_request_id', requestFilter.value)

    const response = await apiFetch(`/api/potential-leads?${params}`).then((res) => res.json())
    leadsData.value = response.data
    totalRows.value = response.total
    selectedLeads.value = []
  } catch (err) {
    console.error('Erro ao buscar leads potenciais:', err)
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    fetchLeads()
  }, 400)
}

const applyRequestFilter = () => {
  currentPage.value = 1
  fetchLeads()
}

const handlePageChange = (page) => {
  currentPage.value = page
  fetchLeads()
}

const handlePageSizeChange = (size) => {
  pageSize.value = size
  currentPage.value = 1
  fetchLeads()
}

const handleSelectionChange = (selection) => {
  selectedLeads.value = selection
}

const markSelectedAsChecked = async () => {
  if (selectedLeads.value.length === 0) return

  try {
    for (const lead of selectedLeads.value) {
      await apiFetch(`/api/potential-leads/${lead.id}`, {
        method: 'PUT',
        body: JSON.stringify({ check: true }),
      })
    }
    await fetchLeads()
    swal.toastSuccess('Lead(s) marcados como verificados!')
  } catch (err) {
    console.error('Falha ao marcar leads como verificados:', err)
    swal.toastError('Falha ao marcar lead(s) como verificados.')
  }
}

const deleteSelectedLeads = async () => {
  if (selectedLeads.value.length === 0) return

  const targets = [...selectedLeads.value]
  const ok = await swal.confirmDelete({
    count: targets.length,
    entity: 'lead',
    entityPlural: 'leads',
  })
  if (!ok) return

  try {
    for (const lead of targets) {
      await apiFetch(`/api/potential-leads/${lead.id}`, { method: 'DELETE' }).then((res) => res.json())
    }
    await fetchLeads()
    swal.toastSuccess(targets.length > 1 ? 'Leads excluídos com sucesso!' : 'Lead excluído com sucesso!')
  } catch (err) {
    console.error('Falha ao excluir leads:', err)
    swal.toastError('Falha ao excluir lead(s).')
  }
}

onMounted(fetchLeads)
</script>

<style scoped>
.list-toolbar {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  gap: 12px;
  width: 100%;
}

.list-toolbar .search-input {
  width: 100%;
  max-width: 420px;
}

.list-toolbar__actions {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
}

.grid-wrap {
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 16px;
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}

:deep(.ag-theme-quartz) {
  --ag-background-color: #ffffff;
  --ag-header-background-color: #f8fafc;
  --ag-border-color: #e2e8f0;
  --ag-header-foreground-color: #0f172a;
  --ag-foreground-color: #0f172a;
  --ag-data-color: #0f172a;
  --ag-row-hover-color: #f8fafc;
  --ag-selected-row-background-color: #fff1f2;
  --ag-odd-row-background-color: #ffffff;
  border: 0;
  font-family: var(--font-sans);
}

:deep(.custom-pagination) {
  background: #f8fafc !important;
  border-top: 1px solid #e2e8f0 !important;
  color: #64748b !important;
}

:deep(.custom-pagination .highlight) {
  color: var(--color-primary) !important;
}

:deep(.custom-pagination .pag-btn) {
  background: #ffffff !important;
  border: 1px solid #e2e8f0 !important;
  color: #0f172a !important;
}

:deep(.custom-pagination .pag-btn:hover:not(:disabled)) {
  background: #f1f5f9 !important;
  border-color: var(--color-primary) !important;
}

:deep(.custom-pagination .pag-btn.page-num.active) {
  background: var(--color-primary) !important;
  border-color: var(--color-primary) !important;
  color: white !important;
}

:deep(.custom-pagination .size-select) {
  background: #ffffff !important;
  border: 1px solid #e2e8f0 !important;
  color: #0f172a !important;
}
</style>
