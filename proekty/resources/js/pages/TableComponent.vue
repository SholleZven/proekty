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
        <Pagination :key="reloadKey" :url="computedUrl" v-slot="{ data, loading }">
            <div v-if="loading" class="loading-wrapper">
                <LoadingDots class="large-loading" />
            </div>

            <div v-else-if="normalizeRows(data).length" class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th @click="onSort('name')" class="sortable">
                                Наименование генерального проектировщика
                                <SortIcon column="name" :sortColumn="sortColumn" :sortDirection="sortDirection" />
                            </th>
                            <th @click="onSort('inn')" class="sortable">
                                ИНН
                                <SortIcon column="inn" :sortColumn="sortColumn" :sortDirection="sortDirection" />
                            </th>
                            <th @click="onSort('rating')" class="sortable">
                                Рейтинг
                                <SortIcon column="rating" :sortColumn="sortColumn" :sortDirection="sortDirection" />
                            </th>
                            <th @click="onSort('quantity_conclusions')" class="sortable">
                                Всего заключений
                                <SortIcon column="quantity_conclusions" :sortColumn="sortColumn"
                                    :sortDirection="sortDirection" />
                            </th>
                            <th @click="onSort('quantity_positive_conclusion')" class="sortable">
                                Положительные
                                <SortIcon column="quantity_positive_conclusion" :sortColumn="sortColumn"
                                    :sortDirection="sortDirection" />
                            </th>
                            <th @click="onSort('quantity_negative_conclusion')" class="sortable">
                                Отрицательные
                                <SortIcon column="quantity_negative_conclusion" :sortColumn="sortColumn"
                                    :sortDirection="sortDirection" />
                            </th>
                            <th @click="onSort('average_expertise_date')" class="sortable">
                                Средний срок экспертизы
                                <SortIcon column="average_expertise_date" :sortColumn="sortColumn"
                                    :sortDirection="sortDirection" />
                            </th>
                            <th @click="onSort('average_complect_date')" class="sortable">
                                Средний срок комплектности
                                <SortIcon column="average_complect_date" :sortColumn="sortColumn"
                                    :sortDirection="sortDirection" />
                            </th>
                            <th @click="onSort('most_common_functional_purpose')" class="sortable">
                                Специализация по направлению
                                <SortIcon column="most_common_functional_purpose" :sortColumn="sortColumn"
                                    :sortDirection="sortDirection" />
                            </th>
                            <th @click="onSort('most_common_stage_construction_works')" class="sortable">
                                Специализация по виду работ
                                <SortIcon column="most_common_stage_construction_works" :sortColumn="sortColumn"
                                    :sortDirection="sortDirection" />
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="product in normalizeRows(data)" :key="product.inn">
                            <td>{{ product.name }}</td>
                            <td>
                                <RouterLink :to="`/products/inn/${encodeURIComponent(product.inn)}`">
                                    {{ product.inn }}
                                </RouterLink>
                            </td>
                            <td>{{ product.rating }}</td>
                            <td>{{ product.quantity_conclusions }}</td>
                            <td class="positive">{{ product.quantity_positive_conclusion }}</td>
                            <td class="negative">{{ product.quantity_negative_conclusion }}</td>
                            <td>{{ product.average_expertise_date }}</td>
                            <td>{{ product.average_complect_date }}</td>
                            <td>{{ product.most_common_functional_purpose }}</td>
                            <td>{{ product.most_common_stage_construction_works }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p v-else>Нет данных.</p>
        </Pagination>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'

import LoadingDots from '../components/LoadingDots.vue'
import Pagination from '../components/Pagination.vue'
import SearchInput from '../components/SearchInput.vue'
import SortIcon from '../components/SortIcon.vue'
import { useSort } from '../composables/useSort'

const file = ref(null)
const fileName = ref('')
const loadingUpload = ref(false)
const searchTerm = ref('')
const reloadKey = ref(0)

const { sortColumn, sortDirection, sortBy } = useSort()

const handleSearch = (term) => {
    searchTerm.value = term
    reloadKey.value++ // перезагрузить список при новом поиске
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
        reloadKey.value++
    } catch (e) {
        console.error('Ошибка загрузки файла', e)
    } finally {
        loadingUpload.value = false
    }
}

// Нормализация данных из Pagination (там может прийти paginator { data: [...] } или просто массив)
const normalizeRows = (payload) => {
    if (payload && Array.isArray(payload.data)) return payload.data
    if (Array.isArray(payload)) return payload
    return []
}

// URL с учётом сортировки и поиска
const computedUrl = computed(() => {
    const base = '/api/products'
    const params = new URLSearchParams()
    if (searchTerm.value) params.set('search', searchTerm.value)
    if (sortColumn.value) params.set('sortColumn', sortColumn.value)
    if (sortDirection.value) params.set('sortDirection', sortDirection.value)
    return `${base}?${params.toString()}`
})

// Обработчик клика по заголовку
const onSort = (column) => {
    sortBy(column)
    reloadKey.value++ // дернуть перерисовку Pagination (если он не реагирует на смену :url)
}
</script>

<style scoped>
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

th.sortable {
    cursor: pointer;
    user-select: none;
}

</style>
