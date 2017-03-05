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


class DELETE_Cleaner extends GENERIC_Cleaner
{
	public function __construct($type){
		parent::__construct($type);
	}
	
	// Check the parameters of request against specific object
	public function verify($request, $handle){
		// For each type in order to delete something
        // we must provide ressource AND an identification (flag or id)
        switch ($this->type) {
            case 'Field':
                
                // If no field is register
                if(!$handle->hasField()){
                    throw new LxrException('No Field.', 204);
                }
                
                // We need a name as id
                if (!isValidName($request->ressource)){
                    throw new LxrException('Invalid name.', 11);
                }
                //Auto transform ressource
                $request->ressource = ucfirst(strtolower($request->ressource));
                
                if (!$handle->fieldExists($request->ressource)){
                    throw new LxrException('Unknown field.', 12);
                }

                break;

            case 'Struct':
                
                // If no struct is register
                if(!$handle->hasStruct()){
                    throw new LxrException('No structure.', 204);
                }
                
                // We need a name as id
                if (!isValidName($request->ressource)){
                    throw new LxrException('Invalid structure name.', 21);
                }
                
                $request->ressource = strtoupper($request->ressource);
                
                if (!$handle->structExists($request->ressource)){
                    throw new LxrException('Unknown structure.', 22);
                }
                
                break;

            case 'View':
                
                // If no view is register
                if(!$handle->hasView()){
                    throw new LxrException('No view.', 204);
                }
                
                // We need a name AND a view type AND a format to delete
                if (empty($request->ressource) || !isValidName($request->ressource)){
                    throw new LxrException('Invalid view.', 31);
                }
                
                if (empty($request->flags[0]) || !isValidView($request->flags[0])){
                    throw new LxrException('Invalid type.', 32);
                }
                
                if (!ctype_alnum($request->flags[1]) || strtolower($request->flags[1]) === "json"){
                    throw new LxrException('Invalid view format.', 33);
                }
                
                // Check what we need for deleting a view
                // You can not delete a non-existing view
                if (!$handle->thisViewExists($request->ressource, $request->flags[0], $request->flags[1])){
                    throw new LxrException('Unknown view.', 34);
                }
                
                break;

            case 'Error':
                
                // If no error is register
                if(!$handle->hasError()){
                    throw new LxrException('No error.', 204);
                }
                
                // Verify lang
                if (!isValidLang($request->lang)) {
                    throw new LxrException('Invalid lang.', 41);
                }

                // We need a code AND a lang to delete an error
                if (empty($request->id) || !isValidID($request->id)){
                    throw new LxrException('Invalid code.', 42);
                }
                
                if (!$handle->errorExists($request->lang, $request->id)){
                    throw new LxrException('Unknown error code.', 43);
                }
                
                break;

            case 'User':
                
                // If no user is register
                if(!$handle->hasUser()){
                    throw new LxrException('No user.', 204);
                }
                
                // We need a name to delete a user
                if (empty($request->ressource) || !isValidName($request->ressource)){
                    throw new LxrException('Invalid name.', 51);
                }
                
                break;

            case 'Flag':
                
                // If no flag is register
                if(!$handle->hasFlag()){
                    throw new LxrException('No flag.', 204);
                }
                
                // We need a ressource, an ID and a flag name to delete a flag
                if (empty($request->ressource) || !isValidName($request->ressource)){
                    throw new LxrException('Invalid type.', 61);
                }
                if (empty($request->id) || !isValidID($request->id)){
                    throw new LxrException('Invalid ID.', 62);
                }
                if (empty($request->flags) || !isValidFlag($request->flags)){
                    throw new LxrException('Invalid flag.', 63);
                }
                
                break;
                
            // We are deleting a specific object
            default:
                
                // We need a name AND an id to delete
                if (empty($request->ressource) || !isValidName($request->ressource)){
                    throw new LxrException('No type set', 71);
                }
                
                // Replace correct type
                $request->type = $request->ressource;
                
                // If no object is register
                if(!$handle->hasObject($request->type)){
                    throw new LxrException('No object.', 204);
                }
                
                if (empty($request->id) || !isValidID($request->id)){
                    throw new LxrException('Invalid ID.', 72);
                }
                
                break;
        }
        
        // If all went good, return true
        return TRUE;
	}

}
?>