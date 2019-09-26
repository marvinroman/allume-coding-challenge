FROM richarvey/nginx-php-fpm

# Bring in custom NGINX configs
COPY ./etc /etc 

# Bring in application source code
COPY ./src /var/www/html