<?php
/**********************************************************************************************************************************/
/*                                           Se define la variable de seguridad                                                   */
/**********************************************************************************************************************************/
define('XMBCXRXSKGC', 1);
/**********************************************************************************************************************************/
/*                                     In case one is using PHP 5.4's built-in server                                             */
/**********************************************************************************************************************************/
$filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}
/**********************************************************************************************************************************/
/*                                                       Include classes                                                          */
/**********************************************************************************************************************************/
require_once __DIR__ . '/util/Router.php';
require_once __DIR__ . '/util/Request.php';
require_once __DIR__ . '/util/Response.php';
/**********************************************************************************************************************************/
/*                                                     Include Controllers                                                        */
/**********************************************************************************************************************************/
require_once __DIR__ . '/controller/UsuariosController.php';
/**********************************************************************************************************************************/
/*                                                           Loads                                                                */
/**********************************************************************************************************************************/
// Loads
$router = new \Tenshi\Router\Router();

// Custom 404 Handler
$router->set404(function () {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    echo '404, route not found!';
});

// Before Router Middleware
$router->before('GET', '/.*', function () {
    header('X-Powered-By: tenshi98/router');
});
/**********************************************************************************************************************************/
/*                                                       Rutas Estaticas                                                          */
/**********************************************************************************************************************************/
// Root
$router->get('/', function () {echo Response::sendWithCode(201, 'Root');});

/**********************************************************************************************************************************/
/*                                                       Rutas Dinamicas                                                          */
/**********************************************************************************************************************************/
//si el token esta activo
if((new UsuariosController())->verifyToken()==true){
    /**************************************/
    // CRUD
    $router->mount('/movies', function () use ($router) {

        //Vistas
        $router->get('/', function () {echo (new UsuariosController())->listAll();});                                   //Listar Todo
        $router->get('/list/(\d+)/(\d+)', function ($ini, $fin) {echo (new UsuariosController())->list($ini, $fin);});  //Listar
        $router->get('/view/(\d+)', function ($id) {echo (new UsuariosController())->view($id);});                      //Ver Datos

        //Datos
        $router->post('/', function () {echo (new UsuariosController())->insert($_POST);});               //Crear
        $router->post('/massive', function () {echo (new UsuariosController())->insertMassive($_POST);}); //Crear Masivo
        $router->put('/', function () {echo (new UsuariosController())->update();});                      //Editar por put (solo modificar datos)
        $router->post('/update', function () {echo (new UsuariosController())->update($_POST);});         //Editar por post (modificar y subir archivos)
        $router->put('/delFiles', function () {echo (new UsuariosController())->delFiles();});            //Permite eliminar archivos
        $router->delete('/', function () {echo (new UsuariosController())->delete();});                   //Borrar

    });
//si no hay token
}else{
    // Login
    $router->post('/api/auth/login', function () {echo (new UsuariosController())->login($_POST);});
    $router->get('/api/auth/login', function () {echo (new UsuariosController())->verifyLogin();});
}
/**********************************************************************************************************************************/
/*                                                           Ejecutar                                                             */
/**********************************************************************************************************************************/
// Ejecutar
$router->run();
