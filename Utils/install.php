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

require_once (__ROOT__ . 'Utils/LXR_Installer.php');

$bot = new Installer();
$options = array();

echo "_____________________________________________________\n";
echo "\n\t3l1x1R v" . __VERSION__ . " Installer process...\n";
echo "_____________________________________________________\n\n";

// Import extended exception
require_once (__ROOT__ . 'Utils/LXR_Exceptions.php');

echo "[1/3] Configuring database.\n";

// Configuration process for DB
while(True){
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
        $options['user'] = $bot->ask('Database user [root]','test');
        $options['password'] = $bot->ask('Database password','testPass');
        $options['bdd'] = $bot->ask('Database name [test]','photos');
    }

    echo "[+] Checking database connection, please validate configuration:\n";

    echo "\t Database type:\t $db_type\n";
    foreach ($options as $key => $value) {
        echo "\t $key\t\t-> $value \n";
    }

    $confirm = $bot->ask('Confirm this configuration [Y/n]','Y',['y','Y','n','N']);
    if (strtolower($confirm) == 'y'){
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

if ($bot->found){

    $rest = $bot->ask('Tables founds do you like to RESTIFY them [Y/n]','y',['y','Y','n','N']);
    $prefix = $bot->get_prefix();
    // If we want to restify databases... should not encode data
    if (strtolower($rest) == 'y'){
        $prefix = $bot->ask("Set table prefix [$prefix]",$prefix,[$prefix,'LXR_','_']);
        $encoding = 'False';
    }
    // If we create brand new elixir instance
    else{
        $encoding = 'True';
    }
}

// Write config and db_config files
$bot->write_config($db_type, $options, $encoding, $prefix);

// Import LXR configuration
require_once (__ROOT__ . 'Config/config.php');

// Import db configuration
require_once (__ROOT__ . 'Config/db_config.php');

echo "\n_____________________________________________________\n\n";
echo "[2/3] Configuring web server.\n";
$host = $bot->ask('Set hostname on which LXR will be listening to [localhost]','localhost');
$server = $bot->ask('What is your web server [apache]','apache',['apache','nginx']);
$htaccess = $bot->ask('Do you use htaccess or vhost [htaccess]','htaccess',['htaccess','vhost']);

echo "\n_____________________________________________________\n\n";
echo "[3/3] Creating database and website...\n";
echo "[+] Creating database tables with '$prefix' prefix.\n";
$bot->install();

echo "[+] Creating 3l1x1R website...\n";
if (strtolower($htaccess) == 'vhost'){
    $https = $bot->ask('Do you use HTTPS [Y/n]','y',['y','Y','n','N']);
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