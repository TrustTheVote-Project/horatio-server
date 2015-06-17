<?php

/*
 * Your unique API key, provided by Mailgun <https://mailgun.com/>. This is required to send
 * absentee ballot request PDFs to local registrars.
 */
MAILGUN_API_KEY = '';

/*
 * The API key that your own server requires for API requests to the "bounce" method. This is
 * how Mailgun provides alerts that a given registrar's email address is rejecting emails. The
 * system will deactivate the registrar's email address. But you don't want anybody to be able
 * to disable transmission of absentee ballots to arbitrary registrars, so you'll instruct
 * Mailgun to use the value of BOUNCE_API_KEY in the URL to which it sends bounce reports. This
 * can be set to any password-like value that you like. It is recommended that you use a
 * 32-character string of letters, numbers, and punctuation.
 */
BOUNCE_API_KEY = '';
