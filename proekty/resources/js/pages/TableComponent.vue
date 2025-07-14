<template>
    <div class="container">
        <h1>Проекты</h1>

        <form @submit.prevent="uploadFile" class="upload">
            <label class="custom-file-upload">
                <input type="file" @change="handleFileUpload" />
                <div class="span-input-upload">
                <span> 📂 Выберите файл Exсel </span>
                <span v-if="fileName" class="file-name">{{ fileName }}</span>
                </div>
            </label>
            <button type="submit" :disabled="loadingUpload">
                <LoadingDots v-if="loadingUpload" />
                <span v-else>Загрузить файл</span>
            </button>
        </form>

        <table class="table" v-if="products.length">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Рейтинг</th>
                    <th>Номер проекта</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="product in products" :key="product.id">
                    <td>{{ product.name }}</td>
                    <td>{{ product.positive_conclusion }}</td>
                    <td>{{ product.project_number }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Пагинация -->
        <div v-if="pagination.last > 1" class="pagination">
            <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1 || loadingPagination">
                <LoadingDots v-if="loadingPagination && currentPage !== 1" />
                <span v-else>Назад</span>
            </button>

            <span>Страница {{ currentPage }} из {{ pagination.last }}</span>

            <button @click="changePage(currentPage + 1)"
                :disabled="currentPage === pagination.last || loadingPagination">
                <LoadingDots v-if="loadingPagination && currentPage !== pagination.last" />
                <span v-else>Вперёд</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import LoadingDots from '../components/LoadingDots.vue'

const products = ref([])
const file = ref(null)
const fileName = ref('')
const currentPage = ref(1)

const pagination = ref({
    current: 1,
    last: 1,
})

const loadingUpload = ref(false)
const loadingPagination = ref(false)

const fetchProducts = async (page = 1) => {
    loadingPagination.value = true
    try {
        const response = await axios.get(`/api/products?page=${page}`)
        products.value = response.data.data
        pagination.value = {
            current: response.data.current_page,
            last: response.data.last_page,
        }
    } finally {
        loadingPagination.value = false
    }
}

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
        await fetchProducts(currentPage.value)
    } finally {
        loadingUpload.value = false
    }
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

button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.pagination {
    margin-top: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.custom-file-upload {
    display: inline-block;
    padding: 10px 20px;
    background-color: #4f46e5;
    color: white;
    font-weight: bold;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.custom-file-upload:hover {
    background-color: #4338ca;
}

.custom-file-upload input[type="file"] {
    display: none;
}

.file-name {
  font-style: italic;
  color: #94dd82;
}

.span-input-upload {
 display: flex;
 flex-direction: column;
 text-align: center;
}

</style>
