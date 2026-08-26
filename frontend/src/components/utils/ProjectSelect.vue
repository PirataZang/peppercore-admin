<template>
  <Select
    v-model="model"
    :options="options"
    label="Projeto"
    placeholder="Selecione um projeto..."
    search
    clearable
    :disabled="loading"
  />
</template>

<script setup>
// Combo de projetos para vincular a um documento na emissão. Props extras
// passadas pelo pai (multiple, clearable, placeholder, label...) caem direto
// no Select via fallthrough attrs — não precisa redeclarar nada aqui.
import { ref, onMounted } from 'vue'
import Select from './Select.vue'
import { apiFetch } from '@/services/api'

const model = defineModel({ default: null })

const options = ref([])
const loading = ref(false)

const fetchProjects = async () => {
  loading.value = true
  try {
    const response = await apiFetch('/api/projects?per_page=200')
    const data = await response.json()
    options.value = (data.data || []).map((project) => ({
      value: project.id,
      label: project.client_name ? `${project.name} (${project.client_name})` : project.name,
    }))
  } catch (err) {
    console.error('Erro ao buscar projetos:', err)
  } finally {
    loading.value = false
  }
}

onMounted(fetchProjects)
</script>
