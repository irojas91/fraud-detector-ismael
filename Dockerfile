FROM php:8.4-cli-alpine
RUN apk add --no-cache git unzip libxml2-dev bash \
    && docker-php-ext-install xml
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /app
CMD ["tail", "-f", "/dev/null"]
