FROM php:8.1-fpm

RUN apt-get update && apt-get install -y cron

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Nginx
RUN apt-get install -y nginx

# Remove default Nginx config
RUN rm /etc/nginx/sites-enabled/default

# Copy custom Nginx config
COPY nginx.conf /etc/nginx/sites-enabled/

# TODO: set up .env.local file for prod

RUN composer install

RUN mkdir -p var/cache var/log
RUN chown -R www-data:www-data var/cache var/log

# if we ever want to scale, crontab needs to be moved to a separate container
COPY crontab /etc/cron.d/psp-crontab
RUN chmod 0644 /etc/cron.d/psp-crontab && crontab /etc/cron.d/psp-crontab
RUN service cron start

EXPOSE 80
