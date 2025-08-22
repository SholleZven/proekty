import { ref } from 'vue'

export function useSort() {
  const sortColumn = ref(null)   // имя колонки
  const sortDirection = ref('desc') // asc | desc

  const sortBy = (column) => {
    if (sortColumn.value === column) {
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
      sortColumn.value = column
      sortDirection.value = 'asc'
    }
  }

  return { sortColumn, sortDirection, sortBy }
}
