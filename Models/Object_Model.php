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


class Object_Model extends Struct_Model
{
    
    protected $collection_list;
    protected $flag_list;
    private $id_array;
    

    function __construct($db_mode, $db_config, $type=NULL) {
        $this->type = (empty($type)) ? 'Object' : $type;
        try{
            parent::__construct($db_mode, $db_config, $this->type);
        }catch(Exception $err){
            throw new LxrException($err->getMessage(),3501);
        }
        
        $this->collection_list = [];
        $this->searching = FALSE;

        try{
            $this->flag_list = $this->lxr->getFlagList();    
        }catch(Exception $err){
            throw new LxrException($err->getMessage(),3502);
        }
        
        if(!empty($this->flag_list)){
            foreach ($this->flag_list as $objectType => $collection) {
                $this->collection_list[$objectType] = array_keys($collection);
            }
        }
    }

    public function hasObject($objectType){

        try {
            # Return only one object to test if exists
            $result = $this->lxr->getObjectListByType($objectType, 1);
        }
        catch(Exception $err) {
            throw new LxrException($err->getMessage(),3503);
        }
        
        return !empty($result);
    }
    
    // Check if object exist
    public function objectExists($objectType, $id) {
        try {
            $this->getObjectByID($objectType, $id, FALSE);
        } catch (Exception $err) {
            throw new LxrException($err->getMessage(),3504);
        }
        return !empty($this->result[0]);
    }
    
    // Return the list of available objects
    public function getObjectList($count = 0) {
        if (empty($this->structure_list)) throw new LxrException("No structure.", 204);
        
        foreach ($this->structure_list as $name => $options) {
            if (isset($options['SYSTEM']) && $options['SYSTEM']) continue;
            $this->result[] = $name;
        }
        
        if (empty($this->result) || !is_array($this->result)) throw new LxrException('No object.',204);
        
        if ($count > 0) $this->result = array_slice($this->result, 0, $count);

        return $this->result;
        
    }
    
    // Return a list of object from a specific type
    // If recursive is set we will also retrieve its childrens
    public function getObjectListByType($objectType, $recursive, $count = 0) {
        try{
            $id_list = $this->getAllIDByType($objectType);
        }catch(Exception $err){
            throw new LxrException($err->getMessage(),$err->getCode());
        }
        
        $raw = $this->getCollectionByID($objectType, $id_list, $recursive);
        
        if (empty($raw) || !is_array($raw)) throw new LxrException('No object.', 204);
        else if ($count > 0) $this->result = array_slice($raw, 0, $count);
        else $this->result = $raw;

        return $this->result;
    }

    public function selectObjectListByType($objectType, $selector, $recursive, $count = 0) {
        try{
            $id_list = $this->selectAllIDByType($objectType, $selector);
        }catch(Exception $err){
            throw new LxrException($err->getMessage(),$err->getCode());
        }
        
        $raw = $this->getCollectionByID($objectType, $id_list, $recursive);
        
        if (empty($raw) || !is_array($raw)){
            $this->result = NULL;
        }
        else if ($count > 0){
            $this->result = array_slice($raw, 0, $count);
        }
        else{
            $this->result = $raw;
        }

        return TRUE;

    }

    private function getAllIDByType($objectType){
        try {
            $raw = $this->lxr->getObjectListByType($objectType);
        }
        catch(Exception $err) {
            throw new LxrException($err->getMessage(),3505);
        }
        
        if (empty($raw) || !is_array($raw)) throw new LxrException('No Object.',204);

        return $raw;
    }

    private function selectAllIDByType($objectType, $selector){
        try {
            $raw = $this->lxr->selectObjectListByType($objectType, $selector);
        }
        catch(Exception $err) {
            throw new LxrException($err->getMessage(),3506);
        }
        
        if (empty($raw) || !is_array($raw)) return NULL;

        return $raw;
    }
    
    // Return a specific structure
    public function getObjectByID($objectType, $id, $recursive) {
        # reset result
        unset($this->result);
        try {
            $this->result = $this->getObjectByIDInner($objectType, $id, $recursive);
        } catch (Exception $err) {
            throw new LxrException($err->getMessage(),$err->getCode());
        }
        
        return $this->result[0];
    }
    
    // Function called by own object
    private function getObjectByIDInner($objectType, $id, $recursive) {
        
        try {
            $result = $this->lxr->getObjectByID($objectType, $id);
        }
        catch(Exception $err) {
            throw new LxrException($err->getMessage(),3507);
        }
        
        if(empty($result)) throw new LxrException("Object not found", 204);
        ;

        $fields = $this->structure_list[$objectType];
        
        // If we want a complete object structure
        if ($recursive) {
            
            // While we found a collection / object field retrieve it with new type and id
            foreach ($fields['STRUCT'] as $field => $options) {
                
                // Let ID and system field in their state
                if ($options['type'] === "system") {
                    $field = strtoupper($field);
                    $clean[$field] = $result[$field];
                }
                
                // Retrieve a complete collection
                else if ($options['type'] === "collection") {
                    $id_list = ID2array($result[$field]);
                    if (!empty($id_list) && is_array($id_list)) {
                        $clean[$field] = $this->getCollectionByID(strtoupper($field), $id_list, $recursive);
                    }
                }
                
                //retrieve a single object
                else if ($options['type'] === "object") {
                    $id = $result[$field];
                    if (!empty($id) && (ctype_digit($id) || is_float($id))) {
                        $clean[$field] = $this->getObjectByIDInner(strtoupper($field), $id, $recursive);
                    }
                }
                
                //decode the corresponding field
                else {
                    $clean[$field] = htmlentities(stripslashes(base64_decode($result[$field])));
                }
            }
        } 
        else {
            foreach ($fields['STRUCT'] as $field => $options) {
                
                // Let ID and system field in their state
                if ($options['type'] === "system") {
                    $field = strtoupper($field);
                    $clean[$field] = $result[$field];
                } 
                else if ($options['type'] === "object") {
                    $clean[$field] = (int)$result[$field];
                } 
                else if ($options['type'] === "collection") {
                    $clean[$field] = ID2array($result[$field]);
                }
                
                //decode fields
                else {
                    $clean[$field] = htmlentities(stripslashes(base64_decode($result[$field])));
                }
            }
        }
        
        return $clean;
    }
    
    public function getCollection($objectType, $flag_list, $recursive=FALSE, $count=0) {
        
        foreach ($flag_list as $key => $flag) {
            if(empty($flag)) continue;

            // Return error if something happened....
            if(!$this->getCollectionByFlag($objectType, $flag)) return FALSE; 
        }
        
        // Return simple empty data set if nothing is found
        if (empty($this->id_array)) throw new LxrException("No object.", 204);
        
        $this->result = $this->getCollectionByID($objectType, $this->id_array, $recursive);
        
        if ($count > 0) $this->result = array_slice($this->result, 0, $count);

        return TRUE;
    }
    
    // Return a list of object from a list of id
    private function getCollectionByID($objectType, $id_list, $recursive=FALSE) {
        
        if (empty($id_list) || !is_array($id_list)) return NULL;
        
        foreach ($id_list as $id) {
            try{
                $tmp = $this->getObjectByIDInner($objectType, (int)$id, $recursive);
                if(!empty($tmp)) $result[] = $tmp;
            }catch(Exception $err){
                throw new LxrException($err->getMessage(),$err->getCode());
            }
        }
        
        return $result;
    }
    
    // Return objects having a NOT indexable flag
    private function getUnindexIDList($objectType, $flag) {
        try{
            return $this->lxr->getUnindexIDList($objectType, $flag);
        }catch(Exception $err){
            throw new LxrException($err->getMessage(),3508);
        }
    }
    
    // Return objects from a collection list
    private function getIDList($objectType, $flag) {
        
        // Correct orphan flag
        if($flag === '_orphans') $flag = strtoupper($flag);

        // Return null if empty array
        if (empty($this->flag_list) || empty($this->flag_list[$objectType]) || empty($this->flag_list[$objectType][$flag])) return NULL;

        // Else return correct set of object
        else return $this->flag_list[$objectType][$flag];
    }
    
    // Return a list of collection
    private function getCollectionByFlag($objectType, $flag) {
        // If some search has already returned empty result... skip searching (AND condition on flags !)
        if($this->searching && empty($this->id_array)) return TRUE;

        $this->searching = TRUE;
        
        // If flag is unindexable
        if (substr($flag, 0, 1) === "_" && $flag !== '_orphans') {
            try {
                // Return all object having the unindexable flag
                $tmp = $this->getUnindexIDList($objectType, $flag);
            }
            catch(Exception $err) {
                throw new LxrException($err->getMessage(),$err->getCode());
            }
            
            // If we already have some results
            if (!empty($this->id_array)) {
                
                // Add corresponding object list to result array
                if (!empty($tmp)) $this->id_array = @array_intersect($this->id_array, $tmp);
                // If array found is empty, result is empty... AND operation between flags !!
                else $this->id_array = NULL;
            }
            
            // If it's the first results we have
            else {
                $this->id_array = $tmp;
            }
        }
        
        // If this is a negation flag
        else if (substr($flag, 0, 1) === "!") {
            
            // Retrieve the real flag
            $flag = substr($flag, 1);
            
            // If flag is unindexable
            if (substr($flag, 0, 1) === "_") {
                try {
                    // Return all object having the unindexable flag
                    $exclude_array = $this->getUnindexIDList($objectType, $flag);
                }
                catch(Exception $err) {
                    throw new LxrException($err->getMessage(),$err->getCode());
                }
            } 
            else {
                try {
                    // Return the collection of object
                    $exclude_array = $this->getIDList($objectType, strtocapital($flag));
                }
                catch(Exception $err) {
                    throw new LxrException($err->getMessage(),$err->getCode());
                }
            }
            
            // exclude corresponding object list from result array
            if(!empty($exclude_array)){
                // If no result is set yet, choose complete set
                if(empty($this->id_array))
                    $this->id_array = $this->getAllIDByType($objectType);
                
                // If id_array is NULL, will return NULL without sending error
                $this->id_array = @array_diff($this->id_array, $exclude_array);
            }
        }
        
        // If the flag is indexable
        else {
            try {
                // Return the collection of object
                $tmp = $this->getIDList($objectType, $flag);
            }
            catch(Exception $err) {
                throw new LxrException($err->getMessage(),$err->getCode());
            }
            
            // If we already have some results
            if (!empty($this->id_array)) {
                // Add corresponding object list to result array
                if (!empty($tmp)) $this->id_array = @array_intersect($this->id_array, $tmp);
                // If array found is empty, result is empty... AND operation between flags !!
                else $this->id_array = NULL;
            }
            
            // If it's the first results we have
            else {
                $this->id_array = $tmp;
            }
        }
        
        return TRUE;
    }
    
    // Check that an object is conform to its structure
    private function checkObject($objectType, $data) {
        
        // If object type does not exists skip action
        if (!array_key_exists($objectType, $this->structure_list)) throw new LxrException("Unknown object type.", 3508);
        
        
        $structure = $this->structure_list[$objectType]['STRUCT'];
        
        // Set the correct char model to data keys
        foreach ($data as $key => $value) {
            unset($data[$key]);
            $data[strtocapital($key) ] = $value;
        }
        
        $clean = array();
        
        // Parse object to validate field value
        foreach ($structure as $field => $fieldOpts) {
            
            // Avoid modifying the id field
            if (strtolower($field) === TABLE_PREFIX."id") continue;
            
            $field = strtocapital($field);
            $fieldType = $fieldOpts['type'];
            
            switch ($fieldType) {
                case 'field':
                    
                    // If field is required
                    if ($fieldOpts['required']) {
                        
                        // If no value is defined
                        if (empty($data[$field])) {
                            
                            // But a default value is set, ajust it
                            if (!empty($fieldOpts['default']) && parent::isValidField($field, $fieldOpts['default'])) {
                                $clean[$field] = base64_encode($fieldOpts['default']);
                            }
                            // If no default value exists return error
                            else {
                                throw new LxrException("$field value is required", 3510);
                            }
                        }
                        
                        // If a value is defined and valid
                        else if (parent::isValidField($field, $data[$field])) {
                            $clean[$field] = base64_encode($data[$field]);
                        }
                        
                        // If a value is set but invalid
                        else {
                            throw new LxrException("$field value is invalid", 3511);
                        }
                    }
                    
                    // If field is not mandatory, and a valid value is set
                    else if (!empty($data[$field]) && parent::isValidField($field, $data[$field])) {
                        $clean[$field] = base64_encode($data[$field]);
                    }
                    
                    // If field is not mandatory and nothing is defined
                    else {
                        
                        // if a default value is set
                        if (!empty($fieldOpts['default']) && parent::isValidField($field, $fieldOpts['default'])) $clean[$field] = base64_encode($fieldOpts['default']);
                        // If no default value is set and nothing is required, just skip this field -- SO COMMENT NEXT LINE ???
                        else $clean[$field] = '';
                    }
                    break;

                case 'object':
                    if ($fieldOpts['required']) {
                        if (!empty($data[$field])) {
                            if (isValidID($data[$field])) $clean[$field] = $data[$field];
                            else{
                                throw new LxrException("$field ID is invalid", 3512);
                            }
                        } 
                        else {
                            throw new LxrException("$field ID is required", 3513);
                        }
                    } 
                    else {
                        if (!empty($data[$field]) && isValidID($data[$field])) $clean[$field] = $data[$field];
                        // If no default value is set and nothing is required, just skip this field -- SO COMMENT NEXT LINE ???
                        else $clean[$field] = '';
                    }
                    
                    break;

                case 'collection':
                    if ($fieldOpts['required']) {
                        if (!empty($data[$field])) {
                            if (is_array($data[$field])) $clean[$field] = array2ID($data[$field]);
                            else if (isValidCollectionID($data[$field])) $clean[$field] = $data[$field];
                            else{
                                throw new LxrException("$field collection is required", 3514);
                            }
                        } 
                        else {
                            throw new LxrException("$field collection is required", 3515);
                        }
                    } 
                    else {
                        if (!empty($data[$field])) {
                            if (is_array($data[$field])) $clean[$field] = array2ID($data[$field]);
                            else if (isValidCollectionID($data[$field])) $clean[$field] = $data[$field];
                            // If no default value is set and nothing is required, just skip this field -- SO COMMENT NEXT LINE ???
                            else $clean[$field] = '';
                        } 
                        else {
                            // If no default value is set and nothing is required, just skip this field -- SO COMMENT NEXT LINE ???
                            $clean[$field] = '';
                        }
                    }
                    
                    break;
                    
                // System fields (ID/ FLAGS / ACCESS & RW_ACCESS)
                default:
                    if (!empty($data[$field])) {
                        if (isValidCollection($data[$field])) {
                            $clean[$field] = $data[$field];
                        } 
                        else if (isValidArrayFlag($data[$field])) {
                            $clean[$field] = array2Flag($data[$field]);
                        } 
                        else if (isValidArrayID($data[$field])) {
                            $clean[$field] = array2ID($data[$field]);
                        } 
                        else {
                            throw new LxrException("$field collection is invalid", 3516);
                        }
                    }
                    
                    break;
            }
        }
            
        return $clean;
    }
        
    // Store a new object based on its structure
    public function storeObject($objectType, $params) {
        
        // First check object consistency
        $clean = $this->checkObject($objectType, $params);
        
        if (!empty($this->error)) return FALSE;
        
        // If everything went fine, store the new object
        try {
            $this->result[TABLE_PREFIX.'id'] = $this->lxr->storeObject($objectType, $clean);
        }
        catch(Exception $err) {
            throw new LxrException($err->getMessage(),3517);
        }
        
        // Register all flag to this object type
        if (!empty($clean['Flags'])) {
            $flags = explode(LXR_SEPARATOR, $clean['Flags']);
            
            foreach ($flags as $f) {
                if (isValidFlag($f)) {
                    $f = ucfirst(strtolower($f));
                    try {
                        $this->addFlagToID($objectType, $f, $this->result[TABLE_PREFIX.'id']);
                    }
                    catch(Exception $err) {
                        throw new LxrException($err->getMessage(),$err->getCode());
                    }
                }
            }
        }
        
        // If no flag is set, asociate object to orphans
        else {
            $this->setOrphan($objectType, $this->result[TABLE_PREFIX.'id']);
        }
        
        $this->collection_list[$objectType] = $this->getFlagByType($objectType);
        
        return TRUE;
    }
    
    // Update an object depending on its structure
    public function updateObject($objectType, $id, $params) {
        
        // First check object consistency
        $clean = $this->checkObject($objectType, $params);
        
        if (!empty($this->error)) return FALSE;
        
        // If everything went fine, update object
        try {
            $this->lxr->updateObject($objectType, $id, $clean);
        }
        catch(Exception $err) {
            throw new LxrException($err->getMessage(),3518);
        }
        
        // Register all flag to this object type
        if (!empty($clean['Flags'])) {
            $flags = explode(LXR_SEPARATOR, $clean['Flags']);
            
            foreach ($flags as $f) {
                if (isValidFlag($f)) {
                    $f = ucfirst(strtolower($f));
                    try {
                        $this->addFlag($objectType, $f, $id);
                    }
                    catch(Exception $err) {
                        throw new LxrException($err->getMessage(),$err->getCode());
                    }
                }
            }
        }
        
        // If no flag is set, asociate object to orphans
        else {
            $this->setOrphan($objectType, $id);
        }
        
        $this->collection_list[$objectType] = $this->getFlagByType($objectType);
        
        $this->result[TABLE_PREFIX.'id'] = $id;

        return TRUE;
    }
    
    // Delete object and all corresponding flags
    public function deleteObject($objectType, $id) {
        
        // First retrieve flags from this object
        $flag_list = $this->getFlagByID($objectType, $id);
        
        if (!empty($flag_list) && is_array($flag_list)) {
            
            // Remove this object from its flags
            foreach ($flag_list as $key => $flag) {
                $this->deleteFlagOfID($objectType, $flag, $id);
            }
        }
        
        // Remove this object from orphans list
        $this->deleteOrphan($objectType, $id);
        
        // Finally remove the object
        try {
            $this->result = $this->lxr->deleteObject($objectType, $id);
        }
        catch(Exception $err) {
            throw new LxrException($err->getMessage(),3519);
        }

        return TRUE;
    }
    
    private function isIndexableFlag($flag) {
        if (isValidFlag($flag) && substr($flag, 0, 1) !== "_") return TRUE;
        else return FALSE;
    }
    
    // Flag an object as orphan
    private function setOrphan($objectType, $id) {
        
        try {
            return $this->lxr->setOrphan($objectType, $id);
        }
        catch(Exception $err) {
            throw new LxrException($err->getMessage(),3520);
        }
    }
    
    // Remove an object from orphan list
    private function deleteOrphan($objectType, $id) {
        
        try {
            return $this->lxr->deleteOrphan($objectType, $id);
        }
        catch(Exception $err) {
            throw new LxrException($err->getMessage(),3521);
        }
    }
    
    // Add a flag to an object and all its childs
    protected function addFlag($objectType, $flag, $id) {
        
        // First retrieve object itself
        $object = $this->getObjectByIDInner($objectType, $id, FALSE);
        
        // parse struct of object
        $struct = $this->structure_list[$objectType]['STRUCT'];

        foreach ($struct as $field => $option) {
            if ($option['type'] == "object") {
                
                // If no id is set skip this
                if (empty($object[$field])) continue;
                
                // Instantiate flag to it
                $this->addFlag(strtoupper($field), $flag, $object[$field]);
            } 
            else if ($option['type'] == "collection") {
                
                // Get list of id
                if (is_array($object[$field]) && isValidArrayID($object[$field])) $id_list = $object[$field];
                else $id_list = ID2array($object[$field]);
                
                if (empty($id_list) || !is_array($id_list)) continue;
                
                foreach ($id_list as $key => $new_id) {
                    // Instantiate flag to it
                    $this->addFlag(strtoupper($field), $flag, $new_id);
                }
            }
        }
        
        // Then add flag to object itself
        $this->addFlagToID($objectType, $flag, $id);

        return TRUE;
    }
    
    // Index a new flag to an object
    private function addFlagToID($objectType, $flag, $id) {
        
        try {
            
            // If flag is indexable (NOT starting with underscore)
            if (substr($flag, 0, 1) !== "_") {
                
                //If this object was an orphan remove it from orphan collection
                if ($this->lxr->isOrphan($objectType, $id)) {
                    try {
                        $this->lxr->deleteOrphan($objectType, $id);
                    }
                    catch(Exception $err) {
                        throw new LxrException($err->getMessage(),3522);
                    }
                }
                
                // If flag does not exists for this type of object create it
                if (empty($this->collection_list[$objectType]) || !in_array($flag, $this->collection_list[$objectType])) {
                    try {
                        $this->lxr->addFlag($objectType, $flag);
                    }
                    catch(Exception $err) {
                        throw new LxrException($err->getMessage(),3523);
                    }
                    
                    // update local flag list
                    array_push($this->collection_list[$objectType], $flag);
                }
                
                // As this flag is indexable, register it
                try {
                    $this->lxr->indexFlagToID($objectType, $flag, $id);
                }
                catch(Exception $err) {
                    throw new LxrException($err->getMessage(),3524);
                }
            }
            
            $this->lxr->addFlagToID($objectType, $flag, $id);
        }
        catch(Exception $err) {
            throw new Exception($err->getMessage(), $err->getCode());
        }
        
        return TRUE;
    }
    
    // Return all flags of object
    protected function getFlagByID($objectType, $id) {
        
        try {
            return $this->lxr->getFlagByID($objectType, $id);
        }
        catch(Exception $err) {
            throw new LxrException($err->getMessage(),3525);
        }
    }
    
    // Return the array of flags of an object Type
    private function getFlagByType($objectType) {
        
        // If objectType does not exists skip it
        if (!array_key_exists($objectType, $this->structure_list)) throw new LxrException('Invalid object type',3526);
        
        if (empty($this->collection_list[$objectType])) $result[$objectType] = NULL;
        else $result[$objectType] = $this->collection_list[$objectType];
        
        return $result;
    }
    
    // Delete flag from object and all its children
    protected function deleteFlag($objectType, $flag, $id) {
        
        // First retrieve object itself
        $object = $this->getObjectByIDInner($objectType, $id, FALSE);
        
        // parse struct of object
        $struct = $this->structure_list[$objectType]['STRUCT'];
        
        foreach ($struct as $field => $option) {
            if ($option['type'] == "object") {
                
                // If no id is set skip this
                if (empty($object[$field])) continue;
                
                // Delete flag of it
                $this->deleteFlagOfID(strtoupper($field), $flag, $object[$field]);
            } 
            else if ($option['type'] == "collection") {
                
                // Get list of id
                $id_list = ID2array($object[$field]);
                if (empty($id_list) || !is_array($id_list)) continue;
                
                foreach ($id_list as $key => $new_id) {
                    
                    // Delete flag for each of them
                    $this->deleteFlagOfID(strtoupper($field), $flag, $new_id);
                }
            }
        }
        
        // Then delete flag from object itself
        $this->deleteFlagOfID($objectType, $flag, $id);

        return TRUE;
    }
    
    // Delete flag of objectType for specific id
    private function deleteFlagOfID($objectType, $flag, $id) {
        
        // Set correct case
        // $flag = strtocapital($flag);
        
        // If objectType does not exists skip it
        if (!array_key_exists($objectType, $this->structure_list)) return FALSE;
        
        try {
            $this->lxr->deleteFlagOfID($objectType, $flag, $id);
            
            if (substr($flag, 0, 1) !== "_") $this->lxr->unindexIDFromFlag($objectType, $flag, $id);
        }
        catch(Exception $err) {
            throw new LxrException($err->getMessage(),3526);
        }
        
        // Retrieve id list for specific flag
        try {
            $id_list = $this->lxr->getIDList($objectType, $flag);
        }
        catch(Exception $err) {
            throw new LxrException($err->getMessage(),3527);
        }
        
        // If object has no more flag set, remove flag
        if (empty($id_list)) {
            
            try {
                $this->lxr->deleteFlag($objectType, $flag);
            }
            catch(Exception $e) {
                throw new LxrException($err->getMessage(),3528);
            }
            
            // If object as not more indexable flag, register it as orphan
            $flag_list = $this->lxr->getFlagByID($objectType, $id);
            
            if (empty($flag_list) || !$this->hasIndexableFlag($objectType, $id)) $this->setOrphan($objectType, $id);
        }
        
        // update local flag list
        if (($key = array_search($flag, $this->collection_list[$objectType])) !== FALSE) {
            unset($this->collection_list[$objectType][$key]);
        }
        
        return TRUE;
    }
    
    // Check if an object as at least one indexable flag
    private function hasIndexableFlag($objectType, $id) {
        $flag_list = $this->lxr->getFlagByID($objectType, $id);
        
        foreach ($flag_list as $value) {
            if (substr($value, 0, 1) !== "_") return TRUE;
        }
        
        return FALSE;
    }
    
    // Special selection function
    public function get($expression, $recursive = FALSE) {
        if (empty($expression)) throw new LxrException('Empty expression',3529);
        
        // Remove first and last charactere
        $expression = substr(substr($expression, 1), 0, -1);
        
        // Explode expression in usable var
        $params = explode(" ", $expression);
        
        // Discover the params defined
        if (!empty($params[0])) $objectType = strtoupper($params[0]);
        
        if (!empty($params[1])) $flag_list = $params[1];
        
        // Check for optional parameters
        for ($i = 2; $i < sizeof($params); $i++) {
            if (empty($params[$i])) continue;
            
            list($key, $value) = explode(":", $params[$i]);
            
            switch ($key) {
                case 'SORT':
                    if (substr($value, 0, 2) === "R_") {
                        $sort = substr($value, 2);
                        $reverse = TRUE;
                    } 
                    else {
                        $sort = $value;
                        $reverse = FALSE;
                    }
                    
                    // If the field does not exists in this object skip the sort action
                    if (!array_key_exists(strtocapital($sort), $this->structure_list[$objectType])) {
                        unset($sort);
                        break;
                    }
                    
                    break;

                case 'COUNT':
                    $count = $value;
                    break;

                default:
                    break;
            }
        }
        
        // Minimum set is type and flag
        if (!isValidObjectName($objectType)) throw new LxrException('Invalid object name',3530);
        
        // Fields check
        $flags = explode(",", $flag_list);
        
        $this->id_array = array();
        
        foreach ($flags as $f) {
            if (empty($f)) continue;
            // Return error if something happened....
            if(!$this->getCollectionByFlag($objectType, $f)) return FALSE; 
        }
        
        // If nothing has been found, we can stop here
        if (empty($this->id_array)) throw new LxrException('No Object.',204);
        
        // Array of id is parsed to be unique
        $this->id_array = array_unique($this->id_array);
        $result_array = $this->getCollectionByID($objectType, $this->id_array, $recursive);
        
        // Order treatment if asked
        if (!empty($sort)) {
            
            // Define the key on wich the array will be sorted
            foreach ($result_array as $key => $value) {
                $row[$key] = $value[$sort];
            }
            
            // Return the oredered array ascendant or descendant
            if ($reverse) array_multisort($row, SORT_DESC, SORT_REGULAR, $result_array);
            else array_multisort($row, SORT_ASC, SORT_REGULAR, $result_array);
        }
        
        // Specific count if asked
        if (!empty($count)) $result_array = array_slice($result_array, 0, $count);
        
        return $result_array;
    }
}   