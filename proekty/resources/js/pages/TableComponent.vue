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

        <!-- Поиск -->
        <SearchInput @search="handleSearch" />

        <!-- Пагинация + Таблица -->
        <Pagination :key="reloadKey"
            :url="searchTerm ? `/api/products?search=${encodeURIComponent(searchTerm)}` : '/api/products'"
            v-slot="{ data, loading }">
            <div v-if="loading" class="loading-wrapper">
                <LoadingDots class="large-loading" />
            </div>

            <table v-else-if="data.length" class="table">
                <thead>
                    <tr>
                        <th>Наименование генерального проектировщика</th>
                        <th>ИНН</th>
                        <th>Всего заключений</th>
                        <th>Положительных</th>
                        <th>Отрицательных</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in data" :key="product.inn">
                        <td>
                            <RouterLink :to="`/products/name/${encodeURIComponent(product.name)}`">
                                {{ product.name }}
                            </RouterLink>
                        </td>
                        <td>{{ product.inn }}</td>
                        <td>{{ product.quantity_conclusions }}</td>
                        <td class="positive">{{ product.quantity_positive_conclusion }}</td>
                        <td class="negative">{{ product.quantity_negative_conclusion }}</td>
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
import SearchInput from '../components/SearchInput.vue'

const file = ref(null)
const fileName = ref('')
const loadingUpload = ref(false)
const searchTerm = ref('')
const reloadKey = ref(0) // Ключ для обновления Pagination

const handleSearch = (term) => {
    searchTerm.value = term
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
        reloadKey.value++ // Обновляем пагинацию
    } catch (error) {
        console.error('Ошибка загрузки файла', error)
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

.positive {
    color: green;
    font-weight: bold;
}

.negative {
    color: red;
    font-weight: bold;
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
