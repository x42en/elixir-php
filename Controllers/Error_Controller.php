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


class Error_Controller extends LXR_Controller
{
    
    // Create an instance of error object
    public function __construct($db_mode, $db_config) {
        try{
            parent::__construct('Error', $db_mode, $db_config);
        }catch(Exception $err){
            throw $err;
        }
    }
    
    // GET all errors, or a specific one
    public function getAction($request) {
        try{
            // Check request params against action type
            parent::GETisClean($request);

            // If error code is set return associated error
            if (!empty($request->id) && !empty($request->lang)){
                $result = $this->handle->getError($request->id, $request->lang);
            }
            
            // If nothing is set list errors
            else{
                $result = $this->handle->getErrorList($request->count, $request->lang);
            }
        }catch(Exception $err){
            throw $err;
        }

        return $result;

    }
    
    // Add a new error
    public function postAction($request) {
        try{
            // Check request params against action type
            parent::POSTisClean($request);
            $result = $this->handle->newError($request->data['Code'], $request->data['Message'], $request->lang);
        }catch(Exception $err){
            throw $err;
        }

        return $result;

    }
    
    // Update an error
    public function putAction($request) {
        try{
            // Check request params against action type
            parent::PUTisClean($request);
            $result =  $this->handle->updateError($request->id, $request->data['Code'], $request->data['Message'], $request->lang);
        }catch(Exception $err){
            throw $err;
        }

        return $result;

    }
    
    // Delete an error
    public function deleteAction($request) {
        try{
            // Check request params against action type
            parent::DELETEisClean($request);
            $result = $this->handle->deleteError($request->id, $request->lang);
        }catch(Exception $err){
            throw $err;
        }

        return $result;
        
    }
}
?>