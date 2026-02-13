FROM php:8.2-fpm
# Install the PHP extensions Symfony depends on.
RUN apt update && apt install -y \
    zlib1g-dev g++ git libicu-dev zip libzip-dev zip unzip curl \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libxslt1-dev pkg-config \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*
# for wkhtmltopdf we install wkhtmltopdf libxrender1 libfontconfig1 libxext6 xfonts-base xfonts-75dpi \

# enable cache system to increase performance
RUN docker-php-ext-enable opcache

# for image manipulation (thumbnails for example) (so we install libpng-dev libjpeg-dev libfreetype6-dev \)
RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install gd xsl

# https://matthewsetter.com/setup-step-debugging-php-xdebug3-docker/
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# -------------------------
# Install Node + npm + Yarn
# -------------------------
ENV NODE_VERSION=18.20.1
ENV NPM_VERSION=9.7.2
ENV YARN_VERSION=1.22.19

# Install Node
RUN curl -fsSL https://nodejs.org/dist/v$NODE_VERSION/node-v$NODE_VERSION-linux-x64.tar.xz \
    -o node.tar.xz \
    && tar -xJf node.tar.xz -C /usr/local --strip-components=1 \
    && rm node.tar.xz \
    && npm install -g npm@$NPM_VERSION \
    && npm install -g yarn@$YARN_VERSION

# Verify installation
RUN node -v && npm -v && yarn -v

#Set the working directory of the container to /var/www/project
WORKDIR /var/www/project

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# Install the Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash
# The second command RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony moves the symfony binary from the /root/.symfony/bin/ directory to the /usr/local/bin/ directory.
# This makes the symfony command available globally within the container, so it can be called from any location.
RUN mv /root/.symfony*/bin/symfony /usr/local/bin/symfony


