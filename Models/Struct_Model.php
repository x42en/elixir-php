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


class Struct_Model extends Field_Model
{
    
    // Instantiation of local variable
    protected $structure_list;
    
    function __construct($db_mode, $db_config, $type=NULL) {
        $this->type = (empty($type)) ? 'Struct' : $type;
        try {
            parent::__construct($db_mode, $db_config, $this->type);
            // Retrieve object structure list for futur operations
            $this->structure_list = $this->lxr->getStructureList();
        }catch(Exception $err) {
            throw $err;
        }
        
        // Add system structure to array
        $this->structure_list['OBJECT'] = array("SYSTEM" => true);
        $this->structure_list['USER'] = array("STRUCT" => array("Access" => array("type" => "system"), "RW_Access" => array("type" => "system"), "Username" => array("type" => "system_field", "required" => true, "default" => null)), "DESCRIPTION" => "System structure for user", "SYSTEM" => true);
        $this->structure_list['FIELD'] = array("STRUCT" => array("Access" => array("type" => "system"), "RW_Access" => array("type" => "system"), "Name" => array("type" => "system_field", "required" => true, "default" => null), "Regex" => array("type" => "system_field", "required" => true, "default" => null), "Description" => array("type" => "system_field", "required" => false, "default" => null)), "DESCRIPTION" => "System structure for field", "SYSTEM" => true);
        $this->structure_list['ERROR'] = array("STRUCT" => array("Access" => array("type" => "system"), "RW_Access" => array("type" => "system"), "Id" => array("type" => "system"), "Code" => array("type" => "system_field", "required" => true, "default" => null), "Message" => array("type" => "system_field", "required" => true, "default" => null)), "DESCRIPTION" => "System structure for error", "SYSTEM" => true);
        $this->structure_list['FLAG'] = array("STRUCT" => array("Access" => array("type" => "system"), "RW_Access" => array("type" => "system"), "Id" => array("type" => "system"), "Flag" => array("type" => "system_field", "required" => true, "default" => null), "Type" => array("type" => "system_field", "required" => true, "default" => null), "Id_list" => array("type" => "system_field", "required" => true, "default" => null)), "DESCRIPTION" => "System structure for flag", "SYSTEM" => true);
        $this->structure_list['STRUCT'] = array("STRUCT" => array("Access" => array("type" => "system"), "RW_Access" => array("type" => "system"), "Id" => array("type" => "system"), "Name" => array("type" => "system_field", "required" => true, "default" => null), "Description" => array("type" => "system_field", "required" => false, "default" => null), "Privacy" => array("type" => "system_field", "required" => false, "default" => null), "Struct" => array("type" => "system_collection", "required" => true, "default" => null)), "DESCRIPTION" => "System structure for struct", "SYSTEM" => true);
        $this->structure_list['VIEW'] = array("STRUCT" => array("Access" => array("type" => "system"), "RW_Access" => array("type" => "system"), "Object" => array("type" => "system_field", "required" => true, "default" => null), "Type" => array("type" => "system_field", "required" => true, "default" => null), "Format" => array("type" => "system_field", "required" => true, "default" => null), "Raw" => array("type" => "system_field", "required" => true, "default" => null), "Description" => array("type" => "system_field", "required" => false, "default" => null), "Mode" => array("type" => "system_field", "required" => false, "default" => null)), "DESCRIPTION" => "System structure for view", "SYSTEM" => true);
    }
    
    // Define if structure are set
    public function hasStruct(){
        return !empty($this->structure_list);
    }
    
    // Check if structure exist
    public function structExists($name) {
        return !empty($this->structure_list[$name]);
    }
    
    // Retreive a specific object structure
    public function getStructureByName($name) {
        if (!$this->structExists($name)){
            throw new LxrException('Unknown structure.', 10);
        }

        $this->result[$name] = $this->structure_list[$name];
        
        return $this->result;
    }
    
    // Retreive list of all structure with their fields
    public function getStructureList($count = 0) {
        $structure_list = $this->structure_list;

        // Avoid sending SYSTEM struct
        foreach ($structure_list as $key => $value) {
            if(!empty($value['SYSTEM']) && $value['SYSTEM'])
                unset($structure_list[$key]);
        }

        if(empty($structure_list)) throw new LxrException('No struct.', 204);
        
        if ($count > 0) $this->result = array_slice($structure_list, 0, $count);
        else $this->result = $structure_list;
        
        return $this->result;
    }
    
    // Add a new object structure to Framework
    public function newStructure($structName = NULL, $struct = NULL) {
        if($this->updateStructure($structName, $struct))
            // Create an entry for orphans object
            return $this->lxr->addFlag($structName, "_ORPHANS");
        else
            return FALSE;
    }
    
    // Update object structure in Framework
    public function updateStructure($structName = NULL, $struct = NULL) {
        
        // If structure empty or not correct return error
        if (!isValidJson($struct)){
            throw new LxrException('Invalid JSON structure', 12);
        }
        
        $struct = json_decode($struct, TRUE);

        foreach ($struct as $name => $options) {
            $name = strtoupper($name);
            
            // If object structure is not defined, skip it
            if (empty($options['STRUCT'])) continue;
            
            // Set the privacy options
            if (!empty($options['PRIVACY']) && isValidCollection($options['PRIVACY'])) $clean['PRIVACY'] = $options['PRIVACY'];
            else $clean['PRIVACY'] = null;
            
            // Set the description option
            if (!empty($options['DESCRIPTION']) && isValidDescription($options['DESCRIPTION'])) $clean['DESCRIPTION'] = $options['DESCRIPTION'];
            else $clean['DESCRIPTION'] = 'N/A';
            
            $fields = $objects = $collections = array();
            
            // Cleaning the requested structure
            foreach ($options['STRUCT'] as $field => $fieldOpts) {
                
                // foreach ($entry as $field => $fieldOpts) {
                $fieldType = strtolower($fieldOpts['type']);
                $field = strtocapital($field);
                
                switch ($fieldType) {
                    case 'field':
                        if (array_key_exists($field, $this->field_list)) {
                            $fields[$field]['type'] = 'field';
                            
                            if (!empty($fieldOpts['required']) && $fieldOpts['required']) $fields[$field]['required'] = True;
                            else $fields[$field]['required'] = FALSE;
                            
                            if (!empty($fieldOpts['default']) && parent::isValidField($field, $fieldOpts['default'])) $fields[$field]['default'] = $fieldOpts['default'];
                            else $fields[$field]['default'] = NULL;
                        }
                        break;

                    case 'object':
                        if (array_key_exists(strtoupper($field), $this->structure_list)) {
                            $objects[$field]['type'] = 'object';
                            
                            if (!empty($fieldOpts['required']) && $fieldOpts['required']) $objects[$field]['required'] = True;
                            else $objects[$field]['required'] = FALSE;
                            
                            if (!empty($fieldOpts['default']) && isValidID($fieldOpts['default'])) $objects[$field]['default'] = $fieldOpts['default'];
                            else $objects[$field]['default'] = NULL;
                        }
                        break;

                    case 'collection':
                        if (array_key_exists(strtoupper($field), $this->structure_list)) {
                            $collections[$field]['type'] = 'collection';
                            
                            if (!empty($fieldOpts['required']) && $fieldOpts['required']) $collections[$field]['required'] = True;
                            else $collections[$field]['required'] = FALSE;
                            
                            if (!empty($fieldOpts['default']) && isValidCollection($fieldOpts['default'])) $collections[$field]['default'] = $fieldOpts['default'];
                            else $collections[$field]['default'] = NULL;
                        }
                        break;

                    default:
                        break;
                }
         
            }
        }
        
        // Add the common structure
        $fields['_id']['type'] = "system";
        $fields['FLAGS']['type'] = "system";
        $fields['ACCESS']['type'] = "system";
        $fields['RW_ACCESS']['type'] = "system";
        
        
        // Merge fields found for this structure in array
        $obj_struct = array_merge($fields, $objects, $collections);
        
        $renamed = FALSE;
        
        // Check if we need to rename structure
        if ($name !== $structName) {
            
            try {
                $this->lxr->renameStruct($structName, $name);
            }
            catch(Exception $err) {
                throw $err;
            }
            
            // Rename local variables
            $this->structure_list[$name] = $this->structure_list[$structName];
            unset($this->structure_list[$structName]);
            
            $renamed = TRUE;
        }
        
        // If object does not exists create it
        if (!$renamed && (empty($this->structure_list) || !array_key_exists($structName, $this->structure_list))) {
            try {
                $this->result['_id'] = $this->lxr->newStruct($structName, $clean['DESCRIPTION'], $obj_struct);
            }
            catch(Exception $err) {
                throw $err;
            }
        }
        
        // If object already exists
        else {
            
            // Check if we need to change structure description
            if ($clean['DESCRIPTION'] !== $this->structure_list[$name]['DESCRIPTION']) {
                try {
                    $this->lxr->updateStructDesc($name, $clean['DESCRIPTION']);
                }
                catch(Exception $err) {
                    throw $err;
                }
            }
            
            // Compare each field with previous one
            foreach ($obj_struct as $field => $options) {
                
                // Skip system fields already set
                if ($options['type'] === "system") continue;
                
                // If field is brand new
                if (empty($this->structure_list[$name]['STRUCT']) || !array_key_exists($field, $this->structure_list[$name]['STRUCT'])) {
                    
                    // echo "looking for $field in ".json_encode($this->structure_list[$name]);
                    $this->structure_list[$name]['STRUCT'][$field] = $options;
                    try {
                        $this->lxr->addObjectField($name, $field, $options);
                    }
                    catch(Exception $err) {
                        throw $err;
                    }
                }
                
                // If field has changed
                if ($this->structure_list[$name]['STRUCT'][$field] !== $options) {
                    $this->structure_list[$name]['STRUCT'][$field] = $options;
                    try {
                        $this->lxr->updateObjectField($name, $field, $field, $options);
                    }
                    catch(Exception $err) {
                        throw $err;
                    }
                }
            }
            
            // If structure was not empty, in which case, there is nothing to delete ;)
            if (!empty($this->structure_list[$name]['STRUCT'])) {
                
                // Check for fields to delete in object
                foreach ($this->structure_list[$name]['STRUCT'] as $field => $options) {
                    
                    // If field no longer exists
                    if (!array_key_exists($field, $obj_struct)) {
                        unset($this->structure_list[$name]['STRUCT'][$field]);
                        try {
                            $this->lxr->deleteObjectField($name, $field);
                        }
                        catch(Exception $err) {
                            throw $err;
                        }
                    }
                }
            }
            
            // Finally update struct in Objects table
            try {
                $this->lxr->updateStruct($name, $obj_struct);
            }
            catch(Exception $err) {
                throw $err;
            }

            $this->result['_id'] = $name;
        }

        return $this->result;

    }
        
    // Delete object structure from Framework
    public function deleteStructure($structName = NULL) {
        
        // Delete all objects flag entries
        try {
            $this->lxr->clearFlags($structName);
        }
        catch(Exception $err) {
            throw $err;
        }
        
        // Update local and stored list
        unset($this->structure_list[$structName]);
        try {
            $this->result = $this->lxr->deleteStruct($structName);
        }
        catch(Exception $err) {
            throw $err;
        }

        return $this->result;
        
    }
}
?>