<?php

/**
 * This transport is used for debugging purposes. 
 * You should put DebugWidget somewhere in you page, and you will see emailes.
 *
 * @author t.yacenko
 */
class DebugTransport implements IMailerTransport {
    
    protected $emailes = array();
    
    public function init() {}
    
    public function send($from, $recipient, $headers, $body) {
        $debug = Yii::app()->controller->renderPartial('ext.Mailing.views.debug', array(
            'from' => $from,
            'recipient' => $recipient,
            'source' => $headers.$body,
            'headers' => $headers
        ), true);
        
         
        $this->emailes[] = $debug;
       
        Yii::app()->user->setFlash('emailDebug', $this->emailes);
    }
}
