FROM wyveo/nginx-php-fpm:php73

ADD ./nginx.conf /etc/nginx/conf.d/default.conf

RUN apt-get -y install apt-transport-https lsb-release ca-certificates \
    && wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg \
    && echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list \
    && apt-get update \
    && apt-get install --no-install-recommends --no-install-suggests -q -y \
    libpng-dev \
    libjpeg-dev \
    libpq-dev \
    default-mysql-client \
    git \
    libbz2-dev \
    libgmp-dev \
    acl \
    gnupg \
    bc \
    bzip2 \
    openssh-server \
    make \
    php7.3-gmp \
    php7.3-xdebug \
    php7.3-curl \
    php7.3-gd \
    php7.3-json \
    php7.3-mbstring \
    php7.3-pdo \
    php7.3-xml \
    patch \
    rsyslog \
    && rm -rf /var/lib/apt/lists/* 

  
# Install latest chrome.
RUN curl -ssL "https://dl-ssl.google.com/linux/linux_signing_key.pub" | apt-key add - \
  && echo "deb http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google-chrome.list \
  && apt-get update && apt-get install -y google-chrome-stable --no-install-recommends \
  && google-chrome --version

# Install nodejs
RUN curl -sL https://deb.nodesource.com/setup_12.x | bash - \
  && apt-get install -y --no-install-recommends nodejs \
  && node --version

# Install drush, to use for site and module installs
RUN wget -O drush.phar https://github.com/drush-ops/drush-launcher/releases/download/0.6.0/drush.phar \
  && chmod +x drush.phar \
  && mv drush.phar /usr/local/bin/drush

# Register the COMPOSER_HOME environment variable
ENV COMPOSER_HOME /composer

# Add global binary directory to PATH and make sure to re-export it
ENV PATH /composer/vendor/bin:$PATH

# Allow Composer to be run as root
ENV COMPOSER_ALLOW_SUPERUSER 1

# Install composer
RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
  && php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer && rm -rf /tmp/composer-setup.php

# Allows for parallel composer downloads
RUN composer global require "hirak/prestissimo:^0.3"

# Drupal console
RUN curl https://drupalconsole.com/installer -L -o drupal.phar \
  && chmod +x drupal.phar \
  && mv drupal.phar /usr/local/bin/drupal
