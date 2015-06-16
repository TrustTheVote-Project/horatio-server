<?php

/*
 * Create an empty array to encode our response.
 */
$response = array();

/*
 * If there's no file, there's nothing to be done.
 */
if (!isset($uploaded_file))
{

    $response['valid'] = FALSE;
    $response['errors'] = 'No file provided.';
    echo json_encode($response);
    exit();

}

/*
 * Change the variable name for the absentee ballot application.
 */
$ab = $uploaded_file;
unset($uploaded_file);

/*
 * Identify the registrar to whom this application should be sent.
 */
$gnis_id = $ab->election->locality_gnis;
$registrars = json_decode(file_get_contents('includes/registrars.json'));
$send_to = $registrars->$gnis_id->email;

/*
 * Save this application as a PDF.
 */
$values = $ab;
require('includes/pdf_generation.inc.php');

/*
 * Send a response to the browser.
 */
$json = json_encode($response);
if ($json === FALSE)
{
	$response['errors'] = TRUE;
	$json = json_encode($response);
}
echo $json;
