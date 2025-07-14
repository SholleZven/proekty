<template>
  <div class="pagination">
    <button
      @click="$emit('change', current - 1)"
      :disabled="current === 1 || loading"
    >
      <LoadingDots v-if="loading && current !== last" />
      <span v-else>Назад</span>
    </button>

    <span>Страница {{ current }} из {{ last }}</span>

    <button
      @click="$emit('change', current + 1)"
      :disabled="current === last || loading"
    >
      <LoadingDots v-if="loading && current !== 1" />
      <span v-else>Вперёд</span>
    </button>
  </div>
</template>

<script setup>

import LoadingDots from './LoadingDots.vue';

defineProps({
  current: Number,
  last: Number,
  loading: Boolean,
})
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
