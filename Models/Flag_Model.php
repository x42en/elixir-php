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


class Flag_Model extends Object_Model
{
    
    // Instantiation of local variable
    protected $flag_list;
    
    function __construct($db_mode, $db_config) {
        try{
            parent::__construct($db_mode, $db_config);
            // Retrieve flag list for futur operations
            $this->flag_list = $this->lxr->getFlagList();
        }
        catch(Exception $err) {
            throw $err;
        }

    }

    // Define if flag are set
    public function hasFlag(){
    	return !empty($this->flag_list);
    }
    
    // Check if flag exist
    public function flagExists($name) {
        return !empty($this->flag_list[$name]);
    }
    
    // Return flag list
    public function getFlagList($count = 0) {
        if ($count > 0) $this->result = array_slice($this->flag_list, 0, $count);
        else $this->result = $this->flag_list;

        return $this->result;
    }
    
    public function checkIdHasFlag($objectType, $id, $flag) {
        
        // If no flag is registered for this type
        if (empty($this->flag_list[$objectType])){
            throw new LxrException('Empty flag list.', 11);
        }
        
        $this->getFlagForID($objectType, $id);
        
        $this->result = in_array($flag, $this->result[$objectType][$id]);

        return $this->result;
    }
    
    public function getFlagByType($objectType, $count = 0) {
        
        if (empty($this->collection_list[$objectType])){
            throw new LxrException('Empty collection list.', 12);
        }
        
        if ($count > 0) $this->result[$objectType] = array_slice($this->collection_list[$objectType], 0, $count);
        else $this->result[$objectType] = $this->collection_list[$objectType];
        
        return $this->result;
    }
    
    public function getFlagByName($objectType, $flagName) {
        
        if (empty($this->flag_list[$objectType])){
            throw new LxrException('Empty flag list.', 13);
        }
        
        if (empty($this->flag_list[$objectType][$flagName])){
            throw new LxrException('Unknown flag.', 14);
        }
        
        $this->result[$objectType][$flagName] = $this->flag_list[$objectType][$flagName];
        
        return $this->result;
    }
    
    public function getFlagForID($objectType, $id) {
        
        // If no flag is registered for this type
        if (empty($this->flag_list[$objectType])){
            throw new LxrException('Empty flag list', 15);
        }
        
        try{
            $this->result[$objectType][$id] = $this->lxr->getFlagByID($objectType, $id);
        }catch(Exception $err){
            throw $err;
        }
        
        
        return $this->result;
    }
    
    // Allow us to had a new flag
    // All children element will receive the flag
    public function newFlag($objectType = NULL, $id = NULL, $flag_list = NULL) {
        
        foreach ($flag_list as $key => $flag) {
        	$flag = ucfirst(strtolower($flag));
            try{
                parent::addFlag($objectType, $flag, $id);
            }catch(Exception $err){
                throw $err;
            }
            
        }
        
        return $this->result;
    }
    
    // Suppress a flag from an object AND all its children
    public function deleteFlag($objectType = NULL, $id = NULL, $flagName = NULL) {
        
        try{
            parent::deleteFlag($objectType, $flagName, $id);
        }catch(Exception $err){
            throw $err;
        }
        
        return $this->result;
    }
}
?>