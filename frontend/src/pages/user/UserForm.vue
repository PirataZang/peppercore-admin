<template>
    <div class="page fade-in">
        <PageHeader :title="isEdit ? 'Editar Usuário' : 'Novo Usuário'" :subtitle="isEdit ? 'Atualize as informações do usuário selecionado.' : 'Cadastre um novo usuário administrador no sistema.'" :icon="isEdit ? 'fa-solid fa-user-pen' : 'fa-solid fa-user-plus'" />

        <div class="form-card">
            <div v-if="loading" class="loading-state">
                <i class="fa-solid fa-spinner fa-spin" aria-hidden="true" />
                <span>Carregando dados do usuário...</span>
            </div>

            <form v-else @submit.prevent="submitForm">
                <div class="form-grid">
                    <div class="col-6">
                        <Input v-model="form.name" label="Nome Completo" placeholder="Digite o nome..." required />
                    </div>

                    <div class="col-6">
                        <Input v-model="form.email" label="Endereço de E-mail" type="email" placeholder="exemplo@peppercore.com" required />
                    </div>

                    <Input class="col-10" v-model="form.password" :label="passwordLabel" type="password" :required="!isEdit" placeholder="Mínimo 8 caracteres..." />
                </div>

                <div v-if="error" class="form-error" style="margin-top: 16px">
                    {{ error }}
                </div>

                <FormActions :saving="saving" @cancel="$router.push('/user')" />
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { apiFetch } from '@/services/api'
import Input from '@/components/utils/Input.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import FormActions from '@/components/ui/FormActions.vue'

const router = useRouter()
const route = useRoute()

const userId = computed(() => route.params.id)
const isEdit = computed(() => !!userId.value)
const passwordLabel = computed(() => (isEdit.value ? 'Senha de Acesso (deixe em branco para não alterar)' : 'Senha de Acesso'))

const loading = ref(false)
const saving = ref(false)
const error = ref('')

const form = ref({
    name: '',
    email: '',
    password: '',
})

const fetchUser = async () => {
    if (!isEdit.value) return

    loading.value = true
    error.value = ''
    try {
        const response = await apiFetch(`/api/users/${userId.value}`)
        if (!response.ok) {
            throw new Error('Não foi possível carregar os dados deste usuário.')
        }
        const data = await response.json()
        form.value.name = data.name
        form.value.email = data.email
        form.value.password = ''
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
        email: form.value.email,
    }

    if (form.value.password) {
        payload.password = form.value.password
    }

    const url = isEdit.value ? `/api/users/${userId.value}` : '/api/users'
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

        router.push('/user')
    } catch (err) {
        error.value = err.message
    } finally {
        saving.value = false
    }
}

onMounted(fetchUser)
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
