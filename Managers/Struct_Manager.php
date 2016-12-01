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

class Struct_Manager extends DB_Manager
{
    public function __construct($type, $param){
        parent::__construct($type, $param);
    }
    
    // Return all structures available
    public function getStructureList(){
        try{
            $structs =  $this->driver->getData(DB_PREFIX.'Structures');
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1010);
        }

        if(empty($structs) || !is_array($structs))
            return NULL;

        $structs_list = array();
        foreach ($structs as $key => $value) {
            $name = strtoupper($value['NAME']);
            if(!empty($value['STRUCT']))
                $structs_list[$name]['STRUCT'] = json_decode(parent::decode_data($value['STRUCT']), True);
            else
                $structs_list[$name]['STRUCT'] = '';
            if(!empty($value['DESCRIPTION']))
                $structs_list[$name]['DESCRIPTION'] = parent::decode_data($value['DESCRIPTION']);
            else
                $structs_list[$name]['DESCRIPTION'] = '';
        }
        
        return $structs_list;
    }

    // Create a new structure
    public function newStruct($structName, $structDesc, $structure){
        
        // Add an entry to the Structures table
        // and set a new table corresponding to the object
        $struct = parent::encode_data(json_encode($structure));
        $structDesc = parent::encode_data($structDesc);

        $req_params = array('NAME' => $structName,
                            'DESCRIPTION' => $structDesc,
                            'STRUCT' => $struct);

        try{
            $this->driver->insertData(DB_PREFIX.'Structures', $req_params, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1016);
        }

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
        

        return True;

    }

    public function renameStruct($oldName, $newName){
        // Rename entry in objects table
        $req_params = array('NAME' => $newName);
        $where = array('NAME' => $oldName);
        try{
            $this->driver->updateData(DB_PREFIX.'Structures', $req_params, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1018);
        }

        // Rename entry in collections
        $req_params = array('TYPE' => $newName);
        $where = array('TYPE' => $oldName);
        try{
            $this->driver->updateData(DB_PREFIX.'Flags', $req_params, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1019);
        }
        
        // Update all other structures with this struct as field
        $struct_list = $this->getStructureList();

        $oldName = strtocapital($oldName);
        $newName = strtocapital($newName);

        $modified = array();
        foreach ($struct_list as $object => $params) {

            // If structure contains field, rename it
            if(array_key_exists($oldName, $params['STRUCT'])){
                $struct_list[$object]['STRUCT'][$newName] = $struct_list[$object]['STRUCT'][$oldName];
                unset($struct_list[$object]['STRUCT'][$oldName]);

                // Update corresponding structure entry
                $this->updateStruct($object, $struct_list[$object]['STRUCT']);
                
                // Store modified object
                $modified[] = $object;
            }

        }

        // Update all object with this field
        foreach ($modified as $k => $object) {
            $this->updateObjectField($object, $oldName, $newName, $struct_list[$object]['STRUCT'][$newName]);
        }

        $oldName = strtoupper($oldName);
        $newName = strtoupper($newName);

        $params = array('OBJECT' => $newName);
        $where = array('OBJECT' => $oldName);
        // Rename views in view table
        try{
            $this->driver->updateData(DB_PREFIX.'Views', $params, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1020);
        }

        // Rename object table
        try{
            return $this->driver->renameTable(USER_PREFIX.$oldName, USER_PREFIX.$newName);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1021);
        }

    }

    // Suppress a structure and all corresponding objects
    public function deleteStruct($structName){
        // Delete the entry in the objects table
        $where = array('NAME' => $structName);
        try{
            $this->driver->deleteData(DB_PREFIX.'Structures', $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1022);
        }

        // Delete entry in collections table
        $where = array('TYPE' => $structName);
        try{
            $this->driver->deleteData(DB_PREFIX.'Flags', $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1023);
        }

        // Delete entry in views table
        $where = array('OBJECT' => $structName);
        try{
            $this->driver->deleteData(DB_PREFIX.'Views', $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1024);
        }

        // Delete structure object from all structure
        $struct_list = $this->getStructureList();

        // Deal with structure as a field for other object
        $structName = strtocapital($structName);
        $modified = array();
        foreach ($struct_list as $object => $params) {
            
            // If structure contains field, remove it
            if(array_key_exists($structName, $params['STRUCT'])){
                unset($struct_list[$object]['STRUCT'][$structName]);

                // Update corresponding structure entry
                $this->updateStruct($object, $struct_list[$object]['STRUCT']);
                
                // Store modified object
                $modified[] = $object;
            }

        }

        // Delete field from all object modified
        foreach ($modified as $k => $object) {
            $this->deleteObjectField($object, $structName);
        }

        // Delete the corresponding table
        try{
            return $this->driver->deleteTable(USER_PREFIX.strtoupper($structName));
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1025);
        }

        
    }

    // Modify structure description
    public function updateStructDesc($structName, $description){
        $description = parent::encode_data($description);
        $req_params = array('DESCRIPTION' => $description);
        $where = array('NAME' => $structName);
        try{
            return $this->driver->updateData(DB_PREFIX.'Structures', $req_params, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1026);
        }
    }

    // Modify structure
    public function updateStruct($structName, $struct){
        $struct = parent::encode_data(json_encode($struct));
        $req_params = array('STRUCT' => $struct);
        $where = array('NAME' => $structName);
        try{
            return $this->driver->updateData(DB_PREFIX.'Structures', $req_params, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1027);
        }
    }

    

    // Add a column on specific object
    public function addObjectField($objectName, $fieldName, $params=null){
        $table = USER_PREFIX.$objectName;
        // Update corresponding objects
        try{
            return $this->driver->addColumn($table, $fieldName, $params);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1032);
        }
    }

    // Update column options on specific object
    public function updateObjectField($objectName, $oldFieldName, $newFieldName, $params=null){
        $table = USER_PREFIX.$objectName;
        try{
            return $this->driver->updateColumn($table, $oldFieldName, $newFieldName, $params);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1033);
        }
    }

    // Delete column on specific object
    public function deleteObjectField($objectName, $fieldName){
        $table = USER_PREFIX.$objectName;
        try{
            $this->driver->removeColumn($table, $fieldName);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1034);
        }
    }

    
}

?>