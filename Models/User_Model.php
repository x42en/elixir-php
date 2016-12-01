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


class User_Model extends LXR_Model
{
    
    // Instantiation of local variable
    protected $user_list;
    
    function __construct($db_mode, $db_config) {
        try{
            parent::__construct($db_mode, $db_config, 'User');
            // Retrieve field list for futur operations
            $this->user_list = $this->lxr->getUserList();
        }
        catch(Exception $err) {
            throw $err;
        }
    }

    public function hasUser(){
    	return !empty($this->user_list);
    }
    
    public function getUserList($count = 0) {
        if ($count > 0) $this->result = array_slice($this->user_list, 0, $count);
        else $this->result = $this->user_list;

        return $this->result;
    }
    
    public function getUser($name) {
        if (empty($this->user_list) || !isset($this->user_list[$name])){
            throw new LxrException('Unknown user.', 11);
        }
        
        $this->result[$name] = $this->user_list[$name];

        return $this->result;
    }
    
    public function newUser($name = NULL, $description = NULL) {
        return $this->updateUser($name, $name, $description);
    }
    
    public function updateUser($oldName = NULL, $newName = NULL, $description = NULL) {
        
        // User name can not be modified
        if ($oldName !== $newName) {
            throw new LxrException('User name can NOT be modified.', 12);
        }
        
        // If error exists update it, either create it
        if (empty($this->user_list) || !array_key_exists($newName, $this->user_list)) {
            
            try {
                $this->result['ID'] = $this->lxr->newUser($newName, $description);
            }
            catch(Exception $err) {
                throw $err;
            }
        } 
        else {
            
            try {
                $this->lxr->updateUser($oldName, $description);
            }
            catch(Exception $err) {
                throw $err;
            }

            $this->result['ID'] = $oldName;
        }
        
        return $this->result;
    }
    
    public function deleteUser($name = NULL) {
        
        // If field does not exists skip action
        if (empty($this->user_list) || !array_key_exists($name, $this->user_list)) return TRUE;
        
        try {
            $this->result = $this->lxr->deleteUser($name);
        }
        catch(Exception $err) {
            throw $err;
        }
        
        return $this->result;
    }
}
?>