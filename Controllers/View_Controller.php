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



class View_Controller extends LXR_Controller {

    private $view;
    
    // Create an instance of field object
    public function __construct($db_mode, $db_config){
        try{
            parent::__construct('View', $db_mode, $db_config);
        }catch(Exception $err){
            throw $err;
        }
    }

    // GET all fields, or a specific one
    public function getAction($request){
        try{
            // Check request params against action type
            parent::GETisClean($request);

            // If at least one object is defined
            if(!empty($request->ressource)){
                
                // If at least one view type is set
                if(!empty($request->flags)){
                    if(count($request->flags) > 1){
                        $result = $this->handle->getView($request->ressource, $request->flags[0], $request->format);
                    }
                    else{
                        $result = $this->handle->getView($request->ressource, $request->flags, $request->format);
                    }
                }
                else{
                    // Retrieve an empty editor to create a view
                    if($request->view === "NEW"){
                        $result = $this->handle->getView($request->type, $request->view, $request->format, $request->ressource);
                    }
                    // List all view available for this object
                    else{
                        $result = $this->handle->getViewByType($request->ressource, $request->count);
                    }
                    
                }

            }
            // Else list view available
            else{
                $result = $this->handle->getViewList($request->count);
            }
        }catch(Exception $err){
            throw $err;
        }

        return $result;

        
    }

    // Add a new view
    public function postAction($request){
        try{
            // Check request params against action type
            parent::POSTisClean($request);
            $result = $this->handle->newView($request->ressource, $request->data['View_type'], $request->format, $request->data['Code']);
        }catch(Exception $err){
            throw $err;
        }

        return $result;
    
    }

    // Update a view
    public function putAction($request){
        try{
            // Check request params against action type
            parent::PUTisClean($request);

            if(empty($request->data['Code']))
                $result = $this->handle->renameView($request->ressource, $request->flags, $request->data['View_type'], $request->format);
            else
                $result = $this->handle->updateView($request->ressource, $request->data['View_type'], $request->format, $request->data['Code']);

        }catch(Exception $err){
            throw $err;
        }

        return $result;
            
    }

    // Delete a view
    public function deleteAction($request){
        try{
            // Check request params against action type
            parent::DELETEisClean($request);
            $result = $this->handle->deleteView($request->ressource, $request->flags[0], $request->flags[1]);
        }catch(Exception $err){
            throw $err;
        }

        return $result;
    }
}

?>