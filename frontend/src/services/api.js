import { useAuthStore } from '@/stores/auth'

export async function apiFetch(url, options = {}) {
  const auth = useAuthStore()
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
