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


class Object_Controller extends LXR_Controller
{
    
    // Create an instance of field object
    public function __construct($db_mode, $db_config) {
        try{
            parent::__construct('Object', $db_mode, $db_config);
        }catch(Exception $err){
            throw $err;
        }
    }
    
    // GET all fields, or a specific one
    public function getAction($request) {
        try{
            // Check request params against action type
            parent::GETisClean($request);

            // If at least one object is defined
            if (!empty($request->ressource)) {
                
                // If at least one flag is set
                if (!empty($request->flags)) {
                    $flags = implode(',', $request->flags);
                    $result = $this->handle->getCollection($request->ressource, $request->flags, $request->recursive, $request->count);
                }
                
                // If id is specified print object
                else if (!empty($request->id)) {
                    $result =  $this->handle->getObjectByID($request->ressource, (int)$request->id, $request->recursive);
                } 
                else {
                    if(!empty($request->selector)){
                        $result = $this->handle->selectObjectListByType($request->ressource, $request->selector, $request->recursive, $request->count);
                    }
                    else{
                        $result = $this->handle->getObjectListByType($request->ressource, $request->recursive, $request->count);
                    }
                }
            }
            // Else list object
            else {
                $result = $this->handle->getObjectList($request->count);
            }

        }catch(Exception $err){
            throw $err;
        }

        return $result;
        
    }
    
    // Add a new field
    public function postAction($request) {
        try{
            // Check request params against action type
            parent::POSTisClean($request);
            $result = $this->handle->storeObject($request->ressource, $request->data);
        }catch(Exception $err){
            throw $err;
        }

        return $result;
        
    }
    
    // Update a field
    public function putAction($request) {
        try{
            // Check request params against action type
            parent::PUTisClean($request);
            $result = $this->handle->updateObject($request->ressource, $request->id, $request->data);
        }catch(Exception $err){
            throw $err;
        }

        return $result;
        
    }
    
    // Delete a field
    public function deleteAction($request) {
        try{
            // Check request params against action type
            parent::DELETEisClean($request);
            $result = $this->handle->deleteObject($request->ressource, $request->id);
        }catch(Exception $err){
            throw $err;
        }

        return $result;
        
    }
}
?>