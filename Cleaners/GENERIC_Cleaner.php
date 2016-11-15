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


class GENERIC_Cleaner
{
	protected $error_base;
	protected $system_object;
	protected $type;

	public function __construct($type){
		
		// Define forbiden object name
		$this->system_object = array("FIELD", "STRUCT", "VIEW", "FLAG", "USER", "ERROR", "CLASS", "CONFIG", "CONTROLLERS","DRIVERS","MODELS","UTILS","VIEWS","CLEANERS");

		$this->type = $type;
		
	}

	protected function exception_error_handler($errno, $errstr, $errfile, $errline) {
        $this->_regex_has_errors = TRUE;
    }
    
    protected function validate($regex) {
        
        $this->_regex_has_errors = FALSE;
        set_error_handler(array($this, "exception_error_handler"));
        preg_match($regex, "");
        restore_error_handler();
        if ($this->_regex_has_errors) return FALSE;
        else return TRUE;
    }

}

?>