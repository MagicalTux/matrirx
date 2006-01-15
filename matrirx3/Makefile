#make
# MatrIRX III

CFLAGS=-g -ggdb -O0 --std=gnu99
# CFLAGS=-O3
CC=gcc
export CC CFLAGS

INTERNAL_LIBS=matrirx_core php5

all: title matrirx

.PHONY: title

title:
	@echo "MatrIRX III - Makefile"

libs/libphp5.a: php php/src php/sapi
	@make -C php

libs/libmatrirx_%.a: src/%
	@make -C $< ../../$@ TARGET=../../$@ CPPFLAGS="-I`pwd`/includes -I`pwd`/php/php -I`pwd`/php/php/Zend -I`pwd`/php/php/TSRM -I`pwd`/php/php/main"

matrirx: $(patsubst %,libs/lib%.a,$(INTERNAL_LIBS))
	$(CC) -o $@ $^ `cat libs/*.dep`

clean:
	@make -C php clean
	@make -C src/core clean
	@rm -f libs/* matrirx

