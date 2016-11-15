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

require_once (__ROOT__ . 'Drivers/MYSQL_Driver.php');
require_once (__ROOT__ . 'Drivers/FILE_Driver.php');

Class DBManager{

    private $driver;

    function __construct($type, $param){

        $db = strtoupper($type).'_Driver';

        if(class_exists($db)){
            // Initialize the proper driver
            $this->driver = new $db($param);

            try{
                $this->driver->loadData();
            }catch(Exception $e){
                if($e->getMessage() === "Database is empty.")
                    $this->initialize();
                else
                    throw new LxrException($e->getMessage(),1000);
            }
        }
        else{
            throw new LxrException('Unsupported Database...', 911);
        }
        
    }

    // Allow optional encoding
    private function encode_data($str){
        if(ENCODING) return base64_encode($str);
        else return $str;
        
    }


    // Allow optional decoding
    private function decode_data($str){
        if(ENCODING) return base64_decode($str);
        else return $str;
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
        $params['ID']['type'] = 'bigint';
        $params['ID']['required'] = TRUE;
        $params['ID']['increment'] = TRUE;
        $params['ID']['primary'] = TRUE;
        $params['CODE']['type'] = 'text';
        $params['CODE']['required'] = TRUE;
        $params['MESSAGE']['type'] = 'text';
        $params['MESSAGE']['required'] = TRUE;

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
        $params['ID']['type'] = 'bigint';
        $params['ID']['required'] = TRUE;
        $params['ID']['increment'] = TRUE;
        $params['ID']['primary'] = TRUE;
        $params['NAME']['type'] = 'varchar';
        $params['NAME']['required'] = TRUE;
        $params['NAME']['unique'] = TRUE;
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
        $params['ID']['type'] = 'bigint';
        $params['ID']['required'] = TRUE;
        $params['ID']['increment'] = TRUE;
        $params['ID']['primary'] = TRUE;
        $params['FLAG']['type'] = 'varchar';
        $params['FLAG']['required'] = TRUE;
        $params['TYPE']['type'] = 'varchar';
        $params['TYPE']['required'] = TRUE;
        $params['ID_LIST']['type'] = 'text';
        $params['ID_LIST']['required'] = TRUE;

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
        $params['OBJECT']['unique'] = TRUE;
        $params['TYPE']['type'] = 'vchar';
        $params['TYPE']['required'] = TRUE;
        $params['TYPE']['unique'] = TRUE;
        $params['FORMAT']['type'] = 'vchar';
        $params['FORMAT']['required'] = TRUE;
        $params['FORMAT']['unique'] = TRUE;
        $params['RAW']['type'] = 'field';
        $params['RAW']['required'] = TRUE;
        
        // Create table with correct params
        try{
            $this->driver->createTable(DB_PREFIX.'Views', $params, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1007);
        }

        $data = array('ID' => 0,
                        'FLAG' => '_GROUPS',
                        'TYPE' => 'GLOBALS',
                        'ID_LIST' => '');

        try{
            $this->driver->insertData(DB_PREFIX.'Flags', $data);
        } catch(Exception $e){
            throw new LxrException($e->getMessage(),1008);
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
            $fields_list[$name]['REGEX'] = $this->decode_data($value['REGEX']);
            $fields_list[$name]['DESCRIPTION'] = $this->decode_data($value['DESCRIPTION']);
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
                $structs_list[$name]['STRUCT'] = json_decode($this->decode_data($value['STRUCT']), True);
            else
                $structs_list[$name]['STRUCT'] = '';
            if(!empty($value['DESCRIPTION']))
                $structs_list[$name]['DESCRIPTION'] = $this->decode_data($value['DESCRIPTION']);
            else
                $structs_list[$name]['DESCRIPTION'] = '';
        }
        
        return $structs_list;
    }

    // Return all templates available
    public function getViewList(){
        $table = DB_PREFIX.'Views';
        $field = array('OBJECT', 'TYPE', 'FORMAT');

        try{
            $views =  $this->driver->selectData($table, $field);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1011);
        }

        if(empty($views) || !is_array($views))
            return NULL;

        $views_list = array();
        foreach ($views as $key => $value) {
            $tmp = array('Name' => $value['TYPE'], 'Format' => $value['FORMAT']);
            $views_list[$value['OBJECT']][] = $tmp;
        }

        return $views_list;
    }

    // Return all templates available
    public function getViewTypeList(){
        $table = DB_PREFIX.'Views';
        $field = array('TYPE');

        try{
            $view_type =  $this->driver->selectData($table, $field);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1012);
        }

        if(empty($view_type) || !is_array($view_type))
            return NULL;

        $types = array();
        foreach ($view_type as $key => $value) {
            $types[] = $value['TYPE'];
        }

        return array_unique($types);
    }

    // Return all ID entries of a certain type
    public function getObjectListByType($objectType, $count=0){
        $table = USER_PREFIX.$objectType;
        $field = array ('ID');

        try{
            $objects = $this->driver->selectData($table, $field, null, $count);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1013);
        }

        if(empty($objects) || !is_array($objects))
            return NULL;

        foreach ($objects as $key => $value) {
            $id_list[] = $value['ID'];
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
        // Add the ID
        $field[] = 'ID';

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
            $id_list[] = $value['ID'];
        }

        return $id_list;
    }

    // Return all flags registered
    public function getFlagList(){
        $table = DB_PREFIX.'Flags';
        $field = array ('FLAG', 'TYPE', 'ID_LIST');

        try{
            $tmp_list = $this->driver->selectData($table, $field);
        }catch (Exception $e){
            throw new LxrException($e->getMessage(),1015);
        }

        if(empty($tmp_list) || !is_array($tmp_list))
            return NULL;

        foreach ($tmp_list as $id => $entry) {
            $flag_list[$entry['TYPE']][$entry['FLAG']] = ID2array($entry['ID_LIST']);
        }

        return $flag_list;
    }

    // Create a new structure
    public function newStruct($structName, $structDesc, $structure){
        
        // Add an entry to the Structures table
        // and set a new table corresponding to the object
        $struct = $this->encode_data(json_encode($structure));
        $structDesc = $this->encode_data($structDesc);

        $req_params = array('name' => $structName,
                            'description' => $structDesc,
                            'struct' => $struct);

        try{
            $this->driver->insertData(DB_PREFIX.'Structures', $req_params, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1016);
        }

        // Add default fields
        $params['ID']['type'] = 'object';
        $params['ID']['required'] = TRUE;
        $params['ID']['increment'] = TRUE;
        $params['ID']['primary'] = TRUE;
        $params['FLAGS']['type'] = 'collection';
        $params['ACCESS']['type'] = 'collection';
        $params['RW_ACCESS']['type'] = 'collection';

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

    // Convert existing table to Elixir format
    // public function convertStruct($structName){

    //     $structure = $this->driver->getStruct($structName);

    //     // Add default fields
    //     $params['ID']['type'] = 'object';
    //     $params['ID']['required'] = TRUE;
    //     $params['ID']['increment'] = TRUE;
    //     $params['ID']['primary'] = TRUE;
    //     $params['FLAGS']['type'] = 'collection';
    //     $params['ACCESS']['type'] = 'collection';
    //     $params['RW_ACCESS']['type'] = 'collection';

    //     // Append user defined values to query
    //     foreach ($structure as $key => $opts) {
    //         // Skip system defined fields
    //         if(!empty($opts['type']) && $opts['type'] == "system") continue;
    //         $params[ucfirst($key)] = $opts;
    //     }

    //     // Create table with correct params
    //     try{
    //         $this->driver->addColumn(USER_PREFIX.$structName, $params);
    //     }catch(Exception $e){
    //         throw new LxrException($e->getMessage(),1017);
    //     }

    //     return True;
    // }

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
        $description = $this->encode_data($description);
        $req_params = array('description' => $description);
        $where = array('NAME' => $structName);
        try{
            return $this->driver->updateData(DB_PREFIX.'Structures', $req_params, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1026);
        }
    }

    // Modify structure
    public function updateStruct($structName, $struct){
        $struct = $this->encode_data(json_encode($struct));
        $req_params = array('struct' => $struct);
        $where = array('NAME' => $structName);
        try{
            return $this->driver->updateData(DB_PREFIX.'Structures', $req_params, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1027);
        }
    }

    // Create a new Field
    public function newField($fieldName, $regex, $description){
        // Add field and regex condition to Fields table
        $regex = $this->encode_data($regex);
        $description = $this->encode_data($description);

        $req_params = array('name' => $fieldName,
                            'regex' => $regex,
                            'description' => $description);

        try{
            return $this->driver->insertData(DB_PREFIX.'Fields', $req_params, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1028);
        }
    }

    // Update an existing field
    public function updateField($fieldName, $regex, $description){
        // Update field structure from field table
        $regex = $this->encode_data($regex);
        $description = $this->encode_data($description);

        $req_params = array('regex' => $regex,
                            'description' => $description);
        $where = array('NAME' => $fieldName);
        try{
            return $this->driver->updateData(DB_PREFIX.'Fields', $req_params, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1029);
        }

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
        $where = array("ID" => $id);
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
        $where = array("ID" => $id);
        try{
            return $this->driver->deleteData($table, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1037);
        }

    }

    // Get all registered flags of a specific object type
    public function getFlagByType($objectType){
        $table = DB_PREFIX.'Flags';
        $field = array("FLAG");
        $where = array("TYPE" => $objectType);

        try{
            $result = $this->driver->selectData($table, $field, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1038);
        }

        if(empty($result[0]['FLAG']) || $result[0]['FLAG'] === "NULL")
            return NULL;

        foreach ($result as $key => $value) {
            $tmp[] = $value['FLAG'];
        }

        return $tmp;
    }
    
    // Get all flags of a specific object
    public function getFlagByID($objectType, $id){
        $table = USER_PREFIX.$objectType;
        $field = array("FLAGS");
        $where = array('ID' => $id);

        try{
            $result = $this->driver->selectData($table, $field, $where, 1);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1039);
        }

        if(empty($result[0]['FLAGS']) || $result[0]['FLAGS'] === "NULL")
            return NULL;

        return Flag2array($result[0]['FLAGS']);
    }
    
    // Get all registered flags of a specific object
    public function getIDList($objectType, $flag){
        $field = array("ID_LIST");
        $where = array('TYPE' => $objectType,
                        'FLAG' => $flag);
        try{
            $result = $this->driver->selectData(DB_PREFIX.'Flags', $field, $where, 1);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1040);
        }

        if(empty($result[0]['ID_LIST']) || $result[0]['ID_LIST'] == "NULL")
            return NULL;
        
        return ID2array($result[0]['ID_LIST']);
    }
    
    // Register a flag to a specific object type
    public function addFlag($objectType, $flag){
        // Add an entry in the flags table
        $data = array('TYPE' => $objectType,
                        'FLAG' => $flag);
        
        try{
            return $this->driver->insertData(DB_PREFIX.'Flags', $data, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1041);
        }
    }

    // Delete a specific flag of object type
    public function deleteFlag($objectType, $flag){
        // Delete an entry in the flags table
        $where = array('TYPE' => $objectType,
                        'FLAG' => $flag);
        
        try{
            return $this->driver->deleteData(DB_PREFIX.'Flags', $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1042);
        }
    }

    // Add an id to id_list of flag in table flags
    public function indexFlagToID($objectType, $flag, $id){
        
        // Add an entry in the object id list
        $id_list = $this->getIDList($objectType, $flag);

        if(empty($id_list) || !in_array($id, $id_list))
            $id_list[] = $id;

        $data['ID_LIST'] = array2ID($id_list);
        
        // Set the where conditions
        $where['TYPE'] = $objectType;
        $where['FLAG'] = $flag;
        
        // Execute the update query
        try{
            return $this->driver->updateData(DB_PREFIX.'Flags', $data, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1043);
        }
    }

    // Set a flag to a specific object
    public function addFlagToID($objectType, $flag, $id){

        $table = USER_PREFIX.$objectType;
        // Add the flag to the object flag list
        $flags = $this->getFlagByID($objectType, $id);
        
        if(empty($flags) || !in_array($flag, $flags))
            $flags[] = $flag;
        
        $data['FLAGS'] = array2Flag($flags);
        
        // Set the where condition
        $where['ID'] = $id;

        // Execute the update query
        try{
            return $this->driver->updateData($table, $data, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1044);
        }
        
    }

    // Suppress a flag from an object
    public function deleteFlagOfID($objectType, $flag, $id){
        
        $table = USER_PREFIX.$objectType;
        $raw_list = $this->getFlagByID($objectType, $id);
        
        // If flag array is empty or invalid just skip action
        if(empty($raw_list) || !is_array($raw_list))
            return NULL;

        // Delete flag from flag list
        $flag_list = array_diff( $raw_list, array($flag) );
        
        $data['FLAGS'] = array2Flag($flag_list);

        $where['ID'] = $id;
        
        try{
            return $this->driver->updateData($table, $data, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1045);
        }
        
            
    }

    // Suppress an indexable flag from collections
    public function unindexIDFromFlag($objectType, $flag, $id){
        $raw_list = $this->getIDList($objectType, $flag);

        // If flag array is empty or invalid just skip action
        if(empty($raw_list) || !is_array($raw_list))
            return NULL;

        // Delete id from id list
        $id_list = array_diff( $raw_list, array($id) );
        
        
        $data['ID_LIST'] = array2ID($id_list);

        $where['TYPE'] = $objectType;
        $where['FLAG'] = $flag;
        
        try{
            return $this->driver->updateData(DB_PREFIX.'Flags', $data, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1046);
        }
    }

    // Create entry for orphan object
    public function setOrphan($objectType, $id){
        return $this->indexFlagToID($objectType, "_ORPHANS", $id);
    }

    // Remove object from orphan list
    public function deleteOrphan($objectType, $id){
        return $this->unindexIDFromFlag($objectType, "_ORPHANS", $id);
    }

    // Define if an object is orphan
    public function isOrphan($objectType, $id){
        return $this->hasFlag($objectType, "_ORPHANS", $id);
    }

    // Define if an object has a specific flag
    public function hasFlag($objectType, $flag, $id){
        
        $table = DB_PREFIX.'Flags';
        $field = array("ID_LIST");
        $where = array("TYPE" => $objectType,
                        "FLAG" => $flag);
        $count = 1;
        try{
            $result = $this->driver->selectData($table, $field, $where, $count);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1047);
        }

        $id_list = ID2array($result[0]['ID_LIST']);

        if(!empty($id_list) && is_array($id_list))
            return in_array($id, $id_list);
        else
            return FALSE;

    }

    // Delete all flags of object type
    public function clearFlags($objectType){
        
        // Add an entry in the object table
        $where['TYPE'] = $objectType;
        
        try{
            return $this->driver->deleteData(DB_PREFIX.'Flags', $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1048);
        }
    }

    // Return the complete data of an object
    public function getObjectByID($objectType, $id){
        
        $table = USER_PREFIX.$objectType;
        $field = null;
        $where = array("ID" => $id);

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

    // Retrieve the list of id having a specific flag in an object type
    public function getUnindexIDList($objectType, $flag){
        
        $table = USER_PREFIX.$objectType;
        $field = array("ID");
        $where = array("FLAGS" => $flag);

        try{
            $tmp = $this->driver->searchData($table, $field, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1050);
        }

        if(!is_array($tmp) || empty($tmp[0]['ID']) || $tmp[0]['ID'] === "NULL")
            return null;
        
        foreach ($tmp as $key => $value) {
            $result[$key] = (int)$value['ID'];
        }

        return $result;

    }

    // Retrieve all the flags of a specific object type
    public function getFlagsByType($objectType = Null){
        
        $table = DB_PREFIX.'Flags';
        $field = array('FLAG');
        $where = null;

        if(!empty($objectType)){
            // $req .= " WHERE TYPE = :type";
            $where = array("type" => $objectType);
        }
        
        try{
            return $this->driver->selectData($table, $field, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1051);
        }

    }

    // Retrieve the template of object, view, format
    public function getTemplate($objectType, $type, $format){
        
        $table = DB_PREFIX.'Views';
        $field = 'RAW';
        $where = array('object' => $objectType,
                        'type' => $type,
                        'format' => $format);
        $count = 1;

        try{
            $tmp = $this->driver->selectData($table, $field, $where, $count);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1052);
        }

        if(!is_array($tmp) || empty($tmp[0]['RAW']) || $tmp[0]['RAW'] === "NULL")
            return null;

        return $tmp[0]['RAW'];

    }

    // Add a new view for an object/view/format
    public function addView($objectType, $type, $format, $raw){
        
        $req_params = array('object' => $objectType,
                            'type' => $type,
                            'format' => $format,
                            'raw' => $raw);
        try{
            return $this->driver->insertData(DB_PREFIX.'Views', $req_params, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1053);
        }

    }

    // Update a specific view of object where view/format
    public function updateView($objectType, $type, $format, $raw){
        $table = DB_PREFIX.'Views';
        $where = array('object' => $objectType,
                        'type' => $type,
                        'format' => $format);

        $req_params = array('raw' => $raw);

        try{
            return $this->driver->updateData($table, $req_params, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1054);
        }

    }

    // Rename a specific view
    public function renameView($objectType, $oldViewName, $newViewName, $format){
        $table = DB_PREFIX.'Views';
        $where = array('object' => $objectType,
                        'type' => $oldViewName,
                        'format' => $format);
        $req_params = array('type' => $newViewName);

        try{
            return $this->driver->updateData($table, $req_params, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1055);
        }
    }

    // Delete a view
    public function deleteView($objectType, $type, $format){
        
        $where = array('object' => $objectType,
                        'type' => $type,
                        'format' => $format);

        try{
            return $this->driver->deleteData(DB_PREFIX.'Views', $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1056);
        }

    }

    // Return user list
    public function getUserList(){
        $table = DB_PREFIX.'Users';
        $field = array("USERNAME", "DESCRIPTION");

        try{
            $tmp_list = $this->driver->selectData($table, $field);
        }catch (Exception $e){
            throw new LxrException($e->getMessage(),1057);
        }

        if(empty($tmp_list) || !is_array($tmp_list))
            return NULL;

        foreach ($tmp_list as $id => $user) {
            $users[$user['USERNAME']] = $user['DESCRIPTION'];
        }
        return $users;

    }

    // Store a new user
    public function newUser($name, $description){
        $req_params = array( 'USERNAME' => $name, 'DESCRIPTION' => $description);

        try{
            return $this->driver->insertData(DB_PREFIX.'Users', $req_params, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1058);
        }
    }

    // Edit a user
    public function updateUser($oldName, $newName, $description){
        $req_params = array( 'USERNAME' => $newName, 'DESCRIPTION' => $description);
        $where = array('USERNAME' => $oldName);

        try{
            return $this->driver->updateData(DB_PREFIX.'Users', $req_params, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1059);
        }
    }

    // Delete a user
    public function deleteUser($name){
        $where = array( 'USERNAME' => $name);

        try{
            return $this->driver->deleteData(DB_PREFIX.'Users', $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1060);
        }
    }

    // Return error list by lang
    public function getErrorList($lang){
        $table = DB_PREFIX.'Errors';
        $field = array('ID', 'CODE', 'MESSAGE');
        $where = array('LANG' => $lang);

        try{
            $tmp_list = $this->driver->selectData($table, $field);
        }catch (Exception $e){
            throw new LxrException($e->getMessage(),1061);
        }

        if(empty($tmp_list) || !is_array($tmp_list))
            return NULL;

        foreach ($tmp_list as $key => $value) {
            $result[$lang][$value['CODE']] = $value['MESSAGE'];
        }

        return $result;

    }

    // Return specific error
    public function getError($code, $lang){
        $table = DB_PREFIX.'Errors';
        $field = array('MESSAGE');
        $where = array('CODE' => $code, 'LANG' => $lang);

        try{
            $result = $this->driver->selectData($table, $field, $where);
        }catch (Exception $e){
            throw new LxrException($e->getMessage(),1062);
        }

        // If nothing has been found for a specific lang
        if(empty($result) || !is_array($result))
            // If language was english, send null result
            if($lang === 'en')
                return NULL;
            // Otherwise try to retrieve the english result
            else
                $this->getError($code, 'en');

        return $result;

    }

    // Store a new error
    public function newError($code, $message, $lang){
        
        $req_params = array('CODE' => $code,
                            'MESSAGE' => $message,
                            'LANG' => $lang);
        try{
            return $this->driver->insertData(DB_PREFIX.'Errors', $req_params, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1063);
        }

    }

    // Update an error
    public function updateError($code, $message, $lang){
        $req_params = array('MESSAGE' => $message);

        $where = array('CODE' => $code,
                        'LANG' => $lang);
        try{
            return $this->driver->updateData(DB_PREFIX.'Errors', $req_params, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1064);
        }
    }

    // Delete a specific error
    public function deleteError($code, $lang){
        $where = array('CODE' => $code,
                        'LANG' => $lang);

        try{
            return $this->driver->deleteData(DB_PREFIX.'Errors', $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1065);
        }

    }
}

?>