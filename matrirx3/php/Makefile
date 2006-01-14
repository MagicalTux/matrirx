#!/bin/make
PHP_VERSION=5.1.2

all: ../libs/libphp5.a

../libs/libphp5.a: php-$(PHP_VERSION).tar.bz2 php-$(PHP_VERSION) php-$(PHP_VERSION)/ext/matrirx
	@cd php-$(PHP_VERSION); CPPFLAGS="-I`pwd`/../../includes" ./configure --disable-cli --disable-cgi --enable-embed=static --disable-all --enable-matrirx --enable-pcre --enable-curl --enable-zlib --enable-sockets --enable-iconv --with-sqlite --enable-sqlite-utf8 --enable-mbstring --enable-calendar
	@make -C php-$(PHP_VERSION)
	@. php-5.1.2/libphp5.la; cp "$$libdir/$$old_library" ../libs/libphp5.a; echo "$$dependency_libs" >../libs/libphp5.dep

php-$(PHP_VERSION).tar.bz2:
	wget http://fr2.php.net/get/php-$(PHP_VERSION).tar.bz2/from/this/mirror

php-$(PHP_VERSION):
	tar xjf php-$(PHP_VERSION).tar.bz2

php-$(PHP_VERSION)/ext/matrirx: src
	rm -fr php-$(PHP_VERSION)/ext/matrirx; cp -a src/ php-$(PHP_VERSION)/ext/matrirx
	cd php-$(PHP_VERSION); ./buildconf --force

clean:
	rm -f ../libs/libphp5.a

