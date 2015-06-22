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

/*
 * Get the message headers, so we can get our absentee ballot ID ("X-AB-ID") from it.
 */
$headers = filter_input(INPUT_GET, 'message-headers', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_HIGH);

/*
 * If an X-AB-ID header isn't present, something is fishy. Halt.
 */
if (!isset($headers['X-AB-ID'] || strlen($headers['X-AB-ID']) !=  10)
{
	exit();
}

/*
 * This header is safe, so bring it into the local scope.
 */
$ab_id = $headers['X-AB-ID'];

/*
 * If we don't have a copy of this application, again, something is fishy. Halt.
 */
if (!file_exists('applications/' . $ab_id  . '.json'))
{
	exit();
}

/*
 * Get the data from this absentee ballot submission.
 */
$ab = json_decode(file_get_contents('applications/' . $ab_id  . '.json'));

/*
 * If the Mailgun message ID on record doesn't match the one that's been provided then, again,
 * something is fishy. Halt.
 */
if ($ab->mailgun_message_id != filter_input(INPUT_GET, 'Message-Id', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_HIGH))
{
	exit();
}

/*
 * Update the registrars JSON file to mark this email address as unusable.
 */
$registrars = json_decode(file_get_contents('includes/registrars.json'));
foreach ($registrars as &$registrar)
{

	if ($registrar->email = $ab->registrar->email)
	{
		$registrar->invalid_email = TRUE;
		break;
	}

}

/*
 * Save the updated registrars JSON file. We don't bother to check whether this works, because it's
 * not clear what we'd do with the knowledge that it didn't work.
 */
file_put_contents(json_encode($registrars)));
