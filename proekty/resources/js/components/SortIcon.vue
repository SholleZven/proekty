<template>
  <span class="sort-icon" :class="{ active: sortColumn === column }">
    <span v-if="sortColumn === column">
      <span v-if="sortDirection === 'asc'">▲</span>
      <span v-else>▼</span>
    </span>
    <span v-else>↕</span>
  </span>
</template>

<script setup>
defineProps({
  column: { type: String, required: true },
  sortColumn: { type: String, default: null },
  sortDirection: { type: String, default: 'desc' }
})
</script>

<style scoped>
.sort-icon {
  margin-left: 6px;
  font-size: 12px;
  opacity: .6;
}
.sort-icon.active { opacity: 1; }
</style>
