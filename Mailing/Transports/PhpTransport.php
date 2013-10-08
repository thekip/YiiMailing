<?php

/**
 * Send Emailes via php mail() function
 * @see mail()
 * @author t.yacenko
 */
class PhpTransport implements IMailerTransport{
    
    public function init() {
        ;
    }
    public function send($from, $recipient, $headers, $body) {
        return mail($recipient, "", $body, $headers);
    }
}
