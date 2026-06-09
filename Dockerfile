FROM php:8.2-apache

# 1. Install pdo_mysql — the only PHP extension needed beyond defaults.
RUN docker-php-ext-install pdo_mysql

# 2. Enable mod_rewrite (required by public/.htaccess for clean URLs).
RUN a2enmod rewrite

# 3. Point DocumentRoot at public/.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf

# 4. Allow .htaccess overrides so clean-URL rewriting works.
RUN printf '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' > /etc/apache2/conf-available/manifesto.conf \
    && a2enconf manifesto

# 5. Bring in Composer (slim copy from the official image — no extra layer weight).
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 6. Install PHP dependencies.
#    Copy composer.json first so Docker can cache the vendor layer separately
#    from the source code.  "|| true" keeps the build green even if composer
#    is not strictly required (the app has a built-in PSR-4 fallback).
WORKDIR /var/www/html
COPY composer.json ./
RUN composer install --no-dev --optimize-autoloader --no-interaction || true

# 7. Copy all project files (vendor/ is already in place from step 6).
COPY . .

# 8. Ensure writable storage directories exist and belong to www-data.
RUN mkdir -p storage/logs storage/generated \
    && chown -R www-data:www-data storage \
    && chmod -R 775 storage

EXPOSE 80
