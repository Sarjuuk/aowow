FROM php:8.2-apache AS php-base

RUN <<EOF
set -aeux
apt-get update
apt-get install -y libfreetype-dev libjpeg62-turbo-dev libpng-dev libonig-dev libxml2-dev default-mysql-client libarchive-tools
docker-php-ext-configure gd --with-freetype --with-jpeg
docker-php-ext-install -j$(nproc) gd mbstring xml pdo_mysql mysqli
EOF

RUN a2enmod rewrite

COPY ./docker/aowow/usr/local/bin/* /usr/local/bin

WORKDIR /var/www/html

CMD ["/usr/local/bin/entrypoint.sh"]

FROM php-base AS php-dev

# fixme: workaround to ignore TLS error on connection to mysql from aowow service: fix it correctly, instead ignoring
COPY './docker/aowow/(home)/.my.cnf' /root

FROM php-base AS php-prod

COPY . .

FROM mariadb:12.0 AS mysql-dev

RUN apt update && apt-get install p7zip curl -y
RUN mkdir -p /usr/local/share/mysql ; chown mysql:mysql /usr/local/share/mysql
