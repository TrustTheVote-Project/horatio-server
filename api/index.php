<?php

/*
 * Show debug code.
 */
if (DEBUG_MODE === TRUE)
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
}


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
	 * TODO: Figure out how to sanitize JSON. Maybe check that the file contains colons, 
	 * that's its within a certain length limit? Don't do anything to filter out non-ASCII
	 * characters, because billions of people's names cannot be validated with ([A-Za-z]{2,}).
	 *
	 * Read the file into memory.
	 */
	if (!empty($_FILES))
	{

		reset($_FILES);
		$file = $_FILES[key($_FILES)];
		$uploaded_file = json_decode(file_get_contents($file['tmp_name']));

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

}

/*
 * Pass off the request to the relevant router.
 */
include 'includes/' . $method . '.inc.php';
