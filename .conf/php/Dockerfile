FROM php:7.4-fpm

# postgresql
RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo pdo_pgsql pgsql

RUN apt-get update && \
    apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libicu-dev \
    libxml2-dev \
    libmagickwand-dev \
    libpq-dev \
    libedit-dev \
    libgmp-dev \
    libssh2-1-dev \
    libbz2-dev \
    libzip-dev \
    zlib1g-dev \
    postgresql-client \
    graphviz \
    wget \
    gettext-base \
    bash-completion \
    nano \
    git \
    zip \
    procps \
    rsyslog \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
RUN apt-get clean && rm -rf /var/lib/apt/lists/*
# ///////////////////////////////////////////////

WORKDIR /var/www