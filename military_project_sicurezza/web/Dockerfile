FROM php:7.4-apache

# Installo dipendenze per PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev curl \
    && docker-php-ext-install pgsql pdo_pgsql

# Abilito mod_rewrite
RUN a2enmod rewrite

# Copio i file dell'applicazione
COPY html/ /var/www/html/

# Permessi
RUN chown -R www-data:www-data /var/www/html