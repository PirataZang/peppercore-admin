<template>
  <div v-if="t.status !== 'paid'" class="row-actions" @click.stop>
    <Button
      variant="ghost"
      size="sm"
      icon="fa-solid fa-link"
      :disabled="!canCharge"
      :title="canCharge ? (t.gateway_payload ? 'Gerar nova cobrança' : 'Gerar cobrança') : 'Vincule um cliente com CPF/CNPJ ao projeto para gerar cobranças'"
      @click="params.context.onCharge(t)"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import Button from '@/components/utils/Button.vue'

const props = defineProps({
  params: { type: Object, required: true },
})

const t = computed(() => props.params.data)
const canCharge = computed(() => !!t.value?.project?.client?.document)
</script>

<style scoped>
.row-actions {
  display: flex;
  align-items: center;
  gap: 4px;
}
</style>
