import { defineStore } from 'pinia'

const STORAGE_KEYS = {
  token: 'peppercore_token',
  user: 'peppercore_user',
  expireAt: 'peppercore_expire_at',
}

const POLL_INTERVAL_MS = 60_000

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: null,
    user: null,
    expireAt: null,
    pollingTimer: null,
  }),

  getters: {
    isAuthenticated(state) {
      return !!state.token && !!state.user
    },

    userInitials(state) {
      if (!state.user?.name) return '?'
      return state.user.name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0].toUpperCase())
        .join('')
    },
  },

  actions: {
    restoreSession() {
      this.token = localStorage.getItem(STORAGE_KEYS.token)
      this.expireAt = localStorage.getItem(STORAGE_KEYS.expireAt)

      const storedUser = localStorage.getItem(STORAGE_KEYS.user)
      this.user = storedUser ? JSON.parse(storedUser) : null

      if (this.isAuthenticated && !this.isTokenExpired()) {
        this.startPolling()
      } else if (this.token || this.user) {
        this.clearSession()
      }
    },

    isTokenExpired() {
      if (!this.expireAt) return true
      return new Date(this.expireAt).getTime() <= Date.now()
    },

    persistSession(token, user, expireAt) {
      this.token = token
      this.user = user
      this.expireAt = expireAt

      localStorage.setItem(STORAGE_KEYS.token, token)
      localStorage.setItem(STORAGE_KEYS.user, JSON.stringify(user))
      localStorage.setItem(STORAGE_KEYS.expireAt, expireAt)
    },

    clearSession() {
      this.stopPolling()
      this.token = null
      this.user = null
      this.expireAt = null

      localStorage.removeItem(STORAGE_KEYS.token)
      localStorage.removeItem(STORAGE_KEYS.user)
      localStorage.removeItem(STORAGE_KEYS.expireAt)
    },

    async login(email, password) {
      const response = await fetch('/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
      })

      const data = await response.json()

      if (!response.ok) {
        const message = data.errors?.email?.[0] || data.message || 'Falha ao realizar login.'
        throw new Error(message)
      }

      this.persistSession(data.token, data.user, data.expire_at)
      this.startPolling()
    },

    async logout({ redirect = true } = {}) {
      if (this.token) {
        try {
          await fetch('/api/auth/logout', {
            method: 'POST',
            headers: {
              Authorization: `Bearer ${this.token}`,
            },
          })
        } catch {
          // Ignora falha de rede no logout remoto.
        }
      }

      this.clearSession()

      if (redirect) {
        const { default: router } = await import('@/router')
        if (router.currentRoute.value.path !== '/login') {
          await router.push('/login')
        }
      }
    },

    async validateSession() {
      if (!this.isAuthenticated) {
        await this.logout()
        return false
      }

      if (this.isTokenExpired()) {
        await this.logout()
        return false
      }

      try {
        const response = await fetch('/api/status', {
          headers: {
            Authorization: `Bearer ${this.token}`,
          },
        })

        if (!response.ok) {
          await this.logout()
          return false
        }

        return true
      } catch {
        return true
      }
    },

    startPolling() {
      this.stopPolling()

      this.pollingTimer = setInterval(() => {
        this.validateSession()
      }, POLL_INTERVAL_MS)
    },

    stopPolling() {
      if (this.pollingTimer) {
        clearInterval(this.pollingTimer)
        this.pollingTimer = null
      }
    },
  },
})
