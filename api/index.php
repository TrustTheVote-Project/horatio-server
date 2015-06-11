<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
 * Establish our routing table.
 */
$router = array();
$router['registrars'] = 'registrars/';
$router['validator'] = 'validator/';
$router['submit'] = 'submit/';
$router['bounce'] = 'bounce/';

/*
 * Identify which method is being requested.
 */
$url_components = parse_url($_SERVER['REQUEST_URI']);
$method = str_replace('/api/', '', $url_components['path']);
if (strpos($method, '/') !== FALSE)
{
	$method = substr($url_components['path'], 1, strpos($method, '/'));
}
if ( ($method === FALSE) || !isset($router[$method]) )
{
	header("HTTP/1.0 404 Not Found");
	echo '404 Not Found';
}

include $router[$method] . '.php';
/*
 * Enable cross-origin resource sharing (CORS).
 */
header("Access-Control-Allow-Origin: *");

