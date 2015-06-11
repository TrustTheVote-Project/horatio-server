<?php

/*
 * Get the schema and the submitted JSON.
 */
$retriever = new JsonSchema\Uri\UriRetriever;
$schema = $retriever->retrieve('file://' . realpath('includes/schema.json'));
$data = json_decode(file_get_contents('includes/ballot-completed.json'));

/*
 * Validate the submitted JSON against the schema.
 */
$validator = new JsonSchema\Validator();
$validator->check($data, $schema);

/*
 * Create an empty array to encode our response.
 */
$response = array();

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
