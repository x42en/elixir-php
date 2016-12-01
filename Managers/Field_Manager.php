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

class Field_Manager extends DB_Manager
{
    public function __construct($type, $param){
        parent::__construct($type, $param);
    }
    
    // Return all structures available
    private function getStructureList(){
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

    // Modify structure
    private function updateStruct($structName, $struct){
        $struct = parent::encode_data(json_encode($struct));
        $req_params = array('STRUCT' => $struct);
        $where = array('NAME' => $structName);
        try{
            return $this->driver->updateData(DB_PREFIX.'Structures', $req_params, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1027);
        }
    }

    // Delete column on specific object
    private function deleteObjectField($objectName, $fieldName){
        $table = USER_PREFIX.$objectName;
        try{
            $this->driver->removeColumn($table, $fieldName);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1034);
        }
    }

    // Return all fields available
    public function getFieldList(){
        try{
            $fields =  $this->driver->getData(DB_PREFIX.'Fields');
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1009);
        }

        if(empty($fields) || !is_array($fields))
            return NULL;

        $fields_list = array();
        foreach ($fields as $key => $value) {
            $name = strtocapital($value['NAME']);
            $fields_list[$name]['REGEX'] = parent::decode_data($value['REGEX']);
            $fields_list[$name]['DESCRIPTION'] = parent::decode_data($value['DESCRIPTION']);
        }

        return $fields_list;
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
            return $this->driver->insertData(DB_PREFIX.'Fields', $req_params);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1028);
        }
    }

    // Update an existing field
    public function updateField($fieldName, $regex, $description){
        // Update field structure from field table
        $regex = parent::encode_data($regex);
        $description = parent::encode_data($description);

        $req_params = array('REGEX' => $regex,
                            'DESCRIPTION' => $description);
        $where = array('NAME' => $fieldName);
        $updated = FALSE;

        try{
            $this->driver->updateData(DB_PREFIX.'Fields', $req_params, $where);
            $updated = $req_params;
            $updated['NAME'] = $fieldName;
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1029);
        }

        // Return updated field object
        return $updated;

    }

    // Rename an existing field
    public function renameField($oldFieldName, $newFieldName){
        // Update all structures with this field
        $struct_list = $this->getStructureList();

        $modified = array();
        foreach ($struct_list as $object => $params) {

            // If structure contains field, rename it
            if(array_key_exists($oldFieldName, $params['STRUCT'])){
                $struct_list[$object]['STRUCT'][$newFieldName] = $struct_list[$object]['STRUCT'][$oldFieldName];
                unset($struct_list[$object]['STRUCT'][$oldFieldName]);

                // Update corresponding structure entry
                $this->updateStruct($object, $struct_list[$object]['STRUCT']);
                
                // Store modified object
                $modified[] = $object;
            }

        }

        // Update all object with this field
        foreach ($modified as $k => $object) {
            $this->updateObjectField($object, $oldFieldName, $newFieldName, $struct_list[$object]['STRUCT'][$newFieldName]);
        }

        // Update field name from field table
        $req_params = array('NAME' => $newFieldName);
        $where = array('NAME' => $oldFieldName);
        try{
            return $this->driver->updateData(DB_PREFIX.'Fields', $req_params, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1030);
        }

    }

    // Delete a field
    public function deleteField($fieldName){
        // Delete field from all structure
        $struct_list = $this->getStructureList();

        $modified = array();
        foreach ($struct_list as $object => $params) {
            
            // If structure contains field, remove it
            if(array_key_exists($fieldName, $params['STRUCT'])){
                unset($struct_list[$object]['STRUCT'][$fieldName]);

                // Update corresponding structure entry
                $this->updateStruct($object, $struct_list[$object]['STRUCT']);
                
                // Store modified object
                $modified[] = $object;
            }

        }

        // Delete field from all object modified
        foreach ($modified as $k => $object) {
            $this->deleteObjectField($object, $fieldName);
        }


        // Delete Field from Fields Table
        $where['NAME'] = $fieldName;
        try{
            return $this->driver->deleteData(DB_PREFIX.'Fields', $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1031);
        }
    }
        
}

?>