<?php

/**
 * The main page for the site, which doubles as a router.
 *
 * PHP version 5
 *
 * @license		https://github.com/TrustTheVote-Project/horatio-server/blob/master/LICENSE
 * @version		1.0
 * @link		https://github.com/TrustTheVote-Project/horatio-server/
 * @since		1.0
 *
 */

/*
 * Include the site settings.
 */
require 'includes/settings.inc.php';

/*
 * Show PHP errors when debug mode is enabled.
 */
if (DEBUG_MODE === TRUE)
{
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
}

/*
 * Refuse non-secured connections, unless the server is in debug mode.
 */
if ( (DEBUG_MODE === FALSE) && empty($_SERVER['HTTPS']) )
{

	header('HTTP/1.1 400 Bad Request');
	$response = array();
	$response['valid'] = FALSE;
	$response['errors'] = array();
	$response['errors']['invalid protocol'] = 'request must be submitted via HTTPS';
	$json = json_encode($response);
	echo $json;
	exit();
}

/*
 * Load Composer requirements.
 */
require 'vendor/autoload.php';

/*
 * Set up our own autoloader.
 */
function my_autoloader($class)
{
    require 'includes/class.' . $class . '.inc.php';
}
spl_autoload_register('my_autoloader');

/*
 * Establish our routing table, describing the permitted HTTP request method.
 */
$router = array();
$router['registrars'] = 'GET';
$router['validator'] = 'POST';
$router['submit'] = 'POST';
$router['bounce'] = 'GET';

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
 * Enable cross-origin resource sharing (CORS).
 */
header("Access-Control-Allow-Origin: *");

/*
 * If our API method is invalid, fail with a 404.
 */
if ( ($method === FALSE) || !isset($router[$method]) )
{

	header("HTTP/1.0 404 Not Found");
	echo '404 Not Found';
	exit();

}

/*
 * If our API method requires POST data, make sure that it's present and make its
 * contents available.
 */
if ($router[$method] == 'POST')
{
	
	/*
	 * Get the provided JSON.
	 */
	$uploaded_file = json_decode(file_get_contents('php://input'));

	/*
	 * If the uploaded JSON file cannot be converted into an object by PHP, then display
	 * an error.
	 */
	if ($uploaded_file === FALSE)
	{

		header("HTTP/1.0 422 Unprocessable Entity");

		$response = array();
		$response['valid'] = FALSE;
    	$response['errors'] = array();
    	$response['errors']['invalid JSON'] = 'JSON is too broken to decode';
		$json = json_encode($response);
		echo $json;
		exit();

	}

}

/*
 * Pass off the request to the relevant router.
 */
include 'includes/methods/' . $method . '.inc.php';
