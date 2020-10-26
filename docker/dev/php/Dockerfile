FROM php:7.3-cli-alpine3.10

ARG HOST_USER_ID

# SYS: Install required packages
RUN apk --no-cache upgrade && \
     apk --no-cache add bash git sudo autoconf gcc g++ make make shadow

COPY dev/php/files/bin/ /usr/local/bin/

# Set host user id if given, to avoid permission conflicts in dev
RUN /bin/bash -c 'if [ -n "$HOST_USER_ID" ] && [ "$HOST_USER_ID" -lt 60000 ]; then \
        usermod -u ${HOST_USER_ID} www-data; \
    fi'

RUN usermod -s /bin/bash www-data && \
    echo 'www-data ALL=(ALL) NOPASSWD: ALL' > '/etc/sudoers.d/www-data'

# PHP: Install php extensions
RUN pecl channel-update pecl.php.net && \
    pecl install pcov && \
    php-ext-enable pcov

# COMPOSER: install binary
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

USER www-data

WORKDIR /var/www/html
