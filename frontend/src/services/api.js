import { useAuthStore } from '@/stores/auth'

// Base URL da API obtida da variável de ambiente VITE_API_URL
// Em desenvolvimento, o Vite proxy cuida do /api (VITE_API_URL vazio ou não definido)
// Em produção, VITE_API_URL deve apontar para o path base onde o nginx serve o frontend
// Ex: '/peppercore-admin' se o frontend está em /peppercore-admin/ e o nginx faz proxy de /peppercore-admin/api para o backend
// Ex: 'https://api.exemplo.com' se o backend está em domínio separado
export const API_BASE_URL = import.meta.env.VITE_API_URL || ''

/**
 * Constrói a URL completa da API
 * @param {string} endpoint - Endpoint da API (ex: '/api/auth/login', 'api/auth/login' ou 'auth/login')
 * @returns {string} URL completa
 */
export function buildApiUrl(endpoint) {
  // Remove barra inicial se houver
  let cleanEndpoint = endpoint.startsWith('/') ? endpoint.slice(1) : endpoint

  // Remove prefixo /api/ se já estiver presente (com ou sem barra inicial)
  if (cleanEndpoint.startsWith('api/')) {
    cleanEndpoint = cleanEndpoint.slice(4)
  }

  // Se não há base URL configurada, usa caminho relativo (funciona com proxy do Vite em dev)
  if (!API_BASE_URL) {
    return `/api/${cleanEndpoint}`
  }

  // Remove barra final da base URL se houver
  const base = API_BASE_URL.endsWith('/') ? API_BASE_URL.slice(0, -1) : API_BASE_URL

  return `${base}/api/${cleanEndpoint}`
}

export async function apiFetch(endpoint, options = {}) {
  const auth = useAuthStore()
  const url = buildApiUrl(endpoint)

  const headers = {
    ...(options.headers || {}),
  }

  if (!headers['Content-Type'] && options.body) {
    headers['Content-Type'] = 'application/json'
  }

  if (auth.token) {
    headers.Authorization = `Bearer ${auth.token}`
  }

  const response = await fetch(url, {
    ...options,
    headers,
  })

  if (response.status === 401) {
    await auth.logout()
    throw new Error('Sessão expirada. Faça login novamente.')
  }

  return response
}