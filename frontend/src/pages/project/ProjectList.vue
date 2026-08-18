<template>
  <div class="page fade-in">
    <PageHeader
      title="Projetos"
      subtitle="Projetos e clientes atendidos: tipo, mensalidade e situação de pagamento."
      icon="fa-solid fa-diagram-project"
    />

    <div class="list-toolbar">
      <Input
        class="search-input"
        type="search"
        v-model="searchQuery"
        @input="debounceSearch"
        placeholder="Buscar por projeto, cliente ou domínio..."
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
          @click="$router.push('/project/form')"
        />
        <Button
          variant="edit"
          icon="fa-solid fa-pen-to-square"
          label="Alterar"
          :disabled="selectedProjects.length !== 1"
          @click="editSelected"
        />
        <Button
          variant="danger"
          icon="fa-solid fa-trash-can"
          :label="selectedProjects.length > 0 ? `Excluir (${selectedProjects.length})` : 'Excluir'"
          :disabled="selectedProjects.length === 0"
          @click="deleteSelectedProjects"
        />
      </div>
    </div>

    <div class="grid-wrap">
      <AgGrid
        :rowData="projectsData"
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
import { ref, h, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AgGrid from '@/components/utils/AgGrid.vue'
import Button from '@/components/utils/Button.vue'
import Input from '@/components/utils/Input.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import { paymentBadge, typeLabel } from '@/config/projectStatus'
import { apiFetch } from '@/services/api'
import { swal } from '@/plugins/swal'

const router = useRouter()

const projectsData = ref([])
const totalRows = ref(0)
const currentPage = ref(1)
const pageSize = ref(10)
const searchQuery = ref('')
const selectedProjects = ref([])
let searchTimeout = null

const columnDefs = ref([
  { field: 'name', headerName: 'Projeto', flex: 1, sortable: true, filter: true },
  {
    field: 'type',
    headerName: 'Tipo',
    width: 120,
    sortable: true,
    filter: true,
    valueFormatter: (params) => typeLabel(params.value),
  },
  { field: 'client_name', headerName: 'Cliente', flex: 1, sortable: true, filter: true },
  { field: 'domain', headerName: 'Domínio', flex: 1, sortable: true, filter: true },
  {
    field: 'monthly_value',
    headerName: 'Mensalidade',
    type: 'money',
    width: 150,
    sortable: true,
  },
  {
    field: 'payment_status',
    headerName: 'Pagamento',
    width: 150,
    sortable: true,
    filter: true,
    cellRenderer: (params) => {
      const { label, variant } = paymentBadge(params.value)
      return h(StatusBadge, { label, variant })
    },
  },
])

const fetchProjects = async () => {
  try {
    const url = `/api/projects?page=${currentPage.value}&per_page=${pageSize.value}&search=${searchQuery.value}`
    const response = await apiFetch(url).then((res) => res.json())
    projectsData.value = response.data
    totalRows.value = response.total
    selectedProjects.value = []
  } catch (err) {
    console.error('Erro ao buscar lista de projetos:', err)
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    fetchProjects()
  }, 400)
}

const handlePageChange = (page) => {
  currentPage.value = page
  fetchProjects()
}

const handlePageSizeChange = (size) => {
  pageSize.value = size
  currentPage.value = 1
  fetchProjects()
}

const handleSelectionChange = (selection) => {
  selectedProjects.value = selection
}

const handleRowClick = (rowData) => {
  router.push(`/project/${rowData.id}`)
}

const editSelected = () => {
  if (selectedProjects.value.length !== 1) return
  router.push(`/project/form/${selectedProjects.value[0].id}`)
}

const deleteSelectedProjects = async () => {
  if (selectedProjects.value.length === 0) return

  const targets = [...selectedProjects.value]
  const ok = await swal.confirmDelete({
    count: targets.length,
    entity: 'projeto',
    entityPlural: 'projetos',
  })
  if (!ok) return

  try {
    for (const project of targets) {
      await apiFetch(`/api/projects/${project.id}`, { method: 'DELETE' }).then((res) => res.json())
    }
    await fetchProjects()
    swal.toastSuccess(
      targets.length > 1 ? 'Projetos excluídos com sucesso!' : 'Projeto excluído com sucesso!',
    )
  } catch (err) {
    console.error('Falha ao excluir projetos:', err)
    swal.toastError('Falha ao excluir projeto(s).')
  }
}

onMounted(fetchProjects)
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
