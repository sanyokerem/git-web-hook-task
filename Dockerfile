FROM formapro/nginx-php-fpm:latest-all-exts

RUN apt-get update && \
    apt-get -y --no-install-recommends --no-install-suggests install git && \
    rm -rf /var/lib/apt/lists/*
