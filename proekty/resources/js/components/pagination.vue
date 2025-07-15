<template>
    <slot :data="data" :loading="loading" />

    <div class="pagination" v-if="last > 1">
        <button @click="current--" :disabled="current === 1 || loading">
            <LoadingDots v-if="loading && current !== last" />
            <span v-else>Назад</span>
        </button>

        <span>Страница {{ current }} из {{ last }}</span>

        <button @click="current++" :disabled="current === last || loading">
            <LoadingDots v-if="loading && current !== 1" />
            <span v-else>Вперёд</span>
        </button>
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'
import LoadingDots from './LoadingDots.vue'

const props = defineProps({
    url: {
        type: String,
        required: true,
    },
})

const current = ref(1)
const last = ref(1)
const data = ref([])
const loading = ref(false)

const fetchData = async () => {
    loading.value = true
    try {
        const urlWithPage = props.url.includes('?')
            ? `${props.url}&page=${current.value}`
            : `${props.url}?page=${current.value}`

        const response = await axios.get(urlWithPage)

        data.value = response.data.data
        current.value = response.data.current_page
        last.value = response.data.last_page
    } catch (error) {
        console.error('Ошибка при загрузке данных:', error)
        data.value = []
        current.value = 1
        last.value = 1
    } finally {
        loading.value = false
    }
}

watch(() => props.url, () => {
    current.value = 1
    fetchData()
})

watch(current, fetchData)

onMounted(fetchData)
</script>

<style scoped>
.pagination {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

button {
    background: #4f46e5;
    color: white;
  border: none;
  padding: 0.6rem 1.4rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

button:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
}

button:hover:enabled {
  background-color: #4338ca;
}
</style>
