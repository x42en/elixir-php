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

class Router{
    private $private_object;
    private $allowed_methods;
    private $allowed_format;
    private $controller_name;

    public $format;
    public $verb;
    public $type;
    public $ressource;
    public $data;
    public $flags;
    public $recursive;
    public $view;

    public $selector;
    public $count;
    public $offset;
    public $sort;
    public $infos;
    public $lang;
    
    public function __construct(){
        $this->allowed_methods = array('get', 'post', 'put', 'patch', 'delete', 'options');
        $this->private_object = array('Field', 'Struct', 'View', 'Object', 'Error', 'User', 'Flag');
        $this->allowed_format = array('json', 'html', 'txt', 'xml');

        $this->controller_name = NULL;
        $this->format =  DEFAULT_FORMAT;
        $this->verb = NULL;
        $this->type = NULL;
        $this->ressource = NULL;
        $this->flags = array();
        $this->recursive = FALSE;
        $this->view = NULL;

        $this->selector = NULL;
        $this->count = 0;
        $this->offset = 0;
        $this->sort = NULL;
        $this->infos = array();
        $this->data = NULL;
        $this->lang = DEFAULT_LANG;
        // $this->id = NULL;
    }

    // Parse current request
    public function parse($server){
        
        // Get action requested
        $this->verb = strtolower($server['REQUEST_METHOD']);
        if(empty($this->verb)) throw new Exception('Empty request verb.', 911);
        if(!in_array($this->verb, $this->allowed_methods)) throw new Exception('Invalid request verb.', 912);

        // Avoid OPTIONS or HEAD request
        if($this->verb === 'options') $this->verb = 'get';
        
        // Auto correct equivalent actions
        if($this->verb === 'patch') $this->verb = 'put';

        // Parse url requested
        $path = $server['REQUEST_URI'];
        $uri = 'http://' . $server['HTTP_HOST'] . $path;
        $raw = parse_url($uri);
        
        if(empty($raw)) throw new LxrException('Unable to parse request', 913);

        if(empty($raw['path'])) throw new LxrException('Empty Request', 914);

        $this->infos = explode('/', substr($raw['path'], 1));

        if(empty($this->infos[0])) throw new LxrException("Welcome !", 1);

        try{
            $this->getLang();
            $this->getType();
            $this->getRessource();
            $this->getFlags();
            $this->getData();

            // Get specific LXR params
            if(!empty($raw['query'])){
                $this->getOptionals($raw['query']);
            }
        }catch(Exception $err){
            throw $err;
        }

        // If type not set, we're dealing with object
        if(!in_array($this->type, $this->private_object)) $this->type = 'Object';
        $this->controller_name = $this->type . '_Controller';

        return $this->controller_name;

    }

    private function getLang(){
        if(empty($this->infos[0])) throw new LxrException("Error Processing Request", 915);
        
        // If lang is set
        if(strlen($this->infos[0]) == 2){
            $this->lang = strtolower($this->infos[0]);
            // Remove it from url
            array_splice($this->infos, 0, 1);
        }
    }

    private function getType(){
        if(empty($this->infos[0])) throw new LxrException("Error Processing Request", 916);

        // Get Type
        $this->type = ucfirst(strtolower($this->infos[0]));
        // Remove it from url
        array_splice($this->infos, 0, 1);
    }

    private function getRessource(){
        if(empty($this->infos)) return TRUE;

        // Get Ressource
        $this->ressource = $this->infos[0];
        if (isValidID($this->ressource)) $this->id = $this->ressource;
        // Remove it from url
        array_splice($this->infos, 0, 1);
    }

    private function getFlags(){
        if(empty($this->infos)) return TRUE;

        // Get flags
        foreach ($this->infos as $value) {
            // Avoid double slash troubles ;)
            if(empty($value)) continue;
            // Set recursive flag if needed
            if($value === '@'){
                $this->recursive = TRUE;
                continue;
            }
            // Catch ID if needed and exit, as no flags are necessary for an ID
            // if(isValidID($value)){
            //     $this->id = $value;
            //     break;
            // }
            $this->flags[] = $value;
        }
        
    }

    // Initialize query parameters
    private function getOptionals($query){
        parse_str($query, $params);
        $params = array_change_key_case($params);
        
        if(!empty($params['_format']) && in_array(strtolower($params['_format']), $this->allowed_format)) $this->format = strtolower($params['_format']);

        // if(!empty($params['_select'])) $this->selector = json_decode(base64_decode($params['_select']), TRUE);
        if(!empty($params['_count'])) $this->count = intval($param['_count']);
        if(!empty($params['_offset'])) $this->offset = intval($param['_offset']);
        if(!empty($params['_sort'])) $this->sort = $params['_sort'];

        // Parse each parameters set
        foreach ($params as $field_name => $value) {
            // Do not deal with system parameters
            if($field_name[0] == '_') continue;

            // Store field selection
            $this->selector[$field_name] = base64_decode($value);
        }
    }

    // Retrieve data from request
    private function getData(){
        // first of all, pull the GET vars
        if (isset($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $this->data);
        }
        
        // Dealing with PUT/POST bodies override what we got from GET
        $body = file_get_contents("php://input");
        
        $content_type = FALSE;
        
        if (isset($_SERVER['CONTENT_TYPE'])) $content_type = $_SERVER['CONTENT_TYPE'];
        
        switch ($content_type) {
                
            // Json content
            case "application/json":
                return $this->parseJson($body);
                
            // Html content
            case "text/html":
                return $this->parseHtml($body);

            // Html content from form
            case "application/x-www-form-urlencoded":
                return $this->parseHtml($body);
                
            // Xml content
            case "text/xml":
                return $this->parseXml($body);

            case "application/xml":
                return $this->parseXml($body);
                
            // Default parsing and format
            default:
                if(ucfirst(strtolower(DEFAULT_FORMAT)) === 'Html')
                    return $this->parseHtml($body);
                else
                    return $this->parseJson($body);

                break;
        }

        return FALSE;
    }

    private function parseHtml($body) {
        $this->format = "html";
        
        parse_str($body, $postvars);
        return $this->internalParser($postvars);
    }
    
    private function parseXml($body) {
        $this->format = "xml";
        
        $postvars = simplexml_load_file($body);
        return $this->internalParser($postvars);
    }
    
    private function parseJson($body) {
        $this->format = "json";
        
        $postvars = json_decode($body, TRUE);
        return $this->internalParser($postvars);
    }

    private function internalParser($params){
        if (empty($params)) return FALSE;
        if (!is_array($params)) return FALSE;

        foreach ($params as $param_name => $param_value) {
            $this->data[$param_name] = $param_value;
        }

        return TRUE;
    }

}

?>