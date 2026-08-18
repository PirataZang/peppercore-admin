<template>
  <div class="page fade-in">
    <PageHeader
      :title="project?.name || 'Projeto'"
      :subtitle="project?.domain || 'Domínio não informado'"
      icon="fa-solid fa-diagram-project"
    >
      <Button
        variant="ghost"
        icon="fa-solid fa-clock-rotate-left"
        label="Histórico"
        @click="activityLogOpen = true"
      />
      <Button
        variant="secondary"
        icon="fa-solid fa-pen-to-square"
        label="Editar"
        @click="$router.push(`/project/form/${projectId}`)"
      />
    </PageHeader>

    <div v-if="loadingProject" class="loading-state">
      <i class="fa-solid fa-spinner fa-spin" aria-hidden="true" />
      <span>Carregando projeto...</span>
    </div>

    <template v-else-if="project">
      <div class="summary-card">
        <div class="summary-badges">
          <StatusBadge :label="payment.label" :variant="payment.variant" />
          <StatusBadge :label="typeLabel(project.type)" variant="info" />
        </div>

        <dl class="summary-grid">
          <div class="summary-item">
            <dt>Cliente</dt>
            <dd>{{ project.client_name }}</dd>
          </div>
          <div class="summary-item">
            <dt>Contato</dt>
            <dd>{{ project.client_contact || '—' }}</dd>
          </div>
          <div class="summary-item">
            <dt>Mensalidade</dt>
            <dd>{{ formatMoney(project.monthly_value) }}</dd>
          </div>
          <div class="summary-item">
            <dt>Vencimento</dt>
            <dd>{{ project.due_day ? `Dia ${project.due_day}` : '—' }}</dd>
          </div>
          <div class="summary-item">
            <dt>Último pagamento</dt>
            <dd>{{ formatDate(txSummary?.last_payment_at, true) }}</dd>
          </div>
        </dl>

        <p v-if="project.description" class="summary-description">{{ project.description }}</p>
      </div>

      <div class="tab-panel">
        <div class="tab-panel__header">
          <h3>Pagamentos</h3>
          <div class="tab-panel__actions">
            <Button variant="ghost" size="sm" icon="fa-solid fa-rotate" label="Atualizar" @click="loadTransactions" />
            <Button variant="create" size="sm" icon="fa-solid fa-plus" label="Registrar Pagamento" @click="openNewTransaction" />
          </div>
        </div>

        <div v-if="txLoading" class="tab-loading"><i class="fa-solid fa-spinner fa-spin" /> Carregando pagamentos...</div>

        <template v-else>
          <div class="kpi-grid">
            <div class="kpi-card">
              <span class="kpi-label">Total Recebido</span>
              <span class="kpi-value">{{ formatMoney(txSummary?.total_received) }}</span>
            </div>
            <div class="kpi-card">
              <span class="kpi-label">Pagamentos Registrados</span>
              <span class="kpi-value">{{ txSummary?.paid_count ?? 0 }}</span>
            </div>
            <div class="kpi-card">
              <span class="kpi-label">Pagos com Atraso</span>
              <span class="kpi-value" :class="{ 'is-danger': txSummary?.late_count }">{{ txSummary?.late_count ?? 0 }}</span>
            </div>
            <div class="kpi-card">
              <span class="kpi-label">Pontualidade</span>
              <span class="kpi-value">{{ punctualityLabel }}</span>
            </div>
          </div>

          <div class="chart-block">
            <div class="chart-legend">
              <span class="legend-item"><i class="legend-dot legend-dot--success" />Pago em dia</span>
              <span class="legend-item"><i class="legend-dot legend-dot--warning" />Pago com atraso</span>
              <span class="legend-item"><i class="legend-dot legend-dot--neutral" />Pendente</span>
              <span class="legend-item"><i class="legend-dot legend-dot--danger" />Falhou</span>
            </div>
            <p v-if="!monthlyChartOption" class="empty-note">Nenhum pagamento registrado ainda.</p>
            <EChart v-else :option="monthlyChartOption" height="240px" />
          </div>

          <div class="history-list">
            <p v-if="!transactions.length" class="empty-note">Nenhum pagamento registrado ainda.</p>
            <table v-else class="data-table">
              <thead>
                <tr>
                  <th>Mês</th>
                  <th>Valor</th>
                  <th>Vencimento</th>
                  <th>Pago em</th>
                  <th>Status</th>
                  <th>Método</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="t in transactions" :key="t.id">
                  <td>{{ formatMonth(t.reference_month) }}</td>
                  <td>{{ formatMoney(t.amount) }}</td>
                  <td>{{ formatDate(t.due_date, true) }}</td>
                  <td>{{ t.paid_at ? formatDate(t.paid_at, true) : '—' }}</td>
                  <td><StatusBadge :label="transactionBadge(t).label" :variant="transactionBadge(t).variant" /></td>
                  <td>{{ t.payment_method || '—' }}</td>
                  <td>
                    <Button variant="ghost" size="sm" icon="fa-solid fa-trash-can" @click="deleteTransaction(t)" />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>
      </div>
    </template>

    <Modal v-model="txModalOpen" title="Registrar Pagamento" size="md">
      <form id="tx-form" class="form-grid" @submit.prevent="submitTransaction">
        <Input class="col-6" v-model="txForm.reference_month" label="Mês de Referência" type="month" required />
        <Input class="col-6" v-model="txForm.due_date" label="Vencimento" type="date" required />
        <Input class="col-6" v-model="txForm.amount" label="Valor" type="number" step="0.01" min="0" required>
          <template #prefix>R$</template>
        </Input>
        <Input class="col-6" v-model="txForm.paid_at" label="Pago em (deixe em branco se pendente)" type="date" />
        <Select class="col-6" v-model="txForm.status" label="Status" :options="TRANSACTION_STATUS_OPTIONS" :clearable="false" />
        <Input class="col-6" v-model="txForm.payment_method" label="Método" placeholder="pix, boleto, cartão..." />
        <p v-if="txError" class="form-error col-12">{{ txError }}</p>
      </form>
      <template #footer>
        <Button variant="secondary" label="Cancelar" @click="txModalOpen = false" />
        <Button variant="primary" native-type="submit" form="tx-form" :label="txSaving ? 'Salvando...' : 'Salvar'" :disabled="txSaving" />
      </template>
    </Modal>

    <ActivityLogModal v-model="activityLogOpen" subject-type="project" :subject-id="projectId" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { apiFetch } from '@/services/api'
import Button from '@/components/utils/Button.vue'
import Input from '@/components/utils/Input.vue'
import Select from '@/components/utils/Select.vue'
import Modal from '@/components/utils/Modal.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import EChart from '@/components/ui/EChart.vue'
import ActivityLogModal from '@/components/ui/ActivityLogModal.vue'
import { paymentBadge, typeLabel } from '@/config/projectStatus'
import { swal } from '@/plugins/swal'

const TRANSACTION_STATUS_OPTIONS = [
  { value: 'pending', label: 'Pendente' },
  { value: 'paid', label: 'Pago' },
  { value: 'failed', label: 'Falhou' },
  { value: 'refunded', label: 'Reembolsado' },
]

const STATUS_COLORS = {
  onTime: '#059669',
  late: '#d97706',
  pending: '#94a3b8',
  failed: '#dc2626',
}

const route = useRoute()
const projectId = computed(() => route.params.id)

const activityLogOpen = ref(false)

const project = ref(null)
const loadingProject = ref(false)
const payment = computed(() => paymentBadge(project.value?.payment_status))

const transactions = ref([])
const txSummary = ref(null)
const txLoading = ref(false)

const punctualityLabel = computed(() => {
  const paid = txSummary.value?.paid_count || 0
  if (!paid) return '—'
  const onTime = paid - (txSummary.value?.late_count || 0)
  return `${Math.round((onTime / paid) * 100)}%`
})

const transactionBadge = (t) => {
  if (t.status === 'failed') return { label: 'Falhou', variant: 'danger' }
  if (t.status === 'refunded') return { label: 'Reembolsado', variant: 'neutral' }
  if (t.status === 'pending') return { label: 'Pendente', variant: 'neutral' }
  return t.paid_late ? { label: 'Pago com atraso', variant: 'warning' } : { label: 'Pago em dia', variant: 'success' }
}

const monthlyChartOption = computed(() => {
  if (!transactions.value.length) return null
  const sorted = [...transactions.value].sort((a, b) => a.reference_month.localeCompare(b.reference_month))

  const data = sorted.map((t) => {
    let color = STATUS_COLORS.pending
    if (t.status === 'failed') color = STATUS_COLORS.failed
    else if (t.status === 'paid') color = t.paid_late ? STATUS_COLORS.late : STATUS_COLORS.onTime
    return { value: t.amount, itemStyle: { color, borderRadius: [4, 4, 0, 0] } }
  })

  return {
    grid: { left: 8, right: 16, top: 16, bottom: 28, containLabel: true },
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'shadow' },
      valueFormatter: (v) => formatMoney(v),
    },
    xAxis: {
      type: 'category',
      data: sorted.map((t) => formatMonth(t.reference_month)),
      axisLine: { lineStyle: { color: '#e2e8f0' } },
      axisTick: { show: false },
      axisLabel: { color: '#64748b' },
    },
    yAxis: {
      type: 'value',
      splitLine: { lineStyle: { color: '#e2e8f0' } },
      axisLabel: { color: '#64748b', formatter: (v) => `R$ ${v}` },
    },
    series: [{ type: 'bar', data, barMaxWidth: 28 }],
  }
})

const emptyTxForm = () => ({
  reference_month: '',
  due_date: '',
  amount: '',
  paid_at: '',
  status: 'paid',
  payment_method: '',
})

const txModalOpen = ref(false)
const txSaving = ref(false)
const txError = ref('')
const txForm = ref(emptyTxForm())

const openNewTransaction = () => {
  txForm.value = emptyTxForm()
  txError.value = ''
  txModalOpen.value = true
}

const submitTransaction = async () => {
  txSaving.value = true
  txError.value = ''

  const payload = {
    ...txForm.value,
    reference_month: txForm.value.reference_month ? `${txForm.value.reference_month}-01` : null,
    paid_at: txForm.value.paid_at || null,
  }

  try {
    const response = await apiFetch(`/api/projects/${projectId.value}/transactions`, {
      method: 'POST',
      body: JSON.stringify(payload),
    })
    const data = await response.json()
    if (!response.ok) throw new Error(data.message || 'Erro ao registrar pagamento.')

    txModalOpen.value = false
    await loadTransactions()
    swal.toastSuccess('Pagamento registrado com sucesso!')
  } catch (err) {
    txError.value = err.message
  } finally {
    txSaving.value = false
  }
}

const deleteTransaction = async (t) => {
  const ok = await swal.confirmDelete({ entity: 'pagamento' })
  if (!ok) return

  try {
    await apiFetch(`/api/projects/${projectId.value}/transactions/${t.id}`, { method: 'DELETE' })
    await loadTransactions()
    swal.toastSuccess('Pagamento excluído com sucesso!')
  } catch (err) {
    swal.toastError('Falha ao excluir pagamento.')
  }
}

const loadTransactions = async () => {
  txLoading.value = true
  try {
    const [list, summary] = await Promise.all([
      apiFetch(`/api/projects/${projectId.value}/transactions`).then((r) => r.json()),
      apiFetch(`/api/projects/${projectId.value}/transactions/summary`).then((r) => r.json()),
    ])
    transactions.value = list
    txSummary.value = summary
  } finally {
    txLoading.value = false
  }
}

const formatMoney = (value) => {
  if (!value) return 'R$ 0,00'
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)
}

const formatDate = (value, dateOnly = false) => {
  if (!value) return '—'
  const date = new Date(value)
  return dateOnly ? date.toLocaleDateString('pt-BR') : date.toLocaleString('pt-BR')
}

const formatMonth = (value) => {
  if (!value) return '—'
  const [year, month] = value.split('-')
  return new Date(Number(year), Number(month) - 1, 1).toLocaleDateString('pt-BR', { month: 'short', year: '2-digit' })
}

const fetchProject = async () => {
  loadingProject.value = true
  try {
    project.value = await apiFetch(`/api/projects/${projectId.value}`).then((r) => r.json())
  } finally {
    loadingProject.value = false
  }
}

onMounted(async () => {
  await fetchProject()
  await loadTransactions()
})
</script>

<style scoped lang="scss">
.loading-state,
.tab-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 40px 24px;
  color: var(--color-text-muted);
  font-size: 0.9rem;
}

.summary-card {
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 16px;
  box-shadow: var(--shadow-sm);
  padding: 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.summary-badges {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 16px;
  margin: 0;
}

.summary-item {
  display: flex;
  flex-direction: column;
  gap: 2px;

  dt {
    font-size: 0.75rem;
    color: var(--color-text-muted);
  }

  dd {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--color-text);
  }
}

.summary-description {
  margin: 0;
  padding-top: 12px;
  border-top: 1px solid var(--color-border);
  color: var(--color-text-secondary);
  font-size: 0.875rem;
  line-height: 1.5;
}

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

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 16px;
}

.kpi-card {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 14px 16px;
  border-radius: 12px;
  background: var(--color-bg-muted);
}

.kpi-label {
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

.kpi-value {
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--color-text);
}

.kpi-value.is-danger {
  color: var(--color-danger);
}

.chart-legend {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 8px;
}

.legend-item {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.8125rem;
  color: var(--color-text-muted);
}

.legend-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

.legend-dot--success { background: #059669; }
.legend-dot--warning { background: #d97706; }
.legend-dot--neutral { background: #94a3b8; }
.legend-dot--danger { background: #dc2626; }

.empty-state,
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

.history-list {
  max-height: 340px;
  overflow-y: auto;
  border: 1px solid var(--color-border);
  border-radius: 12px;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;

  th {
    position: sticky;
    top: 0;
    text-align: left;
    padding: 10px 12px;
    color: var(--color-text-muted);
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    background: var(--color-bg-muted);
    border-bottom: 1px solid var(--color-border);
  }

  td {
    padding: 12px;
    border-bottom: 1px solid var(--color-border);
    color: var(--color-text);
    vertical-align: middle;
  }

  tr:last-child td {
    border-bottom: 0;
  }
}
</style>
