<template>
    <div class="container">
        <h2>Записи по ИНН: "{{ inn }}"</h2>

        <Pagination :url="`/api/products/by-inn/${encodeURIComponent(inn)}`" :watchParams="[inn]"
            v-slot="{ data, loading }">
            <div v-if="loading" class="loading-wrapper">
                <LoadingDots class="large-loading" />
            </div>

            <div v-else-if="data.length" class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Наименование проекта</th>
                            <th>Вид объекта</th>
                            <th>Функциональное назначение объекта</th>
                            <th>Вид услуги</th>
                            <th>Дата выдачи заключения</th>
                            <th>Результат заключения</th>
                            <th>Заявленная сметная стоимость в текущих ценах, млн. руб.</th>
                            <th>Откорректированная сметная стоимость в текущих ценах, млн. руб.</th>
                            <th>Этап строительных работ</th>
                            <th>Cрок экспертизы, раб. дни</th>
                            <th>Cрок комплектности, раб. дни</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="product in data" :key="product.id">
                            <td>{{ product.project_name }}</td>
                            <td>{{ product.object_type }}</td>
                            <td>{{ product.functional_purpose }}</td>
                            <td>{{ product.service_type }}</td>
                            <td>{{ product.conclusion_date }}</td>
                            <td>{{ product.conclusion_result }}</td>
                            <td>{{ product.cost_declared }}</td>
                            <td>{{ product.cost_adjusted }}</td>
                            <td>{{ product.stage_construction_works }}</td>
                            <td>{{ product.expertise_date }}</td>
                            <td>{{ product.complect_date }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

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
const inn = ref(route.params.inn)
const loading = ref(false)

// обновляем name при переходе по роуту
watch(() => route.params.inn, (newVal) => {
    inn.value = newVal
})
</script>

<style scoped>
.loading-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 200px;
}

.large-loading span {
    width: 10px;
    height: 10px;
    background-color: #324598e8;
}

.back-link {
    display: inline-block;
    margin-top: 2rem;
    color: #324598e8;
    text-decoration: none;
    font-weight: bold;
    transition: color 0.2s;
}

.back-link:hover {
    color: #a9a5f566;
}
</style>
