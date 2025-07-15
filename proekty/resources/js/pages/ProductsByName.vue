<template>
  <div class="container">
    <h2>Записи с названием: "{{ name }}"</h2>

    <!-- Загрузка -->
    <div v-if="loading" class="loading-wrapper">
      <LoadingDots class="large-loading" />
    </div>

    <!-- Таблица -->
    <table v-else-if="products.length" class="table">
      <thead>
        <tr>
          <th>Название</th>
          <th>Рейтинг</th>
          <th>Номер проекта</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in products" :key="product.id">
          <td>
            <router-link :to="`/products/name/${product.name}`">
              {{ product.name }}
            </router-link>
          </td>
          <td>{{ product.positive_conclusion }}</td>
          <td>{{ product.project_number }}</td>
        </tr>
      </tbody>
    </table>

    <!-- Нет данных -->
    <p v-else>Нет записей с таким названием.</p>

    <!-- Пагинация -->
    <Pagination
      v-if="!loading && lastPage > 1"
      :current="currentPage"
      :last="lastPage"
      :loading="loadingPagination"
      @change="changePage"
    />

    <!-- Назад -->
    <router-link v-if="!loading" to="/" class="back-link">← Назад к списку</router-link>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import axios from 'axios'
import { useRoute } from 'vue-router'

import LoadingDots from '../components/LoadingDots.vue'
import Pagination from '../components/Pagination.vue'

const route = useRoute()
const name = ref(route.params.name)

const products = ref([])
const loading = ref(false)
const loadingPagination = ref(false)

const currentPage = ref(1)
const lastPage = ref(1)

const fetchByName = async (page = 1) => {
  loading.value = page === 1
  loadingPagination.value = page !== 1
  try {
    const response = await axios.get(`/api/products/by-name/${encodeURIComponent(name.value)}?page=${page}`)
    products.value = response.data.data
    currentPage.value = response.data.current_page
    lastPage.value = response.data.last_page
  } catch (e) {
    products.value = []
  } finally {
    loading.value = false
    loadingPagination.value = false
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= lastPage.value) {
    fetchByName(page)
  }
}

// Следим за маршрутом (если название изменится)
watch(() => route.params.name, (newVal) => {
  name.value = newVal
  currentPage.value = 1
  fetchByName(1)
})

onMounted(() => fetchByName(1))
</script>

<style scoped>
.container {
  max-width: 960px;
  margin: 2rem auto;
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.loading-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 200px;
}

.large-loading span {
  width: 10px;
  height: 10px;
  background-color: #4f46e5;
}

.table {
  width: 100%;
  border-spacing: 0 10px;
}

.table th,
.table td {
  padding: 1rem;
}

.back-link {
  display: inline-block;
  margin-top: 2rem;
  color: #4f46e5;
  text-decoration: none;
  font-weight: bold;
  transition: color 0.2s;
}

.back-link:hover {
  color: #3730a3;
}
</style>
