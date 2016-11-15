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

// Import the CerealDB file management
require_once ( __ROOT__ . 'Utils/cerealDB.php');

Class FILE_DB{

	private $is_initialized;
	private $dir;
	private $filename;

	function __construct($param){
		if (!is_dir($param['dir'])) {
			$this->initialize();
		}

	}

	public function isInitialized(){
		return $this->is_initialized;
	}

	public function initialize(){
		// Parsing of all directory specified
		$directory = split(DIRECTORY_SEPARATOR, $this->dir);

		// Init the created_path
		if(OS_NAME == "win"){
			$created_path = "C:\\";
		}
		else{
			$created_path = "";
		}
		

		// Recursivly search for directory of path
		foreach ($directory as $dir) {
			// Construct the created path
			$created_path = $created_path.DIRECTORY_SEPARATOR.$dir;
			
			// If directory does not exists create it
			if (!is_dir($created_path)) 
				mkdir($created_path);

		}

		$this->is_initialized = True;
		
	}

}