<?php
/*******************************************************************************************************************/
/*                                              Bloque de seguridad                                                */
/*******************************************************************************************************************/
if( ! defined('XMBCXRXSKGC')) {
    die('No tienes acceso a esta carpeta o archivo (Access Code 1003-011).');
}
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
/******************************/
//Se define la clase
class Constants{

    const DB = [
        'HOSTNAME' => 'localhost',
        'USERNAME' => 'root',
        'PASSWORD' => '',
        'DATABASE' => 'backend',
        'PORT'     => 3306,
    ];

    const JWT = [
        'TOKEN_AUTHENTICATION' => true, // (boolean) - enable/disable token authentication
        'SECRET_KEY'           => 'sd65fg16e8rgs6dfv516sd5fv1s6df5v16aef5g4164g6e8gaw6df51a21', // (string) secret key for token encryption
        'TIME_TO_LIVE'         => 86400, // (int) seconds  - token life time
    ];

}