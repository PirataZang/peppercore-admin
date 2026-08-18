<template>
  <div class="page fade-in">
    <PageHeader
      title="Clientes"
      subtitle="Cadastro de clientes atendidos pela PepperCore."
      icon="fa-solid fa-address-book"
    />

    <div class="list-toolbar">
      <Input
        class="search-input"
        type="search"
        v-model="searchQuery"
        @input="debounceSearch"
        placeholder="Buscar clientes por nome, e-mail ou telefone..."
      >
        <template #prefix>
          <i class="fa-solid fa-magnifying-glass" aria-hidden="true" />
        </template>
      </Input>

      <div class="list-toolbar__actions">
        <Button
          variant="create"
          icon="fa-solid fa-plus"
          label="Incluir"
          @click="$router.push('/client/form')"
        />
        <Button
          variant="edit"
          icon="fa-solid fa-pen-to-square"
          label="Alterar"
          :disabled="selectedClients.length !== 1"
          @click="editSelected"
        />
        <Button
          variant="danger"
          icon="fa-solid fa-trash-can"
          :label="selectedClients.length > 0 ? `Excluir (${selectedClients.length})` : 'Excluir'"
          :disabled="selectedClients.length === 0"
          @click="deleteSelectedClients"
        />
      </div>
    </div>

    <div class="grid-wrap">
      <AgGrid
        :rowData="clientsData"
        :columnDefs="columnDefs"
        :currentPage="currentPage"
        :pageSize="pageSize"
        :totalRows="totalRows"
        :selectable="true"
        @update:page="handlePageChange"
        @update:pageSize="handlePageSizeChange"
        @update:selection="handleSelectionChange"
        @row-click="handleRowClick"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AgGrid from '@/components/utils/AgGrid.vue'
import Button from '@/components/utils/Button.vue'
import Input from '@/components/utils/Input.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import { apiFetch } from '@/services/api'
import { swal } from '@/plugins/swal'

const router = useRouter()

const clientsData = ref([])
const totalRows = ref(0)
const currentPage = ref(1)
const pageSize = ref(10)
const searchQuery = ref('')
const selectedClients = ref([])
let searchTimeout = null

const columnDefs = ref([
  { field: 'name', headerName: 'Nome', flex: 1, sortable: true, filter: true },
  { field: 'phone', headerName: 'Telefone', width: 160, sortable: true, filter: true },
  { field: 'email', headerName: 'E-mail', flex: 1, sortable: true, filter: true },
  { field: 'address', headerName: 'Endereço', flex: 1, sortable: true, filter: true },
])

const fetchClients = async () => {
  try {
    const url = `/api/clients?page=${currentPage.value}&per_page=${pageSize.value}&search=${searchQuery.value}`
    const response = await apiFetch(url).then((res) => res.json())
    clientsData.value = response.data
    totalRows.value = response.total
    selectedClients.value = []
  } catch (err) {
    console.error('Erro ao buscar lista de clientes:', err)
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    fetchClients()
  }, 400)
}

const handlePageChange = (page) => {
  currentPage.value = page
  fetchClients()
}

const handlePageSizeChange = (size) => {
  pageSize.value = size
  currentPage.value = 1
  fetchClients()
}

const handleSelectionChange = (selection) => {
  selectedClients.value = selection
}

const handleRowClick = (rowData) => {
  router.push(`/client/form/${rowData.id}`)
}

const editSelected = () => {
  if (selectedClients.value.length !== 1) return
  router.push(`/client/form/${selectedClients.value[0].id}`)
}

const deleteSelectedClients = async () => {
  if (selectedClients.value.length === 0) return

  const targets = [...selectedClients.value]
  const ok = await swal.confirmDelete({
    count: targets.length,
    entity: 'cliente',
    entityPlural: 'clientes',
  })
  if (!ok) return

  try {
    for (const client of targets) {
      await apiFetch(`/api/clients/${client.id}`, { method: 'DELETE' }).then((res) => res.json())
    }
    await fetchClients()
    swal.toastSuccess(
      targets.length > 1 ? 'Clientes excluídos com sucesso!' : 'Cliente excluído com sucesso!',
    )
  } catch (err) {
    console.error('Falha ao excluir clientes:', err)
    swal.toastError('Falha ao excluir cliente(s).')
  }
}

onMounted(fetchClients)
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
