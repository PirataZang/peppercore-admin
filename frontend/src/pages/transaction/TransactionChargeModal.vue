<template>
  <Modal v-model="isOpen" title="Gerar Cobrança" size="sm">
    <div class="charge-modal">
      <div v-if="!result" class="charge-modal__form">
        <p class="charge-modal__hint">O cliente paga com Pix, boleto ou cartão. O vencimento é definido automaticamente pelo Mercado Pago.</p>

        <div class="charge-field">
          <Select
            v-model="clientId"
            label="Cliente"
            placeholder="Selecione o cliente que vai pagar"
            :options="clientOptions"
            :clearable="false"
            search
          />
          <p v-if="loadingClients" class="charge-field__hint">
            <i class="fa-solid fa-spinner fa-spin" aria-hidden="true" /> Carregando clientes...
          </p>
          <p v-else-if="selectedClient && !selectedClient.document" class="charge-field__hint charge-field__hint--warning">
            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true" />
            Este cliente não tem CPF/CNPJ cadastrado.
            <a :href="`/client/form/${selectedClient.id}`" target="_blank" rel="noopener noreferrer">Cadastrar agora</a>
          </p>
        </div>

        <Select v-model="method" label="Método de pagamento" :options="PAYMENT_METHOD_OPTIONS" :clearable="false" />

        <div v-if="method === 'credit_card'" id="card-payment-brick-container" class="charge-modal__brick" />

        <p v-if="error" class="form-error" role="alert">{{ error }}</p>
      </div>

      <div v-else class="charge-result">
        <StatusBadge :label="resultStatusLabel" :variant="resultStatusVariant" />

        <img
          v-if="result.qr_code_base64"
          class="charge-result__qr"
          :src="`data:image/png;base64,${result.qr_code_base64}`"
          alt="QR Code Pix"
        />

        <div v-if="result.qr_code" class="charge-result__copy">
          <label>Código Pix Copia e Cola</label>
          <div class="charge-result__copy-row">
            <input type="text" readonly :value="result.qr_code" />
            <Button variant="secondary" size="sm" icon="fa-solid fa-copy" @click="copy(result.qr_code)" />
          </div>
        </div>

        <div v-if="result.digitable_line" class="charge-result__copy">
          <label>Linha Digitável</label>
          <div class="charge-result__copy-row">
            <input type="text" readonly :value="result.digitable_line" />
            <Button variant="secondary" size="sm" icon="fa-solid fa-copy" @click="copy(result.digitable_line)" />
          </div>
        </div>

        <a v-if="result.ticket_url" class="charge-result__link" :href="result.ticket_url" target="_blank" rel="noopener noreferrer">
          <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true" />
          Abrir página de pagamento
        </a>

        <p v-if="method === 'credit_card'" class="charge-modal__hint">Pagamento processado. Acompanhe o status na listagem.</p>
      </div>
    </div>

    <template #footer>
      <Button variant="secondary" label="Fechar" @click="isOpen = false" />
      <Button
        v-if="!result && method !== 'credit_card'"
        variant="primary"
        :label="submitting ? 'Gerando...' : 'Gerar cobrança'"
        :disabled="submitting || !canSubmit"
        @click="submitCharge"
      />
    </template>
  </Modal>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue'
import Modal from '@/components/utils/Modal.vue'
import Select from '@/components/utils/Select.vue'
import Button from '@/components/utils/Button.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import { PAYMENT_METHOD_OPTIONS } from '@/config/projectStatus'
import { apiFetch } from '@/services/api'
import { getMercadoPago } from '@/services/mercadoPago'
import { swal } from '@/plugins/swal'

const props = defineProps({
  transaction: { type: Object, default: null },
  chargeUrl: { type: String, default: '' },
})

const emit = defineEmits(['charged'])

const isOpen = defineModel({ default: false })

const clients = ref([])
const loadingClients = ref(false)
const clientId = ref(null)
const method = ref('pix')
const submitting = ref(false)
const error = ref('')
const result = ref(null)
let brickController = null

const clientOptions = computed(() => clients.value.map((c) => ({ value: c.id, label: c.name })))
const selectedClient = computed(() => clients.value.find((c) => c.id === clientId.value) || null)
const canSubmit = computed(() => !!selectedClient.value?.document)

const resultStatusLabel = computed(() => {
  const status = result.value?.status
  if (status === 'action_required') return 'Aguardando pagamento'
  if (status === 'processed') return 'Pago'
  if (status === 'canceled') return 'Cancelado'
  return status || 'Pendente'
})

const resultStatusVariant = computed(() => {
  const status = result.value?.status
  if (status === 'processed') return 'success'
  if (status === 'canceled') return 'danger'
  return 'warning'
})

const copy = async (text) => {
  try {
    await navigator.clipboard.writeText(text)
    swal.toastSuccess('Copiado!')
  } catch (err) {
    swal.toastError('Não foi possível copiar.')
  }
}

const fetchClients = async () => {
  loadingClients.value = true
  try {
    const response = await apiFetch('/api/clients?per_page=200')
    const data = await response.json()
    clients.value = data.data || data
  } finally {
    loadingClients.value = false
  }
}

const preselectClient = () => {
  const linked = props.transaction?.client || props.transaction?.project?.client
  clientId.value = linked?.id ?? null
}

const submitCharge = async (card = null) => {
  if (!canSubmit.value) {
    error.value = 'Selecione um cliente com CPF/CNPJ cadastrado.'
    return
  }

  submitting.value = true
  error.value = ''

  try {
    const payload = { method: method.value, client_id: clientId.value }
    if (card) payload.card = card

    const response = await apiFetch(props.chargeUrl, {
      method: 'POST',
      body: JSON.stringify(payload),
    })
    const data = await response.json()
    if (!response.ok) throw new Error(data.message || 'Erro ao gerar cobrança.')

    result.value = data.gateway_payload
    emit('charged', data)
    swal.toastSuccess('Cobrança gerada com sucesso!')
  } catch (err) {
    error.value = err.message
  } finally {
    submitting.value = false
  }
}

const mountCardBrick = async () => {
  await nextTick()
  const container = document.getElementById('card-payment-brick-container')
  if (!container) return

  try {
    const mp = await getMercadoPago()
    const amount = Number(props.transaction?.amount) || 0

    brickController = await mp.bricks().create('cardPayment', 'card-payment-brick-container', {
      initialization: { amount },
      callbacks: {
        onReady: () => {},
        onError: (err) => {
          error.value = err?.message || 'Erro ao carregar o formulário de cartão.'
        },
        onSubmit: (cardData) => submitCharge({
          token: cardData.token,
          payment_method_id: cardData.payment_method_id,
          issuer_id: cardData.issuer_id,
          installments: cardData.installments,
        }),
      },
    })
  } catch (err) {
    error.value = err.message || 'Não foi possível carregar o Mercado Pago.'
  }
}

const unmountCardBrick = () => {
  brickController?.unmount?.()
  brickController = null
}

watch(method, (value) => {
  unmountCardBrick()
  if (value === 'credit_card') mountCardBrick()
})

watch(isOpen, async (open) => {
  if (open) {
    method.value = 'pix'
    result.value = null
    error.value = ''
    if (!clients.value.length) await fetchClients()
    preselectClient()
  } else {
    unmountCardBrick()
  }
})

onMounted(async () => {
  if (isOpen.value) {
    await fetchClients()
    preselectClient()
  }
})
</script>

<style scoped lang="scss">
.charge-modal {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.charge-modal__hint {
  font-size: 0.8125rem;
  color: var(--color-text-muted);
  line-height: 1.5;
}

.charge-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.charge-field__hint {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.75rem;
  color: var(--color-text-muted);

  a {
    color: var(--color-info);
    font-weight: 600;

    &:hover {
      text-decoration: underline;
    }
  }
}

.charge-field__hint--warning {
  color: var(--color-warning);
}

.charge-modal__brick {
  min-height: 220px;
}

.charge-result {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  text-align: center;
}

.charge-result__qr {
  width: 200px;
  height: 200px;
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 8px;
}

.charge-result__copy {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 6px;
  text-align: left;

  label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--color-text-muted);
  }
}

.charge-result__copy-row {
  display: flex;
  gap: 8px;

  input {
    flex: 1;
    min-width: 0;
    padding: 10px 12px;
    border: 1px solid var(--color-border);
    border-radius: 10px;
    background: var(--color-bg-muted);
    color: var(--color-text);
    font-size: 0.8125rem;
  }
}

.charge-result__link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--color-info);

  &:hover {
    text-decoration: underline;
  }
}

.form-error {
  padding: 10px 12px;
  border-radius: 10px;
  background: var(--color-danger-soft);
  color: var(--color-danger);
  font-size: 0.8125rem;
}
</style>
