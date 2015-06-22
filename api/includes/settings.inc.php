<?php

/*
 * Whether the server is in debug mode. If it is in debug mode, HTTPS is not required, and PDFs
 * will not be delivered to registrars. If it is not in debug mode, HTTPS is strictly enforced for
 * all requests, and PDFs will be emailed to the site operator instead of the relevant registrar.
 */
define('DEBUG_MODE', TRUE);

/*
 * Your Mailgun <https://mailgun.com/> credentials, required to send absentee ballot request PDFs
 * to local registrars.. This is your unique API key and the email domain that you've set up with
 * them.
 */
define('MAILGUN_API_KEY', '');
define('MAILGUN_DOMAIN', '');

/*
 * The API key that your own server requires for API requests to the "bounce" method. This is
 * how Mailgun provides alerts that a given registrar's email address is rejecting emails. The
 * system will deactivate the registrar's email address. But you don't want anybody to be able
 * to disable transmission of absentee ballots to arbitrary registrars, so you'll instruct
 * Mailgun to use the value of BOUNCE_API_KEY in the URL to which it sends bounce reports. This
 * can be set to any password-like value that you like. It is recommended that you use a
 * 32-character string of letters, numbers, and punctuation.
 */
define('BOUNCE_API_KEY', '');

/*
 * The base URL for the site, e.g., https://example.com/, or https://example.com/ballot/.
 */
define('SITE_URL', '');

/*
  * The site owner and email address are required when sending absentee ballot request PDFs to
 * registrars. They need to know where the requests are coming from, and who to contact if
 * something is wrong with the requests. The site owner could be a person's name or it could
 * be an organization's name. It will appear as the "From" field in the email, and SITE_EMAIL
 * will be the email address that the email is sent from. This email address MUST work. Do
 * not use an address like do_not_reply@example.com. When the site is in debug mode, all
 * absentee ballots requests will be emailed to SITE_EMAIL.
 */
define('SITE_OWNER', '');
define('SITE_EMAIL', '');

/*
 * This is the email address to use if there is no valid email address for a registrar.
 * In Virginia, that's info@elections.virginia.gov, which is the email address to which the
 * State Board of Elections asks that ballot requests be emailed under such circumstances.
 */
define('FALLBACK_REGISTRAR_EMAIL', '');
