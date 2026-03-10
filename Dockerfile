FROM php:8.2-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite headers

# Copy all files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Apache config to allow .htaccess
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/custom.conf \
    && a2enconf custom

EXPOSE 80
