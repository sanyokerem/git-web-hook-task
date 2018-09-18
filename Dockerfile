FROM formapro/nginx-php-fpm:latest-all-exts

RUN apt-get update && apt-get install -y git