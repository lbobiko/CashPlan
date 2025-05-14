FROM php:8.2-apache

# Skopiuj kod do katalogu domyślnego Apache
COPY ./html/ /var/www/html/

# Włącz mod_rewrite (jeśli będzie potrzebny)
RUN a2enmod rewrite

# Ustawienie uprawnień (opcjonalnie)
RUN chown -R www-data:www-data /var/www/html