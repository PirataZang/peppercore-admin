<template>
  <div class="page fade-in">
    <PageHeader
      title="Transações"
      subtitle="Cobranças e pagamentos, vinculados ou não a um projeto."
      icon="fa-solid fa-file-invoice-dollar"
    />

    <div class="list-toolbar">
      <Input
        class="search-input"
        type="search"
        v-model="searchQuery"
        @input="debounceSearch"
        placeholder="Buscar por projeto ou cliente..."
      >
        <template #prefix>
          <i class="fa-solid fa-magnifying-glass" aria-hidden="true" />
        </template>
      </Input>

      <Select
        class="status-filter"
        v-model="statusFilter"
        placeholder="Todos os status"
        :options="TRANSACTION_STATUS_OPTIONS"
        @change="handleFilterChange"
      />

      <div class="list-toolbar__actions">
        <Button
          variant="create"
          icon="fa-solid fa-plus"
          label="Incluir"
          @click="$router.push('/financial/transactions/form')"
        />
        <Button
          variant="edit"
          icon="fa-solid fa-pen-to-square"
          label="Alterar"
          :disabled="selectedTransactions.length !== 1"
          @click="editSelected"
        />
        <Button
          variant="danger"
          icon="fa-solid fa-trash-can"
          :label="selectedTransactions.length > 0 ? `Excluir (${selectedTransactions.length})` : 'Excluir'"
          :disabled="selectedTransactions.length === 0"
          @click="deleteSelectedTransactions"
        />
      </div>
    </div>

    <div class="grid-wrap">
      <AgGrid
        :rowData="transactionsData"
        :columnDefs="columnDefs"
        :currentPage="currentPage"
        :pageSize="pageSize"
        :totalRows="totalRows"
        :selectable="true"
        :context="gridContext"
        @update:page="handlePageChange"
        @update:pageSize="handlePageSizeChange"
        @update:selection="handleSelectionChange"
        @row-click="handleRowClick"
      />
    </div>

    <TransactionChargeModal
      v-model="chargeModalOpen"
      :transaction="chargingTransaction"
      :charge-url="chargingTransaction ? `/api/transactions/${chargingTransaction.id}/charge` : ''"
      @charged="fetchTransactions"
    />
  </div>
</template>

<script setup>
import { ref, computed, h, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AgGrid from '@/components/utils/AgGrid.vue'
import Button from '@/components/utils/Button.vue'
import Input from '@/components/utils/Input.vue'
import Select from '@/components/utils/Select.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import TransactionCheckoutCell from './TransactionCheckoutCell.vue'
import TransactionRowActions from './TransactionRowActions.vue'
import TransactionChargeModal from './TransactionChargeModal.vue'
import { TRANSACTION_STATUS_OPTIONS, transactionBadge } from '@/config/projectStatus'
import { apiFetch } from '@/services/api'
import { swal } from '@/plugins/swal'

const router = useRouter()

const transactionsData = ref([])
const totalRows = ref(0)
const currentPage = ref(1)
const pageSize = ref(10)
const searchQuery = ref('')
const statusFilter = ref(null)
const selectedTransactions = ref([])
let searchTimeout = null

const formatMoney = (value) => {
  if (!value) return 'R$ 0,00'
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)
}

const formatMonth = (value) => {
  if (!value) return '—'
  const [year, month] = value.split('-')
  return new Date(Number(year), Number(month) - 1, 1).toLocaleDateString('pt-BR', { month: 'short', year: '2-digit' })
}

const chargeModalOpen = ref(false)
const chargingTransaction = ref(null)

const chargeTransaction = (t) => {
  chargingTransaction.value = t
  chargeModalOpen.value = true
}

const gridContext = computed(() => ({
  onCharge: chargeTransaction,
}))

const columnDefs = ref([
  {
    field: 'project',
    headerName: 'Projeto / Cliente',
    flex: 1,
    sortable: false,
    valueGetter: (params) => params.data.project?.name || params.data.payer_name || 'Sem projeto',
  },
  {
    field: 'reference_month',
    headerName: 'Mês',
    width: 110,
    valueFormatter: (params) => formatMonth(params.value),
  },
  {
    field: 'amount',
    headerName: 'Valor',
    width: 140,
    valueFormatter: (params) => formatMoney(params.value),
  },
  {
    field: 'due_date',
    headerName: 'Vencimento',
    type: 'date',
    width: 130,
  },
  {
    field: 'status',
    headerName: 'Status',
    width: 150,
    cellRenderer: (params) => {
      const { label, variant } = transactionBadge(params.data)
      return h(StatusBadge, { label, variant })
    },
  },
  { field: 'gateway', headerName: 'Gateway', width: 130 },
  {
    field: 'checkout_link',
    headerName: 'Link de Pagamento',
    width: 170,
    sortable: false,
    filter: false,
    cellRenderer: TransactionCheckoutCell,
  },
  {
    field: 'actions',
    headerName: '',
    width: 60,
    sortable: false,
    filter: false,
    cellRenderer: TransactionRowActions,
  },
])

const fetchTransactions = async () => {
  try {
    const params = new URLSearchParams({
      page: currentPage.value,
      per_page: pageSize.value,
      search: searchQuery.value,
    })
    if (statusFilter.value) params.set('status', statusFilter.value)

    const response = await apiFetch(`/api/transactions?${params}`).then((res) => res.json())
    transactionsData.value = response.data
    totalRows.value = response.total
    selectedTransactions.value = []
  } catch (err) {
    console.error('Erro ao buscar transações:', err)
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    fetchTransactions()
  }, 400)
}

const handleFilterChange = () => {
  currentPage.value = 1
  fetchTransactions()
}

const handlePageChange = (page) => {
  currentPage.value = page
  fetchTransactions()
}

const handlePageSizeChange = (size) => {
  pageSize.value = size
  currentPage.value = 1
  fetchTransactions()
}

const handleSelectionChange = (selection) => {
  selectedTransactions.value = selection
}

const handleRowClick = (rowData) => {
  router.push(`/financial/transactions/form/${rowData.id}`)
}

const editSelected = () => {
  if (selectedTransactions.value.length !== 1) return
  router.push(`/financial/transactions/form/${selectedTransactions.value[0].id}`)
}

const deleteSelectedTransactions = async () => {
  if (selectedTransactions.value.length === 0) return

  const targets = [...selectedTransactions.value]
  const ok = await swal.confirmDelete({
    count: targets.length,
    entity: 'transação',
    entityPlural: 'transações',
  })
  if (!ok) return

  try {
    for (const transaction of targets) {
      await apiFetch(`/api/transactions/${transaction.id}`, { method: 'DELETE' }).then((res) => res.json())
    }
    await fetchTransactions()
    swal.toastSuccess(
      targets.length > 1 ? 'Transações excluídas com sucesso!' : 'Transação excluída com sucesso!',
    )
  } catch (err) {
    console.error('Falha ao excluir transações:', err)
    swal.toastError('Falha ao excluir transação(ões).')
  }
}

onMounted(fetchTransactions)
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

.list-toolbar .status-filter {
  width: 100%;
  max-width: 220px;
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
