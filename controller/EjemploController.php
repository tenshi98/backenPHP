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
//Se llaman los modelos
require_once __DIR__ . '/../models/QueryBuilderModel.php';

/******************************/
//Se define la clase
class EjemploController{

    private $queryBuilder;

    public function __construct(){
        $this->queryBuilder  = new QueryBuilderModel();
    }

    /******************************************************************************/
    public function login($data){
        return Response::sendData(201, $data);
    }

    /******************************************************************************/
    public function verifyLogin(){
        try {
            /***************************/
            //Validaciones
            $headers     = getallheaders();
            if (!isset($headers['X-Auth-Token'])) {     return Response::sendData(401, "X-Auth-Token header is missing.");exit();}
            $authHeader  = $headers['X-Auth-Token'];
            if (strpos($authHeader, 'Bearer ') !== 0) { return Response::sendData(401, "Invalid X-Auth-Token format.");exit();}
            $token       = substr($authHeader, 7);
            $currentDate = date('Y-m-d H:i:s');

            /***************************/
            //Se genera la query
            $query = [
                'data'   => 'expiration_date',
                'table'  => 'usuarios_accesos',
                'join'   => '',
                'where'  => 'token = "'.$token.'"',
                'group'  => '',
                'having' => '',
                'order'  => 'expiration_date DESC'
            ];
            //Obtengo los datos
            $apiKeyData = $this->queryBuilder->queryRow($query);

            if (!$apiKeyData) {                                   return Response::sendData(401, "Token has expired.");exit();}
            if ($currentDate > $apiKeyData['expiration_date']) {  return Response::sendData(401, "Token has expired.");exit();}

            return true;

        } catch (PDOException $e) {
            return Response::sendData(500, "Server error : {$e}");
        }
    }

    /******************************************************************************/
    public function verifyToken(){
        try {
            /***************************/
            //Validaciones
            $headers     = getallheaders();
            if (!isset($headers['X-Auth-Token'])) {     return false;exit();}
            $authHeader  = $headers['X-Auth-Token'];
            if (strpos($authHeader, 'Bearer ') !== 0) { return false;exit();}
            $token       = substr($authHeader, 7);
            $currentDate = date('Y-m-d H:i:s');

            /***************************/
            //Se genera la query
            $query = [
                'data'   => 'expiration_date',
                'table'  => 'usuarios_accesos',
                'join'   => '',
                'where'  => 'token = "'.$token.'"',
                'group'  => '',
                'having' => '',
                'order'  => 'expiration_date DESC'
            ];
            //Obtengo los datos
            $apiKeyData = $this->queryBuilder->queryRow($query);

            if (!$apiKeyData) {                                   return false;exit();}
            if ($currentDate > $apiKeyData['expiration_date']) {  return false;exit();}

            return true;

        } catch (PDOException $e) {
            return Response::sendData(500, "Server error : {$e}");
        }
    }

    /******************************************************************************/
    //Listar Todo
    public function listAll(){
        /******************************/
        //Se genera la query
        $query = [
            'data'   => 'idUsuario,email,Nombre,Rut',
            'table'  => 'usuarios_listado',
            'join'   => '',
            'where'  => 'idEstado = 1',
            'group'  => '',
            'having' => '',
            'order'  => 'Nombre DESC',
            'limit'  => ''
        ];
        //Obtengo los datos
        $result = $this->queryBuilder->queryArray($query);

        /******************************/
        //Si hay resultados
        if ($result!=false) {
            return Response::sendData(200, $result);
        //si no hay resultados
        } else {
            return Response::sendData(400, "No hay resultados");
        }
    }

    /******************************************************************************/
    //Listar
    public function list($ini, $fin){
        /******************************/
        //Se genera la query
        $query = [
            'data'   => 'idUsuario,email,Nombre,Rut',
            'table'  => 'usuarios_listado',
            'join'   => '',
            'where'  => 'idEstado = 1',
            'group'  => '',
            'having' => '',
            'order'  => 'Nombre DESC',
            'limit'  => $ini.', '.$fin
        ];

        //Obtengo los datos
        $result = $this->queryBuilder->queryArray($query);

        /******************************/
        //Si hay resultados
        if ($result!=false) {
            return Response::sendData(200, $result);
        //si no hay resultados
        } else {
            return Response::sendData(400, "No hay resultados");
        }
    }

    /******************************************************************************/
    //Ver Datos
    public function view($id){
        /******************************/
        //Se genera la query
        $query = [
            'data'   => 'idUsuario,email,Nombre,Rut',
            'table'  => 'usuarios_listado',
            'join'   => '',
            'where'  => 'idUsuario = "'.$id.'"',
            'group'  => '',
            'having' => '',
            'order'  => 'Nombre DESC'
        ];
        //Obtengo los datos
        $result = $this->queryBuilder->queryRow($query);

        /******************************/
        //Si hay resultados
        if ($result!=false) {
            return Response::sendData(200, $result);
        //si no hay resultados
        } else {
            return Response::sendData(400, "No hay resultados");
        }
    }

    /******************************************************************************/
    //Crear
    public function insert($dataPost){
        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'idUsuario,idEstado,email,Nombre,Rut',
            'required'  => 'email,Nombre,Rut',
            'unique'    => 'email,Nombre-Rut',
            'table'     => 'usuarios_listado',
            'Post'      => $dataPost,
            'files'     => [
                [
                    'Identificador' => 'Direccion_img',
                    'SubCarpeta'    => '',
                    'NombreArchivo' => '',
                    'SufijoArchivo' => 'Sufijo_',
                    'ValidarTipo'   => 'word,excel,powerpoint,pdf,image,txt,zip,video,music',
                    'ValidarPeso'   => 10000
                ],
            ]
        ];
        //Obtengo los datos
        $result = $this->queryBuilder->queryInsert($query);

        /******************************/
        //Si hay resultados
        if ($result) {
            return Response::sendData(201, $result);
        //si no hay resultados
        } else {
            return Response::sendData(500, "Un error ha ocurrido");
        }
    }

    /******************************************************************************/
    //Editar
    public function update($dataPut = null){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataPut);
            $DataPOST = $dataPut;
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $DataPOST = $_POST;
        }else {
            return Response::sendData(500, "Error en el Request Method");
        }

        /******************************/
        //Se genera la query
        $query = [
            'data'      => 'email,Nombre',
            'required'  => 'email,Nombre',
            'unique'    => 'email,Nombre',
            'table'     => 'usuarios_listado',
            'where'     => 'idUsuario',
            'Post'      => $DataPOST,
            'files'     => [
                [
                    'Identificador' => 'Direccion_img',
                    'SubCarpeta'    => '',
                    'NombreArchivo' => '',
                    'SufijoArchivo' => 'Sufijo_',
                    'ValidarTipo'   => 'word,excel,powerpoint,pdf,image,txt,zip,video,music',
                    'ValidarPeso'   => 10000
                ],
            ]
        ];
        //Obtengo los datos
        $result = $this->queryBuilder->queryUpdate($query);

        /******************************/
        //Si hay resultados
        if ($result) {
            return Response::sendData(200, $result);
        //si no hay resultados
        } else {
            return Response::sendData(500, "Un error ha ocurrido");
        }
    }

    /******************************************************************************/
    //Borrar
    public function delete(){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataDelete);
            /******************************/
            //Se genera la query
            $query = [
                'files'       => 'Direccion_img',
                'table'       => 'usuarios_listado',
                'where'       => 'idUsuario',
                'SubCarpeta'  => '',
                'Post'        => $dataDelete
            ];
            //Obtengo los datos
            $result = $this->queryBuilder->queryDelete($query);

            /******************************/
            //Si hay resultados
            if ($result) {
                return Response::sendData(200, $result);
            //si no hay resultados
            } else {
                return Response::sendData(500, "Un error ha ocurrido");
            }
        }else {
            return Response::sendData(500, "Error en el Request Method");
        }
    }

    /******************************************************************************/
    //Borrar Archivos
    public function delFiles(){
        //Verificacion metodo PUT
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            //Se parsean los datos
            parse_str(file_get_contents("php://input"),$dataPut);
            /******************************/
            //Se genera la query
            $query = [
                'files'       => 'Direccion_img',
                'table'       => 'usuarios_listado',
                'where'       => 'idUsuario',
                'SubCarpeta'  => '',
                'Post'        => $dataPut
            ];
            //Obtengo los datos
            $result = $this->queryBuilder->delFiles($query);

            /******************************/
            //Si hay resultados
            if ($result) {
                return Response::sendData(200, $result);
            //si no hay resultados
            } else {
                return Response::sendData(500, "Un error ha ocurrido");
            }
        }else {
            return Response::sendData(500, "Error en el Request Method");
        }
    }

}
