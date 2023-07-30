<?php

/**
 * This class enables email sending for the API.
 * This is used to send security notifications.
 */
class Mailer
{
    private string $from = "webmaster@localhost";

    function __construct()
    {
        $this->from = "mailer@".$_SERVER['HTTP_HOST'];
    }

    public function send_mail($to, $subject, $title, $html_body, $template = "global")
    {
        $mail_body = file_get_contents(__DIR__."/templates/$template.template.html");
        $mail_body = str_replace('{{subject}}', htmlentities($subject), $mail_body);
        $mail_body = str_replace('{{title}}', htmlentities($title), $mail_body);
        $mail_body = str_replace('{{html_body}}', $html_body, $mail_body);

        return mail(
            $to,
            $subject,
            $mail_body,
            implode("\r\n", [
                "Content-Type: text/html; charset=utf-8",
                "From: MajestiCloud <".$this->from.">",
                'X-Mailer: PHP/' . phpversion()
            ])
        );
    }
}
