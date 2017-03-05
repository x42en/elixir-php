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


class GET_Cleaner extends GENERIC_Cleaner
{
	public function __construct($type){
		parent::__construct($type);
	}
	
	// Check the parameters of request against specific object
	public function verify($request, $handle){
		switch ($this->type) {
                
            // GET Field need a single valid name or nothing
            case 'Field':

                // If no field is registered
                if(!$handle->hasField()){
                    throw new LxrException('No field.', 204);
                }
                
                // If flag is set
                if (!empty($request->flags) && count($request->flags) > 1) {
                    throw new LxrException('Field can NOT have multiple flags.', 11);
                }
                
                // If field name is set
                if (!empty($request->ressource)) {
                    if (!isValidName($request->ressource)){
                        throw new LxrException('Invalid field name.', 12);
                    }
                    //Auto transform ressource
                    $request->ressource = ucfirst(strtolower($request->ressource));
                    
                    if(in_array($request->ressource, $this->system_object)){
                        throw new LxrException('Forbidden field name.', 13);
                    }
                    if (!$handle->fieldExists($request->ressource)){
                        throw new LxrException('Unkwown field.', 14);
                    }
                }

                break;
                
            // GET Struct need a single valid name or nothing
            case 'Struct':
                
                // If no struct is registered
                if(!$handle->hasStruct()){
                    throw new LxrException('No structure.', 204);
                }
                
                // If flag is set
                if (!empty($request->flags)) {
                    throw new LxrException('Structure can NOT have flag.', 21);
                }

                $request->ressource = strtoupper($request->ressource);
                
                // If struct name is set
                if (!empty($request->ressource)) {
                    if (!isValidName($request->ressource)){
                        throw new LxrException('Invalid structure name.', 22);
                    }
                    if(in_array($request->ressource, $this->system_object)){
                        throw new LxrException('Forbidden structure name.', 23);
                        
                    }
                    if (!$handle->structExists($request->ressource)){
                        throw new LxrException('Unknown structure name.', 24);
                    }
                }
                
                break;
                
            // GET View need a single valid flag and/or a valid name or nothing
            case 'View':

                // If no view is registered
                if(!$handle->hasView()){
                    throw new LxrException('No view.', 204);
                }
                
                // If flag is set
                if (!empty($request->flags)) {
                    // If we have several flags or invalid one
                    $count = count($request->flags);
                    if ($count === 1 && !isValidFlag($request->flags[0])){
                        throw new LxrException('Invalid view name.', 31);
                    }
                    if ($count > 2){
                        throw new LxrException('Invalid flag number.', 32);
                    }
                    if (!isValidFlag($request->flags[1])){
                        throw new LxrException('Invalid view format.', 33);
                    }

                    // Set view if needed
                    $request->view = $request->flags[1];
                }
                
                // If View name is set
                if (!empty($request->ressource)) {
                    if (!isValidName($request->ressource)){
                        throw new LxrException('Invalid view type.', 34);
                    }
                    if (!$handle->viewExists($request->ressource)){
                        throw new LxrException('Unknown view type.', 35);
                    }
                }
                
                break;

            case 'Error':
                
                // If no error is registered
                if(!$handle->hasError()){
                    throw new LxrException('No error.', 204);
                }
                
                // Verify lang
                if (!isValidLang($request->lang)) {
                    throw new LxrException('Invalid lang.', 41);
                }
                
                // If error code is set
                if (!empty($request->id)) {
                    if (!isValidID($request->id)) {
                        throw new LxrException('Invalid ID.', 42);
                    }
                    if (!$handle->errorExists($request->lang, $request->id)){
                        throw new LxrException('Unknown error ID.', 43);
                    }
                }
                
                break;

            case 'User':
                
                // If no user is registered
                if(!$handle->hasUser()){
                    throw new LxrException('No user.', 204);
                }
                
                // If user name is set
                if (!empty($request->ressource)) {
                    if (!isValidName($request->ressource)) {
                        throw new LxrException('Invalid user name.', 51);
                    }
                }
                
                break;

            case 'Flag':
                
                // If no flag is registered
                if(!$handle->hasFlag()){
                    throw new LxrException('No flag.', 204);
                }
                
                // If flag name is set
                if (!empty($request->ressource)) {
                    if (!isValidFlag($request->ressource)) {
                        throw new LxrException('Invalid flag.', 61);
                    }
                    else if (!$handle->flagExists($request->ressource)){
                       throw new LxrException('Unknown flag.', 62);
                    }
                }
                
                break;
                
            // GET Object need at least ONE object type and several/one valid flag
            default:
                // If Object name is set
                if (!empty($request->ressource)){

                    if (!isValidName($request->ressource)){
                        throw new LxrException('Invalid name.', 71);
                    }
                    if (!$handle->structExists($request->ressource)) {
                        throw new LxrException('Unknown object type.', 72);
                    } 
                    
                    $request->type = $request->ressource;

                    // If no object is register
                    if(!$handle->hasObject($request->type)){
                        throw new LxrException('No object.', 204);
                    }

                    // If ID is set
                    if(!empty($request->id)){
                        if (!isValidID($request->id)){
                            throw new LxrException('Invalid ID', 73);
                        }
                        if(!$handle->objectExists($request->type, $request->id)){
                            throw new LxrException('Unknown object.', 74);
                        }
                    }

                    // If flag is set (but not ID you can not set both!!)
                    else if (!empty($request->flags)) {
                        
                       // Check each of them
                        foreach ($request->flags as $key => $flag) {
                            // If the flag is not valid drop it
                            if (!isValidFlag($flag)) array_splice($request->flags, $key, 1);
                        }
                    }

                    
                }
            
        }
        
        // If all went good, return true
        return TRUE;
	}
}

?>