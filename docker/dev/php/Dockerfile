FROM public.ecr.aws/docker/library/php:8.4-cli-alpine3.22

ARG USER_ID

COPY files/bin /usr/local/bin/
COPY files/php /phpIni

# SYS: Install required packages
RUN apk --no-cache upgrade --force-missing-repositories && \
     apk --no-cache add bash git sudo autoconf gcc g++ make make shadow

# Set host user id if given, to avoid permission conflicts in dev
RUN if [ -n "$USER_ID" ] && [ "$USER_ID" -lt 60001 ]; then \
        usermod -u ${USER_ID} -o www-data; \
    fi

RUN usermod -s /bin/bash www-data && \
    echo 'www-data ALL=(ALL) NOPASSWD: ALL' > '/etc/sudoers.d/www-data'

# USER: copy home
COPY --chown=www-data:www-data files/user-home /home/www-data

# PIE: Install PIE binary
COPY --from=ghcr.io/php/pie:1.2.1-bin /pie /usr/bin/pie

# PHP: Install php extensions
RUN pie install pecl/pcov

# COMPOSER: install binary
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

USER www-data

WORKDIR /var/www/html
