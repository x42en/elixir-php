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


// Load DBManager
require_once (__ROOT__ . 'Class/DBManager.php');

// Define a class of object structure manipulation
Class LXR_Model
{
    
    // Instantiation of local variable
    protected $lxr;
    protected $view;
    protected $system_fields;
    
    protected $result;
    
    // Construction method take only 2 parameters
    // -first the db connection type
    // -second parameters useful for this db type
    function __construct($storage = "file", $param = NULL) {
        
        $this->result = null;
        $this->error = null;
        $this->message = null;
        $this->system_fields = array("ID", "ACCESS", "RW_ACCESS", "FLAGS");
        
        // Check param for each DB type
        $storage = strtolower($storage);
        switch ($storage) {
                
            // If storage is via mysql
            case 'mysql':
                
                // If no param are specified return error
                if (empty($param)) {
                    throw new LxrException('Empty parameters.', 911);
                }
                
                // If one of the config value is missing return error
                if (empty($param['host']) || empty($param['port']) || empty($param['user']) || empty($param['password']) || empty($param['bdd'])) {
                    throw new LxrException('Missing parameter', 912);
                }
                
                break;
                
            // Default storage method is by file
            default:
                $storage = "file";
                
                // If no param are specified set default values
                if ($param === NULL) {
                    $param['dir'] = ".";
                    $param['file'] = "elixir.lxr";
                }
                
                break;
        }
        
        try {
            $this->lxr = new DBManager($storage, $param);
        }
        catch(Exception $err) {
            throw $err;
        }
    }

    // public function getResult(){
    // 	$result = new stdClass();

    // 	if(!empty($this->error)){
    // 		$result->Error = $this->error;
    // 		$result->Msg = $this->message;
    // 	}
    // 	else{
    // 		$result->Data = $this->result;
    // 	}

    // 	return $result;
    // }
    
    public function getTemplate($request) {
        return stripslashes(base64_decode($this->lxr->getTemplate($request->type, $request->view, $request->format)));
    }
}
?>