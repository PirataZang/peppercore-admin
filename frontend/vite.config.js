import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

// https://vitejs.dev/config/
export default defineConfig({
  // App servido sob /peppercore-admin/ pelo nginx — sem isso o build gera
  // caminhos absolutos (/assets/...) que caem na raiz e dão 404.
  base: '/peppercore-admin/',
  // O .env com as variáveis do projeto (API_ROUTE, VITE_*) fica na raiz do
  // repositório, não dentro de frontend/. envDir + envPrefix fazem o Vite
  // ler de lá e expor API_ROUTE via import.meta.env.
  envDir: fileURLToPath(new URL('..', import.meta.url)),
  envPrefix: ['VITE_', 'API_ROUTE'],
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
        target: 'http://backend:8000',
        changeOrigin: true,
        secure: false
      },
      // Mesmo caminho usado em produção (API_ROUTE=/peppercore-admin/):
      // remove o prefixo antes de encaminhar para o backend.
      '/peppercore-admin/api': {
        target: 'http://backend:8000',
        changeOrigin: true,
        secure: false,
        rewrite: (path) => path.replace(/^\/peppercore-admin/, '')
      }
    }
  }
})
