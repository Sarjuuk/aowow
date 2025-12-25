FROM php:8.2-apache AS php-base

RUN <<EOF
set -aeux
apt-get update
apt-get install -y --no-install-recommends --no-install-suggests \
  libfreetype-dev \
  libjpeg62-turbo-dev \
  libpng-dev \
  libonig-dev \
  libxml2-dev \
  libicu-dev \
  libgmp-dev \
  default-mysql-client \
  libarchive-tools
EOF

RUN <<EOF
docker-php-ext-configure gd --with-freetype --with-jpeg
docker-php-ext-install -j$(nproc) gd mbstring xml pdo_mysql mysqli intl gmp
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

FROM mariadb:12.0 AS mariadb-dev

RUN apt update && apt-get install p7zip curl -y
RUN ln -s /usr/bin/mariadb /usr/bin/mysql

HEALTHCHECK --interval=5s --timeout=5s --retries=30 CMD mariadb-admin ping -h 127.0.0.1 -u root -P3306 -p$MYSQL_ROOT_PASSWORD || exit 1

# DEPRECATED: consider to don't use
FROM mysql:5.7-debian AS mysql-dev
RUN <<EOF
set -aeu

cat << 'EOL' > /etc/apt/sources.list
deb http://archive.debian.org/debian buster main contrib non-free
deb http://archive.debian.org/debian-security buster/updates main contrib non-free
deb http://archive.debian.org/debian buster-updates main contrib non-free
EOL

# cat /etc/apt/sources.list.d/mysql.list
#curl https://repo.mysql.com/RPM-GPG-KEY-mysql | apt-key add -
#apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 B7B3B788A8D3785C
#apt-key adv --keyserver keyserver.ubuntu.com --recv-keys B7B3B788A8D3785C
#gpg --recv-keys B7B3B788A8D3785C
#gpg --export B7B3B788A8D3785C | apt-key add -

apt-get update -y --allow-insecure-repositories
apt-get install p7zip-full curl -y
EOF

HEALTHCHECK --interval=5s --timeout=5s --retries=30 CMD mysqladmin ping -h 127.0.0.1 -u root -P3306 -p$MYSQL_ROOT_PASSWORD || exit 1
