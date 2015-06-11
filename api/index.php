<?php

/*
 * Show debug code.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
 * Load Composer requirements.
 */
require 'vendor/autoload.php';

/*
 * Establish our routing table, describing the permitted HTTP request method.
 */
$router = array();
$router['registrars'] = 'GET';
$router['validator'] = 'POST';
$router['submit'] = 'POST';
$router['bounce'] = 'POST';

/*
 * Identify which API method is being requested, and whether any parameters are being passed to
 * that API method.
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
include 'includes/' . $method . '.inc.php';
