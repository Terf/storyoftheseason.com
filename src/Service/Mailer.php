<?php
namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    public function send($recipient, $subject, $html)
    {
        $html = '
        <!DOCTYPE html>
        <html>
          <head>
          <style>
        @media only screen and (min-width: 0) {
          .wrapper {
            text-rendering: optimizeLegibility;
          }
        }
        @media only screen and (max-width: 620px) {
          [class=wrapper] {
            min-width: 302px !important;
            width: 100% !important;
          }
        
          [class=wrapper] .block {
            display: block !important;
          }
        
          [class=wrapper] .hide {
            display: none !important;
          }
        
          [class=wrapper] .top-panel,
        [class=wrapper] .header,
        [class=wrapper] .main,
        [class=wrapper] .footer {
            width: 302px !important;
          }
        
          [class=wrapper] .title,
        [class=wrapper] .subject,
        [class=wrapper] .signature,
        [class=wrapper] .subscription {
            display: block;
            float: left;
            width: 300px !important;
            text-align: center !important;
          }
        
          [class=wrapper] .signature {
            padding-bottom: 0 !important;
          }
        
          [class=wrapper] .subscription {
            padding-top: 0 !important;
          }
        }
        </style>
          </head>
          <body style="margin: 0; padding: 0; mso-line-height-rule: exactly; min-width: 100%; background-color: #ffffff;">
            <center class="wrapper" style="display: table; table-layout: fixed; width: 100%; min-width: 620px; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; background-color: #ffffff;">
                <table class="top-panel center" width="602" border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse; border-spacing: 0; margin: 0 auto; width: 602px;">
                    <tbody>
                    <tr>
                        <td class="title" width="300" style="vertical-align: top; text-align: left; width: 300px; padding: 8px 0; color: #616161; font-family: Roboto, Helvetica, sans-serif; font-weight: 400; font-size: 12px; line-height: 14px;" valign="top" align="left">Story of the Season</td>
                        <td class="subject" width="300" style="vertical-align: top; text-align: right; width: 300px; padding: 8px 0; color: #616161; font-family: Roboto, Helvetica, sans-serif; font-weight: 400; font-size: 12px; line-height: 14px;" valign="top" align="right"><a class="strong" href="https://storyoftheseason.com" target="_blank" style="text-decoration: none; color: #616161; font-weight: 700;">storyoftheseason.com</a></td>
                    </tr>
                    <tr>
                        <td class="border" colspan="2" style="padding: 0; vertical-align: top; font-size: 1px; line-height: 1px; background-color: #e0e0e0; width: 1px;" width="1" valign="top" bgcolor="#e0e0e0">&nbsp;</td>
                    </tr>
                    </tbody>
                </table>
        
                <div class="spacer" style="font-size: 1px; width: 100%; line-height: 16px;">&nbsp;</div>
        
                <table class="main center" width="602" border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse; border-spacing: 0; -webkit-box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.12), 0 1px 2px 0 rgba(0, 0, 0, 0.24); -moz-box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.12), 0 1px 2px 0 rgba(0, 0, 0, 0.24); box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.12), 0 1px 2px 0 rgba(0, 0, 0, 0.24); margin: 0 auto; width: 602px;">
                    <tbody>
                    <tr>
                        <td class="column" style="padding: 0; vertical-align: top; text-align: left; background-color: #ffffff; font-size: 14px;" valign="top" align="left" bgcolor="#ffffff">
                            <div class="column-top" style="font-size: 24px; line-height: 24px;">&nbsp;</div>
                            <table class="content" border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse; border-spacing: 0; width: 100%;" width="100%">
                                <tbody>
                                <tr>
                                    <td class="padded" style="vertical-align: top; padding: 0 24px;" valign="top">
                                      '.$html.'
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <div class="column-bottom" style="font-size: 8px; line-height: 8px;">&nbsp;</div>
                        </td>
                    </tr>
                    </tbody>
                </table>
        
                <div class="spacer" style="font-size: 1px; width: 100%; line-height: 16px;">&nbsp;</div>

            </center>
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
