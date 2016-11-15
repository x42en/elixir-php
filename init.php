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


define('__ROOT__', "./");

// Parse and load existing classes
spl_autoload_register('apiAutoload');
function apiAutoload($classname) {
    if (preg_match('/[a-zA-Z]+_Model$/', $classname) && file_exists(__ROOT__ . 'Models/' . $classname . '.php')) {
        include __ROOT__ . 'Models/' . $classname . '.php';
        return TRUE;
    } 
    else if (preg_match('/[a-zA-Z]+_View$/', $classname) && file_exists(__ROOT__ . 'Views/' . $classname . '.php')) {
        include __ROOT__ . 'Views/' . $classname . '.php';
        return TRUE;
    } 
    else if (preg_match('/[a-zA-Z]+_Controller$/', $classname) && file_exists(__ROOT__ . 'Controllers/' . $classname . '.php')) {
        include __ROOT__ . 'Controllers/' . $classname . '.php';
        return TRUE;
    }
    else if (preg_match('/[a-zA-Z]+_Cleaner$/', $classname) && file_exists(__ROOT__ . 'Cleaners/' . $classname . '.php')) {
        include __ROOT__ . 'Cleaners/' . $classname . '.php';
        return TRUE;
    }
}

// Import extended exception
require_once (__ROOT__ . 'Utils/LXR_Exceptions.php');

// Import LXR configuration
require_once (__ROOT__ . 'Config/config.php');

// Import db configuration
require_once (__ROOT__ . 'Config/db_config.php');

// Import various functions
require_once (__ROOT__ . 'Utils/LXR_functions.php');

// Import composer functions
require_once (__ROOT__ . 'vendor/autoload.php');

// Initialize logger if log isset
if(defined('LOG_DIR') && !empty(LOG_DIR)){
    try{
        $logger = new Katzgrau\KLogger\Logger(LOG_DIR, LOG_LEVEL);
    }
    catch(RuntimeException $e) {
        die("Unable to write in log directory: ".LOG_DIR." !!\n");
    }
}
else{
    $logger = NULL;
}

// Import the request class
require_once (__ROOT__ . 'Class/Router.php');
// Import the printer class
require_once (__ROOT__ . 'Views/Printer.php');

?>