FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

# ...
# ვამატებთ ორივე საჭირო დეველოპერულ პაკეტს ერთ ბრძანებაში
RUN apk add --no-cache postgresql-dev libsodium-dev

# მხოლოდ ამის შემდეგ ვაყენებთ PHP გაფართოებებს
RUN docker-php-ext-install pdo pdo_pgsql sodium
# ...

# Composer-ის დაყენება
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
