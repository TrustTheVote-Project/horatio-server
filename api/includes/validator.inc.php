<?php

/*
 * Get the schema and the submitted JSON.
 */
$retriever = new JsonSchema\Uri\UriRetriever;
$schema = $retriever->retrieve('file://' . realpath('../includes/schema.json');
$data = json_decode(file_get_contents('ballot-completed.json'));

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
    echo 'valid'
}

/*
 * It is not valid.
 */
else
{
    echo 'invalid';
    foreach ($validator->getErrors() as $error)
    {
        echo $error['property'] . ': ' $error['message'];
    }
}
