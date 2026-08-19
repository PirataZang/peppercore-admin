import { loadMercadoPago } from '@mercadopago/sdk-js'
import { apiFetch } from '@/services/api'

let mpInstance = null
let mpPromise = null

export async function getMercadoPago() {
  if (mpInstance) return mpInstance

  if (!mpPromise) {
    mpPromise = (async () => {
      const response = await apiFetch('/api/mercado-pago/public-key')
      const data = await response.json()
      if (!response.ok) throw new Error(data.message || 'Não foi possível carregar o Mercado Pago.')

      await loadMercadoPago()
      mpInstance = new window.MercadoPago(data.public_key, { locale: 'pt-BR' })
      return mpInstance
    })()
  }

  return mpPromise
}
