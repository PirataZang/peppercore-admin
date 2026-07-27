<template>
  <div class="tab-content fade-in">
    <div class="welcome-banner glass-panel">
      <div class="banner-content">
        <h2>{{ isEdit ? 'Editar Usuário' : 'Novo Usuário' }}</h2>
        <p>{{ isEdit ? 'Atualize as informações do usuário selecionado.' : 'Cadastre um novo usuário administrador no sistema.' }}</p>
      </div>
      <span class="banner-emoji">{{ isEdit ? '📝' : '👤' }}</span>
    </div>

    <div class="form-card glass-panel glass-panel-glow p-6">
      <div v-if="loading" class="loading-state">
        <svg class="spin" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
        <span>Carregando dados do usuário...</span>
      </div>

      <form v-else @submit.prevent="submitForm">
        <div class="form-grid">
          <Input
            v-model="form.name"
            label="Nome Completo"
            placeholder="Digite o nome..."
            required
          />

          <Input
            v-model="form.email"
            label="Endereço de E-mail"
            type="email"
            placeholder="exemplo@peppercore.com"
            required
          />

          <Input
            v-model="form.password"
            :label="passwordLabel"
            type="password"
            :required="!isEdit"
            placeholder="Mínimo 8 caracteres..."
          />
        </div>

        <div v-if="error" class="form-error mt-4">
          {{ error }}
        </div>

        <div class="form-actions mt-6">
          <Button
            type="button"
            label="Cancelar"
            color="#64748b"
            @click="$router.push('/user')"
          />
          <Button
            native-type="submit"
            :label="saving ? 'Salvando...' : 'Salvar Informações'"
            :disabled="saving"
            color="#ff4d4d"
          />
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { apiFetch } from '@/services/api'
import Button from '@/components/utils/Button.vue'
import Input from '@/components/utils/Input.vue'

const router = useRouter()
const route = useRoute()

const userId = computed(() => route.params.id)
const isEdit = computed(() => !!userId.value)
const passwordLabel = computed(() =>
  isEdit.value ? 'Senha de Acesso (deixe em branco para não alterar)' : 'Senha de Acesso',
)

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

onMounted(() => {
  fetchUser()
})
</script>

<style scoped>
.tab-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.fade-in {
  animation: fadeIn 0.4s ease-in-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(4px); }
  to { opacity: 1; transform: translateY(0); }
}

.welcome-banner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 32px;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(20, 30, 54, 0.8) 0%, rgba(255, 77, 77, 0.05) 100%);
}

.banner-content h2 {
  font-size: 1.5rem;
  margin-bottom: 8px;
  background: linear-gradient(90deg, #ffffff, #dcdcdc);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.banner-content p {
  color: var(--text-secondary);
  font-size: 0.95rem;
  max-width: 600px;
  line-height: 1.5;
}

.banner-emoji {
  font-size: 3rem;
  filter: drop-shadow(0 0 10px rgba(99, 102, 241, 0.4));
}

.form-card {
  border-radius: 16px;
}

.p-6 {
  padding: 24px;
}

.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 40px;
  color: var(--text-secondary);
}

.spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.form-grid {
  display: flex;
  flex-direction: column;
  gap: 20px;
  max-width: 600px;
}

.form-error {
  color: #ef4444;
  font-size: 0.875rem;
  background: rgba(239, 68, 68, 0.1);
  padding: 12px 16px;
  border-radius: 8px;
  border: 1px solid rgba(239, 68, 68, 0.2);
  max-width: 600px;
}

.form-actions {
  display: flex;
  gap: 12px;
}

.mt-4 { margin-top: 16px; }
.mt-6 { margin-top: 24px; }
</style>
