<template>
    <div class="container">
        <h2>Записи с названием: "{{ name }}"</h2>

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
                        <router-link :to="`/products/name/${product.name}`">
                            {{ product.name }}
                        </router-link>
                    </td>
                    <td>{{ product.positive_conclusion }}</td>
                    <td>{{ product.project_number }}</td>
                </tr>
            </tbody>
        </table>

        <p v-else>Нет записей с таким названием.</p>

        <router-link to="/" class="back-link">← Назад к списку</router-link>
    </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import axios from 'axios'
import { useRoute } from 'vue-router'

const route = useRoute()
const name = ref(route.params.name)
const products = ref([])

const fetchByName = async () => {
    const response = await axios.get(`/api/products/by-name/${encodeURIComponent(name.value)}`)
    products.value = response.data
}

// Обновляем при изменении параметра name в маршруте
watch(() => route.params.name, (newVal) => {
    name.value = newVal
    fetchByName()
})

onMounted(fetchByName)
</script>

<!-- <style scoped>
    .back-link {
        display: block;
    }

</style> -->
