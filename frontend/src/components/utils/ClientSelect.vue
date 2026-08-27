<template>
  <Select
    v-model="model"
    :options="options"
    label="Cliente"
    placeholder="Selecione um cliente..."
    search
    clearable
    :disabled="loading"
  />
</template>

<script setup>
// Combo de clientes para vincular a um documento na emissão. Props extras
// passadas pelo pai (multiple, clearable, placeholder, label...) caem direto
// no Select via fallthrough attrs — não precisa redeclarar nada aqui.
import { ref, onMounted } from 'vue'
import Select from './Select.vue'
import { apiFetch } from '@/services/api'

const model = defineModel({ default: null })

const options = ref([])
const loading = ref(false)

const fetchClients = async () => {
  loading.value = true
  try {
    const response = await apiFetch('/api/clients?per_page=200')
    const data = await response.json()
    options.value = (data.data || []).map((client) => ({ value: client.id, label: client.name }))
  } catch (err) {
    console.error('Erro ao buscar clientes:', err)
  } finally {
    loading.value = false
  }
}

onMounted(fetchClients)
</script>
