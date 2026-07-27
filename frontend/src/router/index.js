import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppLayout from '@/layouts/AppLayout.vue'
import Login from '@/pages/Login.vue'
import Dashboard from '@/pages/Dashboard.vue'
import UserList from '@/pages/user/UserList.vue'
import UserForm from '@/pages/user/UserForm.vue'

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
