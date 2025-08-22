# Используем Node для сборки фронтенда
FROM node:20-alpine AS build

WORKDIR /app

# Копируем package-файлы
COPY package*.json ./

# Устанавливаем зависимости
RUN npm install

# Копируем весь проект
COPY . .

# Собираем проект для продакшена
RUN npm run build

# Второй этап — минимальный Nginx образ для отдачи статических файлов
FROM nginx:alpine

# Копируем билд в nginx
COPY --from=build /app/dist /usr/share/nginx/html

# Копируем свой конфиг nginx (опционально)
COPY ./docker/nginx_frontend.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
