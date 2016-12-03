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

function isLogged() {
    // If token is not set
    if(empty($_SESSION['token']))
        return FALSE;

    // Store token in var for convenience
    $token = $_SESSION['token'];

    // If user is not set
    if(empty($_SESSION[$token]['user']))
        return FALSE;

    // If start or duration are not set
    if(empty($_SESSION[$token]['start']) || empty($_SESSION[$token]['duration']))
        return FALSE;

    // If token has expired
    if(((int) $_SESSION[$token]['start'] + (int) $_SESSION[$token]['duration']) <= time())
        return FALSE;

    // If connection comes from invalid IP (stolen token ?)
    if(empty($_SESSION[$token]['ip']) || $_SESSION[$token]['ip'] !== $_SERVER['REMOTE_ADDR'])
        return FALSE;

    return TRUE;
}

function printResult($result) {
    
    // Check if object exists
    if(!printObjectIsValid($result)){
        $result = new stdClass();
        $result->State = FALSE;
        $result->Code = 500;
        $result->Type = 'System';
        $result->Format = 'json';
        $result->Msg = 'Received unknown object';

        return printResult($result);
    }

    // first send HTTP header
    // if($result->Code > 300)
    //  header("HTTP/1.1 400 Bad Request");
    // else
    //  header("HTTP/1.1 200 OK");
    header("HTTP/1.1 200 OK");
    
    // Then send content-Type
    $content_type = 'Content-Type: ';
    switch (strtolower($result->Format)) {
        case 'json':
            $content_type .= 'application/json';
            unset($result->Format);
            $data = json_encode($result);
            break;
        
        case 'xml':
            $content_type .= 'application/xml';
            unset($result->Format);
            $data = wddx_serialize_value($result);
            break;
        
        default:
            $content_type .= 'text/html';
            unset($result->Format);
            if(!empty($result->Data)) $data = json_encode($result->Data);
            else if($result->Code > 200) $data = "<html>\r\n<head>\r\n\t<title>Error - ".$result->Code."</title>\r\n</head>\r\n<body>\r\n\t<h1>".$result->Code." - ".$result->Msg."</h1>\r\n\t<hr/>\r\n\t<p>".htmlentities($result->Type)." error</p>\r\n</body>\r\n</html>";
            break;
    }
    header($content_type.'; charset=UTF-8');

    // Finally if is set, send content (double encoded, so decode once ;))
    if(!empty($data)){
        echo html_entity_decode($data);
    }
}

function print_error($logger, $error){
    $end_line = '<br>';
    echo $error;
    $err_str = "Error (".$error->getCode().": ".$error->getMessage()." - $end_line";
    $err_str .= "from file ".$error->getFile().":".$error->getLine();
    $err_str = "\t[".$_SERVER['REMOTE_ADDR']."]\t - ".$err_str;
    
    if(empty($logger)) die($err_str);

    $code = intval($error->getCode());

    if ($code > 7000){
        $logger->debug($err_str);
    }
    else if ($code > 6000){
        $logger->info($err_str);
    }
    else if ($code > 5000){
        $logger->notice($err_str);
    }
    else if ($code > 4000){
        $logger->warning($err_str);
    }
    else if ($code > 3000){
        $logger->error($err_str);
    }
    else if ($code > 2000){
        $logger->critical($err_str);
    }
    else if ($code > 1000){
        $logger->alert($err_str);
    }
    else{
        $logger->emergency($err_str);
    }
}

function printObjectIsValid($result) {
    if(!is_object($result)) return FALSE;
    if(empty($result->Type)) return FALSE;
    if(empty($result->State) && !is_bool($result->State)) return FALSE;
    if(empty($result->Code) && !is_int($result->Code)) return FALSE;
    if(empty($result->Format)) return FALSE;

    return TRUE;
}

function is_assoc($var) {
    return is_array($var) && array_diff_key($var,array_keys(array_keys($var)));
}

function strtocapital($string) {
    $words = explode(" ", $string);
    $output = '';
    foreach ($words as $i => $word) {
        $output.= ucwords(strtolower($word)) . " ";
    }
    
    return substr($output, 0, -1);
}

function tofloat($num) {
    return sprintf("%.0f", $num);
}

// Transform a string to an array of ID
function ID2array($string) {
    if (!is_string($string)) return null;
    
    // Split the string into an array
    $raw_list = explode(LXR_SEPARATOR, $string);
    
    // If the value is empty or not a table
    if (empty($raw_list) || !is_array($raw_list)) return null;
    
    $id_list = array();
    
    // Check each value in order to clean the table
    foreach ($raw_list as $value) {
        if (!isValidID($value)) continue;
        
        $id_list[] = $value;
    }
    
    // Return a unique array
    return array_unique($id_list);
}

// Transform an array of ID to a string with separator
function array2ID($list) {
    
    // If the value is empty or not a table
    if (empty($list) || !is_array($list)) return null;
    
    $list = array_unique($list);
    
    $id_list = "";
    
    // Clean each values in the array before add them to the list
    foreach ($list as $value) {
        if (!isValidID($value)) continue;
        
        // Concatenate the values in a string
        $id_list.= $value . LXR_SEPARATOR;
    }
    
    return $id_list;
}

// Transform a string to an array of ID
function Flag2array($string) {
    
    // Split the string into an array
    $raw_list = explode(LXR_SEPARATOR, $string);
    
    // If the value is empty or not a table
    if (empty($raw_list) || !is_array($raw_list)) return null;
    
    $flag_list = array();
    
    // Check each value in order to clean the table
    foreach ($raw_list as $value) {
        if (isValidFlag($value)) array_push($flag_list, strtocapital($value));
    }
    
    // Return a unique array
    return array_unique($flag_list);
}

// Transform an array of ID to a string with separator
function array2Flag($list) {
    
    // If the value is empty or not a table
    if (empty($list) || !is_array($list)) return null;
    
    array_unique($list);
    
    $flag_list = "";
    
    // Clean each values in the array before add them to the list
    foreach ($list as $value) {
        if (isValidFlag($value))
        
        // Concatenate the values in a string
        $flag_list.= strtocapital($value) . LXR_SEPARATOR;
    }
    
    return $flag_list;
}

// Check that a flag is aphanumeric and can start by _ or ! or both
function isValidFlag($flag) {
    
    // If flag is empty, exit
    if (empty($flag)) return FALSE;
    
    if (sizeof($flag) > MAX_FLAG_SIZE) return FALSE;
    
    //Drop all spaces
    $flag = str_replace(" ", "", $flag);
    $flag = str_replace("-", "", $flag);
    
    //Drop all underscore except if first letter
    if (substr($flag, 0, 1) === "_") $flag = "_" . str_replace("_", "", substr($flag, 1));
    else $flag = str_replace("_", "", $flag);
    
    // If the flag is not indexable check that it's all alpha num
    if (substr($flag, 0, 1) === "_" && ctype_alnum(substr($flag, 1))) return TRUE;
    
    // If the flag is not indexable check that it's all alpha num
    if (substr($flag, 0, 1) === "!" && ctype_alnum(substr($flag, 1))) return TRUE;
    
    // If the flag is indexable check that it's not only digits
    if (is_int($flag) || ctype_digit($flag)) return FALSE;
    if (ctype_alnum($flag)) return TRUE;
    
    return FALSE;
}

// Check that it's only a number (digit or int)
function isValidID($id) {
    if (empty($id)) return FALSE;
    if (!ctype_digit($id) && !is_int($id))  return FALSE;
    if ((int) $id > MAX_ID_SIZE) return FALSE;
    
    return TRUE;
}

// Check that it's only alphanum with underscore but not as starting char
function isValidName($name) {
    
    // If name is empty, exit
    if (empty($name)) return FALSE;
    if (sizeof($name) > MAX_NAME_SIZE) return FALSE;
    // If name start with _, exit
    if (substr($name, 0, 1) === "_") return FALSE;
    // Drop all remaining _
    $name = str_replace("_", "", $name);
    if (ctype_alnum($name) && !is_int($name) && !ctype_digit($name)) return TRUE;
    
    return FALSE;
}

// Check that a view name is valid
function isValidView($name) {
    // Avoid mixing lang and view name
    if (isValidName($name) && sizeof($name) > 2) return TRUE;
    else return FALSE;
}

// Check that lang is a 2 letters word
function isValidLang($lang) {
    
    // If lang is empty, exit
    if (empty($lang)) return FALSE;
    if (sizeof($lang) > 2) return FALSE;
    if (!ctype_alpha($lang)) return FALSE;
    
    return TRUE;
}

// Check if a field description is valid
function isValidDescription($description) {
    
    // Allow text with accentuated chars
    return preg_match("~^([[:alnum:]]|[&,;.!?=/:()]|[[:space:]]|[àéèêïîûùôâçœñ])+$~", $description);
}

// Check if a json string is valid
function isValidJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

// Put in form the parameters (Key in uppercase/base64decode())
function validateParams($array) {
    if (empty($array) || !is_array($array)) return FALSE;
    
    foreach ($array as $key => $value) {
        $key = strtocapital($key);
        $tmp[$key] = $value;
    }
    
    return $tmp;
}

// Check if a collection string is valid
function isValidCollection($string) {
    if (empty($string) || !is_string($string)) return FALSE;
    
    $entries = explode(LXR_SEPARATOR, $string);
    
    if (empty($entries) || !is_array($entries)) return FALSE;
    
    foreach ($entries as $value) {
        if (empty($value)) continue;
        if (!isValidFlag($value) && !isValidID($value)) return FALSE;
    }
    
    return TRUE;
}

// Check if a collection array is valid
function isValidCollectionArray($array) {
    
    if (empty($array) || !is_array($array)) return FALSE;
    
    foreach ($array as $key => $value) {
        if (empty($value)) continue;
        
        if (!isValidFlag($value) && !isValidID($value)) return FALSE;
    }
    
    return TRUE;
}

// Check if a collection id is valid
function isValidCollectionID($string) {
    if (empty($string) || !is_string($string)) return FALSE;
    
    $entries = explode(LXR_SEPARATOR, $string);
    
    if (empty($entries) || !is_array($entries)) return FALSE;
    
    foreach ($entries as $value) {
        if (empty($value)) continue;
        if (!isValidID($value)) return FALSE;
    }
    
    return TRUE;
}

// Check if an array of ID is valid
function isValidArrayID($array) {
    if (empty($array) || !is_array($array)) return FALSE;
    
    foreach ($array as $key => $value) {
        if (empty($value)) continue;
        
        if (!isValidID($value)) return FALSE;
    }
    
    return TRUE;
}

// Check that an array of flag is valid
function isValidArrayFlag($array) {
    if (empty($array) || !is_array($array)) return FALSE;
    
    foreach ($array as $key => $value) {
        if (empty($value)) continue;
        
        if (!isValidFlag($value)) return FALSE;
    }
    
    return TRUE;
}

// Check an array is associative
function isAssoc($arr) {
    return array_keys($arr) !== range(0, count($arr) - 1);
}
?>