<template>
  <div class="page fade-in">
    <PageHeader
      :title="isEdit ? 'Editar Projeto' : 'Novo Projeto'"
      :subtitle="isEdit ? 'Atualize os dados de cliente e pagamento do projeto.' : 'Cadastre um novo projeto e cliente.'"
      :icon="isEdit ? 'fa-solid fa-pen-to-square' : 'fa-solid fa-diagram-project'"
    />

    <div class="form-card">
      <div v-if="loading" class="loading-state">
        <i class="fa-solid fa-spinner fa-spin" aria-hidden="true" />
        <span>Carregando dados do projeto...</span>
      </div>

      <form v-else @submit.prevent="submitForm">
        <div class="form-grid">
          <Input class="col-4" v-model="form.name" label="Nome do Projeto" placeholder="Ex.: Loja do João" required />
          <Select
            class="col-2"
            v-model="form.type"
            label="Tipo"
            :options="typeOptions"
            :clearable="false"
          />
          <Input class="col-6" v-model="form.client_name" label="Cliente" placeholder="Nome do cliente/empresa" required />

          <Input class="col-6" v-model="form.client_contact" label="Contato do Cliente" placeholder="E-mail ou telefone" />
          <Input class="col-6" v-model="form.domain" label="Domínio" placeholder="exemplo.com.br" />

          <Input class="col-4" v-model="form.monthly_value" label="Mensalidade" type="number" step="0.01" min="0" placeholder="0,00">
            <template #prefix>R$</template>
          </Input>
          <Input class="col-4" v-model="form.due_day" label="Dia de Vencimento" type="number" min="1" max="31" placeholder="10" />
          <Select
            class="col-4"
            v-model="form.payment_status"
            label="Situação"
            :options="paymentOptions"
            :clearable="false"
          />

          <TextArea class="col-12" v-model="form.description" label="Descrição" placeholder="Detalhes do contrato, escopo, observações..." />
        </div>

        <div v-if="error" class="form-error" style="margin-top: 16px">
          {{ error }}
        </div>

        <FormActions :saving="saving" @cancel="$router.push('/project')" />
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
import { PAYMENT_STATUS_OPTIONS, PROJECT_TYPE_OPTIONS } from '@/config/projectStatus'

const router = useRouter()
const route = useRoute()

const projectId = computed(() => route.params.id)
const isEdit = computed(() => !!projectId.value)
const paymentOptions = PAYMENT_STATUS_OPTIONS
const typeOptions = PROJECT_TYPE_OPTIONS

const loading = ref(false)
const saving = ref(false)
const error = ref('')

const emptyForm = () => ({
  name: '',
  type: 'site',
  client_name: '',
  client_contact: '',
  domain: '',
  monthly_value: '',
  due_day: '',
  payment_status: 'pendente',
  description: '',
})

const form = ref(emptyForm())

const NULLABLE_KEYS = ['client_contact', 'domain', 'monthly_value', 'due_day', 'description']

const fetchProject = async () => {
  if (!isEdit.value) return

  loading.value = true
  error.value = ''
  try {
    const response = await apiFetch(`/api/projects/${projectId.value}`)
    if (!response.ok) {
      throw new Error('Não foi possível carregar os dados deste projeto.')
    }
    const data = await response.json()
    form.value = { ...emptyForm(), ...data }
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

  const url = isEdit.value ? `/api/projects/${projectId.value}` : '/api/projects'
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

    router.push('/project')
  } catch (err) {
    error.value = err.message
  } finally {
    saving.value = false
  }
}

onMounted(fetchProject)
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
