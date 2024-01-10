FROM php:8.2 as php

RUN apt-get update -y
RUN apt-get install -y unzip libpq-dev libcurl4-gnutls-dev

RUN docker-php-ext-install pdo pdo_mysql bcmath

RUN apt-get update && \
    apt-get install -y \
        zlib1g-dev 

RUN apt-get update && apt-get install -y libpng-dev 
RUN apt-get install -y \
    libwebp-dev \
    libjpeg62-turbo-dev \
    libpng-dev libxpm-dev \
    libfreetype6-dev

RUN docker-php-ext-configure gd --with-jpeg --with-freetype --with-webp

RUN docker-php-ext-install gd

WORKDIR /var/www

COPY . .

COPY --from=composer:2.3.5 /usr/bin/composer /usr/bin/composer

RUN ["chmod", "+x", "docker/entrypoint.sh"]

ENV PORT=8000
ENTRYPOINT ["docker/entrypoint.sh"]

