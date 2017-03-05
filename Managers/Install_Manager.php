<?php

/**
* Elixir, Stored Objects management
* @author Benoit Malchrowicz
* @version 1.0
*
* Copyright © 2014-2016 Benoit Malchrowicz
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

Class Install_Manager extends DB_Manager
{
	public function __construct($type, $param){
        parent::__construct($type, $param);
    }

    // Initialize the minimal DB structure
    public function initialize(){
        
        // Create table with correct params
        try{
            $this->driver->createTable(DB_PREFIX.'Users', $system_user, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1001);
        }

        // Create table with correct params
        try{
            $this->driver->createTable(DB_PREFIX.'Errors', $system_error, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1002);
        }

        // Create table with correct params
        try{
            $this->driver->createTable(DB_PREFIX.'Groups', $system_group, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1003);
        }

        // Create table with correct params
        try{
            $this->driver->createTable(DB_PREFIX.'Fields', $system_field, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1004);
        }

        // Create table with correct params
        try{
            $this->driver->createTable(DB_PREFIX.'Structures', $system_struct, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1005);
        }

        // Create table with correct params
        try{
            $this->driver->createTable(DB_PREFIX.'Flags', $system_flag, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1006);
        }

        // Create table with correct params
        try{
            $this->driver->createTable(DB_PREFIX.'Views', $system_view, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1007);
        }

        // Add the only one system flag
        $data = array('FLAG' => '_GROUPS',
                        'TYPE' => 'GLOBALS',
                        'OBJECT_ID' => '');

        try{
            $this->driver->insertData(DB_PREFIX.'Flags', $data);
        } catch(Exception $e){
            throw new LxrException($e->getMessage(),1008);
        }
        

    }

    // Convert existing table to Elixir format
    public function lxrify($table){

        // Add default fields
        $params[TABLE_PREFIX.'id']['type'] = 'id';
        $params[TABLE_PREFIX.'id']['required'] = TRUE;
        $params[TABLE_PREFIX.'id']['unique'] = TRUE;
        $params[TABLE_PREFIX.'FLAGS']['type'] = 'system';
        $params[TABLE_PREFIX.'ACCESS']['type'] = 'system';
        $params[TABLE_PREFIX.'RW_ACCESS']['type'] = 'system';

        // Append user defined values to query
        foreach ($params as $key => $opts) {
            // Create table with correct params
            try{
                $this->driver->addColumn(USER_PREFIX.$table, $key, $opts);
            }catch(Exception $e){
                throw new LxrException($e->getMessage(),1009);
            }
        }   

        return True;
    }

    // Create a new Field
    public function newField($fieldName, $regex, $description){
        // Add field and regex condition to Fields table
        $regex = parent::encode_data($regex);
        $description = parent::encode_data($description);

        $req_params = array('NAME' => $fieldName,
                            'REGEX' => $regex,
                            'DESCRIPTION' => $description);

        try{
            return $this->driver->insertData(DB_PREFIX.'Fields', $req_params, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1028);
        }
    }

    // Create a new structure
    public function newStruct($structName, $structDesc, $structure, $brandNew=True){
        
        // Add an entry to the Structures table
        // and set a new table corresponding to the object
        $struct = $this->encode_data(json_encode($structure));
        $structDesc = $this->encode_data($structDesc);

        $req_params = array('NAME' => $structName,
                            'DESCRIPTION' => $structDesc,
                            'STRUCT' => $struct);

        try{
            $this->driver->insertData(DB_PREFIX.'Structures', $req_params, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1016);
        }

        // If we're really creating the table (not restifyng...)
        if($brandNew){
            // Append user defined values to query
            foreach ($structure as $key => $opts) {
                // Skip system defined fields
                if(!empty($opts['type']) && $opts['type'] == "system") continue;
                $params[ucfirst($key)] = $opts;
            }

            // Create table with correct params
            try{
                $this->driver->createTable(USER_PREFIX.$structName, $params);
            }catch(Exception $e){
                throw new LxrException($e->getMessage(),1017);
            }
        }
        
        // Now table exists, lxrify it ;)
        try{
            $this->lxrify(USER_PREFIX.$structName);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(), 1018);
        }

        return True;

    }
    
}

?>