<?php

/**
 * The JSON validation API method
 *
 * PHP version 5
 *
 * @license     https://github.com/TrustTheVote-Project/horatio-server/blob/master/LICENSE
 * @version     1.0
 * @link        https://github.com/TrustTheVote-Project/horatio-server/
 * @since       1.0
 *
 */

/*
 * Create an empty array to encode our response.
 */
$response = array();

if (!isset($uploaded_file))
{

    $response['valid'] = FALSE;
    $response['errors'] = 'No file provided.';
    echo json_encode($response);
    exit();

}

/*
 * Get the schema and the submitted JSON.
 */
$retriever = new JsonSchema\Uri\UriRetriever;
$schema = $retriever->retrieve('file://' . realpath('includes/schema.json'));
$data = $uploaded_file;

/*
 * Validate the submitted JSON against the schema.
 */
$validator = new JsonSchema\Validator();
$validator->check($data, $schema);

/*
 * It's valid.
 */
if ($validator->isValid())
{
    $response['valid'] = TRUE;
}

/*
 * It is not valid.
 */
else
{
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
}

$json = json_encode($response);

if ($json === FALSE)
{
	$response['errors'] = TRUE;
	$json = json_encode($response);
}


echo $json;
