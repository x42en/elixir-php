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

class Flag_Manager extends DB_Manager
{
    public function __construct($type, $param){
        parent::__construct($type, $param);
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

    // Retrieve all the flags of a specific object type
    public function getFlagsByType($objectType = Null){
        
        $table = DB_PREFIX.'Flags';
        $field = array('FLAG');
        $where = null;

        if(!empty($objectType)){
            // $req .= " WHERE TYPE = :type";
            $where = array("TYPE" => $objectType);
        }
        
        try{
            return $this->driver->selectData($table, $field, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1051);
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
    
    // Retrieve the list of id having a specific flag in an object type
    public function getUnindexIDList($objectType, $flag){
        
        $table = USER_PREFIX.$objectType;
        $field = array(TABLE_PREFIX."id");
        $where = array("FLAGS" => $flag);

        try{
            $tmp = $this->driver->searchData($table, $field, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1050);
        }

        if(!is_array($tmp) || empty($tmp[0][TABLE_PREFIX.'id']) || $tmp[0][TABLE_PREFIX.'id'] === "NULL")
            return null;
        
        foreach ($tmp as $key => $value) {
            $result[$key] = (int)$value[TABLE_PREFIX.'id'];
        }

        return $result;

    }

    // Get all flags of a specific object
    public function getFlagByID($objectType, $id){
        $table = USER_PREFIX.$objectType;
        $field = array("FLAGS");
        $where = array(TABLE_PREFIX.'id' => $id);

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
        $field = array("OBJECT_ID");
        $where = array('TYPE' => $objectType,
                        'FLAG' => $flag);
        try{
            $result = $this->driver->selectData(DB_PREFIX.'Flags', $field, $where, 1);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1040);
        }

        if(empty($result[0]['OBJECT_ID']) || $result[0]['OBJECT_ID'] == "NULL")
            return NULL;
        
        return ID2array($result[0]['OBJECT_ID']);
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

        $data['OBJECT_ID'] = array2ID($id_list);
        
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
        $where[TABLE_PREFIX.'id'] = $id;

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

        $where[TABLE_PREFIX.'id'] = $id;
        
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
        
        
        $data['OBJECT_ID'] = array2ID($id_list);

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
        $field = array("OBJECT_ID");
        $where = array("TYPE" => $objectType,
                        "FLAG" => $flag);
        $count = 1;
        try{
            $result = $this->driver->selectData($table, $field, $where, $count);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1047);
        }

        $id_list = ID2array($result[0]['OBJECT_ID']);

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
}

?>