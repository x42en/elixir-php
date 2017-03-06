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

        // Import correct format
        require_once (__ROOT__ . 'Utils/LXR_formats.php');
        
        // Add system structure to array
        $this->structure_list['OBJECT'] = array("SYSTEM" => true);
        $this->structure_list['USER'] = array(
            "STRUCT" => $system_user,
            "DESCRIPTION" => "System structure for user",
            "SYSTEM" => true);
        $this->structure_list['FIELD'] = array(
            "STRUCT" => $system_field,
            "DESCRIPTION" => "System structure for field", 
            "SYSTEM" => true);
        $this->structure_list['ERROR'] = array(
            "STRUCT" => $system_error,
            "DESCRIPTION" => "System structure for error",
            "SYSTEM" => true);
        $this->structure_list['FLAG'] = array(
            "STRUCT" => $system_flag,
            "DESCRIPTION" => "System structure for flag",
            "SYSTEM" => true);
        $this->structure_list['STRUCT'] = array(
            "STRUCT" => $system_struct,
            "DESCRIPTION" => "System structure for struct",
            "SYSTEM" => true);
        $this->structure_list['VIEW'] = array(
            "STRUCT" => $system_view,
            "DESCRIPTION" => "System structure for view",
            "SYSTEM" => true);
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
    public function newStructure($structName = NULL, $struct = NULL, $desc = NULL) {
        try{
            $created = $this->updateStructure(strtoupper($structName), $structName, $struct, $desc);
        }catch(Exception $err){
            throw $err;
        }
        
        try {
            // Create an entry for orphans object
            $this->lxr->addFlag($structName, "_ORPHANS");
        } catch (Exception $err) {
            throw $err;
        }
        
        return $this->result;
        
    }
    
    // Update object structure in Framework
    public function updateStructure($oldName = NULL, $structName = NULL, $struct = NULL, $desc = NULL) {
        
        // If structure empty or not correct return error
        if (!isValidJson($struct)){
            throw new LxrException('Invalid JSON structure', 12);
        }
        
        $struct = json_decode($struct, TRUE);
        $fields = $objects = $collections = array();

        // Set name object
        $this->result['NAME'] = $name = strtoupper($structName);

        // Set the description option
        if (empty($desc) || !isValidDescription($desc)) $this->result['DESCRIPTION'] = 'N/A';
        else $this->result['DESCRIPTION'] = $desc;

        // Cleaning the requested structure
        foreach ($struct as $field => $fieldOpts) {
            
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
        
        // Add the common structure
        $fields[TABLE_PREFIX.'id']['type'] = "id";
        $fields[TABLE_PREFIX.'id']['required'] = TRUE;
        $fields[TABLE_PREFIX.'id']['unique'] = TRUE;
        $fields[TABLE_PREFIX.'FLAGS']['type'] = "system";
        $fields[TABLE_PREFIX.'ACCESS']['type'] = "system";
        $fields[TABLE_PREFIX.'RW_ACCESS']['type'] = "system";
        
        
        // Merge fields found for this structure in array
        $this->result['STRUCT'] = array_merge($fields, $objects, $collections);
        
        $renamed = FALSE;
        
        // Check if we need to rename structure
        if ($name !== $oldName) {
            
            try {
                $this->lxr->renameStruct($oldName, $name);
            }
            catch(Exception $err) {
                throw $err;
            }
            
            // Rename local variables
            $this->structure_list[$name] = $this->structure_list[$oldName];
            unset($this->structure_list[$oldName]);
            
            $renamed = TRUE;
        }
        
        // If object does not exists create it
        if (!$renamed && (empty($this->structure_list) || !array_key_exists($oldName, $this->structure_list))) {
            try {
                $this->result['NAME'] = $this->lxr->newStruct($oldName, $this->result['DESCRIPTION'], $this->result['STRUCT']);
            }
            catch(Exception $err) {
                throw $err;
            }
        }
        
        // If object already exists
        else {
            
            // Check if we need to change structure description
            if ($this->result['DESCRIPTION'] !== $this->structure_list[$name]['DESCRIPTION']) {
                try {
                    $this->lxr->updateStructDesc($name, $this->result['DESCRIPTION']);
                }
                catch(Exception $err) {
                    throw $err;
                }
            }
            
            // Compare each field with previous one
            foreach ($this->result['STRUCT'] as $field => $options) {
                
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
                    if (!array_key_exists($field, $this->result['STRUCT'])) {
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
                $this->lxr->updateStruct($name, $this->result['STRUCT']);
            }
            catch(Exception $err) {
                throw $err;
            }

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