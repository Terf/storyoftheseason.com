FROM php:7-apache
ENV DEBIAN_FRONTEND=noninteractive TZ=America/New_York COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /var/www/html
COPY apache/000-default.conf /etc/apache2/sites-enabled/000-default.conf
COPY composer.* /var/www/html/
COPY .env.local /var/www/html/
# https://gist.github.com/chronon/95911d21928cff786e306c23e7d1d3f3 for possible docker-php-ext-install values
RUN apt-get update && apt-get install -y zlib1g-dev libzip-dev docker.io sudo && \
	echo "www-data ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers && \
	docker-php-ext-install zip pdo_mysql && \
	./composer.install.sh && \
	php composer.phar install && \
	php composer.phar update && \
	php composer.phar dump-env prod && \
	a2enmod rewrite headers
# copy rest of files later to take advantage of cache
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/ && php bin/console --env=prod cache:clear