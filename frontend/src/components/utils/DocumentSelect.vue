<template>
  <Select
    v-model="model"
    :options="options"
    label="Documento"
    placeholder="Selecione um documento ativo..."
    search
    clearable
    :disabled="loading"
  />
</template>

<script setup>
// Combo de documentos ATIVOS para emissão. Props extras passadas pelo pai
// (multiple, clearable, placeholder, label...) caem direto no Select via
// fallthrough attrs — não precisa redeclarar nada aqui pra sobrescrever.
import { ref, onMounted } from 'vue'
import Select from './Select.vue'
import { apiFetch } from '@/services/api'

const model = defineModel({ default: null })

const options = ref([])
const loading = ref(false)

const fetchActiveDocuments = async () => {
  loading.value = true
  try {
    const response = await apiFetch('/api/documents?active=1&per_page=200')
    const data = await response.json()
    options.value = (data.data || []).map((doc) => ({ value: doc.id, label: doc.name }))
  } catch (err) {
    console.error('Erro ao buscar documentos ativos:', err)
  } finally {
    loading.value = false
  }
}

onMounted(fetchActiveDocuments)
</script>
