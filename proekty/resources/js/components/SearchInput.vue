<template>
    <div class="input-wrapper">
        <input type="text" v-model="searchQuery" @input="debouncedSearch" placeholder="Поиск по названию..."
            autocomplete="off" spellcheck="false" />
    </div>
</template>

<script setup>
import { ref } from 'vue'

const emit = defineEmits(['search'])

const searchQuery = ref('')

let timeout = null
const debouncedSearch = () => {
    clearTimeout(timeout)
    timeout = setTimeout(() => {
        emit('search', searchQuery.value.trim())
    }, 400)
}
</script>
