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


class LxrException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        if (DEFAULT_FORMAT == 'html'){
            $ex_str = "<html>\n<head>\n\t<title>Error " . $this->code . "</title>\n</head>\n<body>\n\t<div style='margin: 10%'>";
            $ex_str .= '<h1><span style="color: red">Error ' . $this->code . '</span> - ' . $this->message . "</h1>\n";
            if(defined('DEBUG_STATE') && DEBUG_STATE)
                $ex_str .= '<div style="color: orange">Error from: <small><em>' . $this->file . ':' . $this->line . '</em></small>';

            $ex_str .= "\n\t</div>\n</body>\n</html>";
        }
        else{
            if(defined('DEBUG_STATE') && DEBUG_STATE) $ex_str = "\n[!] Error (".$this->code."): ".$this->message."\n";
            else $ex_str = "\n[!] Error (".$this->code.")\n";
        }

        return $ex_str;
    }
}

?>