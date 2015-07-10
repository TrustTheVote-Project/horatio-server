# Horatio: Absentee Ballot Server

A server for an absentee ballot request form, with [a corresponding client](https://github.com/waldoj/absentee-client). Somebody who wants an absentee ballot can complete a web form, and Horatio will use those values to complete the state-sanctioned PDF and email it to the appropriate registrar. The creation of Horatio was funded by Aneesh Chopra’s Innovate Virginia Fund PAC. It is named for [Horatio Seymour](https://en.wikipedia.org/wiki/Horatio_Seymour), the former governor of New York, who was an ardent opponent of President Abraham Lincoln’s creation of an absentee balloting system to allow deployed Union soldiers to vote in the 1864 election.

## Overview

Fundamentally, this is a system to:

* validate provided JSON against a schema
* use the JSON contents to populate a PDF
* send the contents of that PDF to a geographically-appropriate email and, if the email bounces, send it to a fallback address

Which is to say that it's not very complicated.

First, somebody completes the HTML form produced by [the Horatio client](https://github.com/TrustTheVote-Project/horatio-client/):

![1](https://cloud.githubusercontent.com/assets/656758/8459465/7273aeb0-1feb-11e5-9390-5f7435c55634.png)

The contents of the form are converted to JSON and submitted to the Horatio server:

![2](https://cloud.githubusercontent.com/assets/656758/8459467/72771b36-1feb-11e5-8c51-3a18875bd549.png)

Horatio maps the JSON values to PDF form fields:

![3](https://cloud.githubusercontent.com/assets/656758/8459466/72759950-1feb-11e5-8f12-9898d33c3115.png)

And, finally, it emails that PDF to the appropriate registrar.

## System Requirements

* **PHP.** Horatio has only been tested on Linux with Apache, but it may well work on Windows or with Nginx.
* **SSL (TLS).** Horatio will function over HTTP when it’s in debug mode, but to actually put it to work, it requires that the connection be encrypted, via HTTPS.

## Instructions

1. Download and install the `api/` directory onto a web server. This may be at the root of a domain name, or within a subdirectory.
1. Install [Composer](https://getcomposer.org/) in the directory (e.g., `curl -sS https://getcomposer.org/installer | php`).
1. Auto-install source libraries (JSON Schema, Mailgun, and Guzzle) with `php composer.phar install`.
1. Set up an account with [Mailgun](https://mailgun.com/) (no credit card number required; <10,000 emails/month is free), following their instructions to get SPF records added to DNS for the domain.
1. Choose "Webhooks" from the Mailgun dashboard, and for both "Hard bounces" and "Dropped messages," enter your site’s URL followed by `/bounce/?key=` and a 32-character random string, e.g. e.g., `http://example.com/bounce/?key=qTugfIdCvB9SjymJW5yqQUofQu9iU119`. Keep a copy of this 32-character string until the next step, where it’s the value of `BOUNCE_API_KEY`.
1. Configure the site settings in `includes/settings.inc.php`.
1. Ensure that the directory `applications/` has write permissions for the web server, but not read permissions (i.e., `drwx-wx-wx`), because that is where the completed absentee ballot requests will be stored.

## Credits

Horatio was created by [Waldo Jaquith](https://github.com/waldoj) and funded by the Innovate Virginia Fund PAC.
