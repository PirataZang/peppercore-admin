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
        <router-link to="/dashboard" class="nav-item" active-class="active">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1"></rect><rect x="14" y="3" width="7" height="5" rx="1"></rect><rect x="14" y="12" width="7" height="9" rx="1"></rect><rect x="3" y="16" width="7" height="5" rx="1"></rect></svg>
          Dashboard
        </router-link>
        <router-link to="/user" class="nav-item" active-class="active">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
          Usuários
        </router-link>
        <a href="#" class="nav-item">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
          Serviços
        </a>
        <a href="#" class="nav-item">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
          Logs do Sistema
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
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-secondary"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <input type="text" placeholder="Buscar recursos..." class="bg-transparent border-none outline-none text-white w-full text-sm" />
        </div>
        <div class="topbar-actions">
          <span class="text-secondary text-sm font-semibold">PepperCore Admin v1.0</span>
        </div>
      </header>

      <!-- Router View for Page Components -->
      <div class="content-body">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script>
export default {
  name: 'App'
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

.text-secondary { color: var(--secondary); }
.text-sm { font-size: 0.875rem; }
.font-semibold { font-weight: 600; }
.w-full { width: 100%; }

.content-body {
  display: flex;
  flex-direction: column;
}
</style>
