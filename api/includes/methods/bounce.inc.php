<?php

/*
 * Fail if the bounce API key is not provided in the URL.
 */
if ($_GET['key'] != BOUNCE_API_KEY)
{

	header('HTTP/1.0 403 Forbidden');
	echo '403 Forbidden';
	exit();

}

//message-Id
//recipient
