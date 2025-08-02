<template>
    <div class="container">
        <h2>Записи с названием: "{{ name }}"</h2>

        <Pagination :url="`/api/products/by-name/${encodeURIComponent(name)}`" :watchParams="[name]"
            v-slot="{ data, loading }">
            <div v-if="loading" class="loading-wrapper">
                <LoadingDots class="large-loading" />
            </div>

            <table v-else-if="data.length" class="table">
                <thead>
                    <tr>
                        <th>Наименование генерального проектировщика</th>
                        <!-- <th>Рейтинг</th> -->
                        <th>Номер проекта</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in data" :key="product.id">
                        <td>
                            {{ product.name }}
                        </td>
                        <!-- <td>{{ product.positive_conclusion }}</td> -->
                        <td>{{ product.project_number }}</td>
                    </tr>
                </tbody>
            </table>

            <p v-else>Нет записей с таким названием.</p>
        </Pagination>

        <!-- Назад -->
        <router-link v-if="!loading" to="/" class="back-link">← Назад к списку</router-link>
    </div>
</template>

<script setup>
import { useRoute } from 'vue-router'
import { ref, watch } from 'vue'

import LoadingDots from '../components/LoadingDots.vue'
import Pagination from '../components/Pagination.vue'

const route = useRoute()
const name = ref(route.params.name)
const loading = ref(false)

// обновляем name при переходе по роуту
watch(() => route.params.name, (newVal) => {
    name.value = newVal
})
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
