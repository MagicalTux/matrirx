#!/bin/make
PHP_VERSION=5.1.2

all: ../libs/libphp5.a

../libs/libphp5.a: php-$(PHP_VERSION).tar.bz2 php-$(PHP_VERSION) php-$(PHP_VERSION)/sapi/matrirx php-$(PHP_VERSION)/config.status
	@make -C php-$(PHP_VERSION)
	@. php-5.1.2/libphp5.la; cp "$$libdir/$$old_library" ../libs/libphp5.a; echo "$$dependency_libs" >../libs/libphp5.dep

php-$(PHP_VERSION).tar.bz2:
	wget http://php.net/get/php-$(PHP_VERSION).tar.bz2/from/this/mirror

php-$(PHP_VERSION):
	tar xjf php-$(PHP_VERSION).tar.bz2
	rm -f php; ln -s php-$(PHP_VERSION) php

php-$(PHP_VERSION)/sapi/matrirx: sapi
	rm -fr php-$(PHP_VERSION)/sapi/matrirx; cp -a sapi/ php-$(PHP_VERSION)/sapi/matrirx

php-$(PHP_VERSION)/config.status:
	@cd php-$(PHP_VERSION); ./buildconf --force; CPPFLAGS="-I`pwd`/../../includes" ./configure --disable-cli --disable-cgi --disable-all --enable-matrirx --with-pcre --with-curl --with-zlib --with-iconv --with-sqlite --enable-sqlite-utf8 --enable-mbstring --enable-calendar

clean:
	rm -f ../libs/libphp5.a

