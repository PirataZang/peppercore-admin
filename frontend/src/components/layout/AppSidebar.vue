<template>
  <aside
    class="sidebar"
    :class="{ visible, expanded: isExpanded }"
    @mouseenter="hovered = true"
    @mouseleave="hovered = false"
  >
    <div class="sidebar__panel">
      <div class="brand">
        <div class="brand__mark" aria-hidden="true"><i class="fa-solid fa-pepper-hot" /></div>
        <div v-show="isExpanded" class="brand__text">
          <strong>PepperCore</strong>
          <span>Admin Console</span>
        </div>
      </div>

      <nav class="nav" aria-label="Menu principal">
        <ul class="nav__list">
          <SidebarMenuItem
            v-for="item in menuItems"
            :key="itemKey(item)"
            :item="item"
            :expanded="isExpanded"
            :open-keys="openKeys"
            @toggle="toggleOpen"
          />
        </ul>
      </nav>

      <div class="footer">
        <div class="user">
          <div class="user__avatar">{{ userInitials }}</div>
          <div v-show="isExpanded" class="user__meta">
            <strong>{{ userName }}</strong>
            <span>{{ userEmail }}</span>
          </div>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import SidebarMenuItem from './SidebarMenuItem.vue'

const props = defineProps({
  visible: { type: Boolean, default: true },
  userName: { type: String, default: 'Usuário' },
  userEmail: { type: String, default: '' },
  userInitials: { type: String, default: 'U' },
})

const route = useRoute()
const hovered = ref(false)
const openKeys = ref(new Set())

const isExpanded = computed(() => props.visible && hovered.value)

const menuItems = computed(() => [
  {
    name: 'Dashboard',
    route: '/dashboard',
    icon: '<i class="fa-solid fa-gauge-high"></i>',
    children: [],
  },
  {
    name: 'Clientes',
    route: null,
    icon: '<i class="fa-solid fa-address-book"></i>',
    children: [
      {
        name: 'Clientes',
        route: '/client',
        icon: '<i class="fa-solid fa-address-book"></i>',
        children: [],
      },
      {
        name: 'Projetos',
        route: '/project',
        icon: '<i class="fa-solid fa-diagram-project"></i>',
        children: [],
      },
    ],
  },
  {
    name: 'Financeiro',
    route: null,
    icon: '<i class="fa-solid fa-sack-dollar"></i>',
    children: [
      {
        name: 'Transações',
        route: '/financial/transactions',
        icon: '<i class="fa-solid fa-file-invoice-dollar"></i>',
        children: [],
      },
    ],
  },
  {
    name: 'Usuários',
    route: '/user',
    icon: '<i class="fa-solid fa-users"></i>',
    children: [],
  },
  {
    name: 'Serviços',
    route: null,
    icon: '<i class="fa-solid fa-server"></i>',
    children: [
      {
        name: 'Containers',
        route: null,
        icon: '<i class="fa-solid fa-box"></i>',
        children: [],
      },
      {
        name: 'Monitoramento',
        route: null,
        icon: '<i class="fa-solid fa-heart-pulse"></i>',
        children: [],
      },
    ],
  },
  {
    name: 'Configurações',
    route: '/settings',
    icon: '<i class="fa-solid fa-gear"></i>',
    children: [],
  },
])

const itemKey = (item) => item.route || item.name
const hasChildren = (item) => Array.isArray(item.children) && item.children.length > 0

const isActiveBranch = (item) => {
  if (hasChildren(item)) return item.children.some((c) => isActiveBranch(c))
  if (!item.route) return false
  if (item.route === '/dashboard') return route.path === '/dashboard' || route.path === '/'
  // Listagens marcam o item pai ativo também nos formulários filhos
  return route.path === item.route || route.path.startsWith(`${item.route}/`)
}

const toggleOpen = (key) => {
  const next = new Set(openKeys.value)
  if (next.has(key)) next.delete(key)
  else next.add(key)
  openKeys.value = next
}

watch(
  () => route.path,
  () => {
    const next = new Set(openKeys.value)
    const walk = (items) => {
      items.forEach((item) => {
        if (hasChildren(item) && isActiveBranch(item)) next.add(itemKey(item))
        if (hasChildren(item)) walk(item.children)
      })
    }
    walk(menuItems.value)
    openKeys.value = next
  },
  { immediate: true },
)
</script>

<style lang="scss" scoped>
@use '@/styles/colors' as *;

.sidebar {
  position: relative;
  flex: 0 0 0;
  width: 0;
  height: 100%;
  z-index: $z-sidebar;
  transition: flex-basis $transition-sidebar, width $transition-sidebar;

  &.visible {
    flex-basis: $sidebar-rail;
    width: $sidebar-rail;
  }
}

.sidebar__panel {
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  width: $sidebar-rail;
  display: flex;
  flex-direction: column;
  background: #ffffff;
  border-right: 1px solid $color-border;
  box-shadow: none;
  overflow: hidden;
  transition: width $transition-sidebar, box-shadow $transition-sidebar;

  .sidebar:not(.visible) & {
    transform: translateX(-100%);
  }

  .sidebar.expanded & {
    width: $sidebar-width;
    box-shadow: $shadow-sidebar;
  }
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  height: $topbar-height;
  min-height: $topbar-height;
  padding: 0 16px;
  border-bottom: 1px solid $color-border;
  background: #ffffff;
}

.brand__mark {
  flex: 0 0 40px;
  width: 40px;
  height: 40px;
  display: grid;
  place-items: center;
  border-radius: $radius-md;
  background: $color-primary-soft;
  color: $color-primary;
  font-size: 1.15rem;
}

.brand__text {
  display: flex;
  flex-direction: column;
  min-width: 0;
  line-height: 1.15;

  strong {
    font-size: $font-lg;
    font-weight: 700;
    color: $color-text;
  }

  span {
    font-size: $font-xs;
    color: $color-text-muted;
    font-weight: 500;
  }
}

.nav {
  flex: 1 1 auto;
  min-height: 0;
  overflow-x: hidden;
  overflow-y: auto;
  padding: 12px 10px;
  background: #ffffff;
}

.nav__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.footer {
  flex: 0 0 auto;
  padding: 12px 10px;
  border-top: 1px solid $color-border;
  background: #ffffff;
}

.user {
  display: flex;
  align-items: center;
  gap: 12px;
  min-height: 44px;
  padding: 6px 8px;
  border-radius: $radius-md;
  background: $color-bg-muted;
}

.user__avatar {
  flex: 0 0 36px;
  width: 36px;
  height: 36px;
  display: grid;
  place-items: center;
  border-radius: $radius-full;
  background: $color-primary;
  color: #fff;
  font-size: $font-sm;
  font-weight: 700;
}

.user__meta {
  display: flex;
  flex-direction: column;
  min-width: 0;
  line-height: 1.2;

  strong {
    font-size: $font-sm;
    font-weight: 600;
    color: $color-text;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  span {
    font-size: $font-xs;
    color: $color-text-muted;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
}
</style>
