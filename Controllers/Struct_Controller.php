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

class Struct_Controller extends LXR_Controller
{
    
    // Create an instance of field object
    public function __construct($db_mode, $db_config) {
        try{
            parent::__construct('Struct', $db_mode, $db_config);
        }catch(Exception $err){
            throw $err;
        }
    }
    
    // Retrieve infos
    public function getAction($request) {
        
        try{
            // Check request params against action type
            parent::GETisClean($request);

            if (!empty($request->ressource))
                $result = $this->handle->getStructureByName($request->ressource);
            else
                $result = $this->handle->getStructureList($request->count);
            
        }catch(Exception $err){
            throw $err;
        }

        return $result;
    }
    
    // Add a new structure
    public function postAction($request) {
        try{
            // Check request params against action type
            parent::POSTisClean($request);

            $result = $this->handle->newStructure($request->data['Name'], $request->data['Struct'],$request->data['Description']);
        }catch(Exception $err){
            throw $err;
        }

        return $result;
    }
    
    // Update a structure
    public function putAction($request) {
        try{
            // Check request params against action type
            parent::PUTisClean($request);

            $result = $this->handle->updateStructure($request->ressource, $request->data['Name'], $request->data['Struct'],$request->data['Description']);
        }catch(Exception $err){
            throw $err;
        }
        
        return $result;
    }
    
    // Delete a structure
    public function deleteAction($request) {
        try{
            // Check request params against action type
            parent::DELETEisClean($request);

            $result = $this->handle->deleteStructure($request->ressource);
        }catch(Exception $err){
            throw $err;
        }
        
        return $result;
    }
}
?>