FROM node:alpine as frontend

COPY package.json /app/

RUN set -x ; cd /app \
      && npm install --registry=https://registry.npm.taobao.org

COPY webpack.mix.js webpack.config.js tailwind.config.js /app/
COPY resources/ /app/resources/

RUN set -x ; cd /app \
      && touch artisan \
      && mkdir -p public \
      && npm run production

FROM composer as composer

COPY database/ /app/database/
COPY composer.json composer.lock /app/

RUN set -x ; cd /app \
      && composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/ \
      && composer install \
           --ignore-platform-reqs \
           --no-interaction \
           --no-plugins \
           --no-scripts \
           --prefer-dist


