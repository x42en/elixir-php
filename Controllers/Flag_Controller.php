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


class Flag_Controller extends LXR_Controller
{
    
    // Create an instance of field object
    public function __construct($db_mode, $db_config) {
       try{
            parent::__construct('Flag', $db_mode, $db_config);
        }catch(Exception $err){
            throw $err;
        }
    }
    
    // GET all fields, or a specific one
    public function getAction($request) {
        try{
            // Check request params against action type
            parent::GETisClean($request);

            // If we look for a specific object
            if (!empty($request->id)) {
                
                // If we look for a specific flag
                if (!empty($request->flags))
                    $result = $this->handle->checkIdHasFlag($request->ressource, $request->id, $request->flags);
                else
                    $result = $this->handle->getFlagForID($request->ressource, $request->id);
            }else {
                
                // If value is set verify it against field
                if (!empty($request->flags))
                    $result = $this->handle->getFlagByName($request->ressource, $request->flags);
                // If only field is set retrieve it by name
                else if(!empty($request->ressource))
                    $result = $this->handle->getFlagByType($request->ressource, $request->count);
                // If nothing is set list fields
                else
                    $result = $this->handle->getFlagList($request->count);
                
            }
        }catch(Exception $err){
            throw $err;
        }

        return $result;
    }
    
    // Add a new flag
    public function postAction($request) {
        try{
            // Check request params against action type
            parent::POSTisClean($request);
            $result = $this->handle->newFlag($request->ressource, $request->id, $request->data['Flag_list']);
        }catch(Exception $err){
            throw $err;
        }

        return $result;
        
    }
    
    // Update a flag is pointless
    public function putAction($request) {
        throw new LxrException('Update flag requested, but not supported !!', 920);
    }
    
    // Delete a flag
    public function deleteAction($request) {
        try{
            // Check request params against action type
            parent::DELETEisClean($request);
            $result = $this->handle->deleteFlag($request->ressource, $request->id, $request->flags);
        }catch(Exception $err){
            throw $err;
        }

        return $result;
    }
}
?>