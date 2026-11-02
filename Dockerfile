# --- 1. Construction des assets (Vite / Tailwind) ---
FROM node:22-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# --- 2. Application (PHP 8.4 + FrankenPHP) ---
FROM dunglas/frankenphp:1-php8.4
RUN install-php-extensions pdo_mysql intl gd zip bcmath opcache
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction \
 && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
 && chmod -R 777 storage bootstrap/cache \
 && chmod +x docker/start.sh

ENV PORT=8080
EXPOSE 8080
CMD ["sh", "docker/start.sh"]
