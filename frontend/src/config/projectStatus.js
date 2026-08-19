export const PROJECT_TYPE_OPTIONS = [
  { value: 'site', label: 'Site' },
  { value: 'sistema', label: 'Sistema' },
  { value: 'host', label: 'Host' },
]

export function typeLabel(type) {
  return PROJECT_TYPE_OPTIONS.find((o) => o.value === type)?.label || type || '—'
}

export const PAYMENT_STATUS_OPTIONS = [
  { value: 'pago', label: 'Pago' },
  { value: 'pendente', label: 'Pendente' },
  { value: 'atrasado', label: 'Atrasado' },
]

const PAYMENT_VARIANTS = {
  pago: 'success',
  pendente: 'warning',
  atrasado: 'danger',
}

export function paymentBadge(status) {
  const option = PAYMENT_STATUS_OPTIONS.find((o) => o.value === status)
  return {
    label: option?.label || status || 'Pendente',
    variant: PAYMENT_VARIANTS[status] || 'neutral',
  }
}

export const PAYMENT_METHOD_OPTIONS = [
  { value: 'pix', label: 'Pix' },
  { value: 'boleto', label: 'Boleto' },
  { value: 'credit_card', label: 'Cartão de Crédito' },
]

export const TRANSACTION_STATUS_OPTIONS = [
  { value: 'pending', label: 'Pendente' },
  { value: 'paid', label: 'Pago' },
  { value: 'failed', label: 'Falhou' },
  { value: 'refunded', label: 'Reembolsado' },
]

export function transactionBadge(t) {
  if (t.status === 'failed') return { label: 'Falhou', variant: 'danger' }
  if (t.status === 'refunded') return { label: 'Reembolsado', variant: 'neutral' }
  if (t.status === 'pending') return { label: 'Pendente', variant: 'neutral' }
  return t.paid_late ? { label: 'Pago com atraso', variant: 'warning' } : { label: 'Pago em dia', variant: 'success' }
}

const CONTAINER_VARIANTS = {
  running: 'success',
  restarting: 'warning',
  paused: 'warning',
  exited: 'neutral',
  dead: 'danger',
}

export function containerBadge(state) {
  return {
    label: state ? state.charAt(0).toUpperCase() + state.slice(1) : 'Desconhecido',
    variant: CONTAINER_VARIANTS[state] || 'neutral',
  }
}
