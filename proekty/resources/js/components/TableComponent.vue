<template>
  <div class="container">
    <h1>Проекты</h1>

    <form @submit.prevent="uploadFile" class="upload">
      <input type="file" @change="handleFileUpload" />
      <button type="submit">Загрузить Excel</button>
    </form>

    <table class="table" v-if="products.length">
      <thead>
        <tr>
          <th>Название</th>
          <th>Рейтинг</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in products" :key="product.id">
          <td>{{ product.name }}</td>
          <td>{{ product.positive_conclusion }}</td>
        </tr>
      </tbody>
    </table>

    <!-- Пагинация -->
    <div v-if="pagination.last > 1" class="pagination">
      <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1">Назад</button>
      <span>Страница {{ currentPage }} из {{ pagination.last }}</span>
      <button @click="changePage(currentPage + 1)" :disabled="currentPage === pagination.last">Вперёд</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const products = ref([])
const file = ref(null)

const pagination = ref({
  current: 1,
  last: 1,
})
const currentPage = ref(1)

const fetchProducts = async (page = 1) => {
  const response = await axios.get(`/api/products?page=${page}`)
  products.value = response.data.data
  pagination.value = {
    current: response.data.current_page,
    last: response.data.last_page,
  }
}

const handleFileUpload = (e) => {
  file.value = e.target.files[0]
}

const uploadFile = async () => {
  if (!file.value) return

  const formData = new FormData()
  formData.append('file', file.value)

  await axios.post('/api/products/upload', formData)
  await fetchProducts(currentPage.value)
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last) {
    currentPage.value = page
    fetchProducts(page)
  }
}

onMounted(() => fetchProducts())
</script>

<style scoped>
.pagination {
  margin-top: 1rem;
  display: flex;
  gap: 1rem;
  align-items: center;
}

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
