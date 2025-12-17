FROM php:8.3-apache

# Встановлення системних залежностей
RUN apt-get update && apt-get install -y \
libzip-dev \
zip \
unzip \
git \
&& docker-php-ext-install pdo pdo_mysql zip \
&& apt-get clean \
&& rm -rf /var/lib/apt/lists/*

# Встановлення Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Активація mod_rewrite для Apache
RUN a2enmod rewrite

# Встановлення робочої директорії
WORKDIR /var/www/html

# Копіювання composer.json та встановлення залежностей
COPY composer.json composer.lock* ./
RUN composer install --no-dev --optimize-autoloader --no-scripts || true

# Копіювання коду додатку
COPY . .

# Встановлення прав доступу
RUN chown -R www-data:www-data /var/www/html \
&& chmod -R 755 /var/www/html

EXPOSE 80
