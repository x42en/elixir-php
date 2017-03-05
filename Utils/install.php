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
define('__VERSION__','0.1.0');

// Import various functions
require_once (__ROOT__ . 'Utils/LXR_functions.php');

require_once (__ROOT__ . 'Utils/LXR_Installer.php');

$options = array();

echo "_____________________________________________________\n";
echo "\n\t3l1x1R v" . __VERSION__ . " Installer process...\n";
echo "_____________________________________________________\n\n";

$bot = new Installer();

// Import extended exception
require_once (__ROOT__ . 'Utils/LXR_Exceptions.php');

echo "[1/3] Configuring database.\n";
$write = TRUE;

// If a config already exists
if(file_exists(__ROOT__ . 'Config/db_config.php') && file_exists(__ROOT__ . 'Config/config.php')){
    $confirm = $bot->ask('Config file exists, overwrite it [y/N]','n',['y','n']);
    if($confirm == 'y') $write = TRUE;
    else $write = FALSE;
}

// If config need to be overwritten
if ($write == 'y'){

    // Configuration process for DB
    while(TRUE){
        $db_type = $bot->ask('Database type [MySQL]','mysql', ['mysql','mongo','postgres','file']);

        if ($db_type == 'file'){
            $options['filename'] = $bot->ask('Database file name [elixir.lxr]','elixir.lxr');
        }
        else{
            // Adapt default port
            switch ($db_type) {
                case 'mysql':
                    $options['port'] = 3306;
                    break;
                
                case 'mongo':
                    $options['port'] = 27017;
                    break;
                
                case 'postgres':
                    $options['port'] = 5432;
                    break;
                
                default:
                    # code...
                    break;
            }
            
            $options['host'] = $bot->ask('Database host [localhost]','localhost');
            $options['user'] = $bot->ask('Database user [root]','root');
            $options['password'] = $bot->ask('Database password','');
            $options['bdd'] = $bot->ask('Database name [test]','test');
        }

        echo "[+] Checking database connection, please validate configuration:\n";

        echo "\t Database type:\t $db_type\n";
        foreach ($options as $key => $value) {
            if($key == 'password') echo "\t $key\t-> $value \n";
            else echo "\t $key\t\t-> $value \n";
        }

        $confirm = $bot->ask('Confirm this configuration [Y/n]','y',['y','n']);
        if ($confirm == 'y'){
            try{
                $bot->db_connect($db_type, $options);
                break;
            }catch(Exception $e){
                echo "\n[!] Unable to connect, restart configuration process...\n\n";
            }
            
        }else{
            echo "\n[!] Restarting configuration process...\n\n";
        }
    }

    // Pre-Write files
    $bot->write_config($db_type, $options, FALSE, null);
}

// Import LXR configuration
require_once (__ROOT__ . 'Config/config.php');

// Import db configuration
require_once (__ROOT__ . 'Config/db_config.php');

// Import correct format
require_once (__ROOT__ . 'Utils/LXR_formats.php');

// Connect bot if needed
if(!$bot->isConnected()){
    try{
        $bot->db_connect($db_mode, $db_config);
    }catch(Exception $e){
        echo "\n[!] Unable to connect, check db_config file...\n\n";
        echo "[!] ".$e->getMessage();
        exit;
    }
}

$bot->detect();
// Check if LXR Tables exists
$prefix = $bot->get_prefix();

if ($bot->found){

    $rest = $bot->ask('Tables found, do you like to RESTIFY them [Y/n]','y',['y','n']);
    
    // If we want to restify databases... should not encode data
    if ($rest == 'y') $encoding = 'FALSE';
    // If we create brand new elixir instance
    else $encoding = 'True';
}else{
    echo "\n[!] No table found, or all are restified...\n\n";
    $encoding = ENCODING;
}

// Write final config and final db_config files
$bot->write_config($db_mode, $db_config, $encoding, $prefix);

echo "\n_____________________________________________________\n\n";
echo "[2/3] Configuring web server.\n";
$host = $bot->ask('Set hostname on which LXR will be listening to [localhost]','localhost');
$server = $bot->ask('What is your web server [apache]','apache',['apache','nginx']);
$htaccess = $bot->ask('Do you use htaccess or vhost [htaccess]','htaccess',['htaccess','vhost']);

echo "\n_____________________________________________________\n\n";
echo "[3/3] Creating database and website...\n";
echo "[+] Creating database tables with '$prefix' prefix.\n";
try{
    $bot->install();
}catch(Exception $err){
    echo $err;
    die();
}

echo "\n[+] Creating 3l1x1R website...\n";
if ($htaccess == 'vhost'){
    $https = $bot->ask('Do you use HTTPS [Y/n]','y',['y','n']);
    $bot->write_vhost($server, $host, $https);
}
else{
    $bot->write_htaccess($server, $host);
}


echo "[+] Done !!\n\n";

echo "[+] Check http://$host/field -> will give you the fields list\n";
echo "[+] Check http://$host/struct -> will give you the structures list\n";
echo "[+] Check http://$host/object -> will give you the objects list\n";

echo "[+] Happy Coding !! ;) \n\n";
?>