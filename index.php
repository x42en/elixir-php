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

require_once(__ROOT__ . 'init.php');

// Initialize Request
$router = new Router();

try{    
    // Parse the request and retrieve controller
    $controller_name = $router->parse($_SERVER);
}catch(Exception $err){
    // Initialize printing machine
    $output = new Printer('json');
    $output->error('System', $err);
    exit();
}

// Initialize printing machine
$output = new Printer($router->format);

try{
    // Define the correct action from controller
    $controller = new $controller_name($db_mode, $db_config);
    $action_name = strtolower($router->verb) . 'Action';
    
    // Execute the action
    $result = $controller->$action_name($router);

    // Set template if needed
    if(!empty($router->view) && $router->format === 'html') $output->setTemplate($router->view);
    
    // Render result
    $output->success($router->type, $result);

}catch(Exception $err){
    // Output error
    $output->error($router->type, $err);
}

?>