import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppLayout from '@/layouts/AppLayout.vue'
import Login from '@/pages/Login.vue'
import Dashboard from '@/pages/Dashboard.vue'
import UserList from '@/pages/user/UserList.vue'
import UserForm from '@/pages/user/UserForm.vue'
import ProjectList from '@/pages/project/ProjectList.vue'
import ProjectForm from '@/pages/project/ProjectForm.vue'
import ProjectDetail from '@/pages/project/ProjectDetail.vue'
import ClientList from '@/pages/client/ClientList.vue'
import ClientForm from '@/pages/client/ClientForm.vue'
import TransactionList from '@/pages/transaction/TransactionList.vue'
import TransactionForm from '@/pages/transaction/TransactionForm.vue'
import DocumentList from '@/pages/document/DocumentList.vue'
import DocumentEditor from '@/pages/document/DocumentEditor.vue'
import Settings from '@/pages/settings/Settings.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { public: true },
  },
  {
    path: '/',
    component: AppLayout,
    children: [
      {
        path: '',
        redirect: '/dashboard',
      },
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: Dashboard,
      },
      {
        path: 'user',
        name: 'UserList',
        component: UserList,
      },
      {
        path: 'user/form',
        name: 'UserCreate',
        component: UserForm,
      },
      {
        path: 'user/form/:id',
        name: 'UserEdit',
        component: UserForm,
        props: true,
      },
      {
        path: 'project',
        name: 'ProjectList',
        component: ProjectList,
      },
      {
        path: 'project/form',
        name: 'ProjectCreate',
        component: ProjectForm,
      },
      {
        path: 'project/form/:id',
        name: 'ProjectEdit',
        component: ProjectForm,
        props: true,
      },
      {
        path: 'project/:id',
        name: 'ProjectDetail',
        component: ProjectDetail,
        props: true,
      },
      {
        path: 'client',
        name: 'ClientList',
        component: ClientList,
      },
      {
        path: 'client/form',
        name: 'ClientCreate',
        component: ClientForm,
      },
      {
        path: 'client/form/:id',
        name: 'ClientEdit',
        component: ClientForm,
        props: true,
      },
      {
        path: 'financial/transactions',
        name: 'TransactionList',
        component: TransactionList,
      },
      {
        path: 'financial/transactions/form',
        name: 'TransactionCreate',
        component: TransactionForm,
      },
      {
        path: 'financial/transactions/form/:id',
        name: 'TransactionEdit',
        component: TransactionForm,
        props: true,
      },
      {
        path: 'document',
        name: 'DocumentList',
        component: DocumentList,
      },
      {
        path: 'document/form',
        name: 'DocumentCreate',
        component: DocumentEditor,
      },
      {
        path: 'document/form/:id',
        name: 'DocumentEdit',
        component: DocumentEditor,
        props: true,
      },
      {
        path: 'settings',
        name: 'Settings',
        component: Settings,
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  auth.restoreSession()

  const isPublicRoute = to.meta.public === true
  const hasValidSession = auth.isAuthenticated && !auth.isTokenExpired()

  if (isPublicRoute) {
    if (hasValidSession) {
      return '/dashboard'
    }
    return true
  }

  if (!hasValidSession) {
    return '/login'
  }

  return true
})

export default router
