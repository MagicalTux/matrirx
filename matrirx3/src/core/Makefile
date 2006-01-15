#!make

all:
	@make -C ../..

$(TARGET): $(patsubst %.c,%.o,$(wildcard *.c))
	$(AR) rcs $@ $^
	echo "libs/libphp5.a" >$(TARGET).dep

clean:
	rm -f *.o

