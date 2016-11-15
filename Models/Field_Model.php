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


class Field_Model extends LXR_Model
{
    
    // Instantiation of local variable
    protected $field_list;
    
    function __construct($db_mode, $db_config) {
        try {
            parent::__construct($db_mode, $db_config);
            // Retrieve field list for futur operations
            $this->field_list = $this->lxr->getFieldList();
        }
        catch(Exception $e) {
            throw $err;
        }
    }
    
    // Define if field are set
    public function hasField(){
        return !empty($this->field_list);
    }
    
    // Check if field exist
    public function fieldExists($name) {
        return !empty($this->field_list[$name]);
    }
    
    // Store specific field in result
    public function getFieldByName($name) {
        
        if (!$this->fieldExists($name)){
            throw new LxrException('Unknown field.', 11);
        }
        
        $this->result[$name] = $this->field_list[$name];
        
        return $this->result;
    }
    
    // Store field list in result
    public function getFieldList($count = 0) {
        if ($count > 0) $this->result = array_slice($this->field_list, 0, $count);
        else $this->result = $this->field_list;

        return $this->result;
    }
    
    // Store result of value check against field regex
    public function checkValueAgainstField($fieldName = NULL, $value) {
        
        if (!$this->isValidField($fieldName, $value)){
            throw new LxrException('Invalid value.', 12);
        }

        return TRUE;
    }
    
    // Check if value match field regex
    protected function isValidField($fieldName = NULL, $value) {
        
        if (!$this->fieldExists($fieldName) || empty($this->field_list[$fieldName]['REGEX'])) return FALSE;
        
        if (!is_string($value)) return FALSE;
        
        if (preg_match($this->field_list[$fieldName]['REGEX'], $value)) return TRUE;
        else return FALSE;
    }

    // Return a field regex, this allow creating/updating field with regex based on regex name
    public function getRegex($fieldName) {
        if (!$this->fieldExists($fieldName) || empty($this->field_list[$fieldName]['REGEX'])){
            return NULL;
        }
        else{
            return $this->field_list[$fieldName]['REGEX'];
        }
    }
    
    // Create a new field
    public function newField($fieldName = NULL, $regex = NULL, $description = NULL) {
        if (in_array(strtoupper($fieldName), $this->system_fields)){
            throw new LxrException('Forbidden field name.', 13);
        }
        
        return $this->updateField($fieldName, $fieldName, $regex, $description);
    }
    
    // Update an existing field
    public function updateField($oldFieldName = NULL, $newFieldName = NULL, $regex = NULL, $description = NULL) {
        
        if (in_array(strtoupper($oldFieldName), $this->system_fields)){
            throw new LxrException('Unmodifiable field', 14);
        }

        if (in_array(strtoupper($newFieldName), $this->system_fields)){
            throw new LxrException('Forbidden field name.', 15);
        }

        $renamed = False;
        
        // If field name has changed
        if ($oldFieldName !== $newFieldName) {
            try {
                $this->lxr->renameField($oldFieldName, $newFieldName);
            }
            catch(Exception $err) {
                throw $err;
            }
            
            $this->field_list[$newFieldName] = $this->field_list[$oldFieldName];
            unset($this->field_list[$oldFieldName]);
            
            $renamed = True;
        }
        
        // If field does not exists, create it
        if (!array_key_exists($newFieldName, $this->field_list)) {
            
            try {
                $this->result['ID'] = $this->lxr->newField($newFieldName, $regex, $description);
            }
            catch(Exception $err) {
                throw $err;
            }
        }
        // If field exists, update it
        else {
            
            try {
                $this->lxr->updateField($newFieldName, $regex, $description);
            }
            catch(Exception $err) {
                throw $err;
            }

            $this->result['ID'] = $newFieldName;
        }
        
        return $this->result;
    }
    
    // Delete an existing field
    public function deleteField($fieldName = NULL) {
        
        if (in_array(strtoupper($fieldName), $this->system_fields)){
            throw new LxrException('Undeletable field.', 16);
        }

        try {
            $this->result = $this->lxr->deleteField($fieldName);
        }
        catch(Exception $err) {
            throw $err;
        }
        
        return $this->result;
    }
}
?>