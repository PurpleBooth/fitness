FROM php:7-apache

ENV CODE_DIR /var/code
ENV SYMFONY_ENV prod

# Setup Apache


RUN echo 'deb http://apt.newrelic.com/debian/ newrelic non-free' | tee /etc/apt/sources.list.d/newrelic.list \
    && curl "https://download.newrelic.com/548C16BF.gpg" | apt-key add - \
    && apt-get update \
    && apt-get install -y git zlib1g-dev libicu-dev newrelic-php5 \
    && rm -r /var/lib/apt/lists/* \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-configure opcache \
    && docker-php-ext-install opcache \
    && docker-php-ext-configure pdo_mysql \
    && docker-php-ext-install pdo_mysql \
    && pecl install apcu \
    && echo extension=apcu.so > /usr/local/etc/php/conf.d/apcu.ini \
    && echo default_mimetype="" > /usr/local/etc/php/conf.d/default_mimetype.ini


COPY container/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

COPY container/fitness-api.conf /etc/apache2/sites-available

RUN sed -i -r 's|LogFormat.*\scombined|LogFormat "%t, \\"remoteIP\\":\\"%{X-Forwarded-For}i\\", \\"host\\":\\"%V\\", \\"request\\":\\"%U\\", \\"query\\":\\"%q\\", \\"method\\":\\"%m\\", \\"status\\":\\"%>s\\", \\"userAgent\\":\\"%{User-agent}i\\", \\"referer\\":\\"%{Referer}i\\"" combinedi|' /etc/apache2/apache2.conf \
&& sed -i -r 's|LogFormat.*\svhost_combined|LogFormat "%t, \\"remoteIP\\":\\"%{X-Forwarded-For}i\\", \\"host\\":\\"%V\\", \\"request\\":\\"%U\\", \\"query\\":\\"%q\\", \\"method\\":\\"%m\\", \\"status\\":\\"%>s\\", \\"userAgent\\":\\"%{User-agent}i\\", \\"referer\\":\\"%{Referer}i\\"" vhost_combined|' /etc/apache2/apache2.conf \
&& sed -i -r '/LogFormat.*\scommon/ d' /etc/apache2/apache2.conf

RUN a2dissite 000-default \
    && a2ensite fitness-api \
    && a2enmod rewrite

## Add the code
COPY . $CODE_DIR
WORKDIR $CODE_DIR

RUN sed -i -e 's/app_dev.php/app.php/g' $CODE_DIR/web/.htaccess

EXPOSE 80
ENTRYPOINT ["/var/code/container/start.sh"]
CMD []