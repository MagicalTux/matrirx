/* childs.c
 * Child management functions
 * $Id$
 */

#include <stdio.h>
#include <stdlib.h>
#include <sched.h>
#include <signal.h>
#include <matrirx.h>
#include "hash_tables.h"

#include <sapi/matrirx/php_matrirx.h>

pid_t matrirx_child_list[MATRIRX_MAX_CHILD_COUNT];
hash_table matrirx_child_table;

struct _matrirx_child_info {
	int id;
	char *name;
};

#define CHILD_STACK_SIZE (128*1024)

int child_process(struct _matrirx_child_info *inf) {
	global_addon_name = inf->name;
	sleep(1);
	char *phpcode="echo 'Hello World from child '.CHILD_NAME.' with PHP/'.phpversion().' MatrIRX/'.matrirx_version().' - SAPI='.php_sapi_name().\"\\n\"; var_dump(getmypid()); ob_start(); phpinfo(); $fil=fopen('/tmp/phpinfo.html', 'w'); fputs($fil, ob_get_contents()); ob_end_clean();";
	printf("Passing PHP code to PHP5(sapi:matrirx): %s\n", phpcode);
	PHP_MATRIRX_SAPI_START_BLOCK();
	zend_eval_string_ex(phpcode, NULL, "Test Code", 1);
	PHP_MATRIRX_SAPI_END_BLOCK();
	return 0;
}

int start_child(char *addon_name) {
	int pid, id=-1;
	void **child_stack;
	struct _matrirx_child_info *inf;
	for(int i=0;i<MATRIRX_MAX_CHILD_COUNT;i++) if (matrirx_child_list[i]==0) { id=i; break; }
	if (id == -1) return 0;
	child_stack=(void**)malloc(CHILD_STACK_SIZE);
	inf = malloc(sizeof(struct _matrirx_child_info));
	inf->id = id;
	inf->name = strdup(addon_name);
#if defined(__hpux)
	pid = clone((void*)child_process, child_stack, SIGCHLD, inf);
#else
	pid = clone((void*)child_process, child_stack+(CHILD_STACK_SIZE/sizeof(void **)), SIGCHLD, inf);
#endif
	free(inf->name);
	free(inf);
	if (pid < 0) {
		perror("clone");
		return 0;
	}
	matrirx_child_list[id] = pid;
	printf("Forked child %s with pid %d\n", addon_name, pid);
	return pid;
}

