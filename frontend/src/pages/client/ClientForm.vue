<template>
    <div class="page fade-in">
        <PageHeader :title="isEdit ? 'Editar Cliente' : 'Novo Cliente'" :subtitle="isEdit ? 'Atualize as informações do cliente selecionado.' : 'Cadastre um novo cliente no sistema.'" :icon="isEdit ? 'fa-solid fa-user-pen' : 'fa-solid fa-user-plus'">
            <Button v-if="isEdit" variant="ghost" icon="fa-solid fa-clock-rotate-left" label="Histórico" @click="activityLogOpen = true" />
        </PageHeader>

        <div class="form-card">
            <div v-if="loading" class="loading-state">
                <i class="fa-solid fa-spinner fa-spin" aria-hidden="true" />
                <span>Carregando dados do cliente...</span>
            </div>

            <form v-else @submit.prevent="submitForm">
                <div class="form-grid">
                    <div class="col-6">
                        <Input v-model="form.name" label="Nome Completo" placeholder="Digite o nome..." required />
                    </div>

                    <div class="col-6">
                        <Input v-model="form.phone" label="Telefone" placeholder="(00) 00000-0000" mask="phone" />
                    </div>

                    <div class="col-6">
                        <Input v-model="form.email" label="E-mail" type="email" placeholder="cliente@exemplo.com" />
                    </div>

                    <div class="col-6">
                        <Input v-model="form.address" label="Endereço" placeholder="Rua, número, cidade..." />
                    </div>

                    <div class="col-6">
                        <Input v-model="form.document" label="CPF/CNPJ" placeholder="000.000.000-00" mask="cpf-cnpj" />
                    </div>

                    <div class="form-section-title col-12">
                        <span>Endereço estruturado (para emissão de boleto)</span>
                    </div>

                    <div class="col-3">
                        <Input v-model="form.zip_code" label="CEP" placeholder="00000-000" mask="cep" />
                    </div>
                    <div class="col-5">
                        <Input v-model="form.street_name" label="Rua" placeholder="Nome da rua" />
                    </div>
                    <div class="col-2">
                        <Input v-model="form.street_number" label="Número" placeholder="S/N" />
                    </div>
                    <div class="col-2">
                        <Input v-model="form.state" label="UF" placeholder="SP" maxlength="2" />
                    </div>
                    <div class="col-6">
                        <Input v-model="form.neighborhood" label="Bairro" placeholder="Bairro" />
                    </div>
                    <div class="col-6">
                        <Input v-model="form.city" label="Cidade" placeholder="Cidade" />
                    </div>

                    <TextArea class="col-12" v-model="form.description" label="Descrição" placeholder="Observações sobre o cliente..." />
                </div>

                <div v-if="error" class="form-error" style="margin-top: 16px">
                    {{ error }}
                </div>

                <FormActions :saving="saving" @cancel="$router.push('/client')" />
            </form>
        </div>

        <ActivityLogModal v-if="isEdit" v-model="activityLogOpen" subject-type="client" :subject-id="clientId" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { apiFetch } from '@/services/api'
import Input from '@/components/utils/Input.vue'
import TextArea from '@/components/utils/TextArea.vue'
import Button from '@/components/utils/Button.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import FormActions from '@/components/ui/FormActions.vue'
import ActivityLogModal from '@/components/ui/ActivityLogModal.vue'

const router = useRouter()
const route = useRoute()

const clientId = computed(() => route.params.id)
const isEdit = computed(() => !!clientId.value)
const activityLogOpen = ref(false)

const loading = ref(false)
const saving = ref(false)
const error = ref('')

const form = ref({
    name: '',
    phone: '',
    email: '',
    address: '',
    description: '',
    document: '',
    zip_code: '',
    street_name: '',
    street_number: '',
    neighborhood: '',
    city: '',
    state: '',
})

const fetchClient = async () => {
    if (!isEdit.value) return

    loading.value = true
    error.value = ''
    try {
        const response = await apiFetch(`/api/clients/${clientId.value}`)
        if (!response.ok) {
            throw new Error('Não foi possível carregar os dados deste cliente.')
        }
        const data = await response.json()
        form.value.name = data.name
        form.value.phone = data.phone || ''
        form.value.email = data.email || ''
        form.value.address = data.address || ''
        form.value.description = data.description || ''
        form.value.document = data.document || ''
        form.value.zip_code = data.zip_code || ''
        form.value.street_name = data.street_name || ''
        form.value.street_number = data.street_number || ''
        form.value.neighborhood = data.neighborhood || ''
        form.value.city = data.city || ''
        form.value.state = data.state || ''
    } catch (err) {
        error.value = err.message
    } finally {
        loading.value = false
    }
}

const submitForm = async () => {
    saving.value = true
    error.value = ''

    const payload = {
        name: form.value.name,
        phone: form.value.phone || null,
        email: form.value.email || null,
        address: form.value.address || null,
        description: form.value.description || null,
        document: form.value.document || null,
        zip_code: form.value.zip_code || null,
        street_name: form.value.street_name || null,
        street_number: form.value.street_number || null,
        neighborhood: form.value.neighborhood || null,
        city: form.value.city || null,
        state: form.value.state || null,
    }

    const url = isEdit.value ? `/api/clients/${clientId.value}` : '/api/clients'
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

        router.push('/client')
    } catch (err) {
        error.value = err.message
    } finally {
        saving.value = false
    }
}

onMounted(fetchClient)
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

.form-section-title {
    margin-top: 4px;
    padding-top: 16px;
    border-top: 1px solid var(--color-border);
    font-size: 0.8125rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: var(--color-text-muted);
}
</style>
