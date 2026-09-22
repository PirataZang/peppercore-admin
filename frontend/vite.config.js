import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

// https://vitejs.dev/config/
// `base` (path dos assets + import.meta.env.BASE_URL do router) vem da Env:
// - VITE_BASE_PATH tem prioridade (ex: /peppercore-admin)
// - cai para VITE_API_URL quando for um path (compat: VITE_API_URL=/peppercore-admin)
// - URLs completas (http...) são ignoradas para o `base` (vira '/')
// - vazio => '/' (dev local em localhost:5174/)
function resolveBase(env) {
  const raw = (env.VITE_BASE_PATH ?? env.VITE_API_URL ?? '').trim()
  if (!raw || /^https?:\/\//.test(raw)) return '/'
  const withLeading = raw.startsWith('/') ? raw : `/${raw}`
  return withLeading.endsWith('/') ? withLeading : `${withLeading}/`
}

export default defineConfig(({ mode }) => {
  // loadEnv lê frontend/.env, .env.development, .env.production E também
  // variáveis já injetadas pelo docker-compose (VITE_API_URL, VITE_BASE_PATH).
  const env = loadEnv(mode, process.cwd(), '')
  const base = resolveBase(env)
  const apiTarget = env.VITE_BACKEND_URL || 'http://backend:8000'

  // Em dev, se a API base for um subpath (/peppercore-admin), o api.js vai
  // chamar /peppercore-admin/api/... — precisa de proxy com rewrite para /api.
  const basePrefix = base === '/' ? '' : base.replace(/\/$/, '')

  return {
    // App servido sob /peppercore-admin/ pelo nginx — sem isso o build gera
    // caminhos absolutos (/assets/...) que caem na raiz e dão 404.
    base,
    plugins: [vue()],
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url))
      }
    },
    server: {
      host: '0.0.0.0',
      port: 5174,
      watch: {
        usePolling: true
      },
      proxy: {
        '/api': {
          target: apiTarget,
          changeOrigin: true,
          secure: false
        },
        ...(basePrefix
          ? {
              [`${basePrefix}/api`]: {
                target: apiTarget,
                changeOrigin: true,
                secure: false,
                rewrite: (path) => path.replace(new RegExp(`^${basePrefix}/api`), '/api')
              }
            }
          : {})
      }
    }
  }
})
