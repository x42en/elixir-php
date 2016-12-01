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

require_once (__ROOT__ . 'Managers/Install_Manager.php');

Class Installer{
    
    private $connector;
    private $fields;
    private $structs;
    private $is_root;

    public $found;

    public function __construct(){
        $this->is_root = (0 == posix_getuid()) ? True : False;
        if(!$this->is_root){
            echo "##########################   WARNING !!   ############################\n";
            echo "[!] You're not root !!\n[!] Your web server config files won't be written to disk...\n";
            echo "#######################################################################\n\n";
        }
    }


    // Check db connection
    public function db_connect($type, $params){
        $db = strtoupper($type).'_Driver';
        
        $this->db_type = $type;
        $this->db_params = $params;

        $this->fields = array();
        $this->structs = array();

        if(class_exists($db)){
            // Initialize the proper driver
            $this->driver = new $db($params);

            try{
                $this->driver->loadData();
                // If some data are already set
                $this->detect();
            }catch(Exception $e){
                if($e->getMessage() === "Database is empty.")
                    $this->found = False;
                else
                    throw new LxrException($e->getMessage(),1000);
            }
        }
        else{
            throw new LxrException('Unsupported Database...', 911);
        }

    }

    // Return common prefix chars found
    public function get_prefix(){
        $similar = True;
        $i = 0;
        while($similar){
            // Set found char on $i char of first table name
            $found = $this->tables[0][$i];
            
            foreach ( $this->tables as $key => $value) {
                if ($value[$i] == $found) continue;
                // If one char is different break loop
                $similar = False;
                break;
            }
            // Check next char
            $i++;
        }
    
        $prefix = substr($this->tables[0], 0, $i-1);
        
        return $prefix;
    
    }

    // Detect if data are set in database
    private function detect(){
        echo "[+] Detecting database data...\n";
        $this->tables = $this->driver->getTables();
        foreach ( $this->tables as $indice => $table) {
            $this->found = True;
            $this->build_lxr_struct($table);
        }
    }

    // Initialize LXR structure datatabase
    public function install(){
        

        // First create LXR structure
        $connector = new Install_Manager($this->db_type,$this->db_params);

        try{
            // Build LXR struct
            $connector->initialize();
        }catch (Exception $err){
            throw new LxrException($err->getMessage(), $err->getCode());
        }
        // Second populate fields
        foreach ($this->fields as $name => $regex) {
            echo "\t - Insert field $name ($regex)\n";
            try{
                $connector->newField($name, $regex, 'Auto generated');
            }catch(Exception $e){
                echo "\n[!] Error ".$e->getMessage();
            }
            
        }

        // Third populate structures
        foreach ($this->structs as $name => $options) {
            echo "\t - Insert structure $name \n";
            try{
                $connector->newStruct($name, 'Auto generated', $options['STRUCT']);
            }catch(Exception $e){
                echo "\n[!] Error ".$e->getMessage();
            }
        }
    }

    private function startsWith($haystack, $needle)
    {
         $length = strlen($needle);
         return (substr($haystack, 0, $length) === $needle);
    }

    private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    // Auto-Adapt LXR structure to existing data
    private function build_lxr_struct($table){
        $struct = $this->driver->getStruct($table);
        // Detect if we are dealing with elixir tables
        if(strpos($table, "LXR_") === 0) return False;
        // Detect if table is already in elixir state
        if(array_key_exists('RW_ACCESS', $struct)) return False;

        // If the table is not in Elixir format
        // Store table structure
        echo "\t - NEW structure $table...\n";
        $field = array();

        // Parse table to get struct
        foreach ($struct as $name => $options) {
            // Add table name to field (good idea ?)
            $name = $table . '_' . $name;

            // Add field if necessary
            if(!in_array($name, array_keys($this->fields))){
                
                $field_type = strtolower($options['Type']);
                
                if($this->startsWith($field_type, 'varchar')){
                    preg_match('#\((.*?)\)#', $options['Type'], $match);
                    $max = $match[1];
                    $regex = "~^(.){1,$max}~u";
                }
                else if($this->startsWith($field_type, 'int')){
                    preg_match('#\((.*?)\)#', $options['Type'], $match);
                    $max = $match[1];
                    $regex = "~^([0-9]){1,$max}$~";
                }
                else if($field_type == 'date'){
                    $regex = "~^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])?$~";
                }
                else if($field_type == 'datetime'){
                    $regex = "~^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])(?:( [0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$~";
                }
                else if($field_type == 'timestamp'){
                    $regex = "~^(((\d{4})(-)(0[13578]|10|12)(-)(0[1-9]|[12][0-9]|3[01]))|((\d{4})(-)(0[469]|1‌​1)(-)([0][1-9]|[12][0-9]|30))|((\d{4})(-)(02)(-)(0[1-9]|1[0-9]|2[0-8]))|(([02468]‌​[048]00)(-)(02)(-)(29))|(([13579][26]00)(-)(02)(-)(29))|(([0-9][0-9][0][48])(-)(0‌​2)(-)(29))|(([0-9][0-9][2468][048])(-)(02)(-)(29))|(([0-9][0-9][13579][26])(-)(02‌​)(-)(29)))(\s([0-1][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9]))$~";
                }
                
                // Store field regex
                $this->fields[$name] = $regex;

            }else{
                $regex = $this->fields[$name];
            }

            if(strlen($regex) > 8) $regex_short = substr($regex, 0, 7).' ...';
            else $regex_short = $regex;
            
            echo "\t\t - Field $name ($regex_short)\n";
            // Set field structure
            $field[$name]['type'] = 'field';
            $field[$name]['required'] = ($options['Null'] == 'NO') ? True : False;
            $field[$name]['primary'] = ($options['Key'] == 'PRI') ? True : False;
            $field[$name]['increment'] = ($options['Extra'] == 'auto_increment') ? True : False;

        }

        $this->structs[$table] = array();
        $this->structs[$table]['NAME'] = $table;
        $this->structs[$table]['STRUCT'] = $field;

        return True;
    }

    public function write_config($db_type, $params, $encoding=False, $prefix='_'){
        $config = './Config/config.php';
        $db_config = './Config/db_config.php';

        $host = '$db_config[\'host\'] = "' . $params['host'] . '"';
        $port = '$db_config[\'port\'] = "' . $params['port'] . '"';
        $user = '$db_config[\'user\'] = "' . $params['user'] . '"';
        $pwd = '$db_config[\'password\'] = "' . $params['password'] . '"';
        $bdd = '$db_config[\'bdd\'] = "' . $params['bdd'] . '"';
                    
        $db_conf = "<?php\n//Set the current config\n$host;\n$port;\n$user;\n$pwd;\n$bdd;\n\$db_mode = 'mysql';\n?>";

        
        // Rewrite all db_config file
        $handle = fopen($db_config, "w") or die("Unable to write to file $db_config!");
        
        if (fwrite($handle, $db_conf) === FALSE) {
            echo "\n[!]Unable to write in file ($db_config)\n";
            exit;
        }else{
            echo "[+] Database config wrote to $db_config\n";
        }

        $conf = "\n\n// Define if data should be base64 encoded in database\ndefine('ENCODING', ".$encoding.");\n// Define global debug state (will return useful informations in errors if active)\ndefine('DEBUG_STATE', False);\n\n// Set global var\ndefine('DB_PREFIX', 'LXR_');\ndefine('USER_PREFIX', '".$prefix."');\n?>";

        // Backup previous config
        $previous_conf = file_get_contents($config);
        // Append options to conf file
        $handle = fopen($config, "w") or die("Unable to write to file $config!");
        
        // If some config has already been done
        if(strpos($previous_conf, '?>') !== FALSE){
            str_replace('?>', $conf, $previous_conf);
            $to_write = $previous_conf;
        }
        // If this is first time
        else{
            $to_write = $previous_conf . "\n" . $conf;
        }
        if (fwrite($handle, $to_write) === FALSE) {
            echo "\n[!]Unable to write in file ($config)\n";
            exit;
        }else{
            echo "[+] Application config wrote to $config\n";
        }

        return True;
    }

    public function write_vhost($server, $host, $https=False){
        $port = ( $https == True ? 443 : 80 );
        if($server == 'nginx'){
            $vhost = <<<EOD
server {
    listen $port;

    server_name $host;

    root /var/www/$host;
    
    location / {
        rewrite ^(.*) /run.php?$1;
    }

    # Does not need any icon
    location = /favicon.ico { 
        access_log off; 
        log_not_found off; 
    }

    # serve static files directly
    location ~* ^.+.(jpg|jpeg|gif|css|png|js|ico|html|xml|txt)$ {
        access_log        off;
        expires           max;
    }

    # Prevent nginx from serving any hidden file
    location ~ /\. { access_log off; log_not_found off; deny all; }

    # parse php with config set and proper rewrite rules
    location ~ \.php$ {
        add_header Access-Control-Allow-Origin *;
        add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD';
        add_header 'Access-Control-Allow-Headers' 'DNT,X-CustomHeader,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type';
        include snippets/fastcgi-php.conf;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;

        # With php5-fpm:
        fastcgi_pass unix:/run/php/php7.0-fpm.sock;
     }

}
EOD;
        }
        else{
            $vhost = <<<EOD
<VirtualHost *:80>
        ServerAdmin admin@webboards.fr

        DocumentRoot /var/www/lxr
        ServerName $host
        
        <Directory />
                AllowOverride All
                <IfModule mod_headers.c>
                    SetEnvIf Origin (.*) AccessControlAllowOrigin=$1
                    Header add Access-Control-Allow-Origin %{AccessControlAllowOrigin}e env=AccessControlAllowOrigin

                    Header add Access-Control-Allow-Methods 'POST, GET, OPTIONS, DELETE, PUT'
                    Header add Access-Control-Max-Age '1000'
                    Header add Access-Control-Allow-Headers 'Accept-Encoding, Referer, x-requested-with, Content-Type, origin, authorization, accept, x-file-type, x-file-size, x-file-name'
                </IfModule>

                <IfModule mod_rewrite.c>
                    RewriteEngine On

                    RewriteRule ^(Class/|Cleaners/|Config/|Controllers/|Drivers/|Models/|Utils/|Views/|router\.php|\.htaccess) - [R=404,L,NC]

                    RewriteCond %{REQUEST_FILENAME} !-f
                    RewriteCond %{REQUEST_FILENAME} !-d
                    RewriteRule ^(.*)$ run.php/$1 [QSA,L]

                    ErrorDocument 404 404.html
                </IfModule>
        </Directory>
        <Directory /var/www/lxr/>
                AllowOverride All
                Order allow,deny
                allow from all
        </Directory>

        ErrorLog /var/log/elixir/error.log

        LogLevel warn

        CustomLog /var/log/elixir/access.log combined
</VirtualHost>
EOD;
        }

        $fname = '/etc/'.$server.'/site-available/'.$host;

        if($this->is_root){
            if(file_exists($fname)){
                $rewrite = $this->ask('An homonym vhost already exists, rewrite it [Y/n]','y',['y','Y','n','N']);
                if ($rewrite){
                    $handle = fopen($fname, "w") or die("\n[!] Unable to append to file $fname!");
                }
                else{
                    $handle = fopen($fname, "a") or die("\n[!] Unable to append to file $fname!");
                }
                    
            }
            else{
                $handle = fopen($fname, "w") or die("\n[!] Unable to write to file $fname!");
            }

            if (fwrite($handle, $vhost) === FALSE) {
                echo "[!] Unable to write in file ($fname)";
                exit;
            }else{
                echo "[+] Vhost config wrote to $fname\n";
                echo "[!] Activate your website by typing:\n\t#> ln -s $fname /etc/$server/site-enabled";
            }
        }
        else{
            echo "[+] I should have wrote to $fname :\n";
            echo $vhost;
            echo "\n\n";
        }
            

    }

    public function write_htaccess($server, $host){
        if($server == 'apache'){
            $config = <<<EOD
<IfModule mod_headers.c>
    SetEnvIf Origin (.*) AccessControlAllowOrigin=$1
    Header add Access-Control-Allow-Origin %{AccessControlAllowOrigin}e env=AccessControlAllowOrigin

    Header add Access-Control-Allow-Methods 'POST, GET, OPTIONS, DELETE, PUT'
    Header add Access-Control-Max-Age '1000'
    Header add Access-Control-Allow-Headers 'Accept-Encoding, Referer, x-requested-with, Content-Type, origin, authorization, accept, x-file-type, x-file-size, x-file-name'
</IfModule>

<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteRule ^(Class/|Cleaners/|Config/|Controllers/|Drivers/|Models/|Utils/|Views/|router\.php|\.htaccess) - [R=404,L,NC]

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ run.php/$1 [QSA,L]

    ErrorDocument 404 404.html
</IfModule>
EOD;
        }
        else{
            $config = <<<EOD
add_header Access-Control-Allow-Origin *;
add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD';
add_header 'Access-Control-Allow-Headers' 'DNT,X-CustomHeader,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type';
rewrite ^(.*)$ /run.php?$1;
EOD;
        }       

        $fname = '.htaccess';

        if($this->is_root){
            if(file_exists($fname)){
                $rewrite = $this->ask('An .htaccess already exists, rewrite it [Y/n]','y',['y','Y','n','N']);
                if ($rewrite){
                    $handle = fopen($fname, "w") or die("\n[!] Unable to append to file $fname!");
                }
                else{
                    $handle = fopen($fname, "a") or die("\n[!] Unable to append to file $fname!");
                }
            }
            else{
                $handle = fopen($fname, "a") or die("\n[!] Unable to write to file .htaccess!");
            }

            if (fwrite($handle, $config) === FALSE) {
                echo "[!] Unable to write in file ($fname)";
                exit;
            }else{
                echo "[+] .htaccess config wrote to $fname\n";
                echo "[!] Activate your website by typing:\n\t#> ln -s $fname /etc/$server/site-enabled\n";
            }
        }
        else{
            echo "[+] I should have wrote to $fname (but I'm not root) :\n\n";
            echo $config;
            echo "\n\n"; 
        }
            
    }

    // Ask question to user
    public function ask($question, $def_answer, $answers=null){
        while (True) {
            $resp = null;
            echo '[?] '.$question.': ';
            $resp = trim(fgets(STDIN));

            // Set default anwser is user type enter only
            if ($resp == ''){
                $resp = $def_answer;
            }

            // If no restriction is set, accept answer
            if ($answers == null){
                $result = $resp;
                break;
            }

            else if (in_array($resp, $answers)){
                $result = $resp;
                break;
            }
        }
        
        return $result;
    }

}

?>