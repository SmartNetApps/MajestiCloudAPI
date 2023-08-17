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
        $this->from = "mailer@" . $_SERVER['HTTP_HOST'];
    }

    private function base64_logo()
    {
        $logo_path = __DIR__ . "/../../public_endpoints/logo.png";
        $logo = base64_encode(file_get_contents($logo_path));
        $mime_type = mime_content_type($logo_path);
        return "data:$mime_type;base64,$logo";
    }

    public function send_mail($to, $subject, $title, $html_body, $template = "global")
    {
        $mail_body = file_get_contents(__DIR__ . "/templates/$template.template.html");
        $mail_body = str_replace('{{logo}}', $this->base64_logo(), $mail_body);
        $mail_body = str_replace('{{subject}}', htmlentities($subject), $mail_body);
        $mail_body = str_replace('{{title}}', htmlentities($title), $mail_body);
        $mail_body = str_replace('{{html_body}}', $html_body, $mail_body);

        return mail(
            $to,
            $subject,
            $mail_body,
            implode("\r\n", [
                "Content-Type: text/html; charset=utf-8",
                "From: MajestiCloud <" . $this->from . ">",
                'X-Mailer: PHP/' . phpversion()
            ])
        );
    }

    public function validation_email($to, $validation_key)
    {
        if(empty($to) || empty($validation_key)) return;
        $url = ($_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://').$_SERVER['HTTP_HOST'] . "/user/verify_email.php?email=" . urlencode($to) . "&key=" . urlencode($validation_key);
        $this->send_mail(
            $to,
            "Please validate your email address",
            "Validation is required",
            '<p>Please validate your email address by clicking on the link or by copying and pasting it into a browser window.</p>
            <a href="' . $url . '">' . $url . '</a>'
        );
    }
}
