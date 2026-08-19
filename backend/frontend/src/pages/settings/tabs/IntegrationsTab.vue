<template>
  <div class="tab-panel">
    <div class="tab-panel__header">
      <h3>Integrações</h3>
    </div>

    <div v-if="loading" class="tab-loading">
      <i class="fa-solid fa-spinner fa-spin" /> Carregando integrações...
    </div>

    <div v-else class="integration-grid">
      <IntegrationCard
        title="Mercado Pago"
        description="Emita cobranças via Pix e boleto bancário e receba pagamentos direto na sua conta."
        logo-bg="#eff9f0"
        :is-active="mercadoPago.is_active"
        :has-credentials="mercadoPago.has_credentials"
        :switch-disabled="!mercadoPago.has_credentials"
        @configure="modalOpen = true"
        @toggle="toggleMercadoPago"
      >
        <template #logo>
          <MercadoPagoLogo />
        </template>
      </IntegrationCard>
    </div>

    <MercadoPagoModal
      v-model="modalOpen"
      :is-active="mercadoPago.is_active"
      :has-credentials="mercadoPago.has_credentials"
      @saved="handleSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { apiFetch } from '@/services/api'
import { swal } from '@/plugins/swal'
import IntegrationCard from './IntegrationCard.vue'
import MercadoPagoModal from './MercadoPagoModal.vue'
import MercadoPagoLogo from './MercadoPagoLogo.vue'

const loading = ref(false)
const modalOpen = ref(false)
const mercadoPago = ref({ is_active: false, has_credentials: false })

const loadSettings = async () => {
  loading.value = true
  try {
    const response = await apiFetch('/api/integration-settings')
    const settings = await response.json()
    mercadoPago.value = settings.find((s) => s.provider === 'mercado_pago') || mercadoPago.value
  } finally {
    loading.value = false
  }
}

const handleSaved = (data) => {
  mercadoPago.value = { ...mercadoPago.value, ...data }
}

const toggleMercadoPago = async (value) => {
  const previous = mercadoPago.value.is_active
  mercadoPago.value.is_active = value

  try {
    const response = await apiFetch('/api/integration-settings/mercado_pago', {
      method: 'PUT',
      body: JSON.stringify({ is_active: value }),
    })
    const data = await response.json()
    if (!response.ok) throw new Error(data.message || 'Erro ao atualizar integração.')

    mercadoPago.value = { ...mercadoPago.value, ...data }
    swal.toastSuccess(value ? 'Mercado Pago ativado!' : 'Mercado Pago desativado.')
  } catch (err) {
    mercadoPago.value.is_active = previous
    swal.toastError('Falha ao atualizar a integração.')
  }
}

onMounted(loadSettings)
</script>

<style scoped lang="scss">
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
  h3 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--color-text);
  }
}

.tab-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 40px 24px;
  color: var(--color-text-muted);
  font-size: 0.9rem;
}

.integration-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
}
</style>
