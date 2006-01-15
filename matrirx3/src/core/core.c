/* core.c
 * This is the core of the core !
 * $Id$
 */

#include <stdio.h>
#include <matrirx.h>
#include <sys/types.h>
#include <unistd.h>
#include "childs.h"

int main(int argc, char *argv[]) {
	global_executable_location = argv[0];
	global_addon_name = NULL;
	printf("MatrIRX III - Main process pid: %d\n", getpid());
	start_child("test");
	sleep(2);
	return 0;
}

