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

require_once (__ROOT__ . 'Drivers/MYSQL_Driver.php');
require_once (__ROOT__ . 'Drivers/FILE_Driver.php');

Class DB_Manager{

    protected $driver;

    function __construct($type, $param){

        $db = strtoupper($type).'_Driver';

        if(class_exists($db)){
            // Initialize the proper driver
            $this->driver = new $db($param);

            try{
                $this->driver->loadData();
            }catch(Exception $e){
                if($e->getMessage() === "Database is empty.")
                    $this->initialize();
                else
                    throw new LxrException($e->getMessage(),1000);
            }
        }
        else{
            throw new LxrException('Unsupported Database...', 911);
        }
        
    }

    // Allow optional encoding
    protected function encode_data($str){
        if(ENCODING) return base64_encode($str);
        else return $str;
        
    }

    // Allow optional decoding
    protected function decode_data($str){
        if(ENCODING) return base64_decode($str);
        else return $str;
    }

}

?>