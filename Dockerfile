FROM drupal:10.2.2-php8.2-apache

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
    libldap-common \
    ldap-utils \
    libsasl2-modules \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install ldap

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
	php composer-setup.php --version=2.6.6 && \
	mv composer.phar /usr/local/bin/composer && \
	php -r "unlink('composer-setup.php');"

# Add drush (and other vendor binaries) to path
ENV PATH="${PATH}:/app/web/staff-blog/vendor/bin"

# Remove the default drupal codebase
RUN rm -rf /var/www/html/*

COPY docker/vhost.conf /etc/apache2/sites-enabled/000-default.conf

COPY docker/settings.php /app/settings.php

# Copy the staff-blog codebase to /app/web/staff-blog
COPY . /app/web/staff-blog

# We really don't need the built-in webserver available
RUN rm /app/web/staff-blog/web/.ht.router.php

# Symbolic link to remap web/ to blog/, which is necessary to prevent CAS mismatch errors
RUN ln -sd /app/web/staff-blog/web /app/web/staff-blog/blog

# Install dependcies, set ownership and delete the sync dir under /app/web/blog
RUN cd /app/web/staff-blog && \
	composer install --no-dev --ignore-platform-reqs && \
	chown -R www-data:www-data /app/web/staff-blog && \
	rm -R postgres-init/

WORKDIR /app/web/staff-blog
