<?php

require('vendor/autoload.php');
define('__ROOT_URL__','http://elixir.lxr');

// Run the tests:
//# php vendor/bin/phpunit FieldTest.php

// Process:
// List objects (should return an empty list)
// POST field -> True
// GET field -> check object
// PUT field -> check object
// DELETE field -> ok

// Try to get unknown object -> get error
// Try to set invalid field -> get error
// Try to set invalid regex -> get error
// Try to set invalid description -> no error (desc empty)
// Try to modify unexisting field -> get error
// Try to modify system field -> get error
// Try to update invalid field -> get error
// Try to update invalid regex -> get error
// Try to update invalid description -> no error (same desc)

$types = array('field','struct','error','user','object','view','flag');

foreach ($types as $value) {
	$fname = ucfirst($value) . 'Test.php';
	if(file_exists($fname)){
		echo "[+] Testing $value...\n";
		require_once($fname);
	}
}