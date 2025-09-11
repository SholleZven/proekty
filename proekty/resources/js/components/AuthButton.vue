<template>
    <button class="login" @click="handleClick">
        {{ isAuth ? 'Выйти' : 'Войти' }}
    </button>
</template>

<script setup>
import http, { clearToken, getToken } from '../http'

const props = defineProps({
    isAuth: Boolean
})

const emit = defineEmits(['login', 'logout'])

const handleClick = async () => {
    if (props.isAuth) {
        try {
            const token = getToken()
            if (!token) {
                // Уже нет токена — просто локальный выход
                clearToken()
                emit('logout')
                return
            }
            // Важное: вызывать logout при наличии токена
            await http.post('/auth/logout')
            clearToken()
            emit('logout')
        } catch (e) {
            // Если токен просрочен → сервер даст 401 — чистим локально и выходим
            if (e?.response?.status === 401) {
                clearToken()
                emit('logout')
            } else {
                console.error('Ошибка logout', e)
                // При желании можно показать тост об ошибке
                clearToken()
                emit('logout')
            }
        }
    } else {
        emit('login')
    }
}
</script>

<style scoped>
button {
    background: #2e4968;
    color: #fff;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    height: 2rem;
}

button:hover {
    background: #4576d0ba;
}

.login {
    display: flex;
    flex-direction: column;
    justify-self: end;
}
</style>
