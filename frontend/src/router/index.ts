import { createRouter, createWebHistory } from 'vue-router'
import LinksView from '../views/LinksView.vue'
import QuickShortenView from '../views/QuickShortenView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'links',
      component: LinksView,
    },
    {
      path: '/quick-shorten',
      name: 'quick-shorten',
      component: QuickShortenView,
    },
    {
      path: '/links',
      redirect: '/',
    },
  ],
})

export default router
