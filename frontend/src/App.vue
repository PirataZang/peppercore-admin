<template>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar glass-panel">
      <div class="brand">
        <span class="logo">🌶️</span>
        <div class="brand-text">
          <h1>PepperCore</h1>
          <span class="subtitle">Admin Console</span>
        </div>
      </div>

      <nav class="nav-menu">
        <a href="#" class="nav-item active">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1"></rect><rect x="14" y="3" width="7" height="5" rx="1"></rect><rect x="14" y="12" width="7" height="9" rx="1"></rect><rect x="3" y="16" width="7" height="5" rx="1"></rect></svg>
          Dashboard
        </a>
        <a href="#" class="nav-item">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
          Services
        </a>
        <a href="#" class="nav-item">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
          System Logs
        </a>
        <a href="#" class="nav-item">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
          Settings
        </a>
      </nav>

      <div class="user-profile">
        <div class="avatar">IP</div>
        <div class="user-info">
          <span class="user-name">Igor Projetos</span>
          <span class="user-role">Administrator</span>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <!-- Topbar -->
      <header class="topbar glass-panel">
        <div class="search-bar">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <input type="text" placeholder="Search system resources..." />
        </div>
        <div class="topbar-actions">
          <button @click="testAllConnections" class="action-btn-primary" :disabled="testing">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="{ 'spin': testing }"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
            {{ testing ? 'Testing Environment...' : 'Refresh Status' }}
          </button>
        </div>
      </header>

      <!-- Content Area -->
      <div class="content-body">
        <div class="welcome-banner glass-panel glass-panel-glow">
          <div class="banner-content">
            <h2>Welcome to PepperCore Docker Environment!</h2>
            <p>Your local stack is configured and ready. Manage your Vue, Laravel, Postgres, and Redis containers directly from this interface.</p>
          </div>
          <span class="banner-emoji">🚀</span>
        </div>

        <!-- Service Status Grid -->
        <h3 class="section-title">Stack Connectivity Status</h3>
        <div class="status-grid">
          <!-- Vue Card -->
          <div class="status-card glass-panel">
            <div class="card-header">
              <span class="badge badge-vue">Frontend</span>
              <span class="status-indicator online"></span>
            </div>
            <h4>Vue.js App</h4>
            <p class="description">Vite local development server serving application UI.</p>
            <div class="card-footer">
              <span class="port-label">Port: 5173</span>
              <span class="status-text">Running</span>
            </div>
          </div>

          <!-- Laravel Card -->
          <div class="status-card glass-panel">
            <div class="card-header">
              <span class="badge badge-laravel">Backend</span>
              <span class="status-indicator" :class="laravelStatus"></span>
            </div>
            <h4>Laravel Framework</h4>
            <p class="description">API server backend. Connection with PHP container.</p>
            <div class="card-footer">
              <span class="port-label">Port: 8001</span>
              <span class="status-text">{{ laravelStatusText }}</span>
            </div>
          </div>

          <!-- Postgres Card -->
          <div class="status-card glass-panel">
            <div class="card-header">
              <span class="badge badge-postgres">Database</span>
              <span class="status-indicator" :class="postgresStatus"></span>
            </div>
            <h4>PostgreSQL 16</h4>
            <p class="description">Relational Database. Persistent data storage volume.</p>
            <div class="card-footer">
              <span class="port-label">Port: 5433 (host)</span>
              <span class="status-text">{{ postgresStatusText }}</span>
            </div>
          </div>

          <!-- Redis Card -->
          <div class="status-card glass-panel">
            <div class="card-header">
              <span class="badge badge-redis">Cache / Queue</span>
              <span class="status-indicator" :class="redisStatus"></span>
            </div>
            <h4>Redis 7</h4>
            <p class="description">In-memory caching and background job worker broker.</p>
            <div class="card-footer">
              <span class="port-label">Port: 6379</span>
              <span class="status-text">{{ redisStatusText }}</span>
            </div>
          </div>
        </div>

        <!-- Connection Logs -->
        <h3 class="section-title">Console Output Logs</h3>
        <div class="logs-panel glass-panel">
          <div class="logs-header">
            <span>stdout_activity_log</span>
            <button @click="clearLogs" class="clear-btn">Clear logs</button>
          </div>
          <div class="logs-body">
            <div v-for="(log, idx) in logs" :key="idx" class="log-line">
              <span class="log-time">[{{ log.time }}]</span>
              <span :class="['log-type', log.type]">&lt;{{ log.service }}&gt;</span>
              <span class="log-msg">{{ log.message }}</span>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'

export default {
  name: 'App',
  setup() {
    const testing = ref(false)
    const laravelStatus = ref('pending')
    const laravelStatusText = ref('Checking...')
    const postgresStatus = ref('pending')
    const postgresStatusText = ref('Checking...')
    const redisStatus = ref('pending')
    const redisStatusText = ref('Checking...')
    const logs = ref([])

    const addLog = (service, type, message) => {
      const time = new Date().toLocaleTimeString()
      logs.value.push({ time, service, type, message })
      // Keep only last 50 logs
      if (logs.value.length > 50) logs.value.shift()
    }

    const testAllConnections = async () => {
      testing.value = true
      laravelStatus.value = 'pending'
      laravelStatusText.value = 'Connecting...'
      postgresStatus.value = 'pending'
      postgresStatusText.value = 'Connecting...'
      redisStatus.value = 'pending'
      redisStatusText.value = 'Connecting...'
      
      addLog('frontend', 'info', 'Initiating stack health checks...')

      try {
        // Try requesting real backend status route
        const response = await fetch('/api/status').then(res => res.json())
        
        laravelStatus.value = 'online'
        laravelStatusText.value = 'Connected'
        addLog('backend', 'success', 'Laravel API responding at http://localhost:8001')

        if (response.database === 'connected') {
          postgresStatus.value = 'online'
          postgresStatusText.value = 'Connected'
          addLog('postgres', 'success', `Database connection ok! Connected to: ${response.db_name}`)
        } else {
          postgresStatus.value = 'offline'
          postgresStatusText.value = 'Error'
          addLog('postgres', 'error', `Database error: ${response.database_error}`)
        }

        if (response.redis === 'connected') {
          redisStatus.value = 'online'
          redisStatusText.value = 'Connected'
          addLog('redis', 'success', 'Redis connection ping returned +PONG')
        } else {
          redisStatus.value = 'offline'
          redisStatusText.value = 'Error'
          addLog('redis', 'error', `Redis error: ${response.redis_error}`)
        }

      } catch (err) {
        addLog('frontend', 'error', `Direct connection to backend API failed: ${err.message}`)
        
        // Simulating offline status/mock status as fallback
        setTimeout(() => {
          laravelStatus.value = 'offline'
          laravelStatusText.value = 'Offline'
          postgresStatus.value = 'offline'
          postgresStatusText.value = 'Unreachable'
          redisStatus.value = 'offline'
          redisStatusText.value = 'Unreachable'
          addLog('backend', 'warning', 'Please ensure docker containers are running with "docker compose up"')
          testing.value = false
        }, 1200)
        return
      }

      testing.value = false
    }

    const clearLogs = () => {
      logs.value = []
    }

    onMounted(() => {
      addLog('frontend', 'info', 'PepperCore Admin dashboard loaded.')
      testAllConnections()
    })

    return {
      testing,
      laravelStatus,
      laravelStatusText,
      postgresStatus,
      postgresStatusText,
      redisStatus,
      redisStatusText,
      logs,
      testAllConnections,
      clearLogs
    }
  }
}
</script>

<style scoped>
.dashboard-container {
  display: flex;
  min-height: 100vh;
}

/* Sidebar Styling */
.sidebar {
  width: 260px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  border-radius: 0 16px 16px 0;
  border-left: none;
  background: rgba(15, 23, 42, 0.7);
  z-index: 10;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 40px;
}

.logo {
  font-size: 2rem;
  filter: drop-shadow(0 0 8px rgba(255, 77, 77, 0.4));
  animation: float 4s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}

.brand-text h1 {
  font-size: 1.25rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.brand-text .subtitle {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.nav-menu {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex-grow: 1;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 12px;
  color: var(--text-secondary);
  text-decoration: none;
  font-size: 0.95rem;
  font-weight: 500;
  transition: var(--transition-fast);
}

.nav-item:hover, .nav-item.active {
  color: var(--text-primary);
  background: rgba(255, 255, 255, 0.05);
}

.nav-item.active {
  border-left: 3px solid var(--primary);
  background: linear-gradient(90deg, rgba(255, 77, 77, 0.1) 0%, transparent 100%);
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 12px;
  padding-top: 20px;
  border-top: 1px solid var(--border-color);
}

.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.9rem;
}

.user-info {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-size: 0.85rem;
  font-weight: 600;
}

.user-role {
  font-size: 0.7rem;
  color: var(--text-muted);
}

/* Main Content Area */
.main-content {
  flex-grow: 1;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 24px;
  max-width: calc(100vw - 260px);
}

/* Topbar */
.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 24px;
  border-radius: 16px;
}

.search-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--text-secondary);
  width: 300px;
}

.search-bar input {
  background: transparent;
  border: none;
  outline: none;
  color: var(--text-primary);
  font-family: var(--font-sans);
  font-size: 0.9rem;
  width: 100%;
}

.action-btn-primary {
  background: var(--primary);
  color: white;
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  font-family: var(--font-sans);
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 12px var(--primary-glow);
  transition: var(--transition-fast);
}

.action-btn-primary:hover {
  background: var(--primary-hover);
  transform: translateY(-1px);
}

.action-btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Content Body */
.content-body {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Welcome Banner */
.welcome-banner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 32px;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(20, 30, 54, 0.8) 0%, rgba(255, 77, 77, 0.05) 100%);
}

.banner-content h2 {
  font-size: 1.5rem;
  margin-bottom: 8px;
  background: linear-gradient(90deg, #ffffff, #dcdcdc);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.banner-content p {
  color: var(--text-secondary);
  font-size: 0.95rem;
  max-width: 600px;
  line-height: 1.5;
}

.banner-emoji {
  font-size: 3rem;
  filter: drop-shadow(0 0 10px rgba(99, 102, 241, 0.4));
}

.section-title {
  font-size: 1.1rem;
  font-weight: 600;
  margin-top: 10px;
  letter-spacing: 0.5px;
  color: var(--text-primary);
}

/* Status Cards Grid */
.status-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
}

.status-card {
  padding: 20px;
  border-radius: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  transition: var(--transition-normal);
}

.status-card:hover {
  transform: translateY(-4px);
  border-color: rgba(255, 77, 77, 0.2);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.badge {
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.badge-vue { background: rgba(66, 185, 131, 0.15); color: #42b983; }
.badge-laravel { background: rgba(255, 45, 32, 0.15); color: #ff2d20; }
.badge-postgres { background: rgba(51, 103, 145, 0.15); color: #8faec4; }
.badge-redis { background: rgba(216, 44, 32, 0.15); color: #e96b5c; }

.status-indicator {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}

.status-indicator.online {
  background: var(--success);
  box-shadow: 0 0 8px var(--success);
}

.status-indicator.pending {
  background: var(--warning);
  box-shadow: 0 0 8px var(--warning);
}

.status-indicator.offline {
  background: var(--primary);
  box-shadow: 0 0 8px var(--primary);
}

.status-card h4 {
  font-size: 1.1rem;
  font-weight: 600;
}

.status-card .description {
  font-size: 0.8rem;
  color: var(--text-secondary);
  line-height: 1.4;
  flex-grow: 1;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.75rem;
  color: var(--text-muted);
  border-top: 1px solid var(--border-color);
  padding-top: 10px;
  margin-top: 4px;
}

.status-text {
  font-weight: 600;
}

/* Connection Logs */
.logs-panel {
  display: flex;
  flex-direction: column;
  border-radius: 16px;
  max-height: 250px;
}

.logs-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 20px;
  border-bottom: 1px solid var(--border-color);
  font-family: monospace;
  font-size: 0.85rem;
  color: var(--text-muted);
}

.clear-btn {
  background: transparent;
  border: none;
  color: var(--text-muted);
  font-family: var(--font-sans);
  cursor: pointer;
  font-size: 0.75rem;
  transition: var(--transition-fast);
}

.clear-btn:hover {
  color: var(--primary);
}

.logs-body {
  padding: 16px;
  overflow-y: auto;
  font-family: 'Courier New', Courier, monospace;
  font-size: 0.8rem;
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-height: 120px;
}

.log-line {
  line-height: 1.4;
}

.log-time {
  color: var(--text-muted);
  margin-right: 8px;
}

.log-type {
  font-weight: bold;
  margin-right: 8px;
}

.log-type.info { color: var(--secondary); }
.log-type.success { color: var(--success); }
.log-type.warning { color: var(--warning); }
.log-type.error { color: var(--primary); }

.log-msg {
  color: var(--text-primary);
}
</style>
