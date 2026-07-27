<template>
  <div class="tab-content fade-in">
    <div class="welcome-banner glass-panel">
      <div class="banner-content">
        <h2>Gerenciamento de Usuários</h2>
        <p>Lista oficial de administradores e usuários do sistema PepperCore. Criados, alterados e persistidos no
          PostgreSQL.</p>
      </div>
      <span class="banner-emoji">👥</span>
    </div>

    <!-- Barra de Ações -->
    <div class="flex justify-between items-center flex-wrap gap-4">
      <div class="search-bar glass-panel px-4 py-2 flex items-center gap-2 w-96">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-secondary">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input type="text" v-model="searchQuery" @input="debounceSearch"
          placeholder="Buscar usuários por nome ou email..."
          class="bg-transparent border-none outline-none text-white w-full text-sm" />
      </div>

      <div class="flex gap-2">
        <button v-if="selectedUsers.length > 0" @click="deleteSelectedUsers" class="action-btn-danger">
          <i class="fa-solid fa-trash-can mr-2"></i> Excluir Selecionados ({{ selectedUsers.length }})
        </button>
        <button @click="$router.push('/user/form')" class="action-btn-primary">
          <i class="fa-solid fa-user-plus mr-2"></i> Adicionar Usuário
        </button>
      </div>
    </div>

    <!-- Grid Card Container -->
    <div class="grid-card-container glass-panel-glow">
      <AgGrid :rowData="usersData" :columnDefs="columnDefs" :currentPage="currentPage" :pageSize="pageSize"
        :totalRows="totalRows" :selectable="true" @update:page="handlePageChange"
        @update:pageSize="handlePageSizeChange" @update:selection="handleSelectionChange" @row-click="handleRowClick" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AgGrid from '../../components/utils/AgGrid.vue'
import { apiFetch } from '@/services/api'

const router = useRouter()

const usersData = ref([])
const totalRows = ref(0)
const currentPage = ref(1)
const pageSize = ref(10)
const searchQuery = ref('')
const selectedUsers = ref([])
let searchTimeout = null

// Definição das colunas
const columnDefs = ref([
  { field: 'id', headerName: 'ID', width: 80, sortable: true, filter: 'agNumberColumnFilter', cellClass: 'cell-center' },
  { field: 'name', headerName: 'Nome Completo', flex: 1, sortable: true, filter: true },
  { field: 'email', headerName: 'E-mail Corporativo', flex: 1, sortable: true, filter: true },
  {
    field: 'created_at',
    headerName: 'Data de Cadastro',
    type: 'datetime',
    width: 200,
    sortable: true
  }
])

const fetchUsers = async () => {
  try {
    const url = `/api/users?page=${currentPage.value}&per_page=${pageSize.value}&search=${searchQuery.value}`
    const response = await apiFetch(url).then(res => res.json())

    usersData.value = response.data
    totalRows.value = response.total
    selectedUsers.value = [] // limpa seleção anterior
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
  // Ao clicar em uma linha, redireciona para o formulário de edição com o ID correspondente
  router.push(`/user/form/${rowData.id}`)
}

const deleteSelectedUsers = async () => {
  if (selectedUsers.value.length === 0) return

  const confirmDelete = confirm(`Deseja realmente excluir ${selectedUsers.value.length} usuário(s) selecionado(s)?`)
  if (!confirmDelete) return

  try {
    for (const user of selectedUsers.value) {
      await apiFetch(`/api/users/${user.id}`, { method: 'DELETE' }).then(res => res.json())
    }
    fetchUsers()
  } catch (err) {
    console.error('Falha ao excluir usuários:', err)
  }
}

onMounted(() => {
  fetchUsers()
})
</script>

<style scoped>
.tab-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.grid-card-container {
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border-radius: 16px;
}

.fade-in {
  animation: fadeIn 0.4s ease-in-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(4px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.welcome-banner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 32px;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(20, 30, 54, 0.8) 0%, rgba(255, 77, 77, 0.05) 100%);
}

.banner-content h2 {
  font-size: 1.5rem;
  margin-bottom: 8px;
  background: linear-gradient(90deg, #ffffff, #dcdcdc);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.banner-content p {
  color: var(--text-secondary);
  font-size: 0.95rem;
  max-width: 600px;
  line-height: 1.5;
}

.banner-emoji {
  font-size: 3rem;
  filter: drop-shadow(0 0 10px rgba(99, 102, 241, 0.4));
}

.search-bar {
  display: flex;
  align-items: center;
  background: rgba(20, 30, 54, 0.6);
  backdrop-filter: blur(16px);
  border: 1px solid var(--border-color);
  border-radius: 12px;
}

.action-btn-primary {
  background: var(--primary);
  color: white;
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  font-family: var(--font-sans);
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 12px var(--primary-glow);
  transition: var(--transition-fast);
}

.action-btn-primary:hover {
  background: var(--primary-hover);
  transform: translateY(-1px);
}

.action-btn-danger {
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  font-family: var(--font-sans);
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  transition: var(--transition-fast);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);
}

.action-btn-danger:hover {
  background: #dc2626;
  transform: translateY(-1px);
}

.flex {
  display: flex;
}

.justify-between {
  justify-content: space-between;
}

.items-center {
  align-items: center;
}

.gap-2 {
  gap: 8px;
}

.gap-4 {
  gap: 16px;
}

.w-96 {
  width: 384px;
}

.w-full {
  width: 100%;
}

.px-4 {
  padding-left: 16px;
  padding-right: 16px;
}

.py-2 {
  padding-top: 8px;
  padding-bottom: 8px;
}

.p-4 {
  padding: 16px;
}

.text-sm {
  font-size: 0.875rem;
}

.mr-2 {
  margin-right: 8px;
}

.text-secondary {
  color: var(--secondary);
}

/* AgGrid Dark Theme Customizations */
:deep(.ag-theme-quartz) {
  --ag-background-color: var(--bg-card);
  --ag-header-background-color: var(--bg-secondary);
  --ag-border-color: var(--border-color);
  --ag-header-foreground-color: var(--text-primary);
  --ag-foreground-color: var(--text-primary);
  --ag-data-color: var(--text-primary);
  --ag-row-hover-color: rgba(255, 255, 255, 0.05);
  --ag-selected-row-background-color: rgba(255, 77, 77, 0.15);
  --ag-odd-row-background-color: transparent;

  border-radius: 16px;
  overflow: hidden;
  border: 1px solid var(--border-color);
  font-family: var(--font-sans);
}

:deep(.custom-pagination) {
  background: var(--bg-secondary) !important;
  border-top: 1px solid var(--border-color) !important;
  color: var(--text-secondary) !important;
}

:deep(.custom-pagination .highlight) {
  color: var(--primary) !important;
}

:deep(.custom-pagination .pag-btn) {
  background: var(--bg-card) !important;
  border: 1px solid var(--border-color) !important;
  color: var(--text-primary) !important;
}

:deep(.custom-pagination .pag-btn:hover:not(:disabled)) {
  background: rgba(255, 255, 255, 0.08) !important;
  border-color: var(--primary) !important;
}

:deep(.custom-pagination .pag-btn.page-num.active) {
  background: var(--primary) !important;
  border-color: var(--primary) !important;
  color: white !important;
}

:deep(.custom-pagination .size-select) {
  background: var(--bg-card) !important;
  border: 1px solid var(--border-color) !important;
  color: var(--text-primary) !important;
}
</style>
