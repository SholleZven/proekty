<template>
  <div>
    <!-- Слот с результатами -->
    <slot :data="items" :loading="loading" />

    <!-- Панель пагинации -->
    <div v-if="lastPage > 1" class="pagination">
      <button
        @click="changePage(currentPage - 1)"
        :disabled="currentPage === 1 || loading"
      >
        <LoadingDots v-if="loading && currentPage !== 1" />
        <span v-else>Назад</span>
      </button>

      <span>Страница {{ currentPage }} из {{ lastPage }}</span>

      <button
        @click="changePage(currentPage + 1)"
        :disabled="currentPage === lastPage || loading"
      >
        <LoadingDots v-if="loading && currentPage !== lastPage" />
        <span v-else>Вперёд</span>
      </button>
    </div>
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
  watchParams: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['loaded'])

const items = ref([])
const currentPage = ref(1)
const lastPage = ref(1)
const loading = ref(false)

const fetchPage = async (page = 1) => {
  loading.value = true
  try {
    const response = await axios.get(`${props.url}?page=${page}`)
    items.value = response.data.data
    currentPage.value = response.data.current_page
    lastPage.value = response.data.last_page
    emit('loaded', items.value)
  } catch (e) {
    items.value = []
  } finally {
    loading.value = false
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= lastPage.value) {
    fetchPage(page)
  }
}

// Обновление при изменении параметров
watch(
  () => props.watchParams,
  () => {
    currentPage.value = 1
    fetchPage(1)
  },
  { deep: true }
)

onMounted(() => fetchPage(1))
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

button:hover {
  background-color: #4338ca;
}
</style>
