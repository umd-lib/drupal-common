FROM drupal:10.3.1-php8.3-apache

# Install necessary packages
RUN seq 1 8 | xargs -I{} mkdir -p /usr/share/man/man{} && \
	apt-get update && apt-get install -y --allow-unauthenticated \
	curl \
	git \
	vim \
	wget \
	gettext-base \
	pngcrush \
	advancecomp \
	libjpeg-progs \
	optipng \
	jpegoptim \
	pngquant \
	postgresql-client && \
	rm -rf /usr/share/man/man*

# Configure PHP-LDAP
RUN apt-get install -y libldap2-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install ldap \
    && apt-get purge -y --auto-remove libldap2-dev

# Install APCu
RUN pecl install apcu \
    && docker-php-ext-enable apcu

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
	php composer-setup.php --version=2.6.6 && \
	mv composer.phar /usr/local/bin/composer && \
	php -r "unlink('composer-setup.php');"

# Add drush (and other vendor binaries) to path
ENV PATH="${PATH}:/app/web/app/vendor/bin"

# Remove the default drupal codebase
RUN rm -rf /var/www/html/*

COPY docker/vhost.conf /etc/apache2/sites-enabled/000-default.conf

COPY docker/settings.php /app/settings.php

# Copy the codebase to /app/web/app
COPY . /app/web/app

# We really don't need the built-in webserver available
RUN rm /app/web/app/web/.ht.router.php

# Install dependcies, set ownership and delete the sync dir under /app/web/blog
RUN cd /app/web/app && \
        composer install --ignore-platform-reqs && \
        # composer install --no-dev --ignore-platform-reqs && \
        chown -R www-data:www-data /app/web/app

RUN echo 'php_value upload_max_filesize 15M' >> '/app/web/app/web/.htaccess'
RUN echo 'php_value max_input_vars 3000' >> '/app/web/app/web/.htaccess'
RUN echo 'php_value max_execution_time 60' >> '/app/web/app/web/.htaccess'
RUN echo 'php_value memory_limit 512M' >> '/app/web/app/web/.htaccess'
RUN echo 'php_value suhosin.get.max_vars 3000' >> '/app/web/app/web/.htaccess'
RUN echo 'php_value suhosin.post.max_vars 3000' >> '/app/web/app/web/.htaccess'
RUN echo 'php_value suhosin.request.max_vars 3000' >> '/app/web/app/web/.htaccess'

WORKDIR /app/web/app
