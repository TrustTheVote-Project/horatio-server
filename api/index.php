<?php

/*
 * Show debug code.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
 * Establish our routing table.
 */
$router = array();
$router['registrars'] = 'registrars';
$router['validator'] = 'validator';
$router['submit'] = 'submit';
$router['bounce'] = 'bounce';

/*
 * Identify which method is being requested, and whether any parameters are being passed to that
 * method.
 */
$url_components = parse_url($_SERVER['REQUEST_URI']);
$method = str_replace('/api/', '', $url_components['path']);
if (strpos($method, '/') !== FALSE)
{

	$tmp = explode('/', $method);
	$method = $tmp[0];
	if (!empty($tmp[1]))
	{
		$parameter = $tmp[1];
	}

}

/*
 * If our method is invalid, fail with a 404.
 */
if ( ($method === FALSE) || !isset($router[$method]) )
{

	header("HTTP/1.0 404 Not Found");
	echo '404 Not Found';
	exit();

}

/*
 * Enable cross-origin resource sharing (CORS).
 */
header("Access-Control-Allow-Origin: *");

/*
 * Pass off the request to the relevant router.
 */
include '../includes/' . $router[$method] . '.inc.php';
