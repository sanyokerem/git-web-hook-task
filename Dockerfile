FROM formapro/nginx-php-fpm:latest-all-exts

RUN chmod 757 /var
RUN apt-get update && apt-get install -y git