dnl
dnl $Id: config.m4,v 1.9 2005/05/07 02:51:53 sniper Exp $
dnl

AC_MSG_CHECKING(for MatrIRX support)

AC_ARG_ENABLE(matrirx,
[  --enable-matrirx       Enable building of MatrIRX SAPI library],
[ 
  case $enableval in
    yes)
      PHP_EMBED_TYPE=static
      INSTALL_IT="\$(mkinstalldirs) \$(INSTALL_ROOT)\$(prefix)/lib; \$(INSTALL) -m 0644 $SAPI_STATIC \$(INSTALL_ROOT)\$(prefix)/lib"
      ;;
    *)
      PHP_EMBED_TYPE=no
      ;;
  esac
],[
  PHP_EMBED_TYPE=no
])

AC_MSG_RESULT($PHP_EMBED_TYPE)

if test "$PHP_EMBED_TYPE" != "no"; then
  PHP_SELECT_SAPI(matrirx, $PHP_EMBED_TYPE, php_matrirx.c matrirx_mod.c)
  PHP_INSTALL_HEADERS([sapi/matrirx/php_matrirx.h])
fi
