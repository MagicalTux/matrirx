#!make

all:
	@make -C ../..

$(TARGET): $(patsubst %.c,%.o,$(wildcard *.c))
	$(AR) rcs $@ $^

clean:
	rm -f *.o

