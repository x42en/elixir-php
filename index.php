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


require_once ('./Config/config.php');

$welcome = array('State' => true, 'Msg' => 'Welcome.');
if(defined('DEBUG_STATE') && DEBUG_STATE)
	$welcome['Debug'] = TRUE;

$format = DEFAULT_FORMAT;

if(!empty($_GET['_format']))
	$format = strtolower($_GET['_format']);

header("HTTP/1.1 200 OK");

switch ($format) {
	case 'html':
		header('Content-type: text/html; charset=UTF-8');
		$output = '<html><head><title>3l1x1r - The essence of object</title></head><body>';
		$output .= "<div class='header'><div class='logo'><img src=''/>3l1x1r [e.li.ksiʁ]</div><div class='sublogo'>... The essence of Object</div></div><hr/>";
		$output .= "<div class'container'><h1>" . $welcome['Msg'] . "</h1></div>";
		$output .= '</body></html>';
		echo $output;
		break;

	case 'xml':
		header('Content-type: application/xml; charset=UTF-8');
        echo wddx_serialize_value($welcome);
        break;

    case 'txt':
    	header('Content-type: text/plain; charset=UTF-8');
        print_r($welcome);
        break;
	
	default:
		header('Content-type: application/json; charset=UTF-8');
		echo json_encode($welcome);
		break;
}

?>