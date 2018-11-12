FROM php:7.2.5-fpm

COPY src/ /src/

RUN apt-get update && apt-get install -y \
        curl nano \
		libfreetype6-dev \
		libjpeg62-turbo-dev \
		libmcrypt-dev software-properties-common libpcre3-dev perl-doc\
		libpng-dev libav-tools libavcodec-extra imagemagick libimage-exiftool-perl  libmagickcore-dev libmagickwand-dev libmagic-dev \
		libssl-dev libcurl4-openssl-dev pkg-config curl  g++ libicu-dev libxml2-dev git
		

RUN   git config --global user.email "tobias@gaszmann.de" && git config --global user.name "Tobias Gassmann"
		
RUN docker-php-ext-install -j$(nproc) iconv mbstring exif curl intl zip xml bcmath \
	&& docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-png-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
	&& docker-php-ext-install -j$(nproc) gd
	
	

RUN pecl install redis \
	&& pecl install mongodb \
	&& pecl install imagick \
	&& docker-php-ext-enable redis mongodb imagick


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN cd /src && \
    composer install --no-interaction --prefer-source




