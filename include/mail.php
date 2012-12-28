<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Functions to send emails.
 */

namespace JarisCMS\Email;

/**
 * Sends an email using phpmailer with system configurations for mail on admin/settings/mailer.
 *
 * @param array $to In the format $to["John Smoth"] = "jsmith@domain.com"
 * @param string $html_message html code to send
 * @param string $alt_message optional plain text message in case email client doesn't supports html
 * @param array $attachments files path list to attach
 * @param array $reply_to In the format $reply_to["John Smith"] = "jsmith@domain.com"
 * @param array $bcc In the format $bcc["John Smith"] = "jsmith@domain.com"
 * @param array $cc In the format $cc["John Smith"] = "jsmith@domain.com"
 * @param array $from In the format $cc["John Smith"] = "jsmith@domain.com"
 * 
 * @return bool True if sent false if not.
 */
function Send($to, $subject, $html_message, $alt_message=null, $attachments=array(), $reply_to=array(), $bcc=array(), $cc=array(), $from=array())
{
    $mail = new \PHPMailer();
    
    $mail->PluginDir = 'include/third_party/phpmailer/';
    
    $mail->IsHTML();
    $mail->CharSet = "utf-8";
    $mail->Subject = $subject;
    $mail->AltBody = $alt_message;
    $mail->MsgHTML($html_message);
    $mail->WordWrap = 50;
    
    if(count($from) > 0)
    {
        foreach($from as $from_name=>$from_email)
        {
            $mail->SetFrom($from_email, $from_name);
            break;
        }
    }
    else 
    {
        $mail->SetFrom(\JarisCMS\Setting\Get('mailer_from_email', 'main'), \JarisCMS\Setting\Get('mailer_from_name', 'main'));
    }
    
    switch(\JarisCMS\Setting\Get('mailer', 'main'))
    {
        case 'sendmail':
            $mail->IsSendmail();
            break;
        case 'smtp':
        {
            $mail->IsSMTP();
            
            $mail->SMTPAuth = \JarisCMS\Setting\Get('smtp_auth', 'main');
            if(\JarisCMS\Setting\Get('smtp_ssl', 'main'))
            {
                $mail->SMTPSecure = 'ssl';
            }
            $mail->Host = \JarisCMS\Setting\Get('smtp_host', 'main');
            $mail->Port = \JarisCMS\Setting\Get('smtp_port', 'main');

            $mail->Username = \JarisCMS\Setting\Get('smtp_user', 'main');
            $mail->Password = \JarisCMS\Setting\Get('smtp_pass', 'main');
            break;
        }
        default:
            $mail->IsMail();
    }

    foreach($reply_to as $name=>$email)
    {
        $mail->AddReplyTo($email, $name);
    }
    
    //Add email addresses
    foreach($to as $name=>$email)
    {
        $mail->AddAddress($email, $name);
    }
    
    //Add hidden carbon copies
    foreach($bcc as $name=>$email)
    {
        $mail->AddBCC($email, $name);
    }
    
    //Add carbon copies
    foreach($cc as $name=>$email)
    {
        $mail->AddCC($email, $name);
    }
    
    foreach($attachments as $file_name=>$file_path)
    {
        if(!is_int($file_name))
            $mail->AddAttachment($file_path, $file_name);
        else
            $mail->AddAttachment($file_path);
        
    }

    return $mail->Send();
}
?>
