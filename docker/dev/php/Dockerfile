FROM php:8.1-cli-alpine3.17

ARG USER_ID

COPY files/bin /usr/local/bin/
COPY files/php /phpIni

# SYS: Install required packages
RUN apk --no-cache upgrade && \
     apk --no-cache add bash git sudo autoconf gcc g++ make make shadow

# Set host user id if given, to avoid permission conflicts in dev
RUN if [ -n "$USER_ID" ] && [ "$USER_ID" -lt 60001 ]; then \
        usermod -u ${USER_ID} -o www-data; \
    fi

RUN usermod -s /bin/bash www-data && \
    echo 'www-data ALL=(ALL) NOPASSWD: ALL' > '/etc/sudoers.d/www-data'

# USER: copy home
COPY --chown=www-data:www-data files/user-home /home/www-data

# PHP: Install php extensions
RUN pecl channel-update pecl.php.net && \
    pecl install pcov && \
    php-ext-enable pcov

# COMPOSER: install binary
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

USER www-data

WORKDIR /var/www/html
