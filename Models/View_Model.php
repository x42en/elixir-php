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


class View_Model extends Struct_Model
{
    
    // Instantiation of local variable
    private $view_list;
    private $system_type;
    private $system_viewType;
    private $allowed_format;
    
    function __construct($db_mode, $db_config) {
        try{
            parent::__construct($db_mode, $db_config);
            // Retrieve view list for futur operations
            $this->view_list = $this->lxr->getViewList();
        }
        catch(Exception $err) {
            throw $err;
        }

    }

    public function hasView(){
    	return !empty($this->view_list);
    }
    
    public function getViewList($count = 0) {
        if ($count > 0) $this->result = array_slice($this->view_list, 0, $count);
        else $this->result = $this->view_list;

        return $this->result;
    }
    
    public function getViewByType($objectType, $count = 0) {
        
        if (empty($this->view_list[$objectType])){
        	$this->error = 4501;
            $this->message = 'Invalid view type';
            return FALSE;
        }
        
        if ($count > 0) $this->result[$objectType] = array_slice($this->view_list[$objectType], 0, $count);
        else $this->result[$objectType] = $this->view_list[$objectType];
        
        return $this->result;
    }
    
    public function getView($objectType, $viewType, $format, $tocreate = null) {
        try {
            $raw = $this->lxr->getTemplate($objectType, $viewType, $format);
        }
        catch(Exception $err) {
            throw $err;
        }
        
        if (empty($raw)) $this->result = NULL;
        
        if (!empty($tocreate) && array_key_exists($tocreate, $this->structure_list)) {
            $this->result['OBJECT'] = $tocreate;
            $this->result['TYPE'] = $viewType;
            $this->result['CODE'] = htmlentities(stripslashes(base64_decode($raw)));
        } 
        else {
            $this->result['OBJECT'] = $objectType;
            $this->result['TYPE'] = $viewType;
            $this->result['VIEW'] = $this->lxr->getViewTypeList();
            $this->result['CODE'] = htmlentities(stripslashes(base64_decode($raw)));
        }
        
        $this->result['FORMAT'] = $format;
        
        return $this->result;
    }
    
    public function newView($objectType, $viewType, $format, $raw = null) {
        try {
            $this->result['ID'] = $this->lxr->addView($objectType, $viewType, $format, base64_encode($raw));
        }
        catch(Exception $err) {
            throw $err;
        }
        
        return $this->result;
    }
    
    public function renameView($objectType, $oldViewName, $newViewName, $format) {
        try {
            $this->result = $this->lxr->renameView($objectType, $oldViewName, $newViewName, $format);
        }
        catch(Exception $err) {
            throw $err;
        }
        
        return $this->result;
    }
    
    public function updateView($objectType, $viewType, $format, $raw = null) {
        try {
            $this->lxr->updateView($objectType, $viewType, $format, base64_encode($raw));
        }
        catch(Exception $err) {
            throw $err;
        }

        $this->result['ID'] = $objectType.'/'.$viewType;
        
        return $this->result;
    }
    
    public function deleteView($objectType, $viewType, $format) {
        try {
            $this->result = $this->lxr->deleteView($objectType, $viewType, $format);
        }
        catch(Exception $err) {
            throw $err;
        }
        
        return $this->result;
    }
    
    public function viewExists($objectType) {
        if (empty($this->view_list) || empty($this->view_list[$objectType])) return FALSE;
        else return TRUE;
    }
    
    public function thisViewExists($objectType, $viewType, $format) {
        if (!$this->viewExists($objectType)) return FALSE;
        if (in_array(array("Name" => $viewType, "Format" => $format), $this->view_list[$objectType])) return TRUE;
        else return FALSE;
    }
}
?>