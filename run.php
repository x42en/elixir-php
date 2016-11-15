<?php

require_once('./init.php');

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