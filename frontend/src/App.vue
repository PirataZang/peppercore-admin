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
        <a href="#" class="nav-item" :class="{ active: activeTab === 'dashboard' }" @click.prevent="activeTab = 'dashboard'">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1"></rect><rect x="14" y="3" width="7" height="5" rx="1"></rect><rect x="14" y="12" width="7" height="9" rx="1"></rect><rect x="3" y="16" width="7" height="5" rx="1"></rect></svg>
          Dashboard
        </a>
        <a href="#" class="nav-item" :class="{ active: activeTab === 'users' }" @click.prevent="activeTab = 'users'">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
          Usuários
        </a>
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
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <input 
            v-if="activeTab === 'users'"
            type="text" 
            v-model="searchQuery" 
            @input="debounceSearch"
            placeholder="Buscar usuários por nome ou email..." 
          />
          <input 
            v-else
            type="text" 
            placeholder="Buscar recursos do sistema..." 
          />
        </div>
        <div class="topbar-actions">
          <button v-if="activeTab === 'dashboard'" @click="testAllConnections" class="action-btn-primary" :disabled="testing">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="{ 'spin': testing }"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
            {{ testing ? 'Testando Conexões...' : 'Atualizar Status' }}
          </button>
          
          <div v-if="activeTab === 'users'" class="flex gap-2">
            <button v-if="selectedUsers.length > 0" @click="deleteSelectedUsers" class="action-btn-danger">
              <i class="fa-solid fa-trash-can mr-2"></i> Excluir Selecionados ({{ selectedUsers.length }})
            </button>
            <button @click="showAddModal = true" class="action-btn-primary">
              <i class="fa-solid fa-user-plus mr-2"></i> Adicionar Usuário
            </button>
          </div>
        </div>
      </header>

      <!-- Content Area -->
      <div class="content-body">
        
        <!-- PÁGINA 1: DASHBOARD MONITORING -->
        <div v-if="activeTab === 'dashboard'" class="tab-content fade-in">
          <div class="welcome-banner glass-panel glass-panel-glow">
            <div class="banner-content">
              <h2>Ambiente Docker PepperCore Ativo!</h2>
              <p>Sua stack local está configurada. Gerencie os containers de Vue, Laravel, Postgres e Redis diretamente deste console.</p>
            </div>
            <span class="banner-emoji">🚀</span>
          </div>

          <!-- Service Status Grid -->
          <h3 class="section-title">Status da Conectividade</h3>
          <div class="status-grid">
            <!-- Vue Card -->
            <div class="status-card glass-panel">
              <div class="card-header">
                <span class="badge badge-vue">Frontend</span>
                <span class="status-indicator online"></span>
              </div>
              <h4>Aplicativo Vue.js</h4>
              <p class="description">Servidor de desenvolvimento local Vite servindo a interface do app.</p>
              <div class="card-footer">
                <span class="port-label">Porta: 5174</span>
                <span class="status-text">Executando</span>
              </div>
            </div>

            <!-- Laravel Card -->
            <div class="status-card glass-panel">
              <div class="card-header">
                <span class="badge badge-laravel">Backend</span>
                <span class="status-indicator" :class="laravelStatus"></span>
              </div>
              <h4>Laravel API Framework</h4>
              <p class="description">Servidor de backend de API de dados. Comunicação com container PHP.</p>
              <div class="card-footer">
                <span class="port-label">Porta: 8001</span>
                <span class="status-text">{{ laravelStatusText }}</span>
              </div>
            </div>

            <!-- Postgres Card -->
            <div class="status-card glass-panel">
              <div class="card-header">
                <span class="badge badge-postgres">Banco de Dados</span>
                <span class="status-indicator" :class="postgresStatus"></span>
              </div>
              <h4>PostgreSQL 16</h4>
              <p class="description">Banco de dados relacional. Persistência de dados ativa via Docker Volume.</p>
              <div class="card-footer">
                <span class="port-label">Porta: 5437</span>
                <span class="status-text">{{ postgresStatusText }}</span>
              </div>
            </div>

            <!-- Redis Card -->
            <div class="status-card glass-panel">
              <div class="card-header">
                <span class="badge badge-redis">Cache / Fila</span>
                <span class="status-indicator" :class="redisStatus"></span>
              </div>
              <h4>Redis 7</h4>
              <p class="description">Broker de filas de jobs em background e caching de performance.</p>
              <div class="card-footer">
                <span class="port-label">Porta: 6379</span>
                <span class="status-text">{{ redisStatusText }}</span>
              </div>
            </div>
          </div>

          <!-- Connection Logs -->
          <h3 class="section-title">Console de Logs</h3>
          <div class="logs-panel glass-panel">
            <div class="logs-header">
              <span>stdout_activity_log</span>
              <button @click="clearLogs" class="clear-btn">Limpar logs</button>
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

        <!-- PÁGINA 2: CONTROLE DE USUÁRIOS -->
        <div v-if="activeTab === 'users'" class="tab-content fade-in">
          <div class="welcome-banner glass-panel">
            <div class="banner-content">
              <h2>Gerenciamento de Usuários</h2>
              <p>Lista oficial de administradores e usuários do sistema PepperCore. Criados, alterados e persistidos no PostgreSQL.</p>
            </div>
            <span class="banner-emoji">👥</span>
          </div>

          <!-- Grid Component Wrapper -->
          <div class="grid-card-container glass-panel glass-panel-glow p-4">
            <AgGrid 
              :rowData="usersData" 
              :columnDefs="columnDefs" 
              :currentPage="currentPage" 
              :pageSize="pageSize" 
              :totalRows="totalRows" 
              :selectable="true" 
              gridHeight="450px"
              @update:page="handlePageChange"
              @update:pageSize="handlePageSizeChange"
              @update:selection="handleSelectionChange"
            />
          </div>
        </div>

      </div>
    </main>

    <!-- Glassmorphic Modal Adicionar Usuário -->
    <div v-if="showAddModal" class="modal-overlay">
      <div class="modal-content glass-panel glass-panel-glow">
        <div class="modal-header">
          <h3>Adicionar Novo Usuário</h3>
          <button @click="showAddModal = false" class="close-modal-btn">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
        <form @submit.prevent="addUser">
          <div class="modal-body">
            <div class="form-group">
              <label for="name">Nome Completo</label>
              <input type="text" id="name" v-model="newUser.name" required placeholder="Digite o nome..." />
            </div>
            <div class="form-group">
              <label for="email">Endereço de E-mail</label>
              <input type="email" id="email" v-model="newUser.email" required placeholder="exemplo@peppercore.com" />
            </div>
            <div class="form-group">
              <label for="password">Senha de Acesso</label>
              <input type="password" id="password" v-model="newUser.password" required placeholder="Mínimo 8 caracteres..." />
            </div>
            <div v-if="modalError" class="modal-error">
              {{ modalError }}
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" @click="showAddModal = false" class="action-btn-secondary">Cancelar</button>
            <button type="submit" class="action-btn-primary" :disabled="submittingUser">
              {{ submittingUser ? 'Salvando...' : 'Salvar Usuário' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import AgGrid from './components/utils/AgGrid.vue'

export default {
  name: 'App',
  components: {
    AgGrid
  },
  setup() {
    const activeTab = ref('dashboard')
    
    // Configurações do Health Monitor
    const testing = ref(false)
    const laravelStatus = ref('pending')
    const laravelStatusText = ref('Checando...')
    const postgresStatus = ref('pending')
    const postgresStatusText = ref('Checando...')
    const redisStatus = ref('pending')
    const redisStatusText = ref('Checando...')
    const logs = ref([])

    // Configurações do Grid de Usuários
    const usersData = ref([])
    const totalRows = ref(0)
    const currentPage = ref(1)
    const pageSize = ref(10)
    const searchQuery = ref('')
    const selectedUsers = ref([])
    let searchTimeout = null

    // Definição das colunas da tabela de Usuários
    const columnDefs = ref([
      { field: 'id', headerName: 'ID', width: 80, sortable: true, filter: 'agNumberColumnFilter', cellClass: 'cell-center' },
      { field: 'name', headerName: 'Nome Completo', flex: 1, sortable: true, filter: true },
      { field: 'email', headerName: 'E-mail Corporativo', flex: 1, sortable: true, filter: true },
      { 
        field: 'created_at', 
        headerName: 'Data de Cadastro', 
        type: 'datetime', 
        width: 200, 
        sortable: true 
      }
    ])

    // Controle do Modal
    const showAddModal = ref(false)
    const submittingUser = ref(false)
    const modalError = ref('')
    const newUser = ref({
      name: '',
      email: '',
      password: ''
    })

    const addLog = (service, type, message) => {
      const time = new Date().toLocaleTimeString()
      logs.value.push({ time, service, type, message })
      if (logs.value.length > 50) logs.value.shift()
    }

    // Testa as conexões dos serviços Laravel, Postgres e Redis
    const testAllConnections = async () => {
      testing.value = true
      laravelStatus.value = 'pending'
      laravelStatusText.value = 'Conectando...'
      postgresStatus.value = 'pending'
      postgresStatusText.value = 'Conectando...'
      redisStatus.value = 'pending'
      redisStatusText.value = 'Conectando...'
      
      addLog('frontend', 'info', 'Iniciando testes de conectividade da stack...')

      try {
        const response = await fetch('/api/status').then(res => res.json())
        
        laravelStatus.value = 'online'
        laravelStatusText.value = 'Conectado'
        addLog('backend', 'success', 'Laravel API respondendo em http://localhost:8001')

        if (response.database === 'connected') {
          postgresStatus.value = 'online'
          postgresStatusText.value = 'Conectado'
          addLog('postgres', 'success', `Conexão PostgreSQL bem-sucedida! Banco: ${response.db_name}`)
        } else {
          postgresStatus.value = 'offline'
          postgresStatusText.value = 'Erro'
          addLog('postgres', 'error', `Falha no banco PostgreSQL: ${response.database_error}`)
        }

        if (response.redis === 'connected') {
          redisStatus.value = 'online'
          redisStatusText.value = 'Conectado'
          addLog('redis', 'success', 'Conexão Redis respondendo com +PONG')
        } else {
          redisStatus.value = 'offline'
          redisStatusText.value = 'Erro'
          addLog('redis', 'error', `Falha no Redis: ${response.redis_error}`)
        }

      } catch (err) {
        addLog('frontend', 'error', `Não foi possível conectar à API Laravel: ${err.message}`)
        
        setTimeout(() => {
          laravelStatus.value = 'offline'
          laravelStatusText.value = 'Offline'
          postgresStatus.value = 'offline'
          postgresStatusText.value = 'Inacessível'
          redisStatus.value = 'offline'
          redisStatusText.value = 'Inacessível'
          addLog('backend', 'warning', 'Certifique-se de iniciar os containers com "docker compose up"')
          testing.value = false
        }, 1200)
        return
      }

      testing.value = false
    }

    // Busca os usuários cadastrados
    const fetchUsers = async () => {
      try {
        const url = `/api/users?page=${currentPage.value}&per_page=${pageSize.value}&search=${searchQuery.value}`
        const response = await fetch(url).then(res => res.json())
        
        usersData.value = response.data
        totalRows.value = response.total
        selectedUsers.value = [] // limpa seleção anterior
      } catch (err) {
        addLog('frontend', 'error', `Erro ao buscar lista de usuários: ${err.message}`)
      }
    }

    const debounceSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        currentPage.value = 1 // reinicia página
        fetchUsers()
      }, 400)
    }

    const handlePageChange = (page) => {
      currentPage.value = page
      fetchUsers()
    }

    const handlePageSizeChange = (size) => {
      pageSize.value = size
      currentPage.value = 1
      fetchUsers()
    }

    const handleSelectionChange = (selection) => {
      selectedUsers.value = selection
    }

    // Adiciona um usuário via POST
    const addUser = async () => {
      submittingUser.value = true
      modalError.value = ''
      
      try {
        const response = await fetch('/api/users', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(newUser.value)
        })
        
        const data = await response.json()
        
        if (!response.ok) {
          throw new Error(data.message || 'Erro ao salvar usuário.')
        }

        addLog('frontend', 'success', `Usuário "${data.name}" criado com sucesso!`)
        
        // Reset formulário
        newUser.value = { name: '', email: '', password: '' }
        showAddModal.value = false
        
        // Recarrega grid
        fetchUsers()
      } catch (err) {
        modalError.value = err.message
        addLog('frontend', 'error', `Falha ao criar usuário: ${err.message}`)
      } finally {
        submittingUser.value = false
      }
    }

    // Deleta os usuários selecionados
    const deleteSelectedUsers = async () => {
      if (selectedUsers.value.length === 0) return
      
      const confirmDelete = confirm(`Deseja realmente excluir ${selectedUsers.value.length} usuário(s) selecionado(s)?`)
      if (!confirmDelete) return

      addLog('frontend', 'info', `Excluindo ${selectedUsers.value.length} usuário(s)...`)

      try {
        for (const user of selectedUsers.value) {
          const response = await fetch(`/api/users/${user.id}`, { method: 'DELETE' }).then(res => res.json())
          addLog('backend', 'success', `Usuário id ${user.id} excluído: ${response.message}`)
        }
        
        fetchUsers()
      } catch (err) {
        addLog('frontend', 'error', `Falha ao excluir usuários: ${err.message}`)
      }
    }

    const clearLogs = () => {
      logs.value = []
    }

    // Escuta mudanças de aba para disparar ações
    watch(activeTab, (tab) => {
      if (tab === 'users') {
        fetchUsers()
      }
    })

    onMounted(() => {
      addLog('frontend', 'info', 'Painel de controle PepperCore carregado.')
      testAllConnections()
    })

    return {
      activeTab,
      testing,
      laravelStatus,
      laravelStatusText,
      postgresStatus,
      postgresStatusText,
      redisStatus,
      redisStatusText,
      logs,
      usersData,
      totalRows,
      currentPage,
      pageSize,
      searchQuery,
      selectedUsers,
      columnDefs,
      showAddModal,
      submittingUser,
      modalError,
      newUser,
      testAllConnections,
      clearLogs,
      debounceSearch,
      handlePageChange,
      handlePageSizeChange,
      handleSelectionChange,
      addUser,
      deleteSelectedUsers
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
  width: 380px;
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

.action-btn-secondary {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid var(--border-color);
  color: var(--text-primary);
  border-radius: 8px;
  padding: 8px 16px;
  font-family: var(--font-sans);
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  transition: var(--transition-fast);
}

.action-btn-secondary:hover {
  background: rgba(255, 255, 255, 0.15);
}

.action-btn-danger {
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  font-family: var(--font-sans);
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  transition: var(--transition-fast);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);
}

.action-btn-danger:hover {
  background: #dc2626;
  transform: translateY(-1px);
}

.flex { display: flex; }
.gap-2 { gap: 8px; }
.mr-2 { margin-right: 8px; }
.p-4 { padding: 16px; }

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
}

.tab-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Transitions */
.fade-in {
  animation: fadeIn 0.4s ease-in-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(4px); }
  to { opacity: 1; transform: translateY(0); }
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

/* AgGrid Dark Theme Customizations */
:deep(.ag-theme-quartz) {
  --ag-background-color: var(--bg-card);
  --ag-header-background-color: var(--bg-secondary);
  --ag-border-color: var(--border-color);
  --ag-header-foreground-color: var(--text-primary);
  --ag-foreground-color: var(--text-primary);
  --ag-data-color: var(--text-primary);
  --ag-row-hover-color: rgba(255, 255, 255, 0.05);
  --ag-selected-row-background-color: rgba(255, 77, 77, 0.15);
  --ag-odd-row-background-color: transparent;
  
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid var(--border-color);
  font-family: var(--font-sans);
}

:deep(.custom-pagination) {
  background: var(--bg-secondary) !important;
  border-top: 1px solid var(--border-color) !important;
  color: var(--text-secondary) !important;
}

:deep(.custom-pagination .highlight) {
  color: var(--primary) !important;
}

:deep(.custom-pagination .pag-btn) {
  background: var(--bg-card) !important;
  border: 1px solid var(--border-color) !important;
  color: var(--text-primary) !important;
}

:deep(.custom-pagination .pag-btn:hover:not(:disabled)) {
  background: rgba(255, 255, 255, 0.08) !important;
  border-color: var(--primary) !important;
}

:deep(.custom-pagination .pag-btn.page-num.active) {
  background: var(--primary) !important;
  border-color: var(--primary) !important;
  color: white !important;
}

:deep(.custom-pagination .size-select) {
  background: var(--bg-card) !important;
  border: 1px solid var(--border-color) !important;
  color: var(--text-primary) !important;
}

/* Glassmorphic Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(11, 15, 25, 0.7);
  backdrop-filter: blur(8px);
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-content {
  width: 480px;
  padding: 24px;
  border-radius: 16px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  background: rgba(19, 27, 46, 0.9);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  font-size: 1.25rem;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.close-modal-btn {
  background: transparent;
  border: none;
  color: var(--text-secondary);
  font-size: 1.25rem;
  cursor: pointer;
  transition: var(--transition-fast);
}

.close-modal-btn:hover {
  color: var(--primary);
}

.modal-body {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 0.85rem;
  font-weight: 500;
  color: var(--text-secondary);
}

.form-group input {
  padding: 10px 14px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: rgba(11, 15, 25, 0.5);
  color: var(--text-primary);
  font-family: var(--font-sans);
  outline: none;
  transition: var(--transition-fast);
}

.form-group input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 8px var(--primary-glow);
}

.modal-error {
  color: #ef4444;
  font-size: 0.8rem;
  background: rgba(239, 68, 68, 0.1);
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid rgba(239, 68, 68, 0.2);
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 10px;
}
</style>
