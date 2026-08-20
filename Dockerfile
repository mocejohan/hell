FROM php:8.2-fpm

# 1. Establecer directorio de trabajo
WORKDIR /var/www

# 2. Instalar dependencias del sistema y extensiones de PHP requeridas por Laravel, Filament y DomPDF/Excel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Copiar ejecutable de Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Copiar manifests de dependencias para aprovechar caché de capas Docker
COPY composer.json composer.lock package.json package-lock.json ./

# 5. Instalar dependencias PHP y Node.js
RUN composer install --no-interaction --no-scripts --no-progress --prefer-dist \
    && npm install

# 6. Copiar el código fuente completo del proyecto
COPY . .

# 7. Compilar assets de Vite y ajustar permisos de almacenamiento
RUN npm run build \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# 8. Configurar script de inicio (Entrypoint)
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# 9. Exponer puertos (8000 para Artisan Serve y 5173 para Vite Hot Reloading)
EXPOSE 8000 5173

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# 10. Comando predeterminado para iniciar el servidor de desarrollo
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
