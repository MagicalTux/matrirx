#ifndef PHP_MATRIRX_H
#define PHP_MATRIRX_H 1


#define PHP_MATRIRX_WORLD_VERSION "0.1"
#define PHP_MATRIRX_WORLD_EXTNAME "matrirx"

PHP_FUNCTION(matrirx_version);

extern zend_module_entry matrirx_module_entry;
#define phpext_matrirx_ptr &matrirx_module_entry

#endif
