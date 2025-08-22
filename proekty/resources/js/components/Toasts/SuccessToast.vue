<template>
    <div v-if="show" class="toast">
        <span class="toast-message">{{ message }}</span>
        <button class="toast-close" @click="$emit('close')">✕</button>
    </div>
</template>

<script setup>
import { watch } from 'vue'

const props = defineProps({
    show: Boolean,
    message: String
})

const emit = defineEmits(['close'])

watch(
    () => props.show,
    (val) => {
        if (val) {
            setTimeout(() => emit('close'), 5000)
        }
    }
)
</script>

<style scoped>
.toast {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 9999;
    background-color: #9ce096;
    color: white;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-width: 250px;
    max-width: 300px;
    opacity: 0;
    transform: translateY(-20px);
    animation: slide-fade-in 0.3s forwards;
}

.toast-message {
    flex: 1;
    font-size: 0.875rem;
}

.toast-close {
    background: transparent;
    border: none;
    color: white;
    font-weight: bold;
    cursor: pointer;
    margin-left: 0.5rem;
}

@keyframes slide-fade-in {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slide-fade-out {
    to {
        opacity: 0;
        transform: translateY(-20px);
    }
}
</style>
