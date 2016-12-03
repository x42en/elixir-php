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

Class Object_Manager extends DB_Manager
{
	public function __construct($type, $param){
        parent::__construct($type, $param);
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

    // Return all flags registered
    public function getFlagList(){
        $table = DB_PREFIX.'Flags';
        $field = array ('FLAG', 'TYPE', 'OBJECT_ID');

        try{
            $tmp_list = $this->driver->selectData($table, $field);
        }catch (Exception $e){
            throw new LxrException($e->getMessage(),1015);
        }

        if(empty($tmp_list) || !is_array($tmp_list))
            return NULL;

        foreach ($tmp_list as $id => $entry) {
            $flag_list[$entry['TYPE']][$entry['FLAG']] = ID2array($entry['OBJECT_ID']);
        }

        return $flag_list;
    }

    // Return all ID entries of a certain type
    public function getObjectListByType($objectType, $count=0){
        $table = USER_PREFIX.$objectType;
        $field = array ('_id');

        try{
            $objects = $this->driver->selectData($table, $field, null, $count);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1013);
        }

        if(empty($objects) || !is_array($objects))
            return NULL;

        foreach ($objects as $key => $value) {
            $id_list[] = $value['_id'];
        }

        return $id_list;
    }

    // Return all ID entries of a certain type that meet selector
    public function selectObjectListByType($objectType, $selector, $count=0){
        $table = USER_PREFIX.$objectType;
        if(!empty($selector)){
            $field = array_keys($selector); 
        }else{
            $field = array();
        }
        // Add the _id field
        $field[] = '_id';

        // Adapt key selector for DB
        $selector = array_change_key_case($selector, CASE_LOWER);
        $where = array_combine(array_map('ucfirst', array_keys($selector)), $selector);

        try{
            $objects = $this->driver->selectData($table, $field, $where, $count);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1014);
        }

        if(empty($objects) || !is_array($objects))
            return NULL;

        foreach ($objects as $key => $value) {
            $id_list[] = $value['_id'];
        }

        return $id_list;
    }

    // Return the complete data of an object
    public function getObjectByID($objectType, $id){
        
        $table = USER_PREFIX.$objectType;
        $field = null;
        $where = array("_id" => $id);

        try{
            $result = $this->driver->selectData($table, $field, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1049);
        }

        if(empty($result[0]) || $result[0] === "NULL")
            return NULL;
        
        // Transform the flag list
        $result[0]['FLAGS'] = Flag2array($result[0]['FLAGS']);

        return $result[0];
    }

    // Store a new object
    public function storeObject($objectName, $data){
        // Add an entry in the object table
        $table = USER_PREFIX.$objectName;
        try{
            return $this->driver->insertData($table, $data, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1035);
        }

    }

    // Store a new object
    public function updateObject($objectName, $id, $data){
        // Add an entry in the object table
        $table = USER_PREFIX.$objectName;
        $where = array("_id" => $id);
        try{
            return $this->driver->updateData($table, $data, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1036);
        }

    }

    // Store a new object
    public function deleteObject($objectName, $id){
        // Add an entry in the object table
        $table = USER_PREFIX.$objectName;
        $where = array("_id" => $id);
        try{
            return $this->driver->deleteData($table, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1037);
        }

    }
}

?>

    