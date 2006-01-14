#!/bin/make
PHP_VERSION=5.1.2

all: ../libs/libphp5.a

../libs/libphp5.a: php-$(PHP_VERSION).tar.bz2 php-$(PHP_VERSION)
	@cd php-$(PHP_VERSION); ./configure --disable-cli --disable-cgi --enable-embed=static --disable-all
	make -C php-$(PHP_VERSION)
	cp php-$(PHP_VERSION)/libs/libphp5.a ../libs/libphp5.a

php-$(PHP_VERSION).tar.bz2:
	wget http://fr2.php.net/get/php-$(PHP_VERSION).tar.bz2/from/this/mirror

php-$(PHP_VERSION):
	tar xvjf php-$(PHP_VERSION).tar.bz2

clean:
	rm -f ../libs/libphp5.a

