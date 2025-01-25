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
require_once __DIR__ . '/../models/UsuariosModel.php';

/******************************/
//Se define la clase
class UsuariosController{

    private $user;
    private $queryBuilder;

    public function __construct(){
        $this->queryBuilder  = new QueryBuilderModel();
        $this->user          = new UsuariosModel();
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
            //Verifico si hay un token
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
            //Verifico si hay un token
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
            'data'   => 'idUsuario,usuario,idEstado,email,Nombre,Rut',
            'table'  => 'usuarios_listado',
            'join'   => '',
            'where'  => 'idEstado = 1',
            'group'  => '',
            'having' => '',
            'order'  => 'Nombre DESC',
            'limit'  => ''
        ];
        //Verifico si hay un token
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
            'data'   => 'idUsuario,usuario,idEstado,email,Nombre,Rut',
            'table'  => 'usuarios_listado',
            'join'   => '',
            'where'  => 'idEstado = 1',
            'group'  => '',
            'having' => '',
            'order'  => 'Nombre DESC',
            'limit'  => $ini.', '.$fin
        ];

        //Verifico si hay un token
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
            'data'   => 'idUsuario,usuario,idEstado,email,Nombre,Rut',
            'table'  => 'usuarios_listado',
            'join'   => '',
            'where'  => 'idEstado = "'.$id.'"',
            'group'  => '',
            'having' => '',
            'order'  => 'Nombre DESC'
        ];
        //Verifico si hay un token
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
            'data'      => 'usuario,idEstado,email,Nombre,Rut,password,idTipoUsuario,fNacimiento,Fono,idCiudad,idComuna,Direccion,Direccion_img,Ultimo_acceso,IP_Client,Agent_Transp',
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
        //Verifico si hay un token
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
    //Crear Masivo
    public function insertMassive($dataPost){
        /******************************/
        //Validaciones

        /******************************/
        //Consulto
        $result = '';

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
            $DataPOST = $dataPut;
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
        //Verifico si hay un token
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
            //Verifico si hay un token
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
            //Verifico si hay un token
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
