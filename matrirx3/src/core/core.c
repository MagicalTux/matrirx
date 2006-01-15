/* core.c
 * This is the core of the core !
 * $Id$
 */

#include <stdio.h>
#include <matrirx.h>

#include <sapi/matrirx/php_matrirx.h>

//int zend_eval_string_ex(char *str, void *, char *string_name, int handle_exceptions);

int main(int argc, char *argv[]) {
	char *phpcode="echo 'Hello World from PHP/'.phpversion().' MatrIRX/'.matrirx_version().' - SAPI='.php_sapi_name().\"\\n\"; var_dump($_SERVER);";
	global_executable_location = argv[0];
	printf("Passing PHP code to PHP5(sapi:matrirx): %s\n", phpcode);
	php_matrirx_sapi_init(0, NULL);
	zend_eval_string_ex(phpcode, NULL, "Test Code", 1);
	php_matrirx_sapi_shutdown();
	return 0;
}

