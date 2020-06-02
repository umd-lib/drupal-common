FROM drupal:8.7.8-apache

# Install necessary packages
RUN seq 1 8 | xargs -I{} mkdir -p /usr/share/man/man{} && \
	echo "deb http://apt.postgresql.org/pub/repos/apt/ stretch-pgdg main" | tee /etc/apt/sources.list.d/postgresql.list && \
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
	postgresql-client-10 && \
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

# Install drush
RUN wget -O drush.phar https://github.com/drush-ops/drush-launcher/releases/download/0.4.2/drush.phar && \
	chmod +x drush.phar && \
	mv drush.phar /usr/local/bin/drush

# Remove the default drupal codebase
RUN rm -rf /var/www/html/*

COPY docker/vhost.conf /etc/apache2/sites-enabled/000-default.conf

COPY docker/settings.php /app/settings.php

# Copy the staff-blog codebase to /app/web/staff-blog
COPY . /app/web/staff-blog

RUN ln -sd /app/web/staff-blog/web /app/web/staff-blog/blog

# Install dependcies, set ownership and delete the sync dir under /app/web/blog
RUN cd /app/web/staff-blog && \
	composer install --no-dev && \
	chown -R www-data:www-data /app/web/staff-blog

WORKDIR /app/web/staff-blog
