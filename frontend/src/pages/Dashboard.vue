<template>
  <div class="tab-content fade-in">
    <div class="welcome-banner glass-panel glass-panel-glow">
      <div class="banner-content">
        <h2>Ambiente Docker PepperCore Ativo!</h2>
        <p>Sua stack local está configurada. Gerencie os containers de Vue, Laravel, Postgres e Redis diretamente deste console.</p>
      </div>
      <i class="fa-solid fa-circle-check banner-icon" aria-hidden="true" />
    </div>

    <!-- Projects Summary -->
    <div class="section-head">
      <h3 class="section-title">Projetos</h3>
      <button @click="fetchSummary" class="action-btn-primary" :disabled="summaryLoading">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="{ 'spin': summaryLoading }"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
        {{ summaryLoading ? 'Atualizando...' : 'Atualizar' }}
      </button>
    </div>

    <div class="kpi-grid">
      <div class="kpi-card glass-panel">
        <span class="kpi-label">Projetos Ativos</span>
        <span class="kpi-value">{{ summary?.total ?? '—' }}</span>
      </div>
      <div class="kpi-card glass-panel">
        <span class="kpi-label">Receita Mensal</span>
        <span class="kpi-value">{{ formatMoney(summary?.monthly_revenue) }}</span>
      </div>
      <div class="kpi-card glass-panel">
        <span class="kpi-label">Pagamentos em Atraso</span>
        <span class="kpi-value" :class="{ 'is-danger': summary?.overdue_count }">{{ summary?.overdue_count ?? '—' }}</span>
      </div>
      <div class="kpi-card glass-panel">
        <span class="kpi-label">Próximo Vencimento</span>
        <span class="kpi-value kpi-value--sm">{{ nextDueLabel }}</span>
      </div>
    </div>

    <div class="chart-grid">
      <div class="chart-card glass-panel">
        <h4>Projetos por Tipo</h4>
        <p v-if="!summary?.total" class="empty-note">Nenhum projeto cadastrado ainda.</p>
        <EChart v-else :option="typeChartOption" height="260px" />
      </div>
      <div class="chart-card glass-panel">
        <h4>Valor Mensal por Projeto</h4>
        <p v-if="!summary?.values?.length" class="empty-note">Nenhum projeto cadastrado ainda.</p>
        <EChart v-else :option="valueChartOption" height="260px" />
      </div>
    </div>

    <div class="due-panel glass-panel">
      <h4>Próximos Vencimentos</h4>
      <p v-if="!summary?.upcoming_due?.length" class="empty-note">Nenhum vencimento cadastrado.</p>
      <table v-else class="due-table">
        <thead>
          <tr>
            <th>Projeto</th>
            <th>Cliente</th>
            <th>Dia</th>
            <th>Valor</th>
            <th>Vence em</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in summary.upcoming_due" :key="p.id">
            <td>{{ p.name }}</td>
            <td>{{ p.client_name }}</td>
            <td>Dia {{ p.due_day }}</td>
            <td>{{ formatMoney(p.monthly_value) }}</td>
            <td>{{ p.days_until_due === 0 ? 'Hoje' : `${p.days_until_due} dia(s)` }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Service Status Grid -->
    <div class="section-head">
      <h3 class="section-title">Status da Conectividade</h3>
      <button @click="testAllConnections" class="action-btn-primary" :disabled="testing">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="{ 'spin': testing }"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
        {{ testing ? 'Testando Conexões...' : 'Atualizar Status' }}
      </button>
    </div>

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
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { apiFetch } from '@/services/api'
import EChart from '@/components/ui/EChart.vue'

const CHART_AXIS_COLOR = '#64748b'
const CHART_GRID_COLOR = '#e2e8f0'
const CHART_LABEL_COLOR = '#0f172a'

export default {
  name: 'Dashboard',
  components: { EChart },
  setup() {
    const summary = ref(null)
    const summaryLoading = ref(false)

    const formatMoney = (value) => {
      if (!value) return 'R$ 0,00'
      return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)
    }

    const nextDueLabel = computed(() => {
      const next = summary.value?.upcoming_due?.[0]
      if (!next) return '—'
      const when = next.days_until_due === 0 ? 'hoje' : `em ${next.days_until_due}d`
      return `${next.name} · ${when}`
    })

    const typeChartOption = computed(() => {
      const byType = summary.value?.by_type || { site: 0, sistema: 0, host: 0 }
      return {
        grid: { left: 8, right: 16, top: 24, bottom: 28, containLabel: true },
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        xAxis: {
          type: 'category',
          data: ['Site', 'Sistema', 'Host'],
          axisLine: { lineStyle: { color: CHART_GRID_COLOR } },
          axisTick: { show: false },
          axisLabel: { color: CHART_AXIS_COLOR },
        },
        yAxis: {
          type: 'value',
          minInterval: 1,
          splitLine: { lineStyle: { color: CHART_GRID_COLOR } },
          axisLabel: { color: CHART_AXIS_COLOR },
        },
        series: [{
          type: 'bar',
          data: [byType.site, byType.sistema, byType.host],
          barMaxWidth: 24,
          itemStyle: { color: '#4f46e5', borderRadius: [4, 4, 0, 0] },
          label: { show: true, position: 'top', color: CHART_LABEL_COLOR, fontWeight: 600 },
        }],
      }
    })

    const valueChartOption = computed(() => {
      const values = summary.value?.values || []
      return {
        grid: { left: 8, right: 16, top: 24, bottom: 48, containLabel: true },
        tooltip: {
          trigger: 'axis',
          axisPointer: { type: 'shadow' },
          valueFormatter: (v) => formatMoney(v),
        },
        xAxis: {
          type: 'category',
          data: values.map((v) => v.name),
          axisLine: { lineStyle: { color: CHART_GRID_COLOR } },
          axisTick: { show: false },
          axisLabel: { color: CHART_AXIS_COLOR, rotate: values.length > 4 ? 20 : 0 },
        },
        yAxis: {
          type: 'value',
          splitLine: { lineStyle: { color: CHART_GRID_COLOR } },
          axisLabel: { color: CHART_AXIS_COLOR, formatter: (v) => `R$ ${v}` },
        },
        series: [{
          type: 'bar',
          data: values.map((v) => v.monthly_value),
          barMaxWidth: 24,
          itemStyle: { color: '#e11d48', borderRadius: [4, 4, 0, 0] },
          label: {
            show: true,
            position: 'top',
            color: CHART_LABEL_COLOR,
            fontWeight: 600,
            formatter: (p) => formatMoney(p.value),
          },
        }],
      }
    })

    const fetchSummary = async () => {
      summaryLoading.value = true
      try {
        summary.value = await apiFetch('/api/projects/summary').then((res) => res.json())
      } catch (err) {
        addLog('frontend', 'error', 'Não foi possível carregar o resumo de projetos.')
      } finally {
        summaryLoading.value = false
      }
    }

    const testing = ref(false)
    const laravelStatus = ref('pending')
    const laravelStatusText = ref('Checando...')
    const postgresStatus = ref('pending')
    const postgresStatusText = ref('Checando...')
    const redisStatus = ref('pending')
    const redisStatusText = ref('Checando...')
    const logs = ref([])

    const addLog = (service, type, message) => {
      const time = new Date().toLocaleTimeString()
      logs.value.push({ time, service, type, message })
      if (logs.value.length > 50) logs.value.shift()
    }

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
        const response = await apiFetch('/api/status').then(res => res.json())
        
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

    const clearLogs = () => {
      logs.value = []
    }

    onMounted(() => {
      addLog('frontend', 'info', 'Painel de controle PepperCore carregado.')
      testAllConnections()
      fetchSummary()
    })

    return {
      summary,
      summaryLoading,
      formatMoney,
      nextDueLabel,
      typeChartOption,
      valueChartOption,
      fetchSummary,
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
.tab-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.fade-in {
  animation: fadeIn 0.4s ease-in-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(4px); }
  to { opacity: 1; transform: translateY(0); }
}

.welcome-banner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 28px;
  border-radius: 16px;
  background: #ffffff;
}

.banner-content h2 {
  font-size: 1.35rem;
  margin-bottom: 8px;
  font-weight: 700;
  color: var(--color-text);
}

.banner-content p {
  color: var(--text-secondary);
  font-size: 0.95rem;
  max-width: 600px;
  line-height: 1.5;
}

.banner-icon {
  font-size: 2.75rem;
  color: var(--success);
  filter: drop-shadow(0 0 10px rgba(5, 150, 105, 0.35));
}

.section-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  margin-top: 8px;
}

.section-title {
  font-size: 1.1rem;
  font-weight: 600;
  letter-spacing: 0.5px;
  color: var(--text-primary);
}

.action-btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border: none;
  border-radius: 10px;
  background: var(--primary);
  color: #fff;
  font-family: var(--font-sans);
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition-fast);
}

.action-btn-primary:hover:not(:disabled) {
  background: var(--primary-hover);
}

.action-btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.kpi-card {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 18px 20px;
  border-radius: 16px;
}

.kpi-label {
  font-size: 0.8rem;
  color: var(--text-muted);
}

.kpi-value {
  font-size: 1.6rem;
  font-weight: 700;
  color: var(--text-primary);
}

.kpi-value--sm {
  font-size: 1rem;
  font-weight: 600;
}

.kpi-value.is-danger {
  color: var(--color-danger);
}

.chart-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 20px;
}

.chart-card {
  padding: 20px;
  border-radius: 16px;
}

.chart-card h4,
.due-panel h4 {
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 12px;
}

.due-panel {
  padding: 20px;
  border-radius: 16px;
}

.empty-note {
  color: var(--text-muted);
  font-size: 0.85rem;
  padding: 24px 0;
  text-align: center;
}

.due-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;
}

.due-table th {
  text-align: left;
  padding: 8px 12px;
  color: var(--text-muted);
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  border-bottom: 1px solid var(--border-color);
}

.due-table td {
  padding: 10px 12px;
  border-bottom: 1px solid var(--border-color);
  color: var(--text-primary);
}

.due-table tr:last-child td {
  border-bottom: 0;
}

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
