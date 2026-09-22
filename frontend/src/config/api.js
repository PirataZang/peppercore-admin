// Prefixo global das chamadas de API.
//
// Em dev/local o backend atende na raiz do domínio ("/"), mas em produção o
// app é servido sob /peppercore-admin/ e as rotas de API seguem o mesmo
// prefixo (ex.: /peppercore-admin/api/projects).
//
// O valor vem do .env (API_ROUTE), com default "/":
//   - dev/local : API_ROUTE=/
//   - produção  : API_ROUTE=/peppercore-admin/

function normalizeRoute(route) {
  let value = String(route ?? '').trim() || '/'
  if (!value.startsWith('/')) value = `/${value}`
  if (!value.endsWith('/')) value = `${value}/`
  return value
}

export const API_ROUTE = normalizeRoute(
  import.meta.env.API_ROUTE || import.meta.env.VITE_API_ROUTE || '/'
)

/**
 * Monta a URL final de uma chamada de API aplicando o prefixo API_ROUTE.
 * URLs absolutas (http://, https://, //, data:, blob:) são mantidas como estão.
 */
export function apiUrl(path) {
  if (!path) return API_ROUTE

  const value = String(path)
  if (/^(?:https?:)?\/\//i.test(value) || /^(?:data|blob):/i.test(value)) {
    return value
  }

  return API_ROUTE + value.replace(/^\/+/, '')
}
