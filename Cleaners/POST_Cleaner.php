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


class POST_Cleaner extends GENERIC_Cleaner
{
	public function __construct($type){
		parent::__construct($type);
	}
	
	// Check the parameters of request against specific object
	public function verify($request, $handle){
		// For each type in order to add something
        // we must provide ressource AND some parameters
        switch ($this->type) {
            case 'Field':
                
                // We need parameters only
                if (empty($request->data) || !is_array($request->data)){
                    throw new LxrException("Empty parameters.", 11);
                }
                
                $request->data = validateParams($request->data);
                
                if (empty($request->data['Name']) || !isValidName($request->data['Name'])){
                    throw new LxrException('Invalid name.', 12);
                }
                $request->data['Name'] = ucfirst(strtolower($request->data['Name']));
                
                if ($handle->fieldExists($request->data['Name'])){
                    throw new LxrException('Field exists.', 13);
                }
                
                if (empty($request->data['Regex'])){
                    throw new LxrException('Empty regex.', 14);
                }
                
                // Preformat field name if is valid (not a real regex)
                if (isValidName($request->data['Regex'])){
                    $request->data['Regex'] = ucfirst(strtolower($request->data['Regex']));
                    // Allow registering regex based on existing regex name
                    $request->data['Regex'] = $handle->getRegex($request->data['Regex']);
                }

                if (!parent::validate($request->data['Regex'])){
                    throw new LxrException('Invalid regex.', 15);
                }
                
                // Description is not mandatory
                if (empty($request->data['Description']) && !isValidDescription($request->data['Description'])){
                    $request->data['Description'] = 'N/A';
                }
                
                break;

            case 'Struct':
                
                // We need parameters only
                if (empty($request->data) || !is_array($request->data)){
                    throw new LxrException('Empty parameters.', 21);
                }

                $request->data = validateParams($request->data);
                
                if (empty($request->data['Name']) || !isValidName($request->data['Name'])){
                    throw new LxrException('Invalid name.', 22);
                }

                $request->data['Name'] = strtoupper($request->data['Name']);
                
                if ($handle->structExists($request->data['Name'])){
                    throw new LxrException('Structure exists.', 23);
                }
                
                if (empty($request->data['Struct']) || !isValidJson(stripslashes($request->data['Struct']))){
                    throw new LxrException('Invalid structure.', 24);
                }

                $request->data['Struct'] = stripslashes($request->data['Struct']);

                // Description is not mandatory
                if (empty($request->data['Description']) && !isValidDescription($request->data['Description'])){
                    $request->data['Description'] = 'N/A';
                }
                
                break;

            case 'View':
                
                // We need an object type, a view name and a format
                if (empty($request->ressource) || $request->ressource === 'NULL'){
                    throw new LxrException('Empty type.', 31);
                }

                if(!isValidName($request->ressource)){
                    throw new LxrException('Invalid type.', 32);
                }
                
                // A view can not be add to inexistant structure
                if (!$handle->structExists($request->ressource)){
                    throw new LxrException('Unknown type.', 33);
                }

                if (empty($request->data) || !is_array($request->data)){
                    throw new LxrException('Empty parameters.', 34);
                }
                
                // Prepare parameters
                $request->data = validateParams($request->data);
                
                if(empty($request->data['View_type'])){
                    throw new LxrException('Empty name.', 35);
                }

                $request->data['View_type'] = strtoupper($request->data['View_type']);
                
                if (!isValidView($request->data['View_type'])){
                    throw new LxrException('Invalid name.', 36);
                }

                // A new view can not have system object as name
                if (in_array($request->data['View_type'], $this->system_object)){
                    throw new LxrException('Forbidden name.', 37);
                }
                
                // Check what we need for a new view
                // A new view cannot exists
                if ($handle->thisViewExists($request->ressource, $request->flags, $request->format)){
                    throw new LxrException('Existing name.', 38);
                }
                
                // A view type can NOT be json... it's a view !!
                if (!isValidName($request->format) || $request->format === "json"){
                    throw new LxrException('Invalid format.', 39);
                }
                
                break;

            case 'Error':
                if (empty($request->data) || !is_array($request->data)){
                    throw new LxrException('Empty parameters.', 41);
                }
                if (!isValidLang($request->lang)) {
                    throw new LxrException('Invalid lang.', 42);
                }
                
                // Prepare parameters
                $request->data = validateParams($request->data);
                if (empty($request->data['Code']) || !isValidID($request->data['Code'])){
                    throw new LxrException('Invalid code.', 43);
                }
                if (empty($request->data['Message']) || !isValidDescription($request->data['Message'])){
                    throw new LxrException('Invalid message.', 44);
                }
                
                // Auto-correct lang setting when forget or invalid
                if (empty($request->data['Lang']) || !isValidLang($request->data['Lang'])) $request->data['Lang'] = $request->lang;
                
                break;

            case 'User':
                if (empty($request->data) || !is_array($request->data)){
                    throw new LxrException('Empty parameters.', 51);
                }
                
                // Prepare parameters
                $request->data = validateParams($request->data);
                if (empty($request->data['Name']) || !isValidName($request->data['Name'])){
                    throw new LxrException('Invalid name.', 52);
                }
                
                break;

            case 'Flag':
                
                // We need a ressource, an ID and a flag name to add a flag
                if (empty($request->ressource) || !isValidName($request->ressource)){
                    throw new LxrException('Invalid type.', 61);
                }
                
                if (empty($request->id) || !isValidID($request->id)){
                    throw new LxrException('Invalid ID.', 62);
                }
                
                if (empty($request->data) || !is_array($request->data)){
                    throw new LxrException('Empty parameters.', 63);
                }
                
                // Prepare parameters
                $request->data = validateParams($request->data);
                if (empty($request->data['Flag_list']) || !is_array($request->data['Flag_list'])){
                    throw new LxrException('Empty list.', 64);
                }
                
                foreach ($request->data['Flag_list'] as $key => $flag) {
                    if (!isValidFlag($flag)){
                        throw new LxrException('Invalid flag.', 65);
                    }
                    $request->data['Flag_list'][$key] = strtocapital($flag);
                }
                
                break;
                
            // We are adding an object
            default:
                
                // We need an object type, and some flags if we want to preload them
                if (empty($request->ressource) || !isValidName($request->ressource)){
                    throw new LxrException('Empty type.', 71);
                }
                $request->type = $request->ressource;
                
                if (empty($request->data) || !is_array($request->data)){
                    throw new LxrException('Invalid parameters.', 72);
                }
                $request->data = validateParams($request->data);
                
                if (!empty($request->flags)) {
                    if (count($request->flags) > 1) {
                        foreach ($request->flags as $i => $flag) {
                            if (!isValidFlag($flag)){
                                throw new LxrException('Invalid flag.', 73);
                            }
                        }
                    } 
                    else {
                        if (!isValidFlag($request->flags[0])){
                            throw new LxrException('Invalid flag.', 74);
                        }
                    }
                }
                
                break;
        }
        
        // If all went good, return true
        return TRUE;
	}
}

?>