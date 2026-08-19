<template>
  <span v-if="!url" class="link-empty">—</span>
  <div v-else class="checkout-link" @click.stop>
    <a class="checkout-link__url" :href="url" target="_blank" rel="noopener noreferrer" :title="url">
      <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true" />
      Abrir pagamento
    </a>
    <Button variant="ghost" size="sm" icon="fa-solid fa-copy" title="Copiar link" @click="copy" />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import Button from '@/components/utils/Button.vue'
import { swal } from '@/plugins/swal'

const props = defineProps({
  params: { type: Object, required: true },
})

const url = computed(() => props.params.data?.gateway_payload?.checkout_url)

const copy = async () => {
  try {
    await navigator.clipboard.writeText(url.value)
    swal.toastSuccess('Link copiado!')
  } catch (err) {
    swal.toastError('Não foi possível copiar o link.')
  }
}
</script>

<style scoped lang="scss">
.checkout-link {
  display: flex;
  align-items: center;
  gap: 4px;
  width: 100%;
  height: 100%;
}

.checkout-link__url {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--color-info);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;

  i {
    font-size: 0.75rem;
  }

  &:hover {
    text-decoration: underline;
  }
}

.link-empty {
  color: var(--color-text-faint);
}
</style>
