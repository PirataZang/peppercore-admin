<template>
  <div class="login-page">
    <div class="login-card glass-panel glass-panel-glow">
      <div class="login-header">
        <span class="logo">🌶️</span>
        <div>
          <h1>PepperCore Admin</h1>
          <p>Entre com suas credenciais para acessar o console.</p>
        </div>
      </div>

      <form class="login-form" @submit.prevent="handleSubmit">
        <Input
          v-model="form.email"
          label="E-mail"
          type="email"
          autocomplete="email"
          placeholder="seu@email.com"
          required
        />

        <Input
          v-model="form.password"
          label="Senha"
          type="password"
          autocomplete="current-password"
          placeholder="Digite sua senha"
          required
        />

        <p v-if="error" class="form-error">{{ error }}</p>

        <Button
          native-type="submit"
          :label="loading ? 'Entrando...' : 'Entrar'"
          :disabled="loading"
          color="#ff4d4d"
        />
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Button from '@/components/utils/Button.vue'
import Input from '@/components/utils/Input.vue'

const router = useRouter()
const auth = useAuthStore()

const form = ref({
  email: '',
  password: '',
})

const loading = ref(false)
const error = ref('')

const handleSubmit = async () => {
  loading.value = true
  error.value = ''

  try {
    await auth.login(form.value.email, form.value.password)
    await router.push('/dashboard')
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}

.login-card {
  width: 100%;
  max-width: 420px;
  padding: 32px;
  border-radius: 20px;
}

.login-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 32px;
}

.logo {
  font-size: 2.5rem;
  filter: drop-shadow(0 0 8px rgba(255, 77, 77, 0.4));
}

.login-header h1 {
  font-size: 1.4rem;
  margin-bottom: 4px;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.login-header p {
  color: var(--text-secondary);
  font-size: 0.9rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.login-form :deep(.button-style) {
  width: 100%;
  margin: 0;
}

.form-error {
  color: #ef4444;
  font-size: 0.875rem;
  background: rgba(239, 68, 68, 0.1);
  padding: 12px 16px;
  border-radius: 8px;
  border: 1px solid rgba(239, 68, 68, 0.2);
}
</style>
