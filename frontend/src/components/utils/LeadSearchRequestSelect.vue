<template>
  <Select
    v-model="model"
    :options="options"
    label="Busca"
    placeholder="Todas as buscas..."
    search
    clearable
    :disabled="loading"
  />
</template>

<script setup>
// Combo de buscas de leads para filtrar a grid de leads potenciais por região buscada.
import { ref, onMounted } from 'vue'
import Select from './Select.vue'
import { apiFetch } from '@/services/api'

const model = defineModel({ default: null })

const options = ref([])
const loading = ref(false)

const STATUS_LABELS = {
  pending: 'pendente',
  running: 'em andamento',
  done: 'concluída',
  failed: 'falhou',
}

const fetchRequests = async () => {
  loading.value = true
  try {
    const response = await apiFetch('/api/lead-search-requests?per_page=200')
    const data = await response.json()
    options.value = (data.data || []).map((request) => ({
      value: request.id,
      label: `${request.city} · ${request.radius_km}km (${STATUS_LABELS[request.status] || request.status})`,
    }))
  } catch (err) {
    console.error('Erro ao buscar buscas de leads:', err)
  } finally {
    loading.value = false
  }
}

onMounted(fetchRequests)
</script>
