import { createRouter, createWebHistory } from 'vue-router'
import TableComponent from '../js/pages/TableComponent.vue'
import ProductsByName from '../js/pages/ProductsByName.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: TableComponent
  },
  {
    path: '/products/inn/:inn',
    name: 'ProductsByInn',
    component: ProductsByName,
    props: true
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
