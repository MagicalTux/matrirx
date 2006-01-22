#ifndef PHP_MATRIRX_H
#define PHP_MATRIRX_H 1


#define PHP_MATRIRX_VERSION "0.1"
#define PHP_MATRIRX_EXTNAME "matrirx"

PHP_FUNCTION(matrirx_version);
PHP_MINIT_FUNCTION(matrirx);
PHP_MSHUTDOWN_FUNCTION(matrirx);
PHP_MINFO_FUNCTION(matrirx);

extern zend_module_entry matrirx_module_entry;
#define phpext_matrirx_ptr &matrirx_module_entry

#endif
