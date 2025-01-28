# Backend PHP

Un backend simple hecho en PHP que permite utilizar cualquier ruta ingresada, esta hecho para evitarse la complejidad de la creación de un modelo por cada tabla de la base de datos, utiliza un motor de base de datos que se ejecuta en el lado del Modelo, pero recibe los datos desde el controlador indicando lo que se desea de forma simple (y no tan complicada como los otros ORM).

Después de hacerlo funcionar se llego a la decision de dejarlo por la complejidad de integración de otras librerías (archivos office, envío de correos, etc.).

Se toma la decisión de utilizar un framework PHP liviano, llegando a la conclusión de utilizar https://fatfreeframework.com/

## Modo de uso
Todo metodo debe llevar en el Header Form el siguiente campo

| Campo         | Valor             |
| ------------- |:-----------------:|
| X-Auth-Token  | Bearer asdqwe123  |

El valor debe comenzar con "Bearer ", lo que sigue a este debe coincidir con algún dato en la columna 'token' de la tabla 'usuarios_accesos'

## Rutas y campos utilizados

| Method        | URI                         | Descripcion                                  | Campos de formulario o en la ruta               |
| ------------- |:---------------------------:|:--------------------------------------------:|:-----------------------------------------------:|
| GET           | /                           | Raiz                                         | Ninguno                                         |
| POST          | /auth/login                 | Post del login                               | email,Password (No implementado)                |
| GET           | /auth/login                 | Verificación Login                           | Ninguno                                         |
| GET           | /ejemplos/                  | Listar Todo                                  | Ninguno                                         |
| GET           | /ejemplos/list/(\d+)/(\d+)  | Listar                                       | ejemplos/list/1/5 -> Pagina los resultados      |
| GET           | /ejemplos/view/(\d+)        | Ver Datos                                    | ejemplos/view/1 ->Primer campo                  |
| POST          | /ejemplos/                  | Crear                                        | idEstado,email,Nombre,Rut                       |
| PUT           | /ejemplos/                  | Editar por put (solo modificar datos)        | idUsuario,email,Nombre                          |
| POST          | /ejemplos/update            | Editar por post (modificar y subir archivos) | idUsuario,email,Nombre,Direccion_img            |
| PUT           | /ejemplos/delFiles          | Permite eliminar archivos                    | idUsuario                                       |
| DELETE        | /ejemplos/                  | Borrar                                       | idUsuario                                       |

## Uso del controller

### listAll - list
```bash
//Se genera la query
$query = [
    'data'   => 'idUsuario,email,Nombre,Rut', -> Campos solicitados
    'table'  => 'usuarios_listado',           -> Tabla
    'join'   => '',                           -> Joins
    'where'  => 'idEstado = 1',               -> Where
    'group'  => '',                           -> Group
    'having' => '',                           -> Having
    'order'  => 'Nombre DESC',                -> Ordenamiento
    'limit'  => ''                            -> Limit
];
//Verifico si hay un token
$result = $this->queryBuilder->queryArray($query);
```

### view
```bash
//Se genera la query
$query = [
    'data'   => 'idUsuario,email,Nombre,Rut', -> Campos solicitados
    'table'  => 'usuarios_listado',           -> Tabla
    'join'   => '',                           -> Joins
    'where'  => 'idUsuario = "'.$id.'"',      -> Where limitado por solo un parametro
    'group'  => '',                           -> Group
    'having' => '',                           -> Having
    'order'  => 'Nombre DESC'                 -> Ordenamiento
];
//Obtengo los datos
$result = $this->queryBuilder->queryRow($query);
```

### insert
```bash
Los identificadores del formulario deben coincidir con los datos ingresados en data

//Se genera la query
$query = [
    'data'      => 'idUsuario,idEstado,email,Nombre,Rut', -> Campos a guardar, su existencia es validada
    'required'  => 'email,Nombre,Rut',                    -> Campos obligatorios, si no existen se devuelve error
    'unique'    => 'email,Nombre-Rut',                    -> Validacion campo unico (campos separados por guiones son validados de forma conjunta)
    'table'     => 'usuarios_listado',                    -> Tabla donde guardar
    'Post'      => $dataPost,                             -> Los datos POST del formulario (Incluye los archivos)
    'files'     => [
        [
            'Identificador' => 'Direccion_img',                                       -> Identificador del archivo
            'SubCarpeta'    => '',                                                    -> Subcarpeta opcional donde guardar (puede ser separada por /)
            'NombreArchivo' => '',                                                    -> Cambia el nombre del archivo subido
            'SufijoArchivo' => 'Sufijo_',                                             -> Agrega un sufijo al archivo subido
            'ValidarTipo'   => 'word,excel,powerpoint,pdf,image,txt,zip,video,music', -> Validacion de los formatos
            'ValidarPeso'   => 10000                                                  -> Peso maximo
        ],
    ]
];
//Obtengo los datos
$result = $this->queryBuilder->queryInsert($query);
```

### update
```bash
//Se genera la query
$query = [
    'data'      => 'email,Nombre',     -> Campos a actualizar, su existencia es validada
    'required'  => 'email,Nombre',     -> Campos obligatorios, si no existen se devuelve error
    'unique'    => 'email,Nombre',     -> Validacion campo unico (campos separados por guiones son validados de forma conjunta)
    'table'     => 'usuarios_listado', -> Tabla donde guardar
    'where'     => 'idUsuario',        -> Where para la actualizacion, su existencia es validada
    'Post'      => $DataPOST,          -> Los datos POST del formulario (Incluye los archivos)
    'files'     => [
        [
            'Identificador' => 'Direccion_img',                                       -> Identificador del archivo
            'SubCarpeta'    => '',                                                    -> Subcarpeta opcional donde guardar (puede ser separada por /)
            'NombreArchivo' => '',                                                    -> Cambia el nombre del archivo subido
            'SufijoArchivo' => 'Sufijo_',                                             -> Agrega un sufijo al archivo subido
            'ValidarTipo'   => 'word,excel,powerpoint,pdf,image,txt,zip,video,music', -> Validacion de los formatos
            'ValidarPeso'   => 10000                                                  -> Peso maximo
        ],
    ]
];
//Obtengo los datos
$result = $this->queryBuilder->queryUpdate($query);
```

### delete
```bash
//Se genera la query
$query = [
    'files'       => 'Direccion_img',    -> Archivos a borrar antes de eliminar el dato en la BD
    'table'       => 'usuarios_listado', -> Tabla donde eliminar el dato
    'where'       => 'idUsuario',        -> Where para la eliminacion, su existencia es validada
    'SubCarpeta'  => '',                 -> Subcarpeta opcional donde se alojan los archivos (puede ser separada por /)
    'Post'        => $dataDelete         -> Los datos POST del formulario
];
//Obtengo los datos
$result = $this->queryBuilder->queryDelete($query);
```

### delFiles
```bash
//Se genera la query
$query = [
    'files'       => 'Direccion_img',    -> Archivos a borrar antes de eliminar el dato en la BD
    'table'       => 'usuarios_listado', -> Tabla donde eliminar el dato
    'where'       => 'idUsuario',        -> Where para la eliminacion, su existencia es validada
    'SubCarpeta'  => '',                 -> Subcarpeta opcional donde se alojan los archivos (puede ser separada por /)
    'Post'        => $dataPut            -> Los datos POST del formulario
];
//Obtengo los datos
$result = $this->queryBuilder->delFiles($query);
```

