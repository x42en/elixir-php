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


class Printer{
    private $template;
    private $format;

    public function __construct($format){
        $this->format = $format;
        $this->template = NULL;
    }

    public function setTemplate($template){
        $this->template = $template;
    }

    public function success($type, $data){
        $result = array();
        $result['State'] = TRUE;
        $result['Type'] = $type;
        if(!is_bool($data)) $result['Data'] = $data;

        $this->process("HTTP/1.1 200 OK", $result);
    }

    public function error($type, $error){
        $result = array();
        $result['Type'] = $type;
        $reason = '200 OK';

        // Cheat on empty error
        if($error->getCode() == 204){
            $result['State'] = TRUE;
            $result['Data'] = [];
        }
        else if($error->getCode() == 1){
            $result['State'] = TRUE;
            $result['Code'] = $error->getCode();
            if (defined('DEBUG_STATE') && DEBUG_STATE) $result['Message'] = $error->getMessage();
        }
        else{
            $result['State'] = FALSE;
            $result['Code'] = $error->getCode();
            if (defined('DEBUG_STATE') && DEBUG_STATE) $result['Message'] = $error->getMessage();
        }
        
        $this->process("HTTP/1.1 200 OK", $result);
    }

    private function process($header, $result){
        if(defined('DEBUG_STATE') && DEBUG_STATE && is_array($result))
            $result['Debug'] = TRUE;

        header($header);

        // Set Cors wide open (overwrite nginx troubles on this...)
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

        switch ($this->format) {
            case 'json':
                $this->outputJson($result);
                break;

            case 'html':
                $this->outputHtml($this->template, $result);
                break;

            case 'xml':
                $this->outputXml($result);
                break;

            case 'txt':
                $this->outputTxt($result);
                break;
        }
    }

    private function outputJson($result){
        header('Content-type: application/json; charset=UTF-8');
        echo json_encode($result);
    }

    private function outputHtml($template, $result){
        header('Content-type: text/html; charset=UTF-8');
        if(!empty($template)){
            require_once ('Smarty/Smarty.class.php');

            // New instance of smarty
            $smarty = new Smarty();

            // Disable caching
            $smarty->caching = 0;
            
            // Assign the template
            $smarty->assign('result', (array) $result);

            // Generate the result using given data
            $page = $smarty->display('string:'.$template);

        }else{
            if(is_array($result)){
                $page = '<pre>' . print_r($result, TRUE) . '</pre>';
            }
            else{
                $page = html_entity_decode($result);
            }
            
        }
        echo $page;
    }

    private function outputXml($result){
        header('Content-type: application/xml; charset=UTF-8');
        echo wddx_serialize_value($result);
    }

    private function outputTxt($result){
        header('Content-type: text/plain; charset=UTF-8');
        print_r($result);
    }


}

?>