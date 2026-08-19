<template>
  <Modal v-model="isOpen" title="Configurar Mercado Pago" size="lg">
    <div class="mp-modal">
      <aside class="mp-info">
        <div class="mp-info__logo">
          <MercadoPagoLogo />
        </div>
        <h4>Pagamentos com Mercado Pago</h4>
        <p>
          Conecte sua conta do Mercado Pago para emitir cobranças via <strong>Pix</strong> e
          <strong>boleto bancário</strong> diretamente pelos projetos.
        </p>

        <ol class="mp-info__steps">
          <li>Acesse o <strong>Painel de Desenvolvedores</strong> do Mercado Pago.</li>
          <li>Abra sua aplicação (ou crie uma nova).</li>
          <li>Copie as credenciais de <strong>produção</strong> em "Credenciais".</li>
          <li>Cole abaixo e salve para ativar a integração.</li>
        </ol>

        <a
          class="mp-info__link"
          href="https://www.mercadopago.com.br/developers/panel/app"
          target="_blank"
          rel="noopener noreferrer"
        >
          <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true" />
          Abrir painel de desenvolvedores
        </a>
      </aside>

      <div class="mp-form">
        <div v-if="loading" class="mp-loading">
          <i class="fa-solid fa-spinner fa-spin" /> Carregando configuração...
        </div>

        <template v-else>
          <Switch
            v-model="form.is_active"
            label="Status da integração"
            :text-label="form.is_active ? 'Ativa' : 'Inativa'"
          />

          <div class="mp-form__section">
            <h5>Credenciais</h5>

            <Input
              v-model="form.access_token"
              label="Access Token"
              type="password"
              placeholder="APP_USR-0000000000000000-000000-..."
              autocomplete="off"
              required
            />
            <p class="mp-hint">Chave privada, usada no backend para criar cobranças. Nunca é exibida novamente após salva.</p>

            <Input
              v-model="form.public_key"
              label="Public Key"
              placeholder="APP_USR-00000000-0000-0000-0000-000000000000"
              autocomplete="off"
            />
            <p class="mp-hint">Opcional — necessária apenas se o checkout também rodar no frontend.</p>
          </div>

          <div class="mp-form__section">
            <h5>Webhook</h5>
            <Input
              v-model="form.webhook_secret"
              label="Assinatura secreta (x-signature)"
              type="password"
              placeholder="Gerada no painel > Webhooks"
              autocomplete="off"
            />
            <p class="mp-hint">Usada para validar notificações de status de pagamento recebidas do Mercado Pago.</p>
          </div>

          <p v-if="error" class="mp-error">{{ error }}</p>

          <p v-if="hasStoredCredentials" class="mp-saved-note">
            <i class="fa-solid fa-circle-check" aria-hidden="true" />
            Já existe um Access Token salvo. Deixe os campos em branco para mantê-lo.
          </p>
        </template>
      </div>
    </div>

    <template #footer>
      <Button variant="secondary" label="Cancelar" @click="isOpen = false" />
      <Button
        variant="primary"
        :label="saving ? 'Salvando...' : 'Salvar integração'"
        :disabled="saving || loading"
        @click="submit"
      />
    </template>
  </Modal>
</template>

<script setup>
import { ref, watch } from 'vue'
import Modal from '@/components/utils/Modal.vue'
import Input from '@/components/utils/Input.vue'
import Button from '@/components/utils/Button.vue'
import Switch from '@/components/utils/Switch.vue'
import MercadoPagoLogo from './MercadoPagoLogo.vue'
import { apiFetch } from '@/services/api'
import { swal } from '@/plugins/swal'

const isOpen = defineModel({ default: false })

const props = defineProps({
  isActive: { type: Boolean, default: false },
  hasCredentials: { type: Boolean, default: false },
})

const emit = defineEmits(['saved'])

const loading = ref(false)
const saving = ref(false)
const error = ref('')
const hasStoredCredentials = ref(false)

const emptyForm = () => ({
  is_active: false,
  access_token: '',
  public_key: '',
  webhook_secret: '',
})

const form = ref(emptyForm())

watch(isOpen, (open) => {
  if (open) {
    form.value = emptyForm()
    form.value.is_active = props.isActive
    hasStoredCredentials.value = props.hasCredentials
    error.value = ''
  }
})

const submit = async () => {
  error.value = ''

  if (!hasStoredCredentials.value && !form.value.access_token) {
    error.value = 'Informe o Access Token para ativar a integração.'
    return
  }

  saving.value = true
  try {
    const payload = { is_active: form.value.is_active }

    if (form.value.access_token || form.value.public_key || form.value.webhook_secret) {
      payload.credentials = {
        access_token: form.value.access_token || undefined,
        public_key: form.value.public_key || undefined,
        webhook_secret: form.value.webhook_secret || undefined,
      }
    }

    const response = await apiFetch('/api/integration-settings/mercado_pago', {
      method: 'PUT',
      body: JSON.stringify(payload),
    })
    const data = await response.json()
    if (!response.ok) throw new Error(data.message || 'Erro ao salvar integração.')

    swal.toastSuccess('Integração com Mercado Pago salva com sucesso!')
    emit('saved', data)
    isOpen.value = false
  } catch (err) {
    error.value = err.message
  } finally {
    saving.value = false
  }
}
</script>

<style scoped lang="scss">
.mp-modal {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 28px;
}

.mp-info {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding-right: 24px;
  border-right: 1px solid var(--color-border);

  h4 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--color-text);
  }

  p {
    font-size: 0.8125rem;
    color: var(--color-text-muted);
    line-height: 1.5;
  }
}

.mp-info__logo {
  width: 56px;
  height: 56px;
  display: grid;
  place-items: center;
  border-radius: 14px;
  background: #eff9f0;

  :deep(svg) {
    width: 32px;
    height: 32px;
  }
}

.mp-info__steps {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin: 4px 0 0;
  padding-left: 18px;
  font-size: 0.8125rem;
  color: var(--color-text-secondary);
  line-height: 1.5;

  li {
    padding-left: 2px;
  }
}

.mp-info__link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-top: 8px;
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--color-info);

  &:hover {
    text-decoration: underline;
  }

  i {
    font-size: 0.75rem;
  }
}

.mp-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
  min-width: 0;
}

.mp-form__section {
  display: flex;
  flex-direction: column;
  gap: 10px;

  h5 {
    font-size: 0.8125rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: var(--color-text-muted);
  }
}

.mp-hint {
  margin-top: -4px;
  font-size: 0.75rem;
  color: var(--color-text-faint);
  line-height: 1.4;
}

.mp-error {
  padding: 10px 12px;
  border-radius: 10px;
  background: var(--color-danger-soft);
  color: var(--color-danger);
  font-size: 0.8125rem;
}

.mp-saved-note {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 12px;
  border-radius: 10px;
  background: var(--color-success-soft);
  color: var(--color-success);
  font-size: 0.8125rem;
}

.mp-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 40px;
  color: var(--color-text-muted);
}

@media (max-width: 720px) {
  .mp-modal {
    grid-template-columns: 1fr;
  }

  .mp-info {
    padding-right: 0;
    padding-bottom: 20px;
    border-right: none;
    border-bottom: 1px solid var(--color-border);
  }
}
</style>
