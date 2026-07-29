<template>
  <div class="page fade-in">
    <PageHeader
      title="Gerenciamento de Usuários"
      subtitle="Lista oficial de administradores e usuários do sistema PepperCore."
      icon="fa-solid fa-users"
    />

    <div class="toolbar">
      <label class="search-field">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true" />
        <input
          type="search"
          v-model="searchQuery"
          @input="debounceSearch"
          placeholder="Buscar usuários por nome ou email..."
        />
      </label>

      <div class="toolbar__actions">
        <Button
          v-if="selectedUsers.length === 1"
          variant="edit"
          icon="fa-solid fa-pen-to-square"
          label="Editar"
          @click="editSelected"
        />
        <Button
          v-if="selectedUsers.length > 0"
          variant="danger"
          icon="fa-solid fa-trash-can"
          :label="`Excluir (${selectedUsers.length})`"
          @click="deleteSelectedUsers"
        />
        <Button
          variant="create"
          icon="fa-solid fa-plus"
          label="Incluir"
          @click="$router.push('/user/form')"
        />
      </div>
    </div>

    <div class="grid-wrap">
      <AgGrid
        :rowData="usersData"
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
import AgGrid from '../../components/utils/AgGrid.vue'
import Button from '@/components/utils/Button.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import { apiFetch } from '@/services/api'

const router = useRouter()

const usersData = ref([])
const totalRows = ref(0)
const currentPage = ref(1)
const pageSize = ref(10)
const searchQuery = ref('')
const selectedUsers = ref([])
let searchTimeout = null

const columnDefs = ref([
  {
    field: 'id',
    headerName: 'ID',
    width: 80,
    sortable: true,
    filter: 'agNumberColumnFilter',
    cellClass: 'cell-center',
  },
  { field: 'name', headerName: 'Nome Completo', flex: 1, sortable: true, filter: true },
  { field: 'email', headerName: 'E-mail Corporativo', flex: 1, sortable: true, filter: true },
  {
    field: 'created_at',
    headerName: 'Data de Cadastro',
    type: 'datetime',
    width: 200,
    sortable: true,
  },
])

const fetchUsers = async () => {
  try {
    const url = `/api/users?page=${currentPage.value}&per_page=${pageSize.value}&search=${searchQuery.value}`
    const response = await apiFetch(url).then((res) => res.json())
    usersData.value = response.data
    totalRows.value = response.total
    selectedUsers.value = []
  } catch (err) {
    console.error('Erro ao buscar lista de usuários:', err)
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    fetchUsers()
  }, 400)
}

const handlePageChange = (page) => {
  currentPage.value = page
  fetchUsers()
}

const handlePageSizeChange = (size) => {
  pageSize.value = size
  currentPage.value = 1
  fetchUsers()
}

const handleSelectionChange = (selection) => {
  selectedUsers.value = selection
}

const handleRowClick = (rowData) => {
  router.push(`/user/form/${rowData.id}`)
}

const editSelected = () => {
  if (selectedUsers.value.length !== 1) return
  router.push(`/user/form/${selectedUsers.value[0].id}`)
}

const deleteSelectedUsers = async () => {
  if (selectedUsers.value.length === 0) return

  const confirmDelete = confirm(
    `Deseja realmente excluir ${selectedUsers.value.length} usuário(s) selecionado(s)?`,
  )
  if (!confirmDelete) return

  try {
    for (const user of selectedUsers.value) {
      await apiFetch(`/api/users/${user.id}`, { method: 'DELETE' }).then((res) => res.json())
    }
    fetchUsers()
  } catch (err) {
    console.error('Falha ao excluir usuários:', err)
  }
}

onMounted(fetchUsers)
</script>

<style scoped>
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
