<template>
  <div class="tab-content fade-in">
    <div class="welcome-banner glass-panel glass-panel-glow">
      <div class="banner-content">
        <h2>Bem-vindo ao PepperCore!</h2>
        <p>Acompanhe aqui o resumo dos seus projetos e o desempenho financeiro.</p>
      </div>
      <i class="fa-solid fa-circle-check banner-icon" aria-hidden="true" />
    </div>

    <DashboardFilters @change="handleFiltersChange" />

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

  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { apiFetch } from '@/services/api'
import EChart from '@/components/ui/EChart.vue'
import DashboardFilters from '@/components/ui/DashboardFilters.vue'

const CHART_AXIS_COLOR = '#64748b'
const CHART_GRID_COLOR = '#e2e8f0'
const CHART_LABEL_COLOR = '#0f172a'

export default {
  name: 'Dashboard',
  components: { EChart, DashboardFilters },
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

    const filters = ref({})

    const fetchSummary = async () => {
      summaryLoading.value = true
      try {
        const params = new URLSearchParams(
          Object.fromEntries(Object.entries(filters.value).filter(([, v]) => v !== null && v !== '')),
        )
        summary.value = await apiFetch(`/api/projects/summary?${params}`).then((res) => res.json())
      } catch (err) {
        console.error('Não foi possível carregar o resumo de projetos.', err)
      } finally {
        summaryLoading.value = false
      }
    }

    const handleFiltersChange = (newFilters) => {
      filters.value = newFilters
      fetchSummary()
    }

    return {
      summary,
      summaryLoading,
      formatMoney,
      nextDueLabel,
      typeChartOption,
      valueChartOption,
      fetchSummary,
      handleFiltersChange,
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

.chart-card h4 {
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 12px;
}

.empty-note {
  color: var(--text-muted);
  font-size: 0.85rem;
  padding: 24px 0;
  text-align: center;
}
</style>
