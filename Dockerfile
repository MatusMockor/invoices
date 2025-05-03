FROM alpine:3.21

# Arguments and Environment Variables
ARG SOURCE_COMMIT_VALUE
ENV SOURCE_COMMIT=$SOURCE_COMMIT_VALUE \
    APP_USER=app \
    APP_GROUP=system \
    HTTP_SERVER_NAME="localhost" \
    LOG_LEVEL="info" \
    TZ="UTC" \
    PHP_MEMORY_LIMIT="1024M" \
    PGSSLCERT="/tmp/postgresql.crt"

# Install packages
RUN apk --no-cache --update add apache2 curl dumb-init php84-apache2 php84-bcmath php84-bz2 php84-calendar php84-common php84-gd php84-ctype \
    php84-curl php84-dom php84-fileinfo php84-iconv php84-mbstring php84-opcache php84-openssl php84-pdo_pgsql php84-pdo_sqlite php84-phar  \
    php84-session php84-simplexml php84-tokenizer php84-pecl-xdebug php84-zip php84-xml php84-xmlwriter php84-pecl-memcache \
    && mkdir /var/www/html \
    && ln -s /usr/bin/php84 /usr/bin/php

# User `sail` only for local environment
RUN addgroup -g 2000 -S ${APP_GROUP} && adduser -u 1000 -S -G ${APP_GROUP} ${APP_USER} && adduser -u 1001 -S sail -G ${APP_GROUP}

# Apache and PHP configuration
RUN sed -i "s/^User .*/User ${APP_USER}/" /etc/apache2/httpd.conf \
    && sed -i "s/^Group .*/Group ${APP_GROUP}/" /etc/apache2/httpd.conf \
    && sed -i "s/#ServerName\ www.example.com:80/ServerName\ ${HTTP_SERVER_NAME}/" /etc/apache2/httpd.conf \
    && sed -i 's#^DocumentRoot ".*#DocumentRoot "/var/www/html/public"#g' /etc/apache2/httpd.conf \
    && sed -i 's#Directory "/var/www/localhost/htdocs"#Directory "/var/www/html/public/"#g' /etc/apache2/httpd.conf \
    && sed -i 's#AllowOverride None#AllowOverride All#' /etc/apache2/httpd.conf \
    && sed -i 's#^ErrorLog .*#ErrorLog "/dev/stderr"\nTransferLog "/dev/stdout"#g' /etc/apache2/httpd.conf \
    && sed -i 's#CustomLog .* combined#CustomLog "/dev/stdout" combined#g' /etc/apache2/httpd.conf \
    && sed -i "s#^LogLevel .*#LogLevel ${LOG_LEVEL}#g" /etc/apache2/httpd.conf \
    && sed -i 's/#LoadModule\ rewrite_module/LoadModule\ rewrite_module/' /etc/apache2/httpd.conf \
    && sed -i 's/#LoadModule\ deflate_module/LoadModule\ deflate_module/' /etc/apache2/httpd.conf \
    && sed -i 's/#LoadModule\ expires_module/LoadModule\ expires_module/' /etc/apache2/httpd.conf \
    && sed -i "s/memory_limit = .*/memory_limit = ${PHP_MEMORY_LIMIT}/" /etc/php84/php.ini \
    && sed -i "s#^;date.timezone =\$#date.timezone = \"${TZ}\"#" /etc/php84/php.ini \
    && sed -i "s#^;zend_extension=xdebug.so\$#zend_extension=xdebug.so#" /etc/php84/conf.d/50_xdebug.ini \
    && sed -i "s#^;xdebug.mode=off\$#xdebug.mode=develop,coverage#" /etc/php84/conf.d/50_xdebug.ini

COPY --chown=${APP_USER}:${APP_GROUP} . /var/www/html

WORKDIR /var/www/html

EXPOSE 80

CMD ["httpd", "-D", "FOREGROUND"]