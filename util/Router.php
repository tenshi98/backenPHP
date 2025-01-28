<?php
/******************************/
//namespace
namespace Tenshi\Router;
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
class Router{

    private $afterRoutes = array();   //@var array Los patrones de ruta y sus funciones de manejo
    private $beforeRoutes = array();  //@var array Los patrones de ruta del middleware anterior y sus funciones de manejo
    protected $notFoundCallback = []; //@var array [object|callable] La función que se ejecutará cuando no se haya encontrado ninguna ruta
    private $baseRoute = '';          //@var string Ruta base actual, utilizada para el montaje de (sub)rutas
    private $requestedMethod = '';    //@var string El método de solicitud que se debe manejar
    private $serverBasePath;          //@var string La ruta base del servidor para la ejecución del enrutador
    private $namespace = '';          //@var string Espacio de nombres de controladores predeterminados

    /**
    * Almacena una ruta middleware anterior y una función de manejo que se ejecutará cuando se acceda a ella mediante uno de los métodos especificados.
    * @param string          $methods Métodos permitidos, delimitados por |
    * @param string          $pattern Un patrón de ruta como /about/system
    * @param object|callable $fn      La función de manejo que se ejecutará
    */
    public function before($methods, $pattern, $fn){
        $pattern = $this->baseRoute . '/' . trim($pattern, '/');
        $pattern = $this->baseRoute ? rtrim($pattern, '/') : $pattern;

        if ($methods === '*') {
            $methods = 'GET|POST|PUT|DELETE|OPTIONS|PATCH|HEAD';
        }

        foreach (explode('|', $methods) as $method) {
            $this->beforeRoutes[$method][] = array(
                'pattern' => $pattern,
                'fn' => $fn,
            );
        }
    }

    /**
    * Almacena una ruta y una función de manejo que se ejecutará cuando se acceda a ella utilizando uno de los métodos especificados.
    * @param string          $methods Métodos permitidos, delimitados por |
    * @param string          $pattern Un patrón de ruta como /about/system
    * @param object|callable $fn      La función de manejo que se ejecutará
    */
    public function match($methods, $pattern, $fn){
        $pattern = $this->baseRoute . '/' . trim($pattern, '/');
        $pattern = $this->baseRoute ? rtrim($pattern, '/') : $pattern;

        foreach (explode('|', $methods) as $method) {
            $this->afterRoutes[$method][] = array(
                'pattern' => $pattern,
                'fn' => $fn,
            );
        }
    }

    /**
    * Forma abreviada de una ruta a la que se accede mediante cualquier método.
    * @param string          $pattern Un patrón de ruta como /about/system
    * @param object|callable $fn      La función de manejo que se ejecutará
    */
    public function all($pattern, $fn){
        $this->match('GET|POST|PUT|DELETE|OPTIONS|PATCH|HEAD', $pattern, $fn);
    }

    /**
    * Forma abreviada de una ruta a la que se accede mediante GET.
    * @param string          $pattern Un patrón de ruta como /about/system
    * @param object|callable $fn      La función de manejo que se ejecutará
    */
    public function get($pattern, $fn){
        $this->match('GET', $pattern, $fn);
    }

    /**
    * Forma abreviada de una ruta a la que se accede mediante POST.
    * @param string          $pattern Un patrón de ruta como /about/system
    * @param object|callable $fn      La función de manejo que se ejecutará
    */
    public function post($pattern, $fn){
        $this->match('POST', $pattern, $fn);
    }

    /**
    * Forma abreviada de una ruta a la que se accede mediante PATCH.
    * @param string          $pattern Un patrón de ruta como /about/system
    * @param object|callable $fn      La función de manejo que se ejecutará
    */
    public function patch($pattern, $fn){
        $this->match('PATCH', $pattern, $fn);
    }

    /**
    * Forma abreviada de una ruta a la que se accede mediante DELETE.
    * @param string          $pattern Un patrón de ruta como /about/system
    * @param object|callable $fn      La función de manejo que se ejecutará
    */
    public function delete($pattern, $fn){
        $this->match('DELETE', $pattern, $fn);
    }

    /**
    * Forma abreviada de una ruta a la que se accede mediante PUT.
    * @param string          $pattern Un patrón de ruta como /about/system
    * @param object|callable $fn      La función de manejo que se ejecutará
    */
    public function put($pattern, $fn){
        $this->match('PUT', $pattern, $fn);
    }

    /**
    * Forma abreviada de una ruta a la que se accede mediante OPCIONES.
    * @param string          $pattern Un patrón de ruta como /about/system
    * @param object|callable $fn      La función de manejo que se ejecutará
    */
    public function options($pattern, $fn){
        $this->match('OPTIONS', $pattern, $fn);
    }

    /**
    * Monta una colección de devoluciones de llamadas en una ruta base.
    * @param string    $baseRoute El subpatrón de ruta en el que se montarán las devoluciones de llamadas
    * @param callable  $fn        El método de devolución de llamada
    */
    public function mount($baseRoute, $fn){
        // Seguir la ruta base actual
        $curBaseRoute = $this->baseRoute;

        // Construir nueva cadena de ruta base
        $this->baseRoute .= $baseRoute;

        // Llamar al invocable
        call_user_func($fn);

        // Restaurar la ruta base original
        $this->baseRoute = $curBaseRoute;
    }

    /**
    * Obtener todos los encabezados de solicitud.
    * @return array Los encabezados de solicitud
    */
    public function getRequestHeaders(){
        $headers = array();

        // Si getallheaders() está disponible, úselo
        if (function_exists('getallheaders')) {
            $headers = getallheaders();

            // getallheaders() puede devolver falso si algo salió mal
            if ($headers !== false) {
                return $headers;
            }
        }

        // El método getallheaders() no está disponible o salió mal: extraiga manualmente
        foreach ($_SERVER as $name => $value) {
            if ((substr($name, 0, 5) == 'HTTP_') || ($name == 'CONTENT_TYPE') || ($name == 'CONTENT_LENGTH')) {
                $headers[str_replace(array(' ', 'Http'), array('-', 'HTTP'), ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }

    /**
    * Obtener el método de solicitud utilizado, teniendo en cuenta las modificaciones.
    * @return string El método de solicitud que se va a manejar
    */
    public function getRequestMethod(){
        // Tome el método que se encuentra en $_SERVER
        $method = $_SERVER['REQUEST_METHOD'];

        // Si se trata de una solicitud HEAD, anúlela para que sea GET y evite cualquier salida, según la especificación HTTP
        // @url http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_start();
            $method = 'GET';
        // Si es una solicitud POST, verifique si hay un encabezado de anulación de método
        }elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->getRequestHeaders();
            if (isset($headers['X-HTTP-Method-Override']) && in_array($headers['X-HTTP-Method-Override'], array('PUT', 'DELETE', 'PATCH'))) {
                $method = $headers['X-HTTP-Method-Override'];
            }
        }

        return $method;
    }

    /**
    * Establezca un espacio de nombres de búsqueda predeterminado para los métodos invocables.
    * @param string $namespace Un espacio de nombres determinado
    */
    public function setNamespace($namespace){
        if (is_string($namespace)) {
            $this->namespace = $namespace;
        }
    }

    /**
    * Obtener el espacio de nombres indicado anteriormente.
    * @return string El espacio de nombres indicado si existe
    */
    public function getNamespace(){
        return $this->namespace;
    }

    /**
    * Ejecutar el enrutador: repetir todos los middleware y rutas definidos antes y ejecutar la función de manejo si se encuentra una coincidencia.
    * @param object|callable $callback Función que se ejecutará después de que se haya manejado una ruta coincidente (= después del middleware del enrutador)
    * @return bool
    */
    public function run($callback = null){
        // Definir qué método necesitamos manejar
        $this->requestedMethod = $this->getRequestMethod();

        // Manejar todo antes de los middlewares
        if (isset($this->beforeRoutes[$this->requestedMethod])) {
            $this->handle($this->beforeRoutes[$this->requestedMethod]);
        }

        // Manejar todas las rutas
        $numHandled = 0;
        if (isset($this->afterRoutes[$this->requestedMethod])) {
            $numHandled = $this->handle($this->afterRoutes[$this->requestedMethod], true);
        }

        // Si no se manejó ninguna ruta, activa el 404 (si lo hay)
        if ($numHandled === 0) {
            if (isset($this->afterRoutes[$this->requestedMethod])) {
                $this->trigger404($this->afterRoutes[$this->requestedMethod]);
            } else {
                $this->trigger404();
            }
        // Si se manejó una ruta, realice la devolución de llamada de finalización (si corresponde)
        }elseif ($callback && is_callable($callback)) {
            $callback();
        }

        // Si originalmente era una solicitud HEAD, limpiemos el búfer de salida después de vaciarlo
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_end_clean();
        }

        // Devuelve verdadero si se manejó una ruta, falso en caso contrario
        return $numHandled !== 0;
    }

    /**
    * Establezca la función de manejo de errores 404.
    * @param object|callable|string $match_fn La función que se ejecutará
    * @param object|callable $fn La función que se ejecutará
    */
    public function set404($match_fn, $fn = null){
        if (!is_null($fn)) {
            $this->notFoundCallback[$match_fn] = $fn;
        } else {
            $this->notFoundCallback['/'] = $match_fn;
        }
    }

    /**
    * Activa la respuesta 404
    * @param string $pattern Un patrón de ruta como /about/system
    */
    public function trigger404($match = null){

        // Contador para llevar un registro del número de rutas que hemos manejado
        $numHandled = 0;

        // manejar patrón 404
        if (count($this->notFoundCallback) > 0){
            // rutas de reserva de bucle
            foreach ($this->notFoundCallback as $route_pattern => $route_callable) {

                // resultados de los partidos
                $matches = [];

                // comprobar si hay una coincidencia y obtener las coincidencias como $matches (puntero)
                $is_match = $this->patternMatches($route_pattern, $this->getCurrentUri(), $matches, PREG_OFFSET_CAPTURE);

                // ¿La ruta de respaldo coincide?
                if ($is_match) {

                    // Reelaborar las coincidencias para que solo contengan las coincidencias, no la cadena original
                    $matches = array_slice($matches, 1);

                    // Extraer los parámetros de URL coincidentes (y solo los parámetros)
                    $params = array_map(function ($match, $index) use ($matches) {

                        // Tenemos el siguiente parámetro: toma la subcadena desde la posición del parámetro actual hasta la posición del siguiente (gracias PREG_OFFSET_CAPTURE)
                        if (isset($matches[$index + 1]) && isset($matches[$index + 1][0]) && is_array($matches[$index + 1][0])) {
                            if ($matches[$index + 1][0][1] > -1) {
                                return trim(substr($match[0][0], 0, $matches[$index + 1][0][1] - $match[0][1]), '/');
                            }
                        } // No tenemos los siguientes parámetros: devolver el lote completo

                        return isset($match[0][0]) && $match[0][1] != -1 ? trim($match[0][0], '/') : null;
                    }, $matches, array_keys($matches));

                    $this->invoke($route_callable);

                    ++$numHandled;
                }
            }
        }
        if (($numHandled == 0) && (isset($this->notFoundCallback['/']))) {
            $this->invoke($this->notFoundCallback['/']);
        } elseif ($numHandled == 0) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        }
    }

    /**
    * Reemplaza todas las llaves que coinciden con {} en patrones de palabras (como Laravel)
    * Comprueba si hay una coincidencia de ruta
    * @param $pattern
    * @param $uri
    * @param $matches
    * @param $flags
    * @return bool -> es coincidencia sí/no
    */
    private function patternMatches($pattern, $uri, &$matches, $flags){
        // Reemplazar todas las llaves que coincidan con {} en patrones de palabras (como Laravel)
        $pattern = preg_replace('/\/{(.*?)}/', '/(.*?)', $pattern);

        //¡Quizás tengamos un partido!
        return boolval(preg_match_all('#^' . $pattern . '$#', $uri, $matches, PREG_OFFSET_CAPTURE));
    }

    /**
    * Manejar un conjunto de rutas: si se encuentra una coincidencia, ejecutar la función de manejo relacionada.
    * @param array $routes       Colección de patrones de ruta y sus funciones de manejo
    * @param bool  $quitAfterRun ¿Es necesario que la función de manejo salga después de que se encuentre una coincidencia con una ruta?
    * @return int                La cantidad de rutas manejadas
    */
    private function handle($routes, $quitAfterRun = false){
        // Contador para llevar un registro del número de rutas que hemos manejado
        $numHandled = 0;

        // La URL de la página actual
        $uri = $this->getCurrentUri();

        // Recorrer todas las rutas
        foreach ($routes as $route) {

            // obtener coincidencias de ruta
            $is_match = $this->patternMatches($route['pattern'], $uri, $matches, PREG_OFFSET_CAPTURE);

            //¿Existe una coincidencia válida?
            if ($is_match) {

                // Reelaborar las coincidencias para que solo contengan las coincidencias, no la cadena original
                $matches = array_slice($matches, 1);

                // Extraer los parámetros de URL coincidentes (y solo los parámetros)
                $params = array_map(function ($match, $index) use ($matches) {

                    // Tenemos el siguiente parámetro: toma la subcadena desde la posición del parámetro actual hasta la posición del siguiente (gracias PREG_OFFSET_CAPTURE)
                    if (isset($matches[$index + 1]) && isset($matches[$index + 1][0]) && is_array($matches[$index + 1][0])) {
                        if ($matches[$index + 1][0][1] > -1) {
                            return trim(substr($match[0][0], 0, $matches[$index + 1][0][1] - $match[0][1]), '/');
                        }
                    } // No tenemos los siguientes parámetros: devolver el lote completo

                    return isset($match[0][0]) && $match[0][1] != -1 ? trim($match[0][0], '/') : null;
                }, $matches, array_keys($matches));

                // Llamar a la función de manejo con los parámetros de URL si la entrada deseada es invocable
                $this->invoke($route['fn'], $params);

                ++$numHandled;

                // Si tenemos que dejarlo, entonces dejémoslo.
                if ($quitAfterRun) {
                    break;
                }
            }
        }

        // Devuelve el número de rutas manejadas
        return $numHandled;
    }

    private function invoke($fn, $params = array()){
        if (is_callable($fn)) {
            call_user_func_array($fn, $params);
        // En caso contrario, comprobar la existencia de parámetros especiales
        }elseif (stripos($fn, '@') !== false) {
            // Explotar segmentos de la ruta dada
            list($controller, $method) = explode('@', $fn);

            // Ajuste la clase del controlador si se ha establecido el espacio de nombres
            if ($this->getNamespace() !== '') {
                $controller = $this->getNamespace() . '\\' . $controller;
            }

            try {
                $reflectedMethod = new \ReflectionMethod($controller, $method);
                // Asegúrate de que se pueda llamar
                if ($reflectedMethod->isPublic() && (!$reflectedMethod->isAbstract())) {
                    if ($reflectedMethod->isStatic()) {
                        forward_static_call_array(array($controller, $method), $params);
                    } else {
                        // Asegúrese de que tengamos una instancia, porque un método no estático no debe llamarse estáticamente
                        if (\is_string($controller)) {
                            $controller = new $controller();
                        }
                        call_user_func_array(array($controller, $method), $params);
                    }
                }
            } catch (\ReflectionException $reflectionException) {
                // La clase del controlador no está disponible o la clase no tiene el método $method
            }
        }
    }

    /**
    * Define la URI relativa actual.
    * @return string
    */
    public function getCurrentUri(){
        // Obtener la URI de solicitud actual y eliminar la ruta base de reescritura (= permite ejecutar el enrutador en una subcarpeta)
        $uri = substr(rawurldecode($_SERVER['REQUEST_URI']), strlen($this->getBasePath()));

        // No tenga en cuenta los parámetros de consulta en la URL
        if (strstr($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        // Eliminar la barra diagonal final + aplicar una barra diagonal al comienzo
        return '/' . trim($uri, '/');
    }

    /**
    * Devuelve la ruta base del servidor y la define si no está definida.
    * @return string
    */
    public function getBasePath(){
        // Verifique si la ruta base del servidor está definida, si no, defínala.
        if ($this->serverBasePath === null) {
            $this->serverBasePath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
        }

        return $this->serverBasePath;
    }

    /**
    * Establece explícitamente la ruta base del servidor. Se utiliza cuando la ruta del script de entrada difiere de las URL de entrada.
    * @see https://github.com/bramus/router/issues/82#issuecomment-466956078
    *
    * @param string
    */
    public function setBasePath($serverBasePath){
        $this->serverBasePath = $serverBasePath;
    }
}
