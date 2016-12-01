<?php

/**
 * Elixir, Stored Objects management
 * @author Benoit Malchrowicz
 * @version 1.0
 *
 * Copyright (C) 2014 Benoit Malchrowicz
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

// Set global regex for object and columns name
define('OBJ_REGEX', "~^([[:alpha:]]){4}$~");
define('COL_REGEX', "~^([[:alpha:]]){2}$~");

// Set array separator (should use json array instead...)
define('LXR_SEPARATOR', ":");

// Define the default format when requesting API without "format" parameter
define('DEFAULT_FORMAT', 'json');

// Define useful max var corresponding to your DB values
define('MAX_NAME_SIZE', 25);
define('MAX_ID_SIZE', 65536 * 65536 * 4);
define('MAX_FLAG_SIZE', 50);

// Set the log directory
define('LOG_DIR', '/var/log/3l1x1r');

// Define log level (DEBUG/INFO/NOTICE/WARNING/ERROR/CRITICAL/ALERT/EMERGENCY)
define('LOG_LEVEL', 'notice');

// Define if data should be base64 encoded in database
define('ENCODING', False);
// Define global debug state (will return useful informations in errors if active)
define('DEBUG_STATE', False);

// Set global var
define('DB_PREFIX', 'LXR_');
define('USER_PREFIX', '');
?>
