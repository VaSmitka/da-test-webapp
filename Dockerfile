FROM composer:2 AS vendor

WORKDIR /app

COPY app/helpers.php app/helpers.php
COPY database/ database/
COPY composer.json composer.lock /app/

RUN composer install \
    --optimize-autoloader \
    --ignore-platform-reqs \
    --prefer-dist \
    --no-dev \
    --no-ansi \
    --no-scripts \
    --no-interaction

# -------------------------------

FROM node:20 AS assets

WORKDIR /app

# Too many folder and files required - this is shorter and sufficient for now
COPY . .

RUN npm ci --omit=optional && \
    npm run build

# -------------------------------

FROM webdevops/php-apache:8.2-alpine

WORKDIR /app

ENV WEB_DOCUMENT_ROOT=/app/public

COPY --chown=application:application . /app

COPY --from=vendor /app/vendor/ /app/vendor/

COPY --from=assets /app/public/css/ /app/public/css/
COPY --from=assets /app/public/fonts/ /app/public/fonts/
COPY --from=assets /app/public/js/ /app/public/js/
COPY --from=assets /app/public/mix-manifest.json /app/public/mix-manifest.json

COPY --from=assets /app/resources/views/vendor/mail/html/themes/ /app/resources/views/vendor/mail/html/themes/

RUN mv docker.env.example .env && \
    composer dump-autoload --optimize
