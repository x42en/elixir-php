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


class PUT_Cleaner extends GENERIC_Cleaner
{
	public function __construct($type){
		parent::__construct($type);
	}
	
	// Check the parameters of request against specific object
	public function verify($request, $handle){
		// For each type in order to update something
        // we must provide ressource AND a unique identification (name or id) AND some parameters
        switch ($this->type) {

            case 'Field':
                // If no field is registered
                if(!$handle->hasField()){
                    throw new LxrException('No field.', 204);
                }
                
                // We need a name as id
                if (empty($request->ressource) || !isValidName($request->ressource)){
                    throw new LxrException('Invalid name.', 11);
                }
                
                //Auto transform ressource
                $request->ressource = ucfirst(strtolower($request->ressource));
                
                // If original name does not exists
                if (!$handle->fieldExists($request->ressource)){
                    throw new LxrException('Unknown field.', 12);
                }

                if (empty($request->data) || !is_array($request->data)){
                    throw new LxrException('Empty parameters.', 13);
                }
                
                $request->data = validateParams($request->data);
                
                if (empty($request->data['Name']) || !isValidName($request->data['Name'])){
                    throw new LxrException('Invalid name.', 14);
                }
                
                $request->data['Name'] = ucfirst(strtolower($request->data['Name']));
                
                // New name cannot exists if different
                if ($request->ressource != $request->data['Name'] && $handle->fieldExists($request->data['Name'])){
                    throw new LxrException('Field exists.', 15);
                }
                
                if (empty($request->data['Regex'])){
                    throw new LxrException('Empty regex.', 16);
                }
                
                // Preformat field name if is valid (not a real regex)
                if (isValidName($request->data['Regex'])){
                    $request->data['Regex'] = ucfirst(strtolower($request->data['Regex']));
                    // Allow registering regex based on existing regex name
                    $request->data['Regex'] = $handle->getRegex($request->data['Regex']);
                }

                if (!$this->validate($request->data['Regex'])){
                    throw new LxrException('Invalid regex.', 17);
                }
                
                // Description is not mandatory
                if (empty($request->data['Description']) && !isValidDescription($request->data['Description'])){
                    $request->data['Description'] = 'N/A';
                }
                
                break;

            case 'Struct':
                
                // If no struct is registered
                if(!$handle->hasStruct()){
                    throw new LxrException('No structure.', 204);
                }
                
                // We need a name as id
                if (empty($request->ressource) || !isValidName($request->ressource)){
                    throw new LxrException('Empty or invalid name.', 21);
                }
                
                // Original name must exists
                if (!$handle->structExists($request->ressource)){
                    throw new LxrException('Unknown structure.', 22);
                }
                if (empty($request->data) || !is_array($request->data)){
                    throw new LxrException('Empty parameters.', 23);
                }
                
                $request->data = validateParams($request->data);
                
                // Auto-correct forgotten name
                if(empty($request->data['Name'])) $request->data['Name'] = $request->ressource;

                $request->data['Name'] = strtoupper($request->data['Name']);
                
                // New name cannot exists if different
                if ($request->ressource != $request->data['Name'] && $handle->structExists($request->data['Name'])){
                    throw new LxrException('Structure exists.', 24);
                }

                if (empty($request->data['Struct']) || !isValidJson(stripslashes($request->data['Struct']))){
                    throw new LxrException('Empty or invalid structure.', 25);
                }

                $request->data['Struct'] = stripslashes($request->data['Struct']);
                
                // Description is not mandatory
                if (empty($request->data['Description']) && !isValidDescription($request->data['Description'])){
                    $request->data['Description'] = 'N/A';
                }

                break;

            case 'View':
                
                // If no view is registered
                if(!$handle->hasView()){
                    throw new LxrException('No view.', 204);
                }
                
                // We need a name AND a view type AND a format to update
                if (empty($request->ressource) || !isValidName($request->ressource)){
                    throw new LxrException('Invalid type.', 31);
                }
                if (!$handle->structExists($request->ressource)){
                    throw new LxrException('Unknown type.', 32);
                }
                
                if (empty($request->view) || !isValidView($request->view) || $request->view === 'NULL'){
                    throw new LxrException('Invalid view.', 33);
                }
                if (empty($request->flags) || !isValidView($request->flags)){
                    throw new LxrException('Invalid name.', 34);
                }
                if (empty($request->data) || !is_array($request->data)){
                    throw new LxrException('Empty parameters.', 35);
                }
                
                $request->data = validateParams($request->data);
                
                // Check what we need for updating a view
                // auto-correct view type if missing
                if(empty($request->data['View_type'])) $request->data['View_type'] = $request->flags;
                $request->data['View_type'] = strtoupper($request->data['View_type']);
                
                // A new view can not have system object as name
                if (in_array($request->data['View_type'], $this->system_object)){
                    throw new LxrException('Forbidden name.', 36);
                }
                // A view MUST exists to be updated
                if (!$handle->thisViewExists($request->ressource, $request->flags, $request->data['Format'])){
                    throw new LxrException('Unknown view.', 37);
                }
                // A view type can NOT be in Json... it's a view !
                if (!ctype_alnum($request->format) || $request->format === "json"){
                    throw new LxrException('Invalid view format.', 38);
                }

                $request->data['Format'] = strtocapital($request->data['Format']);
                
                break;

            case 'Error':
                
                // If no error is registered
                if(!$handle->hasError()){
                    throw new LxrException('No error.', 204);
                }
                
                if (!isValidLang($request->lang)) {
                    throw new LxrException('Invalid lang.', 41);
                }

                // We need a code AND a lang to update an error
                if (empty($request->id)){
                    throw new LxrException('Invalid error ID.', 42);
                }
                
                if (empty($request->data) || !is_array($request->data)){
                    throw new LxrException('Empty parameters.', 43);
                }
                
                // Prepare parameters
                $request->data = validateParams($request->data);
                if (empty($request->data['Code']) || !isValidID($request->data['Code'])){
                    throw new LxrException('Invalid error code.', 44);
                }
                if (empty($request->data['Message']) || !isValidDescription($request->data['Message'])){
                    throw new LxrException('Invalid error message.', 45);
                }
                
                // Auto-correct lang setting when forget or invalid
                if (empty($request->data['Lang']) || !isValidLang($request->data['Lang'])) $request->data['Lang'] = $request->lang;
                
                break;

            case 'User':
                
                // If no user is registered
                if(!$handle->hasUser()){
                    throw new LxrException('No user.', 204);
                }
                
                // We need a name to update a user
                if (empty($request->ressource) || !isValidName($request->ressource)){
                    throw new LxrException('Invalid name.', 51);
                }
                if (empty($request->data) || !is_array($request->data)){
                    throw new LxrException('Empty parameters.', 52);
                }
                
                // Prepare parameters
                $request->data = validateParams($request->data);
                if (empty($request->data['Name']) || !isValidName($request->data['Name'])){
                    throw new LxrException('Empty or invalid name', 53);
                }
                
                break;

            case 'Flag':
                
                // There is no point in updating a flag
                throw new LxrException('Why so serious?', 61);
                break;
                
            // We are updating a specific object
            default:
                
                // We need a name AND a type to delete
                if (empty($request->ressource) || !isValidName($request->ressource)){
                    throw new LxrException('Empty or invalid name.', 71);
                }
                $request->type = $request->ressource;
                
                // If no object is register
                if(!$handle->hasObject($request->type)){
                    throw new LxrException('No object.', 204);
                }

                    
                if (empty($request->id) || !isValidID($request->id)){
                    throw new LxrException('Invalid ID.', 72);
                }
                if (empty($request->data) || !is_array($request->data)){
                    throw new LxrException('Empty parameters', 73);
                }
                
                $request->data = validateParams($request->data);
                
                break;
        }
        
        // If all went good, return true
        return TRUE;
	}
}

?>