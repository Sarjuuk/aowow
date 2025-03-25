FROM ubuntu:24.04

WORKDIR  /aowow/

ENV DB_DATABASE=aowow
ENV DB_USER=root
ENV DB_PASSWORD=root

RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server mysql-client php php-gd php-mbstring php-xml php-mysql wget p7zip-full unzip && \
    apt-get clean

COPY . .

# MySQL
RUN mkdir -p /var/run/mysqld && \
chown -R mysql:mysql /var/run/mysqld && \
chmod 777 /var/run/mysqld
RUN service mysql start && \
sleep 5 && \
mysql -e "CREATE DATABASE $DB_DATABASE;" && \
mysql -e "SHOW DATABASES;" && \
bash ./setup/scripts/docker-pipeline.sh

RUN service mysql restart

EXPOSE 3306
EXPOSE 80

# Start MySQL and run a test command
CMD service mysql start && \
    mysql -u$DB_USER -p$DB_PASSWORD -e "SHOW DATABASES;" && \
    php -S 0.0.0.0:80 route.php && \
    tail -f /dev/null  # Keeps container running

