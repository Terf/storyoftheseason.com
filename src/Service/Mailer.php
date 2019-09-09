<?php
namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    public function send($recipient, $subject, $message)
    {
        $html = '
        <!DOCTYPE html>
        <html>
          <head>
          </head>
          <body style="margin: 0; padding: 10px; mso-line-height-rule: exactly; min-width: 100%; background-color: #ffffff;color: #000; font-family: Roboto, Helvetica, sans-serif; font-weight: 400; font-size: 12px; line-height: 14px;"">
            '.$message.'
          </body>
        </html>';

        if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = 'mail.storyoftheseason.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'chris@storyoftheseason.com';
            $mail->Password = getenv('MAILER_PASSWORD');
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom('chris@storyoftheseason.com', 'Story of the season');
            $mail->addAddress($recipient);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $html;
            $mail->AltBody = strip_tags($message);
            //This should be the same as the domain of your From address
            $mail->DKIM_domain = 'storyoftheseason.com';
            //Path to your private key:
            $mail->DKIM_private = '/opendkim/mail.private';
            //Set this to your own selector
            $mail->DKIM_selector = 'mail';
            //Put your private key's passphrase in here if it has one
            $mail->DKIM_passphrase = '';
            //The identity you're signing as - usually your From address
            $mail->DKIM_identity = $mail->From;
            $mail->send();
        }

    }
}
