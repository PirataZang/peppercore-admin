<template>
  <div class="page fade-in">
    <PageHeader
      :title="isEdit ? 'Editar Transação' : 'Nova Transação'"
      :subtitle="isEdit ? 'Atualize os dados desta cobrança.' : 'Registre uma nova cobrança, vinculada ou não a um projeto.'"
      :icon="isEdit ? 'fa-solid fa-pen-to-square' : 'fa-solid fa-file-invoice-dollar'"
    />

    <div class="form-card">
      <div v-if="loading" class="loading-state">
        <i class="fa-solid fa-spinner fa-spin" aria-hidden="true" />
        <span>Carregando dados da transação...</span>
      </div>

      <form v-else @submit.prevent="submitForm">
        <div class="form-grid">
          <Select
            class="col-6"
            v-model="form.project_id"
            label="Projeto"
            placeholder="Nenhum (cobrança avulsa)"
            :options="projectOptions"
            search
          />

          <template v-if="!form.project_id">
            <Input class="col-3" v-model="form.payer_name" label="Nome do Pagador" placeholder="Nome ou empresa" />
            <Input class="col-3" v-model="form.payer_email" label="E-mail do Pagador" type="email" placeholder="email@exemplo.com" />
          </template>

          <Input class="col-6" v-model="form.reference_month" label="Mês de Referência" type="month" required />
          <Input class="col-6" v-model="form.due_date" label="Vencimento" type="date" required />

          <Input class="col-4" v-model="form.amount" label="Valor" type="number" step="0.01" min="0" required>
            <template #prefix>R$</template>
          </Input>
          <Input class="col-4" v-model="form.paid_at" label="Pago em (deixe em branco se pendente)" type="date" />
          <Select class="col-4" v-model="form.status" label="Status" :options="TRANSACTION_STATUS_OPTIONS" :clearable="false" />

          <Select
            class="col-6"
            v-model="form.payment_method"
            label="Método"
            placeholder="Selecione o método"
            :options="PAYMENT_METHOD_OPTIONS"
          />
          <Select
            class="col-6"
            v-model="form.gateway"
            label="Gateway"
            placeholder="Nenhum (registro manual)"
            :options="gatewayOptions"
          />

          <TextArea class="col-12" v-model="form.notes" label="Observações" placeholder="Detalhes adicionais sobre esta cobrança..." />
        </div>

        <p v-if="error" class="form-error" style="margin-top: 16px">{{ error }}</p>

        <FormActions :saving="saving" @cancel="$router.push('/financial/transactions')" />
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { apiFetch } from '@/services/api'
import Input from '@/components/utils/Input.vue'
import Select from '@/components/utils/Select.vue'
import TextArea from '@/components/utils/TextArea.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import FormActions from '@/components/ui/FormActions.vue'
import { TRANSACTION_STATUS_OPTIONS, PAYMENT_METHOD_OPTIONS } from '@/config/projectStatus'

const router = useRouter()
const route = useRoute()

const transactionId = computed(() => route.params.id)
const isEdit = computed(() => !!transactionId.value)

const loading = ref(false)
const saving = ref(false)
const error = ref('')
const projects = ref([])
const gateways = ref([])
const projectOptions = computed(() => projects.value.map((p) => ({ value: p.id, label: `${p.name} (${p.client_name})` })))
const gatewayOptions = computed(() => {
  const options = gateways.value
    .filter((g) => g.is_active)
    .map((g) => ({ value: g.provider, label: g.label }))

  // Keep the currently saved gateway selectable even if the integration was deactivated since.
  if (form.value.gateway && !options.some((o) => o.value === form.value.gateway)) {
    const current = gateways.value.find((g) => g.provider === form.value.gateway)
    options.push({ value: form.value.gateway, label: current?.label || form.value.gateway })
  }

  return options
})

const emptyForm = () => ({
  project_id: null,
  payer_name: '',
  payer_email: '',
  reference_month: '',
  due_date: '',
  amount: '',
  paid_at: '',
  status: 'pending',
  payment_method: '',
  gateway: '',
  notes: '',
})

const form = ref(emptyForm())

const NULLABLE_KEYS = ['project_id', 'payer_name', 'payer_email', 'paid_at', 'payment_method', 'gateway', 'notes']

const fetchProjects = async () => {
  const response = await apiFetch('/api/projects?per_page=200')
  const data = await response.json()
  projects.value = data.data || data
}

const fetchGateways = async () => {
  const response = await apiFetch('/api/integration-settings')
  gateways.value = await response.json()
}

const fetchTransaction = async () => {
  if (!isEdit.value) return

  loading.value = true
  error.value = ''
  try {
    const response = await apiFetch(`/api/transactions/${transactionId.value}`)
    if (!response.ok) throw new Error('Não foi possível carregar os dados desta transação.')

    const data = await response.json()
    form.value = {
      ...emptyForm(),
      ...data,
      reference_month: data.reference_month?.slice(0, 7) || '',
    }
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

const submitForm = async () => {
  saving.value = true
  error.value = ''

  const payload = { ...form.value }
  for (const key of NULLABLE_KEYS) {
    if (payload[key] === '') payload[key] = null
  }
  if (payload.reference_month) payload.reference_month = `${payload.reference_month}-01`
  if (payload.project_id) {
    payload.payer_name = null
    payload.payer_email = null
  }

  const url = isEdit.value ? `/api/transactions/${transactionId.value}` : '/api/transactions'
  const method = isEdit.value ? 'PUT' : 'POST'

  try {
    const response = await apiFetch(url, {
      method,
      body: JSON.stringify(payload),
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Erro ao processar requisição.')
    }

    router.push('/financial/transactions')
  } catch (err) {
    error.value = err.message
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchProjects()
  fetchGateways()
  fetchTransaction()
})
</script>

<style scoped>
.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 48px 24px;
  color: var(--color-text-muted);
  font-size: 0.9rem;
}
</style>
