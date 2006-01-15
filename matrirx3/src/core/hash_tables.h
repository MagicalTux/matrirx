/* hash_tables.h : headers for hash tables
 * $Id$
 *
 *  MatrIRX III Project
 *  Copyright (C) 2006 Robert Karpeles
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 1, or (at your option)
 *   any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, write to the Free Software
 *   Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */

/* We will store linked lists in our hash tables, so first make the linked list typedef
 */

#ifndef _HASH_TABLES_H
#define _HASH_TABLES_H

typedef struct _list_element {
	char *string;
	void *pointer; /* pointer to the value of this string */
	struct _list_element *next;
} list_element;

/* and the base hashtable structure with a dynamic array
 */

typedef struct _hash_table {
	unsigned long size; /* store hash table size for future references */
	struct _list_element **table;
	void (*free_func)(void *);
} hash_table;

/* Our "exported" functions 
 */

hash_table *hash_create_table(unsigned long, void *func);
list_element *hash_lookup_raw(hash_table *, const char *);
void *hash_lookup(hash_table *, const char *);
int hash_set_element(hash_table *, char *, void *, int);
int hash_add_element(hash_table *, char *, void *);
int hash_del_element(hash_table *, char *);
void hash_free_table(hash_table *);
list_element **hash_foreach(hash_table *);

#endif

