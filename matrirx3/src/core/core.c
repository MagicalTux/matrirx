/* core.c
 * This is the core of the core !
 */

#include <stdio.h>

int php_embed_init(int argc, char **argv);
void php_embed_shutdown();
// if (zend_eval_string_ex(exec_direct, NULL, "Command line code", 1 TSRMLS_CC) == FAILURE)
// ZEND_API int zend_eval_string_ex(char *str, zval *retval_ptr, char *string_name, int handle_exceptions TSRMLS_DC)
int zend_eval_string_ex(char *str, void *, char *string_name, int handle_exceptions);

int main(int argc, char *argv[]) {
	php_embed_init(0, NULL);
	zend_eval_string_ex("echo \"Hello World from PHP/\".phpversion().\"\n\";", NULL, "Test Code", 1);
	zend_eval_string_ex("phpinfo();", NULL, "Test Code", 1);
	php_embed_shutdown();
	return 0;
}

