<?php
class Email
{
    /**
     * sendEmail
     * 
     * @param string $to
     * @param string $from
     * @param string $replyto
     * @param string $cc
     * @param string $bcc
     * @param string $subject
     * @param string $body
     */
    public static function sendEmail(
        $to = false, $from = false, $replyto = false, $cc = false, $bcc = false, $subject = false, $body = false
    )
    {
        Errors::debugLogger(__METHOD__.': to: '.$to);
        // Email Headers
        $headers   = array();
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/html; charset=iso-8859-1";
        if (!empty($from)) {
            $headers[] = "From: " . $from;
        }
        if (!empty($cc)) {
            $headers[] = "Cc: " . $cc;
        }
        if (!empty($bcc)) {
            $headers[] = "Bcc: " . $bcc;
        }
        if (!empty($replyto)) {
            $headers[] = "Reply-To: " . $replyto;
        }
        $headers[] = "Subject: " . $subject;
        $headers[] = "X-Mailer: PHP/" . phpversion();
        $headers[] = "";
        
        // Mail it
        try {
            // @TODO smtp server settings
            mail($to, $subject, $body, implode("\r\n", $headers), '-f '.$from);
            Errors::debugLogger(__METHOD__.": Email sent successfully!");
            return true;
        } catch (\Exception $e) {
            trigger_error('Unable to send email. ' . $e->getMessage(), E_USER_WARNING);
            return false;
        }
    }

}