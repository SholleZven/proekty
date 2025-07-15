<template>
    <div class="container">
        <h1>Проекты</h1>

        <!-- Загрузка Excel -->
        <form @submit.prevent="uploadFile" class="upload">
            <label class="custom-file-upload">
                <input type="file" @change="handleFileUpload" />
                <div class="span-input-upload">
                    <span>📂 Выберите файл Exсel</span>
                    <span v-if="fileName" class="file-name">{{ fileName }}</span>
                </div>
            </label>
            <button type="submit" :disabled="loadingUpload">
                <LoadingDots v-if="loadingUpload" />
                <span v-else>Загрузить файл</span>
            </button>
        </form>

        <!-- Таблица -->
        <table v-if="products.length" class="table">
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
                        <RouterLink :to="`/products/name/${encodeURIComponent(product.name)}`">
                            {{ product.name }}
                        </RouterLink>
                    </td>
                    <td>{{ product.positive_conclusion }}</td>
                    <td>{{ product.project_number }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Пагинация -->
        <Pagination
            v-if="pagination.last > 1"
            :current="currentPage"
            :last="pagination.last"
            :loading="loadingPagination"
            @change="changePage" />
    </div>
</template>


<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

import LoadingDots from '../components/LoadingDots.vue'
import Pagination from '../components/pagination.vue'

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
        const res = await axios.get(`/api/products?page=${page}`)
        products.value = res.data.data
        pagination.value = {
            current: res.data.current_page,
            last: res.data.last_page,
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

button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
