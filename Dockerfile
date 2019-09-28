FROM richarvey/nginx-php-fpm

# Bring in custom NGINX configs
COPY ./etc /etc 

# Install mysql client in order to test mysql connection in wait script
RUN apk add mysql-client

# Install SASS for compiling SASS
RUN apk add npm
RUN npm install -g sass

# Copy user scripts and make executable
COPY usr /usr
RUN chmod +x /usr/local/bin/*

# Bring in application source code
COPY ./src /var/www/html

CMD ["/usr/local/bin/wait"]