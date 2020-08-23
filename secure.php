<?php
/**
 * This example shows how to send via Google's Gmail servers using XOAUTH2 authentication.
 */

//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;

// Alias the League Google OAuth2 provider class
use League\OAuth2\Client\Provider\Google;
$config = json_decode(file_get_contents(__DIR__ . "/settings.json"), true);

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

//Load dependencies from composer
//If this causes an error, run 'composer install'
require __DIR__.'/vendor/autoload.php';

//Create a new PHPMailer instance
$mail = new PHPMailer;

//Tell PHPMailer to use SMTP
$mail->isSMTP();

//Enable SMTP debugging
// SMTP::DEBUG_OFF = off (for production use)
// SMTP::DEBUG_CLIENT = client messages
// SMTP::DEBUG_SERVER = client and server messages
$mail->SMTPDebug = SMTP::DEBUG_SERVER;

//Set the hostname of the mail server
$mail->Host = 'smtp.gmail.com';

//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
$mail->Port = 587;

//Set the encryption mechanism to use - STARTTLS or SMTPS
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

//Whether to use SMTP authentication
$mail->SMTPAuth = true;

//Set AuthType to use XOAUTH2
$mail->AuthType = 'XOAUTH2';

//Create a new OAuth2 provider instance
$provider = new Google(
    [
        'clientId' => $config['token']['clientId'],
        'clientSecret' => $config['token']['clientSecret'],
    ]
);

//Pass the OAuth provider instance to PHPMailer
$mail->setOAuth(
    new OAuth(
        [
            'provider' => $provider,
            'clientId' => $config['token']['clientId'],
            'clientSecret' => $config['token']['clientSecret'],
            'refreshToken' => $config['token']['refreshToken'],
            'userName' => $config['username'],
        ]
    )
);

//Set who the message is to be sent from
//For gmail, this generally needs to be the same as the user you logged in as
$mail->setFrom($config['username'], $config['nickname']);

//Set an alternative reply-to address
$mail->addReplyTo($config['username'], $config['nickname']);

//Set who the message is to be sent to
$mail->addAddress($config['sendTo'], $config['sendName']);


//Set the subject line
$mail->Subject = 'PHPMailer GMail XOAUTH2 SMTP test';


$mail->CharSet = PHPMailer::CHARSET_UTF8;
$mail->msgHTML(file_get_contents('contents.html'), __DIR__);

//Replace the plain text body with one created manually
$mail->AltBody = 'This is a plain-text message body';

//send the message, check for errors
if (!$mail->send()) {
    echo 'Mailer Error: '. $mail->ErrorInfo;
} else {
    echo 'Message sent!';
}