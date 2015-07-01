<?php

/**
 * The registrar listing API method
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
 * Get the list of registrars.
 */
$registrars = json_decode(file_get_contents('includes/registrars.json'));

if ($registrars === FALSE)
{

	header('HTTP/1.0 500 Internal Server Error');
	echo '500 Internal Server Error';
	exit();

}

/*
 * If a parameter has been passed, that's a GNIS ID, so display that record.
 */
if (isset($parameter))
{

	if (isset($registrars->$parameter))
	{
		echo json_encode($registrars->$parameter);
	}

}

/*
 * If no GNIS ID has been passed, list all of the registrars' records.
 */
else
{

	/*
	 * The key is the GNIS ID for the locality. Make this an explicit element, and turn the object
	 * into an array.
	 */
	$registrars_new = array();
	$i=0;
	foreach ($registrars as $gnis_id => $registrar)
	{
		$registrar->gnis_id = $gnis_id;
		$registrars_new[$i] = $registrar;
		$i++;
	}
	$registrars = $registrars_new;
	unset($registrars_new);
	echo json_encode($registrars);

}
