FROM php:8.4-apache AS php-base

RUN <<EOF
set -aeu
apt-get update -y
apt-get install -y --no-install-recommends --no-install-suggests \
  libfreetype-dev \
  libjpeg62-turbo-dev \
  libpng-dev \
  libonig-dev \
  libxml2-dev \
  libicu-dev \
  libgmp-dev \
  libzip-dev \
  default-mysql-client \
  libarchive-tools \
  msmtp \
  msmtp-mta \
  p7zip-full unzip \
  git
rm -rf /var/lib/apt/lists/*
EOF

RUN <<EOF
docker-php-ext-configure gd --with-freetype --with-jpeg
docker-php-ext-install -j$(nproc) gd mbstring simplexml mysqli fileinfo intl zip
EOF

RUN a2enmod rewrite

ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY ./docker/aowow/usr/local/bin/* /usr/local/bin

RUN <<EOF
touch /var/log/msmtp.log
chmod ugo+rw /var/log/msmtp.log
EOF

WORKDIR /var/www/html

CMD ["/usr/local/bin/entrypoint.sh"]

HEALTHCHECK --interval=5s --timeout=5s --retries=30 CMD curl -f http://127.0.0.1:80

FROM php-base AS php-dev

ENV ENV=dev APP_ENV=dev

RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

FROM php-base AS php-prod

ENV ENV=prod APP_ENV=prod

COPY --link docker/aowow/etc/php/conf.d/10-app.ini "$PHP_INI_DIR/app.conf.d/"
COPY . .
RUN /usr/bin/composer install --no-dev

FROM mariadb:12.0 AS mariadb-dev

RUN apt update -y && apt-get install -y p7zip curl git
RUN ln -s /usr/bin/mariadb /usr/bin/mysql

RUN <<EOF
mkdir -p /usr/local/share/mysql
chown mysql:mysql /usr/local/share/mysql
EOF

VOLUME /usr/local/share/mysql

HEALTHCHECK --interval=5s --timeout=5s --retries=75 CMD mariadb-admin ping -h 127.0.0.1 -u root -P3306 -p$MYSQL_ROOT_PASSWORD || exit 1
