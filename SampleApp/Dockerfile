FROM php:8.1-fpm
RUN apt update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

RUN pecl install grpc \
    && docker-php-ext-enable grpc

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer


WORKDIR /app
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony
RUN composer install

COPY . .

EXPOSE 8000

CMD symfony server:start