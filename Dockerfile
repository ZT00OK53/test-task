FROM ubuntu:20.04
LABEL Author="Omkar Kurmi" Description="A comprehensive docker image to run Apache-2.4 PHP-8.1 applications like Wordpress, Laravel, etc"

WORKDIR /var/www/html
# Stop dpkg-reconfigure tzdata from prompting for input
ENV DEBIAN_FRONTEND=noninteractive

# Install apache and php8
RUN apt-get update && apt install -qq -y software-properties-common
RUN add-apt-repository ppa:ondrej/php

#dependency
RUN apt-get update && \
    apt-get -y install \
        apache2 \
        php8.1 \
        libapache2-mod-php8.1  \
        libapache2-mod-auth-openidc \
        libcap2-bin \
        supervisor \
        cron \
        nano

RUN apt install -y php8.1-common \
    php8.1-mysql \
    php8.1-xml \
    php8.1-curl \
    php8.1-gd \
    php8.1-imagick \
    php8.1-cli \
    php8.1-dev \
    php8.1-imap \
    php8.1-mbstring \
    php8.1-opcache \
    php8.1-soap \
    php8.1-zip
    
#install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Setup apache
RUN a2enmod rewrite headers expires ext_filter

# Override default apache and php config
COPY .docker/000-default.conf /etc/apache2/sites-available
COPY .docker/local.ini    /etc/php/8.1/apache2/conf.d

RUN crontab -l | { cat; echo "* * * * * cd /var/www/html && php artisan schedule:run >> /var/log/cron.log 2>&1"; } | crontab -

#setup superviser
COPY .docker/laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf
COPY .docker/start.sh .docker/start.sh
CMD supervisorctl reread
CMD supervisorctl update
CMD supervisorctl reload
CMD service supervisor start
RUN apt-get install dos2unix -y
RUN dos2unix -k -o .docker/start.sh
#USER www-data
EXPOSE 80
RUN chmod +x .docker/start.sh
CMD ["/usr/sbin/apachectl", "-D", "FOREGROUND"]