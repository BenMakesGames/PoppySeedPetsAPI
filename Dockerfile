FROM php:8.1-cli

RUN apt-get update && apt-get install -y cron

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
COPY . /app

# TODO: set up .env.local file for prod

RUN composer install

# if we ever want to scale, redis needs to be moved to a separate container (or use an AWS redis server)
# TODO: install & run redis

# if we ever want to scale, crontab needs to be moved to a separate container
COPY crontab /etc/cron.d/psp-crontab
RUN chmod 0644 /etc/cron.d/psp-crontab && crontab /etc/cron.d/psp-crontab
RUN service cron start

EXPOSE 8000

symfony server:start --port=8000