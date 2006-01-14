PHP_ARG_ENABLE(matrirx, whether to enable MatrIRX special functions,
[  --enable-matrirx        Enable MatrIRX support])

if test "$PHP_MATRIRX" = "yes"; then
AC_DEFINE(HAVE_MATRIRX, 1, [Whether you have MatrIRX functions])
PHP_NEW_EXTENSION(matrirx, matrirx.c, $ext_shared)
fi
