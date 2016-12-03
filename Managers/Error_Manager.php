<?php

/**
* Elixir, Stored Objects management
* @author Benoit Malchrowicz
* @version 1.0
*
* Copyright © 2014-2016 Benoit Malchrowicz
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

class Error_Manager extends DB_Manager
{
    public function __construct($type, $param){
        parent::__construct($type, $param);
    }
    
    // Return error list by lang
    public function getErrorList($lang){
        $table = DB_PREFIX.'Errors';
        $field = array('CODE', 'MESSAGE');
        $where = array('LANG' => $lang);

        try{
            $tmp_list = $this->driver->selectData($table, $field);
        }catch (Exception $e){
            throw new LxrException($e->getMessage(),1061);
        }

        if(empty($tmp_list) || !is_array($tmp_list))
            return NULL;

        foreach ($tmp_list as $key => $value) {
            $result[$lang][$value['CODE']] = $value['MESSAGE'];
        }

        return $result;

    }

    // Return specific error
    public function getError($code, $lang){
        $table = DB_PREFIX.'Errors';
        $field = array('MESSAGE');
        $where = array('CODE' => $code, 'LANG' => $lang);

        try{
            $result = $this->driver->selectData($table, $field, $where);
        }catch (Exception $e){
            throw new LxrException($e->getMessage(),1062);
        }

        // If nothing has been found for a specific lang
        if(empty($result) || !is_array($result))
            // If language was english, send null result
            if($lang === 'en')
                return NULL;
            // Otherwise try to retrieve the english result
            else
                $this->getError($code, 'en');

        return $result;

    }

    // Store a new error
    public function newError($code, $message, $lang){
        
        $req_params = array('CODE' => $code,
                            'MESSAGE' => $message,
                            'LANG' => $lang);
        try{
            return $this->driver->insertData(DB_PREFIX.'Errors', $req_params);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1063);
        }

    }

    // Update an error
    public function updateError($code, $message, $lang){
        $req_params = array('MESSAGE' => $message);

        $where = array('CODE' => $code,
                        'LANG' => $lang);
        $updated = FALSE;
        try{
            $this->driver->updateData(DB_PREFIX.'Errors', $req_params, $where);
            $updated = $req_params;
            $updated['CODE'] = $code;
            $updated['LANG'] = $lang;
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1064);
        }

        return $updated;
    }

    // Delete a specific error
    public function deleteError($code, $lang){
        $where = array('CODE' => $code,
                        'LANG' => $lang);

        try{
            return $this->driver->deleteData(DB_PREFIX.'Errors', $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1065);
        }

    }
}

?>