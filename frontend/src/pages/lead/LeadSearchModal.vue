<template>
  <Modal v-model="show" title="Nova busca de leads" size="sm">
    <div class="search-form">
      <Input
        label="Cidade / endereço"
        v-model="city"
        placeholder="Ex: Rio do Sul, SC"
        @keydown.enter.prevent="locate"
      />
      <Button
        variant="secondary"
        icon="fa-solid fa-location-crosshairs"
        label="Localizar no mapa"
        :disabled="!city || locating"
        @click="locate"
      />

      <Input
        label="Raio de busca (km)"
        type="number"
        v-model.number="radiusKm"
        min="1"
        max="200"
      />

      <div ref="mapEl" class="map" />

      <p v-if="!hasLocation" class="hint">Localize a cidade no mapa antes de buscar.</p>
    </div>

    <template #footer>
      <Button variant="secondary" label="Cancelar" @click="show = false" />
      <Button
        variant="create"
        icon="fa-solid fa-magnifying-glass-location"
        label="Buscar leads nesta região"
        :disabled="!hasLocation || saving"
        @click="submit"
      />
    </template>
  </Modal>
</template>

<script setup>
// Geocodifica a cidade digitada via Nominatim (OpenStreetMap, gratuito, sem API key) e
// desenha o raio de busca num mapa Leaflet. Ao confirmar, só cria o "pedido de busca"
// (lead_search_requests) — quem efetivamente pesquisa na web e popula os leads é o
// agente do Claude (sob demanda ou agendado), não esta tela.
import { ref, watch, nextTick, onBeforeUnmount } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import Modal from '@/components/utils/Modal.vue'
import Input from '@/components/utils/Input.vue'
import Button from '@/components/utils/Button.vue'
import { apiFetch } from '@/services/api'
import { swal } from '@/plugins/swal'

const show = defineModel({ default: false })
const emit = defineEmits(['created'])

const city = ref('')
const radiusKm = ref(10)
const locating = ref(false)
const saving = ref(false)
const location = ref(null)
const hasLocation = ref(false)

const mapEl = ref(null)
let map = null
let circle = null
let centerMarker = null

const locate = async () => {
  if (!city.value) return
  locating.value = true
  try {
    const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(city.value)}`
    const response = await fetch(url)
    const results = await response.json()
    if (!results.length) {
      swal.toastError('Endereço não encontrado.')
      return
    }
    location.value = { lat: parseFloat(results[0].lat), lng: parseFloat(results[0].lon) }
    hasLocation.value = true
    await nextTick()
    renderMap()
  } catch (err) {
    console.error('Erro ao geocodificar cidade:', err)
    swal.toastError('Falha ao localizar o endereço.')
  } finally {
    locating.value = false
  }
}

const renderMap = () => {
  if (!mapEl.value || !location.value) return

  if (!map) {
    map = L.map(mapEl.value)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap',
    }).addTo(map)
  }

  const { lat, lng } = location.value
  map.setView([lat, lng], 11)

  if (!centerMarker) {
    centerMarker = L.circleMarker([lat, lng], { radius: 5, color: '#e11d48' }).addTo(map)
  } else {
    centerMarker.setLatLng([lat, lng])
  }

  if (!circle) {
    circle = L.circle([lat, lng], { radius: radiusKm.value * 1000, color: '#e11d48', fillOpacity: 0.1 }).addTo(map)
  } else {
    circle.setLatLng([lat, lng])
  }
  map.fitBounds(circle.getBounds())
}

watch(radiusKm, (value) => {
  if (!circle || !location.value) return
  circle.setRadius((value || 1) * 1000)
  map.fitBounds(circle.getBounds())
})

watch(show, async (value) => {
  if (!value) return
  city.value = ''
  radiusKm.value = 10
  location.value = null
  hasLocation.value = false
  if (map) {
    map.remove()
    map = null
    circle = null
    centerMarker = null
  }
})

const submit = async () => {
  if (!hasLocation.value) return
  saving.value = true
  try {
    const response = await apiFetch('/api/lead-search-requests', {
      method: 'POST',
      body: JSON.stringify({
        city: city.value,
        latitude: location.value.lat,
        longitude: location.value.lng,
        radius_km: radiusKm.value,
      }),
    })
    if (!response.ok) throw new Error('Falha ao criar busca')
    show.value = false
    swal.toastSuccess('Busca solicitada! O agente vai processar em breve.')
    emit('created')
  } catch (err) {
    console.error('Erro ao criar busca de leads:', err)
    swal.toastError('Falha ao solicitar a busca.')
  } finally {
    saving.value = false
  }
}

onBeforeUnmount(() => {
  if (map) map.remove()
})
</script>

<style scoped>
.search-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.map {
  width: 100%;
  height: 260px;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid var(--color-border);
}

.hint {
  margin: 0;
  font-size: 0.8125rem;
  color: var(--color-text-muted);
}
</style>
