FROM drupal:9.5.9-php8.1-apache

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

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
	php composer-setup.php && \
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

WORKDIR /app/web/app
