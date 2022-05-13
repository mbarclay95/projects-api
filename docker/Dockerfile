FROM webdevops/php-nginx:8.1 as develop

ARG env=dev

ENV WEB_DOCUMENT_ROOT /var/www/html/public
ENV WEB_DOCUMENT_INDEX index.php
ENV ACCEPT_EULA y
RUN echo "gzip_types text/plain application/xm application/json application/javascript;" >> /opt/docker/etc/nginx/conf.d/gzip.conf
COPY /docker/start.sh /usr/local/bin/start
RUN chmod u+x /usr/local/bin/start
WORKDIR /var/www/html
CMD ["/usr/local/bin/start"]

FROM develop as production

COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install --prefer-dist --no-scripts --no-dev --no-autoloader && rm -rf /root/.composer

COPY --chown=application:application ./app /var/www/html/app
COPY --chown=application:application ./bootstrap/app.php /var/www/html/bootstrap/app.php
COPY --chown=application:application ./config /var/www/html/config
COPY --chown=application:application ./database /var/www/html/database
COPY --chown=application:application ./public /var/www/html/public
COPY --chown=application:application ./resources /var/www/html/resources
COPY --chown=application:application ./routes /var/www/html/routes
COPY --chown=application:application ./artisan /var/www/html/artisan

RUN composer dump-autoload --no-scripts --no-dev --optimize
RUN mkdir -p /var/www/html/bootstrap/cache
RUN mkdir -p /var/www/html/storage/app/public
RUN mkdir -p /var/www/html/storage/framework/cache
RUN mkdir -p /var/www/html/storage/framework/sessions
RUN mkdir -p /var/www/html/storage/framework/testing
RUN mkdir -p /var/www/html/storage/framework/views
RUN mkdir -p /var/www/html/storage/logs

RUN chown -R application:application /var/www/html/storage
RUN chown -R application:application /var/www/html/bootstrap

RUN chmod -R 775 /var/www/html/storage
RUN chmod -R 775 /var/www/html/bootstrap


