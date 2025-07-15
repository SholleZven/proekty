<template>
  <div class="container">
    <h1>Проекты</h1>

    <!-- Загрузка Excel -->
    <form @submit.prevent="uploadFile" class="upload">
      <label class="custom-file-upload">
        <input type="file" @change="handleFileUpload" />
        <div class="span-input-upload">
          <span>📂 Выберите файл Excel</span>
          <span v-if="fileName" class="file-name">{{ fileName }}</span>
        </div>
      </label>
      <button type="submit" :disabled="loadingUpload">
        <LoadingDots v-if="loadingUpload" />
        <span v-else>Загрузить файл</span>
      </button>
    </form>

    <!-- Пагинация Таблица -->
    <Pagination url="/api/products" v-slot="{ data, loading }">
      <div v-if="loading" class="loading-wrapper">
        <LoadingDots class="large-loading" />
      </div>

      <!-- Таблица -->

      <table v-else-if="data.length" class="table">
        <thead>
          <tr>
            <th>Название</th>
            <th>Рейтинг</th>
            <th>Номер проекта</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in data" :key="product.id">
            <td>
              <RouterLink :to="`/products/name/${encodeURIComponent(product.name)}`">
                {{ product.name }}
              </RouterLink>
            </td>
            <td>{{ product.positive_conclusion }}</td>
            <td>{{ product.project_number }}</td>
          </tr>
        </tbody>
      </table>

      <p v-else>Нет данных.</p>
    </Pagination>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

import LoadingDots from '../components/LoadingDots.vue'
import Pagination from '../components/Pagination.vue'

const file = ref(null)
const fileName = ref('')
const loadingUpload = ref(false)

const handleFileUpload = (e) => {
  const selected = e.target.files[0]
  if (selected) {
    file.value = selected
    fileName.value = selected.name
  }
}

const uploadFile = async () => {
  if (!file.value) return
  loadingUpload.value = true

  const formData = new FormData()
  formData.append('file', file.value)

  try {
    await axios.post('/api/products/upload', formData)
  } finally {
    loadingUpload.value = false
  }
}
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

.custom-file-upload {
  display: inline-block;
  background-color: #4f46e5;
  color: white;
  border-radius: 8px;
  padding: 0.8rem 1.5rem;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.3s;
}

.custom-file-upload:hover {
  background-color: #4338ca;
}

.custom-file-upload input {
  display: none;
}

.file-name {
  font-style: italic;
  color: #94dd82;
}

.upload {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.span-input-upload {
  display: flex;
  flex-direction: column;
  text-align: center;
}

.table {
  width: 100%;
  border-spacing: 0 10px;
}

.table th,
.table td {
  padding: 1rem;
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
</style>
