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
 * Validate the submitted JSON.
 */
$retriever = new JsonSchema\Uri\UriRetriever;
$schema = $retriever->retrieve('file://' . realpath('includes/schema.json'));
$validator = new JsonSchema\Validator();
$validator->check($ab, $schema);

/*
 * If the JSON is not valid, return an error and halt.
 */
if ($validator->isValid() === FALSE)
{

    header('HTTP/1.1 400 Bad Request');
    $response['valid'] = FALSE;
    $response['errors'] = array();
    foreach ($validator->getErrors() as $error)
    {

    	if (empty($error['property']))
    	{
    		$error['property'] = 'undefined';
    	}

        $response['errors'][$error{'property'}] = $error['message'];

    }

	$json = json_encode($response);
	echo $json;
	exit();

}

/*
 * Generate a unique ID for this ballot. A 32-digit hash is excessive, so we just use the first 10
 * digits.
 */
$ab_id = substr(md5(json_encode($ab)), 0, 10);

/*
 * If we've already sent a ballot with this ID, refuse to re-send it.
 */
if (file_exists('applications/' . $ab_id . '.json'))
{

    header('HTTP/1.1 400 Bad Request');
    $response['valid'] = FALSE;
    $response['errors'] = 'This application is a duplicate of one that has already been submitted.';
    $json = json_encode($response);
    echo $json;
    exit();

}

/*
 * Identify the registrar to whom this application should be sent. If the proper registrar's
 * email address is bouncing messages, send the message to the fallback address instead.
 */
$gnis_id = $ab->election->locality_gnis;
$registrars = json_decode(file_get_contents('includes/registrars.json'));
if (!isset($registrars->$gnis_id->invalid_email) || $registrars->$gnis_id->invalid_email === FALSE)
{
	$registrar_email = $registrars->$gnis_id->email;
}
else
{
	$registrar_email = FALLBACK_REGISTRAR_EMAIL;
}

/*
 * Save this application as a PDF.
 */
$values = $ab;
require('includes/pdf_generation.inc.php');

/*
 * Send the PDF to the site operator if the site is in debug mode. Or, if we have an email address
 * for the applicant, send it to that address. (This facilitates multi-user beta beta testing.)
 */
if (DEBUG_MODE === TRUE)
{
    $registrar_email = SITE_EMAIL;
}
elseif ($ab->name->last == 'Jaquith')
{
    $registrar_email = 'waldo@jaquith.org';
}

/*
 * Set up a new Mailgun instance.
 */
use Mailgun\Mailgun;
$mg = new Mailgun(MAILGUN_API_KEY);

/*
 * Assemble the email.
 */
$message = $mg->MessageBuilder();
$message->setFromAddress(SITE_EMAIL, array('first' => SITE_OWNER));
$message->addToRecipient($registrar_email);
$message->setSubject('Absentee Ballot Request');
$message->setTextBody('Please find attached an absentee ballot request.');
$message->addAttachment('@applications/' . $ab_id . '.pdf');
$message->addCustomHeader('X-AB-ID', $ab_id);

/*
 * If there are email addresses to which the application should be BCCed, include them. 
 */
if (defined('APPLICATION_BCC'))
{
    if (strpos(APPLICATION_BCC, ',') !== FALSE)
    {
        $addresses = explode(',', APPLICATION_BCC);
    }
    else
    {
        $addresses = array(APPLICATION_BCC);
    }
    foreach ($addresses as $address)
    {
        if (filter_var(APPLICATION_BCC, FILTER_VALIDATE_EMAIL))
        {
            $message->addBccRecipient(APPLICATION_BCC);
        }
    }
}

/*
 * Send the email.
 */
$result = $mg->post(MAILGUN_DOMAIN . '/messages', $message->getMessage(), $message->getFiles());

/*
 * If there was an error in the process of sending the message, report that to the client.
 */
if ($result->http_response_code != '200')
{

    header('HTTP/1.1 500 Internal Server Error');
	$response['valid'] = TRUE;
	$response['success'] = FALSE;
    $response['errors'] = 'Could not send email. ' . $result->http_response_body->items[0]->message;
    echo json_encode($response);

	/*
     * Also, send a note to the site operator.
     */
    $message = $mg->MessageBuilder();
	$message->setFromAddress(SITE_EMAIL);
	$message->addToRecipient(SITE_EMAIL);
	$message->setSubject('Absentee Ballot Request Failed');
	$message->setTextBody(	'A submitted absentee ballot request on ' . SITE_URL . ' just failed '
							. 'to be sent via email, and requires manual intervention. See '
                            . $ab_id . 'at ' . SITE_URL . 'applications/' . $ab_id . '.pdf');
	$message->addAttachment('@applications/' . $ab_id . '.pdf');
	$mg->post(MAILGUN_DOMAIN . '/messages', $message->getMessage(), $message->getFiles());

    exit();

}

/*
 * Store a copy of the application's data, appending the Mailgun message ID and the identified
 * registrar.
 */
$tmp = $ab;
unset($ab);
$ab = new stdClass();
$ab->request = new stdClass();
$ab->request = $tmp;
unset($tmp);
$ab->message_id = $result->http_response_body->id;
$ab->registrar = $registrars->$gnis_id;
$ab->client_url = $_SERVER['HTTP_REFERER'];
file_put_contents('applications/' . $ab_id . '.json', json_encode($ab));

/*
 * Inform the client of the success.
 */
$response['valid'] = TRUE;
$response['success'] = TRUE;
$response['id'] = $ab_id;
$response['pdf_url'] = SITE_URL . 'applications/' . $ab_id . '.pdf';
$response['registrar'] = (array) $registrars->$gnis_id;

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
