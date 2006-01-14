#make
# MatrIRX III

CFLAGS=-g -ggdb -O0
# CFLAGS=-O3
CC=gcc
export CC CFLAGS

INTERNAL_LIBS=matrirx_core php5

all: title matrirx

.PHONY: title

title:
	@echo "MatrIRX III - Makefile"

libs/libphp5.a: php
	@make -C php

libs/libmatrirx_core.a: src/core
	@make -C src/core ../../$@ TARGET=../../$@ CPPFLAGS=-I`pwd`/includes

matrirx: $(patsubst %,libs/lib%.a,$(INTERNAL_LIBS))
	$(CC) -o $@ -Llibs $(patsubst %,-l%,$(INTERNAL_LIBS)) `cat libs/*.dep`

clean:
	@make -C php clean
	@make -C src/core clean
	@rm -f libs/* matrirx

