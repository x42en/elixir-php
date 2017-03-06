<?php

// Add LXR_Users fields
$system_user = array();
$system_user[TABLE_PREFIX.'id']['type'] = 'id';
$system_user[TABLE_PREFIX.'id']['required'] = TRUE;
$system_user[TABLE_PREFIX.'id']['unique'] = TRUE;
$system_user[TABLE_PREFIX.'ACCESS']['type'] = 'system';
$system_user[TABLE_PREFIX.'ACCESS']['required'] = FALSE;
$system_user[TABLE_PREFIX.'RW_ACCESS']['type'] = 'system';
$system_user[TABLE_PREFIX.'RW_ACCESS']['required'] = FALSE;
$system_user['USERNAME']['type'] = 'varchar';
$system_user['USERNAME']['required'] = TRUE;
$system_user['USERNAME']['primary'] = TRUE;
$system_user['DESCRIPTION']['type'] = 'text';
$system_user['DESCRIPTION']['required'] = FALSE;

// Add LXR_Errors fields
$system_error = array();
$system_error[TABLE_PREFIX.'id']['type'] = 'id';
$system_error[TABLE_PREFIX.'id']['required'] = TRUE;
$system_error[TABLE_PREFIX.'id']['unique'] = TRUE;
$system_error[TABLE_PREFIX.'ACCESS']['type'] = 'system';
$system_error[TABLE_PREFIX.'ACCESS']['required'] = FALSE;
$system_error[TABLE_PREFIX.'RW_ACCESS']['type'] = 'system';
$system_error[TABLE_PREFIX.'RW_ACCESS']['required'] = FALSE;
$system_error['CODE']['type'] = 'int';
$system_error['CODE']['required'] = TRUE;
$system_error['CODE']['required'] = TRUE;
$system_error['CODE']['primary'] = TRUE;
$system_error['MESSAGE']['type'] = 'varchar';
$system_error['MESSAGE']['required'] = TRUE;
$system_error['MESSAGE']['primary'] = TRUE;
$system_error['LANG']['type'] = 'vchar';
$system_error['LANG']['required'] = TRUE;
$system_error['LANG']['primary'] = TRUE;

// Add LXR_Groups fields
$system_group = array();
$system_group[TABLE_PREFIX.'id']['type'] = 'id';
$system_group[TABLE_PREFIX.'id']['required'] = TRUE;
$system_group[TABLE_PREFIX.'id']['unique'] = TRUE;
$system_group['GROUPNAME']['type'] = 'varchar';
$system_group['GROUPNAME']['primary'] = TRUE;
$system_group['GROUPNAME']['required'] = TRUE;
$system_group['USER_LIST']['type'] = 'text';
$system_group['USER_LIST']['required'] = TRUE;

// Add LXR_Fields fields
$system_field = array();
$system_field[TABLE_PREFIX.'id']['type'] = 'id';
$system_field[TABLE_PREFIX.'id']['required'] = TRUE;
$system_field[TABLE_PREFIX.'id']['unique'] = TRUE;
$system_field[TABLE_PREFIX.'ACCESS']['type'] = 'system';
$system_field[TABLE_PREFIX.'ACCESS']['required'] = FALSE;
$system_field[TABLE_PREFIX.'RW_ACCESS']['type'] = 'system';
$system_field[TABLE_PREFIX.'RW_ACCESS']['required'] = FALSE;
$system_field['NAME']['type'] = 'varchar';
$system_field['NAME']['required'] = TRUE;
$system_field['NAME']['primary'] = TRUE;
$system_field['REGEX']['type'] = 'text';
$system_field['REGEX']['required'] = TRUE;
$system_field['DESCRIPTION']['type'] = 'text';
$system_field['DESCRIPTION']['required'] = FALSE;

// Add LXR_Structures fields
$system_struct = array();
$system_struct[TABLE_PREFIX.'id']['type'] = 'id';
$system_struct[TABLE_PREFIX.'id']['required'] = TRUE;
$system_struct[TABLE_PREFIX.'id']['unique'] = TRUE;
$system_struct[TABLE_PREFIX.'ACCESS']['type'] = 'system';
$system_struct[TABLE_PREFIX.'ACCESS']['required'] = FALSE;
$system_struct[TABLE_PREFIX.'RW_ACCESS']['type'] = 'system';
$system_struct[TABLE_PREFIX.'RW_ACCESS']['required'] = FALSE;
$system_struct['NAME']['type'] = 'varchar';
$system_struct['NAME']['required'] = TRUE;
$system_struct['NAME']['primary'] = TRUE;
$system_struct['STRUCT']['type'] = 'text';
$system_struct['STRUCT']['required'] = TRUE;
$system_struct['DESCRIPTION']['type'] = 'text';
$system_struct['DESCRIPTION']['required'] = FALSE;

// Add LXR_Flags fields
$system_flag = array();
$system_flag[TABLE_PREFIX.'id']['type'] = 'id';
$system_flag[TABLE_PREFIX.'id']['required'] = TRUE;
$system_flag[TABLE_PREFIX.'id']['unique'] = TRUE;
$system_flag[TABLE_PREFIX.'ACCESS']['type'] = 'system';
$system_flag[TABLE_PREFIX.'ACCESS']['required'] = FALSE;
$system_flag[TABLE_PREFIX.'RW_ACCESS']['type'] = 'system';
$system_flag[TABLE_PREFIX.'RW_ACCESS']['required'] = FALSE;
$system_flag['FLAG']['type'] = 'varchar';
$system_flag['FLAG']['required'] = TRUE;
$system_flag['FLAG']['primary'] = TRUE;
$system_flag['TYPE']['type'] = 'varchar';
$system_flag['TYPE']['required'] = TRUE;
$system_flag['TYPE']['primary'] = TRUE;
$system_flag['OBJECT_ID']['type'] = 'id';
$system_flag['OBJECT_ID']['required'] = TRUE;
$system_flag['OBJECT_ID']['primary'] = TRUE;

// Add LXR_Views fields
$system_view = array();
$system_view[TABLE_PREFIX.'id']['type'] = 'id';
$system_view[TABLE_PREFIX.'id']['required'] = TRUE;
$system_view[TABLE_PREFIX.'id']['unique'] = TRUE;
$system_view[TABLE_PREFIX.'ACCESS']['type'] = 'system';
$system_view[TABLE_PREFIX.'ACCESS']['required'] = FALSE;
$system_view[TABLE_PREFIX.'RW_ACCESS']['type'] = 'system';
$system_view[TABLE_PREFIX.'RW_ACCESS']['required'] = FALSE;
$system_view['OBJECT']['type'] = 'varchar';
$system_view['OBJECT']['required'] = TRUE;
$system_view['OBJECT']['primary'] = TRUE;
$system_view['TYPE']['type'] = 'vchar';
$system_view['TYPE']['required'] = TRUE;
$system_view['TYPE']['primary'] = TRUE;
$system_view['FORMAT']['type'] = 'vchar';
$system_view['FORMAT']['required'] = TRUE;
$system_view['FORMAT']['primary'] = TRUE;
$system_view['RAW']['type'] = 'field';
$system_view['RAW']['required'] = TRUE;

?>