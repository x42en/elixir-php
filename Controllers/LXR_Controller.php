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


class LXR_Controller
{
    
    protected $handle;
    protected $type;
    protected $error_base;
    protected $logger;
    
    private $system_field;
    private $system_object;
    private $system_view;
    private $format;
    
    public function __construct($type, $db_mode, $db_config) {
        $this->type = $type;

        // $this->system_field = array("ID", "FLAGS", "ACCESS", "RW_ACCESS");
        // $this->system_object = array("FIELD", "STRUCT", "VIEW", "FLAG", "USER", "ERROR", "CLASS", "CONFIG", "CONTROLLERS","DRIVERS","MODELS","UTILS","VIEWS");

        $model = $type . '_Model';
        try{
            $this->handle = new $model($db_mode, $db_config);
        }catch(Exception $err){
            throw $err;
        }
        
    }
    
    // Check the parameters of GET request against specific object
    protected function GETisClean($request) {
        try{
            $cleaner = new GET_Cleaner($this->type);
            $cleaner->verify($request, $this->handle);
        }catch(Exception $err){
            throw $err;
        }

        return TRUE;
    }
    
    // Check the parameters of POST request against specific object
    protected function POSTisClean($request) {
        try{
            $cleaner = new POST_Cleaner($this->type);
            $cleaner->verify($request, $this->handle);
        }catch(Exception $err){
            throw $err;
        }

        return TRUE;
    }
    
    // Check the parameters of PUT request against specific object
    protected function PUTisClean($request) {
        try{
            $cleaner = new PUT_Cleaner($this->type);
            $cleaner->verify($request, $this->handle);
        }catch(Exception $err){
            throw $err;
        }

        return TRUE;
    }
    
    // Check the parameters of DELETE request against specific object
    protected function DELETEisClean($request) {
        try{
            $cleaner = new DELETE_Cleaner($this->type);
            $cleaner->verify($request, $this->handle);
        }catch(Exception $err){
            throw $err;
        }

        return TRUE;
    }
}

?>