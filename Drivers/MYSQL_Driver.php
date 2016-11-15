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

require_once ( __ROOT__ . 'Drivers/Abstract_Driver.php');

Class MYSQL_Driver extends Abstract_Driver
{
    
    // Handle for DB connection
    private $DBH;
    
    // Database var
    protected $dbTables;
    protected $isConnected;
    
    //Initiate the mysql connection
    function __construct($param) {

        // Initialize default value
        $host = (!empty($param['host'])) ? $param['host'] : 'localhost';
        $port = (!empty($param['port'])) ? $param['port'] : 3306;
        $user = (!empty($param['user'])) ? $param['user'] : 'root';
        $pass = (!empty($param['password'])) ? $param['password'] : '';
        $db = (!empty($param['bdd'])) ? $param['bdd'] : 'test';

        try {
            $this->DBH = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
            $this->DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            //Set the charset
            $this->DBH->query("SET NAMES utf8");
            $this->DBH->exec("SET CHARACTER SET utf8");
            $this->DBH->exec("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO'");
            $this->DBH->exec("SET time_zone = '+01:00'");
        }
        
        catch(PDOException $e) {
            throw new Exception($e->getMessage());
        }
        
        //the credential where good, so we are connected
        $this->isConnected = True;
    }
    
    // Load all tables and their struct
    public function loadData() {
        
        // List the tables
        $sql = "SHOW TABLES";
        
        $tables = $this->DBH->query($sql);
        $tables->setFetchMode(PDO::FETCH_NUM);
        
        while ($tName = $tables->fetch()) {
            $tmp[] = $tName[0];
        }
        
        if (empty($tmp)) throw new Exception('Database is empty.');
        
        //Describe each table found
        foreach ($tmp as $table) {
            
            $sql = "DESCRIBE " . $table;
            $fields = $this->DBH->query($sql);
            $fields->setFetchMode(PDO::FETCH_ASSOC);
            
            while ($fName = $fields->fetch()) {
                $this->dbTables[$table][] = $fName;
            }
        }
    }
    
    //List all the tables
    public function getTables() {
        foreach ($this->dbTables as $table => $none) {
            $tables[] = $table;
        }
        return $tables;
    }
    
    //Get the complete structure of specific table
    public function getStruct($table) {
        foreach ($this->dbTables[$table] as $none => $value) {
            $fields[$value['Field']] = $value;
        }
        return $fields;
    }
    
    //Get the field list of specific table
    public function getFields($table) {
        foreach ($this->dbTables[$table] as $none => $value) {
            $fields[] = $value['Field'];
        }
        return $fields;
    }
    
    //Get the required field list of specific table
    public function getRequiredFields($table) {
        foreach ($this->dbTables[$table] as $none => $value) {
            if ($value['Null'] == "NO") $fields[] = $value['Field'];
        }
        return $fields;
    }
    
    // Create a new table
    public function createTable($tableName, $structure, $dropFirst=false) {
        if ($dropFirst) $req = "DROP TABLE IF EXISTS `" . $tableName . "`;";
        else $req = '';
        
        $primary = null;
        
        // Starting query with standard values
        $req.= "CREATE TABLE IF NOT EXISTS `" . $tableName . "` (";
        
        // Append user defined values to query
        foreach ($structure as $key => $opts) {
        	$type = 'text';
        	if(!empty($opts['type'])){
        		switch ($opts['type']) {
        			case 'field':
        				$type = 'text';
        				break;
        			
        			case 'collection':
        				$type = 'text';
        				break;
        			
        			case 'object':
        				$type = 'bigint(20)';
        				break;
        			
        			case 'int':
        				$type = 'int(11)';
        				break;
        			
        			case 'bigint':
        				$type = 'bigint(20)';
        				break;
        			
        			case 'varchar':
        				$type = 'varchar(250)';
        				break;
        			
        			case 'vchar':
        				$type = 'varchar(10)';
        				break;
        			
        			default:
        				$type = 'text';
        				break;
        		}
        	}
            $req.= "`" . $key . "` " . $type;
            
            if (!empty($opts['required']) && $opts['required']) $req.= " NOT NULL";
            else $req.= " NULL";
            
            if (!empty($opts['increment']) && $opts['increment']) $req.= " AUTO_INCREMENT";
            
            // There can be only ONE primary key
            if (!empty($opts['primary']) && $opts['primary']) $primary = $key;
            
            // There can be complex UNIQUE key
            if (!empty($opts['unique']) && $opts['unique']) $unique[] = $key;
            
            $req.= ",";
        }
        
        // Ending query with standard values
        if (!empty($primary)) $req.= "  PRIMARY KEY (`" . $primary . "`)";

        if(!empty($unique) && is_array($unique)){
        	if(!empty($primary)) $req .= ",";

        	$req .= " UNIQUE KEY `".strtoupper($unique[0])."` (";
        	foreach ($unique as $i => $v) {
        		$req .= "`".strtoupper($v)."`, ";
        	}
        	// Drop last comma
        	$req = substr($req, 0, -2);

        	// Close UNIQUE condition
        	$req .= ")";
        }
        
        $req.= ") ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        
        try {
            return $this->execData($req);
        }
        catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    // Rename a table
    public function renameTable($oldTable, $newTable) {
        $sql = "RENAME TABLE `" . $oldTable . "` TO `" . $newTable . "`";
        
        // Execute query against DB
        try {
            return $this->execData($sql);
        }
        catch(Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    // Delete a table
    public function deleteTable($table) {
        $sql = "DROP TABLE `" . $table . "`";
        
        // Execute query against DB
        try {
            return $this->execData($sql);
        }
        catch(Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    // Add a column to a table
    public function addColumn($table, $column, $params=null) {
        $sql = "ALTER TABLE `" . $table . "` ADD `" . $column . "` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci";
        
        if (!empty($params['required']) && $params['required']) $sql.= " NOT NULL";
        else $sql.= " NULL";
        
        // Execute query against DB
        try {
            return $this->execData($sql);
        }
        catch(Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    // Update a column in table (and possibly rename it)
    public function updateColumn($table, $oldColumn, $newColumn, $params=null) {
        $sql = "ALTER TABLE `" . $table . "` CHANGE `" . $oldColumn . "` `" . $newColumn . "` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci";
        
        if (!empty($params['required']) && $params['required']) $sql.= " NOT NULL";
        else $sql.= " NULL";
        
        // Execute query against DB
        try {
            return $this->execData($sql);
        }
        catch(Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    // Delete a column in table
    public function removeColumn($table, $column) {
        $sql = "ALTER TABLE `" . $table . "` DROP `" . $column . "`";
        
        // Execute query against DB
        try {
            return $this->execData($sql);
        }
        catch(Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    // Get all data of a specific table
    public function getData($table) {
        
        // $sql = "SELECT * FROM :table";
        // $sql_param = array(':table' => $table);
        
        $sql = "SELECT * FROM `" . $table . "`";
        
        try {
            
            // Execute the query against the database
            $sth = $this->DBH->query($sql);
            
            // $sth = $this->DBH->prepare($sql);
            // $sth->execute($sql_param);
            
        }
        catch(PDOException $ex) {
            throw new Exception($ex->getMessage());
        }
        
        $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
        $i = 0;
        
        $data = NULL;
        
        if (empty($this->dbTables[$table])) return NULL;
        
        foreach ($rows as $k => $v) {
            foreach ($this->dbTables[$table] as $key => $value) {
                if (empty($v[$value['Field']])) $data[$i][$value['Field']] = "NULL";
                else $data[$i][$value['Field']] = $v[$value['Field']];
            }
            $i++;
        }
        
        return $data;
    }
    
    // Insert data on a given table
    public function insertData($table, $params, $insertID=False) {
        
        // Write query
        $query = "INSERT INTO `" . $table . "` (";
        $end_query = ") VALUES(";
        
        // Parse params array
        foreach ($params as $key => $value) {
            $query.= "`" . strtoupper($key) . "`, ";
            
            // Define PDO key format
            $pdo_key = strtolower($key);
            
            // Adapt null value to mysql
            if (empty($value)) $value = "";
            
            $query_params[$pdo_key] = $value;
            $end_query.= ":" . $pdo_key . ", ";
        }
        
        // Drop the last comma remaining in each query before closing all
        $query = substr($query, 0, -2) . substr($end_query, 0, -2) . ")";
        
        // Execute query against DB
        try {
            return $this->setData($query, $query_params, $insertID);
        }
        catch(Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    // Update data on a given table
    public function updateData($table, $params, $where=null) {
        
        // Write query
        $query = "UPDATE `" . $table . "` SET ";
        
        // Parse params array
        foreach ($params as $key => $value) {
            
            // Define PDO key format
            $pdo_key = strtolower($key);
            
            // Adapt null value to mysql
            if (empty($value)) $value = "";
            
            // Construct query and params array
            $query.= "`" . strtoupper($key) . "`= :" . $pdo_key . ", ";
            $query_params[$pdo_key] = $value;
        }
        
        // Drop the last comma remaining
        $query = substr($query, 0, -2);
        
        // If a condition is specified
        if (!empty($where) && is_array($where)) {
            
            $query.= " WHERE ";
            
            foreach ($where as $key => $value) {
                
                // Define PDO key format avoiding rewriting previous ones
                $pdo_key = 'where_' . strtolower($key);
                
                if (empty($value)) throw new Exception("WHERE condition can not be null");
                
                // Finish construct query and params array
                $query.= "`" . strtoupper($key) . "`= :" . $pdo_key . " AND ";
                $query_params[$pdo_key] = $value;
            }
            
            // Drop the last AND remaining in query before closing all
            $query = substr($query, 0, -5);
        }
        
        // Execute query against DB
        try {
            return $this->setData($query, $query_params);
        }
        catch(Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    // Delete data on a given table
    public function deleteData($table, $where) {
        
        if (empty($where) || !is_array($where)) throw new Exception("WHERE condition is invalid");
        
        $query = "DELETE FROM `" . $table . "` WHERE ";
        
        foreach ($where as $key => $value) {
            $query.= "`" . strtoupper($key) . "`= '" . $value . "' AND ";
        }
        
        // Drop the last AND remaining in query before closing all
        $query = substr($query, 0, -5);
        
        // Execute query against DB
        try {
            return $this->execData($query);
        }
        catch(Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    // Add data on a table and optionally return last inserted ID
    private function setData($query, $query_params, $insertID=False) {
        
        try {
            
            // Execute the query against the database
            $sth = $this->DBH->prepare($query);
            $sth->execute($query_params);
        }
        catch(PDOException $ex) {
            throw new Exception($ex->getMessage());
        }
        
        if ($insertID) return $this->DBH->lastInsertId();
        else return TRUE;
    }
    
    // Select subset of data in table based on $where conditions
    public function selectData($table, $fields, $where=null, $count=0, $returnAssoc=True) {
        
        $query = "SELECT ";
        
        if (empty($fields) || !is_array($fields)) {
            $query.= "* FROM ";
        } 
        else {
            foreach ($fields as $k => $field) {
                $query.= strtoupper($field) . ", ";
            }
            $query = substr($query, 0, -2) . " FROM ";
        }
        
        $query.= "`" . $table . "`";
        
        if (!empty($where) && is_array($where)) {
            $query = $query . " WHERE ";
            foreach ($where as $key => $value) {
                $pdo_key = strtolower($key);
                $query.= strtoupper($key) . " = :" . $pdo_key . " AND ";
                $query_params[$pdo_key] = $value;
            }
            
            $query = substr($query, 0, -5);
        } 
        else {
            $query_params = null;
        }
        
        if (is_int($count) && $count > 0) {
            $query.= " LIMIT " . $count;
        }
        
        try {
            
            // Execute the query against the database
            $sth = $this->DBH->prepare($query);
            $sth->execute($query_params);
        }
        catch(PDOException $ex) {
            throw new Exception($ex->getMessage());
        }
        
        // Return array assoc or numeric assoc depending of flag returnAssoc
        if ($returnAssoc) $results = $sth->fetchAll(PDO::FETCH_ASSOC);
        else $results = $sth->fetchAll(PDO::FETCH_NUM);
        
        return $results;
    }
    
    // Search subset of data in table containing $where conditions
    public function searchData($table, $fields, $where, $count=0, $returnAssoc=True) {
        
        $query = "SELECT ";
        
        // Select all fields if nothing specified
        if (empty($fields) || !is_array($fields)) {
            $query.= "* FROM ";
        } 
        else {
            foreach ($fields as $k => $field) {
                $query.= strtoupper($field) . ", ";
            }
            $query = substr($query, 0, -2) . " FROM ";
        }
        
        $query.= "`" . $table . "`";
        
        // Define a search condition
        if (!empty($where) && is_array($where)) {
            $query = $query . " WHERE ";
            foreach ($where as $key => $value) {
                $pdo_key = strtolower($key);
                $query.= strtoupper($key) . " LIKE :" . $pdo_key . " AND ";
                
                // Check if quotes are set (starts/ends with flag)
                if ($value[0] == "'"){
                    $query_params[$pdo_key] = '%';
                    $value = substr($value, 1);
                }
                else{
                    $query_params[$pdo_key] = '';
                }

                if ($value[-1] == "'") $value .= '%';

                $query_params[$pdo_key] .= $value;
            }
            
            // Remove last trailing ' AND '
            $query = substr($query, 0, -5);
        }
        
        if (is_int($count) && $count > 0) {
            $query.= " LIMIT " . $count;
        }
        
        try {
            
            // Execute the query against the database
            $sth = $this->DBH->prepare($query);
            $sth->execute($query_params);
        }
        catch(PDOException $ex) {
            throw new Exception($ex->getMessage());
        }
        
        // Return array assoc or numeric assoc depending of flag returnAssoc
        if ($returnAssoc) $results = $sth->fetchAll(PDO::FETCH_ASSOC);
        else $results = $sth->fetchAll(PDO::FETCH_NUM);
        
        return $results;
    }
    
    private function execData($query) {
        
        try {
            
            // Execute the query against the database
            $sth = $this->DBH->exec($query);
        }
        catch(PDOException $ex) {
            throw new Exception($ex->getMessage());
        }
        
        return TRUE;
    }
    
    // Close DB connection while destructing the object
    function __destruct() {
        // $this->DBH->close();
        unset($this->DBH);
    }
}
?>