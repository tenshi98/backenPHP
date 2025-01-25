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
require_once __DIR__ . "/../util/Database.php";

/******************************/
//Se define la clase
class QueryBuilderModel{

    private $db_conn;

    public function __construct(){
        $this->db_conn = Database::getConnection();
    }

    /******************************************************************************/
    public function queryRow($query){

        /******************************************/
        //armado de la query
        $ActionSQL = 'SELECT '.$query['data'];
        $ActionSQL.= ' FROM `'.$query['table'].'`';
        if(isset($query['join'])&&$query['join']!=''){      $ActionSQL.= ' '.$query['join'];}
        if(isset($query['where'])&&$query['where']!=''){    $ActionSQL.= ' WHERE '.$query['where'];}
        if(isset($query['group'])&&$query['group']!=''){    $ActionSQL.= ' GROUP BY '.$query['group'];}
        if(isset($query['having'])&&$query['having']!=''){  $ActionSQL.= ' HAVING '.$query['having'];}
        if(isset($query['order'])&&$query['order']!=''){    $ActionSQL.= ' ORDER BY '.$query['order'];}
        $ActionSQL.= ' LIMIT 1';

        /******************************************/
        //Ejecuto la query
        $result = $this->db_conn->query($ActionSQL);

        /******************************************/
        //Si se ejecuta correctamente
        if ($result->num_rows == 1) {
            return $result->fetch_assoc();
        } else {
            return false;
        }

    }

    /******************************************************************************/
    public function queryNRows($query){

        /******************************************/
        //armado de la query
        $ActionSQL = 'SELECT '.$query['data'];
        $ActionSQL.= ' FROM `'.$query['table'].'`';
        if(isset($query['join'])&&$query['join']!=''){      $ActionSQL.= ' '.$query['join'];}
        if(isset($query['where'])&&$query['where']!=''){    $ActionSQL.= ' WHERE '.$query['where'];}
        if(isset($query['group'])&&$query['group']!=''){    $ActionSQL.= ' GROUP BY '.$query['group'];}
        if(isset($query['having'])&&$query['having']!=''){  $ActionSQL.= ' HAVING '.$query['having'];}
        if(isset($query['order'])&&$query['order']!=''){    $ActionSQL.= ' ORDER BY '.$query['order'];}

        /******************************************/
        //Ejecuto la query
        $result = $this->db_conn->query($ActionSQL);

        /******************************************/
        //Si se ejecuta correctamente
        if ($result->num_rows !=0) {
            return $result->num_rows;
        } else {
            return false;
        }

    }

    /******************************************************************************/
    public function queryArray($query){

        /******************************************/
        //armado de la query
        $ActionSQL = 'SELECT '.$query['data'];
        $ActionSQL.= ' FROM `'.$query['table'].'`';
        if(isset($query['join'])&&$query['join']!=''){      $ActionSQL.= ' '.$query['join'];}
        if(isset($query['where'])&&$query['where']!=''){    $ActionSQL.= ' WHERE '.$query['where'];}
        if(isset($query['group'])&&$query['group']!=''){    $ActionSQL.= ' GROUP BY '.$query['group'];}
        if(isset($query['having'])&&$query['having']!=''){  $ActionSQL.= ' HAVING '.$query['having'];}
        if(isset($query['order'])&&$query['order']!=''){    $ActionSQL.= ' ORDER BY '.$query['order'];}
        if(isset($query['limit'])&&$query['limit']!=''){    $ActionSQL.= ' LIMIT '.$query['limit'];}

        /******************************************/
        //Ejecuto la query
        $result = $this->db_conn->query($ActionSQL);

        /******************************************/
        //Si se ejecuta correctamente
        if ($result->num_rows > 0) {
            $arrSelect = array();
            //si hay respuesta se devuelven los resultados
			while ( $row = $result->fetch_assoc()) {
				array_push( $arrSelect,$row );
			}
			//devolver objeto
			return $arrSelect;
        } else {
            return false;
        }

    }

    /******************************************************************************/
    public function queryInsert($query){

        /******************************************/
        //Validacion datos obligatorios
        $dataVal  = $this->validateRequired($query['required'], $query['Post']);
        if($dataVal === true){  /* nada */}else{return $dataVal;}
        //Validacion datos unicos
        $dataUniq = $this->validateUnique($query['unique'], $query['table'], $query['Post'], '');
        if($dataUniq === true){ /* nada */}else{return $dataUniq;}

        /******************************************/
        //Variables
        $DatosNombres  = '';
        $DatosArchivos = '';
	    $arrData       = $this->parseData($query['data']);
        $separator     = '';

        /******************************************/
        //Subida de archivos
        if (!empty($query['files'])&&!empty($_FILES)){
            //Valido los archivos
            $dataFiles = $this->validateFiles($_FILES, $query['files']);
            //Si todos los datos requeridos estan ok
            if($dataFiles === true){  /* nada */}else{return $dataFiles;}
            //Si no hay errores se suben los archivos
            $newFileName = $this->uploadFile($_FILES, $query['files']);
            //Se guardan los nombres
            $DatosNombres  = $newFileName['Nombres'];
            $DatosArchivos = $newFileName['Archivos'];
        }

        /******************************************/
        //armado de la query
        $ActionSQL = 'INSERT INTO '.$query['table'];
        $ActionSQL.= ' ('.$query['data'].$DatosNombres.')';
        $ActionSQL.= ' VALUES (';
        //recorro validando
        foreach ($arrData as $data) {
            if(isset($query['Post'][$data]) && $query['Post'][$data]!=''){
                $ActionSQL .= $separator."'".$query['Post'][$data]."'";
            }else{
                $ActionSQL .= $separator."''";
            }
            $separator = ',';
        }
        $ActionSQL.= $DatosArchivos;
        $ActionSQL.= ')';

        /******************************************/
        //Si se ejecuta correctamente
        if ($this->db_conn->query($ActionSQL) === true) {
            return $this->db_conn->insert_id;;
        } else {
            return false;
        }

    }

    /******************************************************************************/
    public function queryUpdate($query){

        /******************************************/
        //Validacion datos obligatorios
        $dataVal  = $this->validateRequired($query['required'].','.$query['where'], $query['Post']);
        if($dataVal === true){  /* nada */}else{return $dataVal;}
        //Validacion datos unicos
        $dataUniq = $this->validateUnique($query['unique'], $query['table'], $query['Post'], $query['where']);
        if($dataUniq === true){ /* nada */}else{return $dataUniq;}

        /******************************************/
        //Variables
        $arrData      = $this->parseData($query['data']);
        $arrWhere     = $this->parseData($query['where']);
        $DatosUpdate  = '';
        $separator1   = '';
        $separator2   = '';
        /******************************************/
        //Subida de archivos
        if (!empty($query['files'])&&!empty($_FILES)){
            //Valido los archivos
            $dataFiles = $this->validateFiles($_FILES, $query['files']);
            //Si todos los datos requeridos estan ok
            if($dataFiles === true){  /* nada */}else{return $dataFiles;}
            //Si no hay errores se suben los archivos
            $newFileName = $this->uploadFile($_FILES, $query['files']);
            //Se guardan los nombres
            $DatosUpdate  = $newFileName['Update'];
        }

        /******************************************/
        //armado de la query
        $ActionSQL = 'UPDATE '.$query['table'];
        $ActionSQL.= ' SET ';
        //recorro validando
        foreach ($arrData as $data) {
            if(isset($query['Post'][$data]) && $query['Post'][$data]!=''){
                $ActionSQL .= $separator1."`".$data."`='".$query['Post'][$data]."'";
            }
            $separator1 = ',';
        }
        $ActionSQL.= $DatosUpdate;
        $ActionSQL.= ' WHERE ';
        //recorro los campos a validar
        foreach ($arrWhere as $where) {
            //verifico si existe el dato
            if (!empty($query['Post'][$where])) {
                //se crea cadena
                $ActionSQL .= $separator2.$where." = '".$query['Post'][$where]."'";
                //separador
                $separator2 = ' AND ';
            }
        }

        /******************************************/
        //Si se ejecuta correctamente
        if ($this->db_conn->query($ActionSQL) === true) {
            return true;
        } else {
            return false;
        }

    }

    /******************************************************************************/
    public function queryDelete($query){

        /******************************************/
        //Validacion datos obligatorios
        $dataVal  = $this->validateRequired($query['where'], $query['Post']);
        if($dataVal === true){  /* nada */}else{return $dataVal;}

        /******************************************/
        //Se eliminan los archivos en caso de existir
        if(isset($query['files'])&&$query['files']!=''){
            $delFile  = $this->deleteFiles($query['files'], $query['table'], $query['where'], $query['SubCarpeta'], $query['Post']);
            if($delFile === true){  /* nada */}else{return $delFile;}
        }

        /******************************************/
        //Separo los datos
        $arrWhere   = $this->parseData($query['where']);
        $separador  = '';

        /******************************************/
        //armado de la query
        $ActionSQL = 'DELETE FROM '.$query['table'];
        $ActionSQL.= ' WHERE ';
        //recorro los campos a validar
        foreach ($arrWhere as $where) {
            //verifico si existe el dato
            if (!empty($query['Post'][$where])) {
                //se crea cadena
                $ActionSQL .= $separador.$where." = '".$query['Post'][$where]."'";
                //separador
                $separador = ' AND ';
            }
        }

        /******************************************/
        //Si se ejecuta correctamente
        if ($this->db_conn->query($ActionSQL) === true) {
            return true;
        } else {
            return false;
        }

    }

    /******************************************************************************/
    public function delFiles($query){

        /******************************************/
        //Validacion datos obligatorios
        $dataVal  = $this->validateRequired($query['where'], $query['Post']);
        if($dataVal === true){  /* nada */}else{return $dataVal;}

        /******************************************/
        //Se verifica si hay archivos
        if(!isset($query['files']) OR $query['files']==''){ return false; }

        /******************************************/
        //Se eliminan los archivos en caso de existir
        $delFile  = $this->deleteFiles($query['files'], $query['table'], $query['where'], $query['SubCarpeta'], $query['Post']);
        if($delFile === true){
            /******************************************/
            //Separo los datos
            $arrFiles   = $this->parseData($query['files']);
            $arrWhere   = $this->parseData($query['where']);
            $separador1 = '';
            $separador2 = '';


            /******************************************/
            //armado de la query
            $ActionSQL = 'UPDATE '.$query['table'];
            $ActionSQL.= ' SET ';
            //recorro los archivos a borrar
            foreach ($arrFiles as $file) {
                //se elimina el archivo
                if(isset($file)&&$file!=''){
                    //se crea cadena
                    $ActionSQL .= $separador1.$file." = ''";
                    //separador
                    $separador1 = ',';
                }
            }
            $ActionSQL.= ' WHERE ';
            foreach ($arrWhere as $where) {
                //verifico si existe el dato
                if (!empty($query['Post'][$where])) {
                    //se crea cadena
                    $ActionSQL .= $separador2.$where." = '".$query['Post'][$where]."'";
                    //separador
                    $separador2 = ' AND ';
                }
            }

            /******************************************/
            //Si se ejecuta correctamente
            if ($this->db_conn->query($ActionSQL) === true) {
                return true;
            } else {
                return false;
            }
        }else{
            return $delFile;
        }
    }

    /******************************************************************************/
    /******************************************************************************/
    private function validateRequired($SIS_data, $SIS_Post){
        //Variables
        $arrData = $this->parseData($SIS_data);
        $errors  = [];
        //Recorro
        foreach ($arrData as $field) {
            if (empty($SIS_Post[$field])) {
                $errors[] = [$field => $field, "message" => "$field es obligatorio"];
            }
        }
        //si hay errores
        if(empty($errors)){
            return true;
        }else{
            return $errors;
        }
    }
    /******************************************************************************/
    private function validateUnique($SIS_Data, $SIS_Table, $SIS_Post, $SIS_Where){

        /******************************************/
        //Variables
        $arrData    = $this->parseData($SIS_Data);
        $subWhere   = '';
        $separador1 = '';
        $separador2 = '';
        $separador3 = '';
        $errors     = [];

        /******************************************/
        //Verifico si existe el dato
        if(isset($SIS_Where)&&$SIS_Where!=''){
            $arrWhere = $this->parseData($SIS_Where);
            //recorro los campos a validar
            foreach ($arrWhere as $where) {
                //verifico si existe el dato
                if (!empty($SIS_Post[$where])) {
                    //se crea cadena
                    $subWhere .= $separador1.$where." != '".$SIS_Post[$where]."'";
                    //separador
                    $separador1 = ' AND ';
                }
            }
        }

        /******************************************/
        //Recorro
        foreach ($arrData as $data) {
            /******************************************/
            //verifico si hay subgrupos
            if (strpos($data, "-")){
                //Separo los datos
                $arrData2   = $this->parseData2($data);
                $x_data     = '';
                $x_where    = '';
                //recorro los campos a validar
                foreach ($arrData2 as $data2) {
                    //verifico si existe el dato
                    if (!empty($SIS_Post[$data2])) {
                        //se crea cadena
                        $x_data  .= $separador2.$data2;
                        $x_where .= $separador3.$data2." = '".$SIS_Post[$data2]."'";
                        //separador
                        $separador2 = ',';
                        $separador3 = ' AND ';
                    }
                }
                //Se genera la query solo si hay datos
                if(isset($x_data)&&$x_data!=''){
                    //Verifico si dato existe
                    if($subWhere!=''){
                        $whereInternal = $subWhere.' AND '.$x_where;
                    }else{
                        $whereInternal = $x_where;
                    }
                    //se busca si dato existe
                    $query = [
                        'data'  => $x_data,
                        'table' => $SIS_Table,
                        'join'  => '',
                        'where' => $whereInternal,
                        'group' => '',
                        'order' => ''
                    ];
                    //Verifico si hay un dato
                    $ndata = $this->queryNRows($query);
                    //si hay un dato
                    if($ndata > 0) {$errors[] = [$x_data => $x_data, "message" => "$x_data ya existe"];}
                }
            /******************************************/
            //si no hay subgrupo se ejecuta normalmente
            }else{
                //verifico si existe el dato
                if (!empty($SIS_Post[$data])) {
                    //Verifico si dato existe
                    if($subWhere!=''){
                        $whereInternal = $subWhere.' AND '.$data." = '".$SIS_Post[$data]."'";
                    }else{
                        $whereInternal = $data." = '".$SIS_Post[$data]."'";
                    }
                    //Se genera la query
                    $query = [
                        'data'  => $data,
                        'table' => $SIS_Table,
                        'join'  => '',
                        'where' => $whereInternal,
                        'group' => '',
                        'order' => ''
                    ];
                    //Verifico si hay un dato
                    $ndata = $this->queryNRows($query);
                    //si hay un dato
                    if($ndata > 0) {$errors[] = [$data => $data, "message" => "$data ya existe"];}
                }
            }
        }
        //si hay errores
        if(empty($errors)){
            return true;
        }else{
            return $errors;
        }
    }
    /******************************************************************************/
    private function validateFiles($SIS_FILES, $arrArchivos){
        //Variable de errores
        $errors = [];
        //Recorro los archivos
        foreach ($arrArchivos as $archivo) {
            /***************************************************/
            //Verifico la existencia del archivo a subir
            if (empty($SIS_FILES[$archivo['Identificador']])) {
                $errors[] = ['Archivo' => $archivo['Identificador'], "message" => $archivo['Identificador'].' es obligatorio'];
            }
            /***************************************************/
            //Verifico si hay errores
            if ($SIS_FILES[$archivo['Identificador']]["error"] > 0){
                $errors[] = ['Archivo' => $archivo['Identificador'], "message" => $this->uploadPHPError($SIS_FILES[$archivo['Identificador']]["error"])];
            }
            /***************************************************/
            //Verifico si tiene la extension permitida
            //Separo los datos
            $arrTipo   = $this->parseData($archivo['ValidarTipo']);
            $dataTypes = array();
            foreach ($arrTipo as $tipo) {
                switch ($tipo) {
                    /**********************/
                    case 'word':
                        $dataTypes[] = 'application/msword';
                        $dataTypes[] = 'application/vnd.ms-word';
                        $dataTypes[] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                        $dataTypes[] = 'application/x-abiword';
                        $dataTypes[] = 'application/vnd.oasis.opendocument.text';
                        break;
                    /**********************/
                    case 'excel':
                        $dataTypes[] = 'application/msexcel';
                        $dataTypes[] = 'application/vnd.ms-excel';
                        $dataTypes[] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                        $dataTypes[] = 'text/csv';
                        $dataTypes[] = 'application/vnd.oasis.opendocument.spreadsheet';
                        break;
                    /**********************/
                    case 'powerpoint':
                        $dataTypes[] = 'application/mspowerpoint';
                        $dataTypes[] = 'application/vnd.ms-powerpoint';
                        $dataTypes[] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
                        $dataTypes[] = 'application/vnd.oasis.opendocument.presentation';
                        break;
                    /**********************/
                    case 'pdf':
                        $dataTypes[] = 'application/pdf';
                        $dataTypes[] = 'application/octet-stream';
                        $dataTypes[] = 'application/x-real';
                        $dataTypes[] = 'application/vnd.adobe.xfdf';
                        $dataTypes[] = 'application/vnd.fdf';
                        $dataTypes[] = 'binary/octet-stream';
                        $dataTypes[] = 'application/epub+zip';
                        break;
                    /**********************/
                    case 'image':
                        $dataTypes[] = 'image/jpg';
                        $dataTypes[] = 'image/jpeg';
                        $dataTypes[] = 'image/gif';
                        $dataTypes[] = 'image/png';
                        $dataTypes[] = 'image/bmp';
                        $dataTypes[] = 'image/webp';
                        break;
                    /**********************/
                    case 'txt':
                        $dataTypes[] = 'text/plain';
                        $dataTypes[] = 'text/richtext';
                        $dataTypes[] = 'application/rtf';
                        break;
                    /**********************/
                    case 'zip':
                        $dataTypes[] = 'application/x-zip-compressed';
                        $dataTypes[] = 'application/zip';
                        $dataTypes[] = 'multipart/x-zip';
                        $dataTypes[] = 'application/x-7z-compressed';
                        $dataTypes[] = 'application/x-rar-compressed';
                        $dataTypes[] = 'application/gzip';
                        $dataTypes[] = 'application/x-gzip';
                        $dataTypes[] = 'application/x-gtar';
                        $dataTypes[] = 'application/x-tgz';
                        $dataTypes[] = 'application/octet-stream';
                        $dataTypes[] = 'application/x-bzip';
                        $dataTypes[] = 'application/x-bzip2';
                        break;
                    /**********************/
                    case 'video':
                        $dataTypes[] = 'video/x-msvideo';
                        $dataTypes[] = 'video/mpeg';
                        $dataTypes[] = 'video/ogg';
                        $dataTypes[] = 'video/webm';
                        $dataTypes[] = 'application/mp4';
                        break;
                    /**********************/
                    case 'music':
                        $dataTypes[] = 'audio/aac';
                        $dataTypes[] = 'audio/midi';
                        $dataTypes[] = 'audio/ogg';
                        $dataTypes[] = 'audio/x-wav';
                        $dataTypes[] = 'audio/webm';
                        break;
                }
            }
            if (!in_array($SIS_FILES[$archivo['Identificador']]['type'], $dataTypes)) {
                $errors[] = ['Archivo' => $archivo['Identificador'], "message" => 'Tipo de archivo no permitido'];
            }
            /***************************************************/
            //Verifico el peso del archivo
            if ($SIS_FILES[$archivo['Identificador']]['size'] >= ($archivo['ValidarPeso'] * 1024)){
                $errors[] = ['Archivo' => $archivo['Identificador'], "message" => 'Archivo excede el tamaño permitido'];
            }
            /***************************************************/
            //Verifico la existencia del archivo en el servidor
            $NombreArchivo = '';
            if(isset($archivo['NombreArchivo'])&&$archivo['NombreArchivo']!=''){
                $ext = pathinfo($SIS_FILES[$archivo['Identificador']]['name'], PATHINFO_EXTENSION);
                $NombreArchivo = $archivo['NombreArchivo'].'.'.$ext;
            }elseif((!isset($archivo['NombreArchivo']) OR $archivo['NombreArchivo']=='')&&isset($archivo['SufijoArchivo'])&&$archivo['SufijoArchivo']!=''){
                $NombreArchivo = $archivo['SufijoArchivo'].$SIS_FILES[$archivo['Identificador']]['name'];
            }
            //Genero la ruta
            $rutaArchivo = '../upload/';
            if(isset($archivo['SubCarpeta'])&&$archivo['SubCarpeta']!=''){$rutaArchivo.= $archivo['SubCarpeta'].'/';}
            $rutaArchivo.= $NombreArchivo;
            //Verifico la existencia del archivo
            if (file_exists($rutaArchivo)){
                $errors[] = ['Archivo' => $archivo['Identificador'], "message" => 'El archivo '.$SIS_FILES[$archivo['Identificador']]['name'].' ya existe en el servidor'];
            }
        }
        //si hay errores
        if(empty($errors)){
            return true;
        }else{
            return $errors;
        }
    }
    /******************************************************************************/
    private function uploadFile($SIS_FILES, $arrArchivos){
        //Variables
        $Data = [];
        $Data['Nombres']  = '';
        $Data['Archivos'] = '';
        $Data['Update']   = '';
        //Recorro los archivos
        foreach ($arrArchivos as $archivo) {
            //se guarda el archivo
            $NombreArchivo = '';
            if(isset($archivo['NombreArchivo'])&&$archivo['NombreArchivo']!=''){
                $ext = pathinfo($SIS_FILES[$archivo['Identificador']]['name'], PATHINFO_EXTENSION);
                $NombreArchivo = $archivo['NombreArchivo'].'.'.$ext;
            }elseif((!isset($archivo['NombreArchivo']) OR $archivo['NombreArchivo']=='')&&isset($archivo['SufijoArchivo'])&&$archivo['SufijoArchivo']!=''){
                $NombreArchivo = $archivo['SufijoArchivo'].$SIS_FILES[$archivo['Identificador']]['name'];
            }
            //Genero la ruta
            $rutaArchivo = __DIR__ .'/../upload/';
            if(isset($archivo['SubCarpeta'])&&$archivo['SubCarpeta']!=''){$rutaArchivo.= $archivo['SubCarpeta'].'/';}
            //Verifico la existencia del archivo
            if (!file_exists($rutaArchivo.$NombreArchivo)){
                //Se cambian los permisos
                if (!is_dir($rutaArchivo)) {
                    mkdir($rutaArchivo, 0777, true);
                }
                //Se mueve el archivo a la carpeta previamente configurada
                $move_result = @move_uploaded_file($SIS_FILES[$archivo['Identificador']]["tmp_name"], $rutaArchivo.$NombreArchivo);
                if ($move_result){
                    //Se guardan los nombres
                    $Data['Nombres']  .= ",".$archivo['Identificador'];
                    $Data['Archivos'] .= ",".$NombreArchivo."'";
                    $Data['Update']   .= ",".$archivo['Identificador']." = '".$NombreArchivo."'";
                }
            }
        }
        //Devuelvo los resultados
        return $Data;
    }
    /******************************************************************************/
    private function deleteFiles($SIS_Files, $SIS_Table, $SIS_Where, $SIS_Carpeta, $SIS_Post){
        /******************************************/
        //Separo los datos
        $arrWhere   = $this->parseData($SIS_Where);
        $arrFiles   = $this->parseData($SIS_Files);
        $separador  = '';
        $whereInt   = '';
        //recorro los campos a validar
        foreach ($arrWhere as $where) {
            //verifico si existe el dato
            if (!empty($SIS_Post[$where])) {
                //se crea cadena
                $whereInt .= $separador.$where." = '".$SIS_Post[$where]."'";
                //separador
                $separador = ' AND ';
            }
        }

        /******************************************/
        //Se genera la query
        $queryRow = [
            'data'   => $SIS_Files,
            'table'  => $SIS_Table,
            'join'   => '',
            'where'  => $whereInt,
            'group'  => '',
            'having' => '',
            'order'  => ''
        ];
        //Verifico si hay un token
        $result = $this->queryRow($queryRow);

        /******************************************/
        //Se generan las rutas
        $rutaArchivo = __DIR__ .'/../upload/';
        if(isset($SIS_Carpeta)&&$SIS_Carpeta!=''){$rutaArchivo.= $SIS_Carpeta.'/';}

        //recorro los archivos a borrar
        foreach ($arrFiles as $file) {
            //se elimina el archivo
            if(isset($result[$file])&&$result[$file]!=''){
                //Verifico la existencia del archivo
                if (file_exists($rutaArchivo.$result[$file])){
                    unlink($rutaArchivo.$result[$file]);
                }
            }
        }

        //Devuelvo los resultados
        return true;
    }
    /******************************************************************************/
    private function parseData($Data){
        return explode(",", str_replace(' ', '', $Data));
    }
    /******************************************************************************/
    private function parseData2($Data){
        return explode("-", str_replace(' ', '', $Data));
    }
    /******************************************************************************/
    private function uploadPHPError($error) {
        switch ($error) {
            case 0: return "No hay error, el archivo se cargó con éxito"; break;
            case 1: return "El archivo cargado supera la directiva upload_max_filesize en php.ini"; break;
            case 2: return "El archivo cargado excede la directiva MAX_FILE_SIZE que se especificó en el formulario HTML"; break;
            case 3: return "El archivo cargado solo se cargó parcialmente"; break;
            case 4: return "No se cargó ningún archivo"; break;
            case 6: return "Falta una carpeta temporal"; break;
            case 7: return "Error al escribir el archivo en el disco"; break;
            case 8: return "Una extensión PHP detuvo la carga del archivo"; break;
        }
    }

}

