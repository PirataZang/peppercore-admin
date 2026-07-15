import { createRouter, createWebHistory } from 'vue-router'
import Dashboard from '../pages/Dashboard.vue'
import UserList from '../pages/user/UserList.vue'
import UserForm from '../pages/user/UserForm.vue'

const routes = [
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard
  },
  {
    path: '/user',
    name: 'UserList',
    component: UserList
  },
  {
    path: '/user/form',
    name: 'UserCreate',
    component: UserForm
  },
  {
    path: '/user/form/:id',
    name: 'UserEdit',
    component: UserForm,
    props: true
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
