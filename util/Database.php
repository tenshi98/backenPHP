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
//Se llama a la configuracion
require_once __DIR__ . "/../config/Constants.php";

/******************************/
//Se define la clase
class Database{
    public static function getConnection(){
        $db_conn = new mysqli(
            Constants::DB["HOSTNAME"] . ':' . Constants::DB["PORT"],
            Constants::DB["USERNAME"],
            Constants::DB["PASSWORD"],
            Constants::DB["DATABASE"]
        );

        if ($db_conn->connect_error) {
            die("Connection failed: " . $db_conn->connect_error);
        }

        return $db_conn;
    }
}
