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

require_once (__ROOT__ . 'Managers/DB_Manager.php');

Class Install_Manager extends DB_Manager
{
	public function __construct($type, $param){
        parent::__construct($type, $param);
    }

    // Initialize the minimal DB structure
    public function initialize(){
        
        // Add LXR_Users fields
        $params['ACCESS']['type'] = 'system';
        $params['ACCESS']['required'] = FALSE;
        $params['RW_ACCESS']['type'] = 'system';
        $params['RW_ACCESS']['required'] = FALSE;
        $params['USERNAME']['type'] = 'varchar';
        $params['USERNAME']['required'] = TRUE;
        $params['USERNAME']['primary'] = TRUE;
        $params['DESCRIPTION']['type'] = 'text';
        $params['DESCRIPTION']['required'] = FALSE;

        // Create table with correct params
        try{
            $this->driver->createTable(DB_PREFIX.'Users', $params, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1001);
        }

        // Add LXR_Errors fields
        $params = array();
        $params['ACCESS']['type'] = 'system';
        $params['ACCESS']['required'] = FALSE;
        $params['RW_ACCESS']['type'] = 'system';
        $params['RW_ACCESS']['required'] = FALSE;
        $params['CODE']['type'] = 'int';
        $params['CODE']['required'] = TRUE;
        $params['CODE']['required'] = TRUE;
        $params['CODE']['primary'] = TRUE;
        $params['MESSAGE']['type'] = 'text';
        $params['MESSAGE']['required'] = TRUE;
        $params['MESSAGE']['primary'] = TRUE;
        $params['LANG']['type'] = 'text';
        $params['LANG']['required'] = TRUE;
        $params['LANG']['primary'] = TRUE;
        
        // Create table with correct params
        try{
            $this->driver->createTable(DB_PREFIX.'Errors', $params, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1002);
        }

        // Add LXR_Groups fields
        $params = array();
        $params['GROUPNAME']['type'] = 'varchar';
        $params['GROUPNAME']['required'] = TRUE;
        $params['GROUPNAME']['primary'] = TRUE;
        $params['USER_LIST']['type'] = 'text';
        $params['USER_LIST']['required'] = TRUE;

        // Create table with correct params
        try{
            $this->driver->createTable(DB_PREFIX.'Groups', $params, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1003);
        }

        // Add LXR_Fields fields
        $params = array();
        $params['ACCESS']['type'] = 'system';
        $params['ACCESS']['required'] = FALSE;
        $params['RW_ACCESS']['type'] = 'system';
        $params['RW_ACCESS']['required'] = FALSE;
        $params['NAME']['type'] = 'varchar';
        $params['NAME']['required'] = TRUE;
        $params['NAME']['primary'] = TRUE;
        $params['REGEX']['type'] = 'text';
        $params['REGEX']['required'] = TRUE;
        $params['DESCRIPTION']['type'] = 'text';
        $params['DESCRIPTION']['required'] = FALSE;

        // Create table with correct params
        try{
            $this->driver->createTable(DB_PREFIX.'Fields', $params, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1004);
        }

        // Add LXR_Structures fields
        $params = array();
        $params['ACCESS']['type'] = 'system';
        $params['ACCESS']['required'] = FALSE;
        $params['RW_ACCESS']['type'] = 'system';
        $params['RW_ACCESS']['required'] = FALSE;
        $params['NAME']['type'] = 'varchar';
        $params['NAME']['required'] = TRUE;
        $params['NAME']['primary'] = TRUE;
        $params['STRUCT']['type'] = 'text';
        $params['STRUCT']['required'] = TRUE;
        $params['DESCRIPTION']['type'] = 'text';
        $params['DESCRIPTION']['required'] = FALSE;

        // Create table with correct params
        try{
            $this->driver->createTable(DB_PREFIX.'Structures', $params, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1005);
        }

        // Add LXR_Flags fields
        $params = array();
        $params['ACCESS']['type'] = 'system';
        $params['ACCESS']['required'] = FALSE;
        $params['RW_ACCESS']['type'] = 'system';
        $params['RW_ACCESS']['required'] = FALSE;
        $params['FLAG']['type'] = 'varchar';
        $params['FLAG']['required'] = TRUE;
        $params['FLAG']['primary'] = TRUE;
        $params['TYPE']['type'] = 'varchar';
        $params['TYPE']['required'] = TRUE;
        $params['TYPE']['primary'] = TRUE;
        $params['OBJECT_ID']['type'] = 'object';
        $params['OBJECT_ID']['required'] = TRUE;
        $params['OBJECT_ID']['primary'] = TRUE;
        
        // Create table with correct params
        try{
            $this->driver->createTable(DB_PREFIX.'Flags', $params, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1006);
        }

        // Add LXR_Views fields
        $params = array();
        $params['ACCESS']['type'] = 'system';
        $params['ACCESS']['required'] = FALSE;
        $params['RW_ACCESS']['type'] = 'system';
        $params['RW_ACCESS']['required'] = FALSE;
        $params['OBJECT']['type'] = 'varchar';
        $params['OBJECT']['required'] = TRUE;
        $params['OBJECT']['primary'] = TRUE;
        $params['TYPE']['type'] = 'vchar';
        $params['TYPE']['required'] = TRUE;
        $params['TYPE']['primary'] = TRUE;
        $params['FORMAT']['type'] = 'vchar';
        $params['FORMAT']['required'] = TRUE;
        $params['FORMAT']['primary'] = TRUE;
        $params['RAW']['type'] = 'field';
        $params['RAW']['required'] = TRUE;
        
        // Create table with correct params
        try{
            $this->driver->createTable(DB_PREFIX.'Views', $params, TRUE);
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
        $params['_id']['type'] = 'object';
        $params['_id']['required'] = TRUE;
        $params['_id']['increment'] = TRUE;
        $params['_id']['primary'] = TRUE;
        $params['FLAGS']['type'] = 'system';
        $params['ACCESS']['type'] = 'system';
        $params['RW_ACCESS']['type'] = 'system';

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
            // Add default fields
            $params['_id']['type'] = 'system';
            $params['_id']['required'] = TRUE;
            $params['_id']['increment'] = TRUE;
            $params['_id']['primary'] = TRUE;
            $params['FLAGS']['type'] = 'system';
            $params['ACCESS']['type'] = 'system';
            $params['RW_ACCESS']['type'] = 'system';

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
        else{
            try{

            }catch(Exception $e){
                throw new LxrException($e->getMessage(), 1018);
                
            }
            $this->lxrify(USER_PREFIX.$structName);
        }

        return True;

    }
    
}

?>