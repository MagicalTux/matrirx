/* php_matrirx.c
 * SAPI module for MatrIRX, based on "Embed" module.
*/
/* $Id$ */

#include "php_matrirx.h"
#include "matrirx_mod.h"
#include <matrirx.h>

#ifdef PHP_WIN32
#include <io.h>
#include <fcntl.h>
#endif

static char* php_matrirx_sapi_read_cookies(TSRMLS_D) {
	return NULL;
}

static int php_matrirx_sapi_deactivate(TSRMLS_D) {
	fflush(stdout);
	return SUCCESS;
}

static inline size_t php_matrirx_sapi_single_write(const char *str, uint str_length) {
#ifdef PHP_WRITE_STDOUT
	long ret;

	ret = write(STDOUT_FILENO, str, str_length);
	if (ret <= 0) return 0;
	return ret;
#else
	size_t ret;

	ret = fwrite(str, 1, MIN(str_length, 16384), stdout);
	return ret;
#endif
}


static int php_matrirx_sapi_ub_write(const char *str, uint str_length TSRMLS_DC) {
	const char *ptr = str;
	uint remaining = str_length;
	size_t ret;

	while (remaining > 0) {
		ret = php_matrirx_sapi_single_write(ptr, remaining);
		if (!ret) {
			php_handle_aborted_connection();
		}
		ptr += ret;
		remaining -= ret;
	}

	return str_length;
}

static void php_matrirx_sapi_flush(void *server_context) {
	if (fflush(stdout)==EOF) {
		php_handle_aborted_connection();
	}
}

static void php_matrirx_sapi_send_header(sapi_header_struct *sapi_header, void *server_context TSRMLS_DC) {
}

static void php_matrirx_sapi_log_message(char *message) {
	fprintf (stderr, "%s\n", message);
}

static void php_matrirx_sapi_register_variables(zval *track_vars_array TSRMLS_DC) {
//	php_import_environment_variables(track_vars_array TSRMLS_CC);
	php_register_variable("DOCUMENT_ROOT", "/", track_vars_array TSRMLS_CC);
	php_register_variable("SERVER_API", "MatrIRX/" MATRIRX_VERSION, track_vars_array TSRMLS_CC);
	php_register_variable("MATRIRX", "true", track_vars_array TSRMLS_CC);
	php_register_variable("MATRIRX_VERSION", MATRIRX_VERSION, track_vars_array TSRMLS_CC);
}

static int php_matrirx_sapi_startup(sapi_module_struct *sapi_module) {
	if (php_module_startup(sapi_module, phpext_matrirx_ptr, 1)==FAILURE) {
		return FAILURE;
	}
	return SUCCESS;
}

sapi_module_struct php_matrirx_sapi_module = {
	"matrirx",                       /* name */
	"PHP MatrIRX SAPI",        /* pretty name */
	
	php_matrirx_sapi_startup,              /* startup */
	php_module_shutdown_wrapper,   /* shutdown */
  
	NULL,                          /* activate */
	php_matrirx_sapi_deactivate,           /* deactivate */
  
	php_matrirx_sapi_ub_write,             /* unbuffered write */
	php_matrirx_sapi_flush,                /* flush */
	NULL,                          /* get uid */
	NULL,                          /* getenv */
  
	php_error,                     /* error handler */
  
	NULL,                          /* header handler */
	NULL,                          /* send headers handler */
	php_matrirx_sapi_send_header,          /* send header handler */
	
	NULL,                          /* read POST data */
	php_matrirx_sapi_read_cookies,         /* read Cookies */
  
	php_matrirx_sapi_register_variables,   /* register server variables */
	php_matrirx_sapi_log_message,          /* Log message */
	NULL,							/* Get request time */
  
	STANDARD_SAPI_MODULE_PROPERTIES
};
/* }}} */

int php_matrirx_sapi_init(PTSRMLS_DC) {
	zend_llist global_vars;
#ifdef ZTS
	zend_compiler_globals *compiler_globals;
	zend_executor_globals *executor_globals;
	php_core_globals *core_globals;
	sapi_globals_struct *sapi_globals;
	void ***tsrm_ls;
#endif

#ifdef HAVE_SIGNAL_H
#if defined(SIGPIPE) && defined(SIG_IGN)
	signal(SIGPIPE, SIG_IGN); /* ignore SIGPIPE in standalone mode so
								 that sockets created via fsockopen()
								 don't kill PHP if the remote site
								 closes it.  in apache|apxs mode apache
								 does that for us!  thies@thieso.net
								 20000419 */
#endif
#endif

#ifdef PHP_WIN32
  _fmode = _O_BINARY;			/*sets default for file streams to binary */
  setmode(_fileno(stdin), O_BINARY);		/* make the stdio mode be binary */
  setmode(_fileno(stdout), O_BINARY);		/* make the stdio mode be binary */
  setmode(_fileno(stderr), O_BINARY);		/* make the stdio mode be binary */
#endif

#ifdef ZTS
  tsrm_startup(1, 1, 0, NULL);
#endif

#ifdef ZTS
  compiler_globals = ts_resource(compiler_globals_id);
  executor_globals = ts_resource(executor_globals_id);
  core_globals = ts_resource(core_globals_id);
  sapi_globals = ts_resource(sapi_globals_id);
  tsrm_ls = ts_resource(0);
  *ptsrm_ls = tsrm_ls;
#endif

  sapi_startup(&php_matrirx_sapi_module);

  if (php_matrirx_sapi_module.startup(&php_matrirx_sapi_module)==FAILURE) {
	  return FAILURE;
  }
 
  php_matrirx_sapi_module.executable_location = global_executable_location;

  zend_llist_init(&global_vars, sizeof(char *), NULL, 0);  

  /* Set some Embedded PHP defaults */
  SG(options) |= SAPI_OPTION_NO_CHDIR;
  zend_alter_ini_entry("register_argc_argv", 19, "0", 1, PHP_INI_SYSTEM, PHP_INI_STAGE_ACTIVATE);
  zend_alter_ini_entry("html_errors", 12, "0", 1, PHP_INI_SYSTEM, PHP_INI_STAGE_ACTIVATE);
  zend_alter_ini_entry("implicit_flush", 15, "1", 1, PHP_INI_SYSTEM, PHP_INI_STAGE_ACTIVATE);
  zend_alter_ini_entry("max_execution_time", 19, "0", 1, PHP_INI_SYSTEM, PHP_INI_STAGE_ACTIVATE);
  zend_alter_ini_entry("enable_dl", 9, "0", 1, PHP_INI_SYSTEM, PHP_INI_STAGE_ACTIVATE);
  zend_alter_ini_entry("register_globals", 17, "0", 1, PHP_INI_SYSTEM, PHP_INI_STAGE_ACTIVATE);
  zend_alter_ini_entry("short_open_tag", 15, "1", 1, PHP_INI_SYSTEM, PHP_INI_STAGE_ACTIVATE);
  zend_alter_ini_entry("error_reporting", 16, "2039", 4, PHP_INI_SYSTEM, PHP_INI_STAGE_ACTIVATE);

  if (php_request_startup(TSRMLS_C)==FAILURE) {
	  php_module_shutdown(TSRMLS_C);
	  return FAILURE;
  }
  
  SG(headers_sent) = 1;
  SG(request_info).no_headers = 1;
  php_register_variable("PHP_SELF", "-", NULL TSRMLS_CC);

  return SUCCESS;
}

void php_matrirx_sapi_shutdown(TSRMLS_D)
{
	php_request_shutdown((void *) 0);
	php_module_shutdown(TSRMLS_C);
	sapi_shutdown();
#ifdef ZTS
    tsrm_shutdown();
#endif
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: sw=4 ts=4 fdm=marker
 * vim<600: sw=4 ts=4
 */
