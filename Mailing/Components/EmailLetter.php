<?php

/**
 * This class represents one letter
 *
 * @property string $body - if you set body directly, it will be used, otherwise will be used layout and view
 * @property string $recipient
 * @property string $sender
 * @property-write string $subject
 * @property-read EmailAttach[] $attaches
 * @property-read string $messageId
 * @author t.yacenko
 */
class EmailLetter extends CComponent {
    protected $_subject;
    protected $_recipient;
    public $recipientEmail;

    /**
     * Plain Text Message. This Messsage will be shown if mail client doesnt't accept html
     * @var type 
     */
    public $plainText = "";
    
    protected $_text;
    protected $_messageId;
    protected $_sender;
    protected $_body;
    
    /**
     *
     * @var string Email Layout 
     */
    public $layout = "";
    
    /**
     *
     * @var string Email View 
     */
    public $view = "";
    
    /**
     *
     * @var array Email View Params 
     */
    public $viewVars = array();
    
    /**
     *
     * @var string path to views 
     */
    public $viewPath = "";
    
    /**
     *
     * @var string 
     */
    public $senderEmail;
    
    public function __construct($subject=false, $body=false, $recipient= false, $sender = false) {
        if ($subject) {
            $this->subject = $subject;
        }
        
        if ($body) {
             $this->body = $body;
        }
        
        if ($sender) {
            $this->setSender($sender);
        }
        if ($recipient) {
            $this->recipient = $recipient;
        }
        
        $this->_messageId = "trvlhldng" . uniqid(time());
    }
    
    public function setBody($value){
        $this->body = $value;
    }
    
    public function getBody(){
        $message = "";
        if ($this->body != null) {
            $message = $this->body;
        } elseif ($this->view) {
            $message = Yii::app()->controller->renderPartial($this->viewPath.$this->view, CMap::mergeArray(array('email' => $this), $this->viewVars), true);
        }
        
        if ($this->layout) {
            $message = Yii::app()->controller->renderPartial($this->viewPath.$this->layout, array('content' => $message), true);
        }
        
        return $message;
    }
    
     /**
     * Set sender.
     * Sender may be just email, or email with sender name. 
     * Yoy can directly set email and name by this setter, or you can use $emailLetterObject->sender property; 
     *
     * Example:
     * <pre>
     * $emailLetterObject->sender = "Sender Name <sender@email.com>";
     * OR
     * $emailLetterObject->sender = "sender@email.com";
      * OR use setter
      * $emailLetterObject->setSender("sender@email.com", "Sender Name");
     * </pre>
     * @param string $email sender adress (return adress)
     * @param String $name sender name 
     * @return \SmtpMailer
     */
    public function setSender($email, $name = null) {

        if (strpos($email, '<') != -1) {
            $matches = array();
            preg_match("/(.*) ?<(.*)>/", $email, $matches);
            $name = $matches[1];
            $email = $matches[2];
        }
        
        $this->senderEmail = $email;
        $this->_sender = ($name ? $this->encodeString($name) : '') . " <$email>";
 
        return $this;
    }
    
    public function getSender (){
        return $this->_sender;
    }
    

    /**
     *
     * @var EmailAttach[] 
     */
    protected $_attaches;
    
    /**
     * Set subject
     * @param string $subject
     * @return EmailLetter
     */
    public function setSubject($subject) {
        $this->_subject = $this->encodeString($subject);
        return $this;
    }
    
    /**
     * Get Subject
     * @return string
     */
    public function getSubject(){
        return $this->_subject;
    }
    
    /**
     * 
     * @param type $email
     * @param type $name
     * @return \EmailLetter
     */
    public function setRecipient($email, $name = false){
        $this->recipientEmail = $email;
        $this->_recipient = ($name ? $this->encodeString($name) : '') . " <$email>";
 
        return $this;
    }
    
    public function getRecipient(){
        return $this->_recipient;
    }
    
    public function getMessageId(){
        return $this->_messageId;
    }
 
    
    public function getEmailSource(){
        
    }
    
    /**
     * 
     * @return EmailAttach[]
     */
    public function getAttaches(){
        return $this->_attaches;
    }
    
    /**
     * Add attach to letter.
     * @param string $file_path - full path to file
     * @param string $file_name - file name (will be shown in email client)
     * @param string $cid -  attach id (CID)
     * @return string cid random generated CID (if you didn't set it directly)
     */
    public function addAttach($file_path, $file_name = false, $cid=false) {

        $attach = new EmailAttach($file_path, $file_name, $cid);
        $this->_attaches[$attach->cid] = $attach;
      
        return $attach->cid;
    }
    
     /**
     * Кодирует строку в правильном email формате
      кодируем имя в base64 строку со спец символами
     * @param string $string
     * @return string
     */
    protected function encodeString($string) {
        return "=?UTF8?B?" . base64_encode($string) . "?=";
    }
}


/**
 * Represents one attach.
 * You normally access it via EmailLetter::getAttaches() and construct via EmailLetter::addAttach()
 */
class EmailAttach {

    public $filePath;
    public $fileName;
    public $cid;

    /**
     * 
     * @param string $file_path - full path to file
     * @param string $file_name - file name (will be shown in email client)
     * @param string $cid - attach id (CID)
     */
    public function __construct($file_path, $file_name = false, $cid = false) {
        
        if (!file_exists($file_path)) {
            throw new Exception("Can't attach file, resource `$file_path` not found");
        }
        
        if (!$cid) {
            $cid = md5($file_path);
        }

        if (!$file_name) {
            $file_name = basename($file_path);
        }
        
        $this->fileName = $file_name;
        $this->filePath = $file_path;
        $this->cid = $cid;
    }

}