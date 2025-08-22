# Этап сборки Vue
FROM node:20-alpine AS build

WORKDIR /app

# Устанавливаем зависимости
COPY package*.json ./
RUN npm ci --legacy-peer-deps

# Копируем весь проект
COPY . .

# Сборка приложения
RUN npm run build

# Этап Nginx для отдачи статики
FROM nginx:alpine

# Копируем собранные файлы
COPY --from=build /app/public/build /usr/share/nginx/html

# Копируем конфигурацию Nginx
COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
