<template>
    <div class="modal-overlay" @click.self="closeModal">
        <div class="modal">
            <h3 class="modal-title">Авторизация</h3>

            <input v-model="login" type="text" placeholder="Логин" />
            <input v-model="password" type="password" placeholder="Пароль" />

            <div class="actions">
                <button class="btn primary" @click="doLogin">Войти</button>
                <button class="btn secondary" @click="closeModal">Отмена</button>
            </div>

            <ErrorToast :show="showError" :message="errorMessage" @close="showError = false" />
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import http, { setToken } from '../http'
import ErrorToast from './Toasts/ErrorToast.vue'

const emit = defineEmits(['login-success', 'close'])

const login = ref('')
const password = ref('')

const showError = ref(false)
const errorMessage = ref('')

const doLogin = async () => {
    try {
        const { data } = await http.post('/auth/login', {
            login: login.value,
            password: password.value
        })

        if (data?.token) {
            setToken(data.token)
            emit('login-success')
            closeModal()
        } else {
            throw new Error('Сервер не вернул токен')
        }
    } catch (e) {
        console.error('Неверный логин или пароль', e)
        errorMessage.value = 'Неверный логин или пароль'
        showError.value = true
    }
}

const closeModal = () => {
    emit('close')
}
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal {
    background: #fff;
    padding: 2rem;
    border-radius: 12px;
    width: 320px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    position: relative;
}

.modal-title {
    margin-bottom: 1rem;
    font-size: 1.25rem;
    text-align: center;
}

.modal input {
    width: 100%;
    padding: 0.6rem;
    margin-bottom: 1rem;
    border: 1px solid #ccc;
    border-radius: 8px;
}

.actions {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
}

.btn {
    flex: 1;
    padding: 0.6rem 1rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}

.btn.primary {
    background: #2e4968;
    color: #fff;
}

.btn.primary:hover {
    background: #4576d0ba;
}

.btn.secondary {
    background: #e0e0e0;
    color: #333;
}

.btn.secondary:hover {
    background: #c9c9c9;
}
</style>
