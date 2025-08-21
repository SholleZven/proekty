FROM node:22 AS build

WORKDIR /app

# Копируем package.json и ставим зависимости
COPY frontend/package*.json ./
RUN npm install

# Копируем исходники и билдим
COPY frontend/ .
RUN npm run build

# production контейнер nginx
FROM nginx:1.27-alpine

# Копируем билд во фронтовую папку nginx
COPY --from=build /app/dist /usr/share/nginx/html

# Копируем конфиг (переопределим дефолтный)
COPY nginx/default.conf /etc/nginx/conf.d/default.conf
