import axios from 'axios'

const TOKEN_KEY = 'token'

export const getToken = () => localStorage.getItem(TOKEN_KEY)
export const setToken = (t) => localStorage.setItem(TOKEN_KEY, t)
export const clearToken = () => localStorage.removeItem(TOKEN_KEY)

const http = axios.create({
  baseURL: '/api',
  headers: {
    Accept: 'application/json'
  },
  // withCredentials НЕ нужен для JWT в заголовке Authorization
})

// Подставляем Bearer перед КАЖДЫМ запросом
http.interceptors.request.use((config) => {
  const token = getToken()
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Можно мягко обрабатывать 401 (например, чистить токен при /auth/me или /auth/logout)
http.interceptors.response.use(
  (r) => r,
  (error) => {
    const status = error?.response?.status
    if (status === 401) {
      // Внимание: не выходим автоматически, просто даём знать вызывающей стороне.
      // Там решаем — показать форму логина или очистить токен
    }
    return Promise.reject(error)
  }
)

export default http
