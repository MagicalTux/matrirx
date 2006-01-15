#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_matrirx.h"
#include <matrirx.h>

static function_entry matrirx_functions[] = {
    PHP_FE(matrirx_version, NULL)
    {NULL, NULL, NULL}
};

zend_module_entry matrirx_module_entry = {
#if ZEND_MODULE_API_NO >= 20010901
    STANDARD_MODULE_HEADER,
#endif
    PHP_MATRIRX_EXTNAME,
    matrirx_functions,
    PHP_MINIT(matrirx),
    PHP_MSHUTDOWN(matrirx),
    NULL,
    NULL,
    PHP_MINFO(matrirx),
#if ZEND_MODULE_API_NO >= 20010901
    PHP_MATRIRX_VERSION,
#endif
    STANDARD_MODULE_PROPERTIES
};

PHP_MINFO_FUNCTION(matrirx) {
	php_info_print_table_start();
	php_info_print_table_row(2, "MatrIRX support", "enabled");
	php_info_print_table_row(2, "MatrIRX version", "0.0.0");
	php_info_print_table_end();
}

#ifdef COMPILE_DL_MATRIRX
ZEND_GET_MODULE(matrirx)
#endif

PHP_MINIT_FUNCTION(matrirx) {
	REGISTER_STRING_CONSTANT("MATRIRX_VERSION", MATRIRX_VERSION, CONST_CS | CONST_PERSISTENT);
	/* old defines from MatrIRX 1 (shouldn't be used anymore) */
	REGISTER_STRING_CONSTANT("_ROOT", "/", CONST_CS | CONST_PERSISTENT);
	REGISTER_STRING_CONSTANT("_DATA", "/", CONST_CS | CONST_PERSISTENT);
	/* IRC constants */
	REGISTER_STRING_CONSTANT("_CTCP", "\x01", CONST_CS | CONST_PERSISTENT);
	REGISTER_STRING_CONSTANT("_BOLD", "\x02", CONST_CS | CONST_PERSISTENT);
	REGISTER_STRING_CONSTANT("_COLOR", "\x03", CONST_CS | CONST_PERSISTENT);
	REGISTER_STRING_CONSTANT("_RESET", "\x0f", CONST_CS | CONST_PERSISTENT);
	REGISTER_STRING_CONSTANT("_UNDERLINE", "\x1f", CONST_CS | CONST_PERSISTENT);
	REGISTER_STRING_CONSTANT("IRC_SVC_MODES", "+pqioS", CONST_CS | CONST_PERSISTENT);
	return SUCCESS;
}

PHP_MSHUTDOWN_FUNCTION(matrirx) {
	return SUCCESS;
}

PHP_FUNCTION(matrirx_version) {
    RETURN_STRING(MATRIRX_VERSION, 1);
}

