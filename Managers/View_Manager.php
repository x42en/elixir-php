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

class View_Manager extends DB_Manager
{
    public function __construct($type, $param){
        parent::__construct($type, $param);
    }

    // Return all fields available
    public function getFieldList(){
        try{
            $fields =  $this->driver->getData(DB_PREFIX.'Fields');
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1009);
        }

        if(empty($fields) || !is_array($fields))
            return NULL;

        $fields_list = array();
        foreach ($fields as $key => $value) {
            $name = strtocapital($value['NAME']);
            $fields_list[$name]['REGEX'] = parent::decode_data($value['REGEX']);
            $fields_list[$name]['DESCRIPTION'] = parent::decode_data($value['DESCRIPTION']);
        }

        return $fields_list;
    }

    // Return all structures available
    public function getStructureList(){
        try{
            $structs =  $this->driver->getData(DB_PREFIX.'Structures');
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1010);
        }

        if(empty($structs) || !is_array($structs))
            return NULL;

        $structs_list = array();
        foreach ($structs as $key => $value) {
            $name = strtoupper($value['NAME']);
            if(!empty($value['STRUCT']))
                $structs_list[$name]['STRUCT'] = json_decode(parent::decode_data($value['STRUCT']), True);
            else
                $structs_list[$name]['STRUCT'] = '';
            if(!empty($value['DESCRIPTION']))
                $structs_list[$name]['DESCRIPTION'] = parent::decode_data($value['DESCRIPTION']);
            else
                $structs_list[$name]['DESCRIPTION'] = '';
        }
        
        return $structs_list;
    }

    // Return all templates available
    public function getViewList(){
        $table = DB_PREFIX.'Views';
        $field = array('OBJECT', 'TYPE', 'FORMAT');

        try{
            $views =  $this->driver->selectData($table, $field);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1011);
        }

        if(empty($views) || !is_array($views))
            return NULL;

        $views_list = array();
        foreach ($views as $key => $value) {
            $tmp = array('Name' => $value['TYPE'], 'Format' => $value['FORMAT']);
            $views_list[$value['OBJECT']][] = $tmp;
        }

        return $views_list;
    }

    // Return all templates available
    public function getViewTypeList(){
        $table = DB_PREFIX.'Views';
        $field = array('TYPE');

        try{
            $view_type =  $this->driver->selectData($table, $field);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1012);
        }

        if(empty($view_type) || !is_array($view_type))
            return NULL;

        $types = array();
        foreach ($view_type as $key => $value) {
            $types[] = $value['TYPE'];
        }

        return array_unique($types);
    }

    // Retrieve the template of object, view, format
    public function getTemplate($objectType, $type, $format){
        
        $table = DB_PREFIX.'Views';
        $field = 'RAW';
        $where = array('OBJECT' => $objectType,
                        'TYPE' => $type,
                        'FORMAT' => $format);
        $count = 1;

        try{
            $tmp = $this->driver->selectData($table, $field, $where, $count);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1052);
        }

        if(!is_array($tmp) || empty($tmp[0]['RAW']) || $tmp[0]['RAW'] === "NULL")
            return null;

        return $tmp[0]['RAW'];

    }

    // Add a new view for an object/view/format
    public function addView($objectType, $type, $format, $raw){
        
        $req_params = array('OBJECT' => $objectType,
                            'TYPE' => $type,
                            'FORMAT' => $format,
                            'RAW' => $raw);
        try{
            return $this->driver->insertData(DB_PREFIX.'Views', $req_params, TRUE);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1053);
        }

    }

    // Update a specific view of object where view/format
    public function updateView($objectType, $type, $format, $raw){
        $table = DB_PREFIX.'Views';
        $where = array('OBJECT' => $objectType,
                        'TYPE' => $type,
                        'FORMAT' => $format);

        $req_params = array('RAW' => $raw);

        try{
            return $this->driver->updateData($table, $req_params, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1054);
        }

    }

    // Rename a specific view
    public function renameView($objectType, $oldViewName, $newViewName, $format){
        $table = DB_PREFIX.'Views';
        $where = array('OBJECT' => $objectType,
                        'TYPE' => $oldViewName,
                        'FORMAT' => $format);
        $req_params = array('TYPE' => $newViewName);

        try{
            return $this->driver->updateData($table, $req_params, $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1055);
        }
    }

    // Delete a view
    public function deleteView($objectType, $type, $format){
        
        $where = array('OBJECT' => $objectType,
                        'TYPE' => $type,
                        'FORMAT' => $format);

        try{
            return $this->driver->deleteData(DB_PREFIX.'Views', $where);
        }catch(Exception $e){
            throw new LxrException($e->getMessage(),1056);
        }

    }
}

?>