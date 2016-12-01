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


class Error_Model extends LXR_Model
{
    
    // Instantiation of local variable
    protected $error_list;
    
    function __construct($db_mode, $db_config) {
        try{
            parent::__construct($db_mode, $db_config, 'Error');
        }
        catch(Exception $err) {
            throw $err;
        }
    }

    // Define if error are set
    public function hasError(){
        $errors = $this->getErrors('en');
        if(empty($this->error) && !empty($this->error_list)) return TRUE;
        else return FALSE;
    }
    
    // Check if error exist
    public function errorExists($lang, $code) {
        return (!empty($this->error_list['en'][$code]) || !empty($this->error_list[$lang][$code]));
    }
    
    private function getErrors($lang) {

        // If errors are still not set
        if (empty($this->error_list[$lang])) {
            try {
                // Retrieve field list for futur operations
                $this->error_list = $this->lxr->getErrorList($lang);
            }
            catch(Exception $err) {
                throw $err;
            }
        }
    }
    
    public function getErrorList($count=0, $lang=NULL) {
        try {
            $this->getErrors($lang);
        }
        catch(Exception $err) {
           throw $err;
        }
        
        if (empty($this->error_list[$lang])) {
            // If this lang as no error defined, check in english
            if ($lang === 'en'){
                throw new LxrException('No error set.', 10);
            }

            return $this->getErrorList($count, 'en');
        }
        
        // If error found
        if ($count > 0) $this->result = array_slice($this->error_list[$lang], 0, $count);
        else $this->result = $this->error_list[$lang];
        
        return $this->result;
        
    }
    
    public function getError($code, $lang) {
        try {
            $this->getErrors($lang);
        }
        catch(Exception $err) {
            throw $err;
        }
        
        if (empty($this->error_list[$lang][$code])) {
            if ($lang === 'en'){
                throw new LxrException('No error found.', 11);
            }

            // If this lang as no error defined, check in english
            return $this->getError($code, 'en');
        }
        
        $this->result = $this->error_list[$lang][$code];
        
        return $this->result;
        
    }
    
    public function newError($errorCode = NULL, $message = NULL, $lang = NULL) {
        try {
            $this->getErrors($lang);
        }
        catch(Exception $err) {
            throw $err;
        }
        
        if (!empty($this->error_list[$lang]) && array_key_exists($errorCode, $this->error_list[$lang])) {
            throw new LxrException('Error already exists.', 12);
        }

        try {
            // Set POST OK
            $this->result['ID'] = $this->lxr->newError($errorCode, $message, $lang);
        }
        catch(Exception $err) {
            throw $err;
        }
            
        
        return $this->result;
    }
    
    public function updateError($oldErrorCode = NULL, $newErrorCode = NULL, $message = NULL, $lang = NULL) {
        
        // Error code can not be modified
        if ($oldErrorCode != $newErrorCode) {
            throw new LxrException('Error code cannot be modified.', 13);
        }
        
        try {
            $this->getErrors($lang);
        }
        catch(Exception $err) {
            throw $err;
        }
        
        // If error does not exists
        if (empty($this->error_list[$lang]) || !array_key_exists($code, $this->error_list[$lang])){
            throw new LxrException('Error does not exists.', 14);
        }

        try {
            $this->lxr->updateError($newErrorCode, $message, $lang);
        }
        catch(Exception $err) {
            throw $err;
        }

        $this->result['ID'] = $lang.'/'.$newErrorCode;

        return $this->result;
    }
    
    public function deleteError($code = NULL, $lang = NULL) {
        try {
            $this->getErrors($lang);
        }
        catch(Exception $err) {
            throw $err;
        }
        
        // If error does not exists
        if (empty($this->error_list[$lang]) || !array_key_exists($code, $this->error_list[$lang])){
            throw new LxrException('Error does not exists.', 15);
        }
        
        try {
            // Set DELETE OK
            $this->result = $this->lxr->deleteError($code, $lang);
        }
        catch(Exception $err) {
            throw $err;
        }

        return $this->result;
    }
}
?>