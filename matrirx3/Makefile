#make
# MatrIRX III

INTERNAL_LIBS=php5 matrirx_core

all: title matrirx

.PHONY: title

title:
	@echo "MatrIRX III - Makefile"

libs/libphp5.a:
	make -C php

libs/libmatrirx_core.a:
	make -C src/core

matrirx: $(patsubst %,libs/lib%.a,$(INTERNAL_LIBS))
	$(CC) -o $@ $(patsubst %,-l%,$(INTERNAL_LIBS)) `cat libs/*.dep`

