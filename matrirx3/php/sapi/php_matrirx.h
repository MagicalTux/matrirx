/*
   +----------------------------------------------------------------------+
   | PHP Version 5                                                        |
   +----------------------------------------------------------------------+
   | Copyright (c) 1997-2006 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 3.01 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available through the world-wide-web at the following url:           |
   | http://www.php.net/license/3_01.txt                                  |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Author: Edin Kadribasic <edink@php.net>                              |
   +----------------------------------------------------------------------+
*/
/* $Id$ */

#ifndef _PHP_MATRIRX_SAPI_H_
#define _PHP_MATRIRX_SAPI_H_

#include <main/php.h>
#include <main/SAPI.h>
#include <main/php_main.h>
#include <main/php_variables.h>
#include <main/php_ini.h>
#include <zend_ini.h>

#ifdef ZTS
#define PTSRMLS_D        void ****ptsrm_ls
#define PTSRMLS_DC       , PTSRMLS_D
#define PTSRMLS_C        &tsrm_ls
#define PTSRMLS_CC       , PTSRMLS_C

#define PHP_MATRIRX_SAPI_START_BLOCK() { \
    void ***tsrm_ls; \
    php_matrirx_sapi_init(PTSRMLS_CC); \
    zend_first_try {

#else
#define PTSRMLS_D
#define PTSRMLS_DC
#define PTSRMLS_C
#define PTSRMLS_CC

#define PHP_MATRIRX_SAPI_START_BLOCK() { \
    php_matrirx_sapi_init(); \
    zend_first_try {

#endif

#define PHP_MATRIRX_SAPI_END_BLOCK() \
  } zend_catch { \
    /* int exit_status = EG(exit_status); */ \
  } zend_end_try(); \
  php_matrirx_sapi_shutdown(TSRMLS_C); \
}

BEGIN_EXTERN_C() 
int php_matrirx_sapi_init(PTSRMLS_DC);
void php_matrirx_sapi_shutdown(TSRMLS_D);
extern sapi_module_struct php_matrirx_sapi_module;
END_EXTERN_C()


#endif /* _PHP_MATRIRX_SAPI_H_ */
