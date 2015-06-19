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
 * Generate a unique ID for this ballot. A 32-digit hash is excessive, so we just use the first 10
 * digits.
 */
$ab_id = substr(md5(json_encode($ab)), 0, 10);

/*
 * Identify the registrar to whom this application should be sent.
 */
$gnis_id = $ab->election->locality_gnis;
$registrars = json_decode(file_get_contents('includes/registrars.json'));
$registrar_email = $registrars->$gnis_id->email;

/*
 * Save this application as a PDF.
 */
$values = $ab;
require('includes/pdf_generation.inc.php');

/*
 * Send the PDF to the site operator if the site is in debug mode.
 */
if (DEBUG_MODE === TRUE)
{
	$registrar_email = SITE_EMAIL;
}

use Mailgun\Mailgun;
$mg = new Mailgun(MAILGUN_API_KEY);
$domain = MAILGUN_DOMAIN;

/*
 * Assemble and send the message.
 */
$mg->sendMessage($domain, array('from'    => SITE_EMAIL, 
                                'to'      => $registrar_email,
                                'subject' => 'Absentee Ballot Request', 
                                'text'    => 'Please find attached an absentee ballot request.'),
								array(array('filePath'		=> 'applications/' . $ab_id; . '.pdf',
											'remoteName'	=> 'ab-' . $ab_id)));

/*
 * TO DO: Make sure everything went OK.
 */

/*
 * Inform the client of the success.
 */
$response['valid'] = TRUE;
$response['id'] = $ab_id;
$response['pdf_url'] = SITE_URL . 'applications/' . $ab_id; . '.pdf';

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
