<template>
  <div class="page fade-in">
    <div v-if="loadingProject" class="loading-state">
      <i class="fa-solid fa-spinner fa-spin" aria-hidden="true" />
      <span>Carregando projeto...</span>
    </div>

    <template v-else-if="project">
      <header class="project-header">
        <div class="project-header__top">
          <div class="project-header__identity">
            <span class="project-header__icon" aria-hidden="true">
              <i class="fa-solid fa-diagram-project" />
            </span>
            <div class="project-header__title">
              <h1>{{ project.name }}</h1>
              <p>
                <i class="fa-solid fa-globe" aria-hidden="true" />
                {{ project.domain || 'Domínio não informado' }}
              </p>
            </div>
          </div>

          <div class="project-header__actions">
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
          </div>
        </div>

        <div class="project-header__badges">
          <StatusBadge :label="payment.label" :variant="payment.variant" />
          <StatusBadge :label="typeLabel(project.type)" variant="info" />
        </div>

        <dl class="project-header__stats">
          <div class="stat">
            <dt><i class="fa-solid fa-user" aria-hidden="true" /> Cliente</dt>
            <dd>{{ project.client_name }}</dd>
          </div>
          <div class="stat">
            <dt><i class="fa-solid fa-at" aria-hidden="true" /> Contato</dt>
            <dd>{{ project.client_contact || '—' }}</dd>
          </div>
          <div class="stat">
            <dt><i class="fa-solid fa-sack-dollar" aria-hidden="true" /> Mensalidade</dt>
            <dd>{{ formatMoney(project.monthly_value) }}</dd>
          </div>
          <div class="stat">
            <dt><i class="fa-solid fa-calendar-day" aria-hidden="true" /> Vencimento</dt>
            <dd>{{ project.due_day ? `Dia ${project.due_day}` : '—' }}</dd>
          </div>
          <div class="stat">
            <dt><i class="fa-solid fa-circle-check" aria-hidden="true" /> Último pagamento</dt>
            <dd>{{ formatDate(txSummary?.last_payment_at, true) }}</dd>
          </div>
        </dl>

        <p v-if="project.description" class="project-header__description">{{ project.description }}</p>
      </header>

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

          <p v-if="!canCharge" class="charge-warning">
            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true" />
            Para gerar cobranças (Pix, boleto ou cartão), vincule um cliente com CPF/CNPJ cadastrado a este projeto.
          </p>

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
                  <th>Cobrança</th>
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
                    <a
                      v-if="t.gateway_payload?.ticket_url"
                      class="checkout-link__url"
                      :href="t.gateway_payload.ticket_url"
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true" />
                      Ver cobrança
                    </a>
                    <span v-else-if="t.gateway_payload" class="link-empty">Pix/cartão</span>
                    <span v-else class="link-empty">—</span>
                  </td>
                  <td class="row-actions">
                    <Button
                      v-if="t.status !== 'paid'"
                      variant="ghost"
                      size="sm"
                      icon="fa-solid fa-link"
                      :disabled="!canCharge"
                      @click="chargeTransaction(t)"
                      :title="canCharge ? (t.gateway_payload ? 'Gerar nova cobrança' : 'Gerar cobrança') : 'Vincule um cliente com CPF/CNPJ ao projeto para gerar cobranças'"
                    />
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

    <TransactionChargeModal
      v-model="chargeModalOpen"
      :transaction="chargingTransaction"
      :charge-url="chargingTransaction ? `/api/projects/${projectId}/transactions/${chargingTransaction.id}/charge` : ''"
      @charged="loadTransactions"
    />
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
import StatusBadge from '@/components/ui/StatusBadge.vue'
import EChart from '@/components/ui/EChart.vue'
import ActivityLogModal from '@/components/ui/ActivityLogModal.vue'
import TransactionChargeModal from '@/pages/transaction/TransactionChargeModal.vue'
import { paymentBadge, typeLabel, TRANSACTION_STATUS_OPTIONS, transactionBadge } from '@/config/projectStatus'
import { swal } from '@/plugins/swal'

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
const canCharge = computed(() => !!project.value?.client?.document)

const transactions = ref([])
const txSummary = ref(null)
const txLoading = ref(false)

const punctualityLabel = computed(() => {
  const paid = txSummary.value?.paid_count || 0
  if (!paid) return '—'
  const onTime = paid - (txSummary.value?.late_count || 0)
  return `${Math.round((onTime / paid) * 100)}%`
})

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

const chargeModalOpen = ref(false)
const chargingTransaction = ref(null)

const chargeTransaction = (t) => {
  chargingTransaction.value = { ...t, project: project.value }
  chargeModalOpen.value = true
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

.project-header {
  display: flex;
  flex-direction: column;
  gap: 20px;
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 16px;
  box-shadow: var(--shadow-sm);
  padding: 24px;
}

.project-header__top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.project-header__identity {
  display: flex;
  align-items: center;
  gap: 16px;
  min-width: 0;
}

.project-header__icon {
  flex: 0 0 52px;
  width: 52px;
  height: 52px;
  display: grid;
  place-items: center;
  border-radius: 14px;
  background: var(--color-primary-soft);
  color: var(--color-primary);
  font-size: 1.25rem;
}

.project-header__title {
  min-width: 0;

  h1 {
    font-size: 1.375rem;
    font-weight: 700;
    color: var(--color-text);
    line-height: 1.25;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  p {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 4px;
    font-size: 0.8125rem;
    color: var(--color-text-muted);

    i {
      font-size: 0.75rem;
      color: var(--color-text-faint);
    }
  }
}

.project-header__actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.project-header__badges {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.project-header__stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 20px;
  margin: 0;
  padding-top: 20px;
  border-top: 1px solid var(--color-border);
}

.stat {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;

  dt {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
    color: var(--color-text-muted);

    i {
      font-size: 0.6875rem;
      color: var(--color-text-faint);
    }
  }

  dd {
    margin: 0;
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--color-text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
}

.project-header__description {
  margin: 0;
  padding-top: 20px;
  border-top: 1px solid var(--color-border);
  color: var(--color-text-secondary);
  font-size: 0.875rem;
  line-height: 1.5;
}

@media (max-width: 640px) {
  .project-header__top {
    flex-direction: column;
    align-items: stretch;
  }

  .project-header__actions {
    justify-content: flex-end;
  }
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

.charge-warning {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  border-radius: 12px;
  background: var(--color-warning-soft);
  color: var(--color-warning);
  font-size: 0.8125rem;
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

.row-actions {
  display: flex;
  align-items: center;
  gap: 4px;
}

.checkout-link__url {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--color-info);
  white-space: nowrap;

  i {
    font-size: 0.75rem;
  }

  &:hover {
    text-decoration: underline;
  }
}

.link-empty {
  color: var(--color-text-faint);
}
</style>
