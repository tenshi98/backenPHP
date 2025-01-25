<?php
//verifica la capa de desarrollo
$whitelist = array( 'localhost', '127.0.0.1', '::1' );
//si estoy en ambiente de desarrollo
if( in_array( $_SERVER['REMOTE_ADDR'], $whitelist) ){
	//nada
//si estoy en ambiente de produccion
}else{
	//redirijo
	header("location:https://www.google.com");
}

?>
