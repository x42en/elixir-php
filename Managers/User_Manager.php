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

Class User_Manager extends DB_Manager
{
	public function __construct($type, $param){
        parent::__construct($type, $param);
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
}

?>