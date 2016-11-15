<?php

/**
* Elixir, Stored Objects management
* @author Benoit Malchrowicz
* @version 1.0
*
* Copyright (C) 2014-2016 Benoit Malchrowicz
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; either version 2 of the License, or any later
* version.
*
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
* Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*
*/

// Define the minimal method DB Driver has to implement
abstract class Abstract_Driver
{
	// Load all table and their struct
	abstract public function loadData();

	//List all the table
	abstract public function getTables();

	//Get the complete structure of specific table
	abstract public function getStruct($table);

	//Get the field list of specific table
	abstract public function getFields($table);

	//Get the required field list of specific table
	abstract public function getRequiredFields($table);

	// Create a new table
	abstract public function createTable($tableName, $structure, $dropFirst=false);

	// Rename a table
	abstract public function renameTable($oldTable, $newTable);

	// Add a column to a table
	abstract public function addColumn($table, $column, $params=null);

	// Update a column in table (and possibly rename it)
	abstract public function updateColumn($table, $oldColumn, $newColumn, $params=null);

	// Delete a column in table
	abstract public function removeColumn($table, $column);

	// Get all data of a specific table
	abstract public function getData($table);

	// Insert data on a given table
	abstract public function insertData($table, $params, $insertID=False);

	// Update data on a given table
	abstract public function updateData($table, $params, $where=null);

	// Delete data on a given table
	abstract public function deleteData($table, $where);

	// Select subset of data in table based on $where conditions
	abstract public function selectData($table, $fields, $where=null, $count=0, $returnAssoc=True);

	// Search subset of data in table containing $where conditions
	abstract public function searchData($table, $fields, $where, $count=0, $returnAssoc=True);

}

?>