<?php

/**
 * Description of SmtpMailer
 *
 * @author t.yacenko
 * @property array $trasport transport initialization config
 * @property array $emailDefaults email initialization config
 */
Yii::import('ext.Mailing.Transports.*');
Yii::import('ext.Mailing.Components.*');

class Mailer extends CApplicationComponent {

    protected $sender = "";
    protected $_letterConfig = array(
        'class' => 'EmailLetter',
        'layout' => 'ext.Mailing.views.layout'
    );
    protected $_tansportConfig = array(
        'class' => 'PhpTransport'
    );

    /**
     *
     * @var IMailerTransport 
     */
    protected $_transport;

    /**
     *
     * @var EmailLetter[] 
     */
    protected $letters = array();

    /**
     * Разделитель частей сообщения
     * @var string
     */
    protected $boundary;


    public function setTransport($config) {
        $config = CMap::mergeArray($this->_tansportConfig, $config);

        $component = Yii::createComponent($config);
        if ($component instanceof IMailerTransport) {
            $this->_transport = $component;
            $component->init();
        } else {
            throw new CException(get_class($component) . " is not implement IMailerTransport interface!");
        }
    }

    public function setEmailDefaults($config) {
        $this->_letterConfig = CMap::mergeArray($this->_letterConfig, $config);
    }

    public function init() {
        
        $this->boundary = rand(0, 9) . md5(uniqid(time())) . rand(10000, 99999);

        if ($this->_transport == null) {
            $this->setTransport(array());
        }
    }

    /**
     * Letter Factory. Create letter from string and add it into letters array
     * 
     * @param string $subject
     * @param string $body
     * @param string $recipient
     * @param string $sender
     * @return EmailLetter
     */
    public function createLetterFromString($subject, $body, $recipient = null, $sender = null) {
        /* @var EmailLetter $letter */
        $letter = Yii::createComponent($this->_letterConfig, $subject, $body, $recipient, $sender);

        $this->addLetter($letter);
        return $letter;
    }

    /**
     * Letter Factory. Create letter from view and layout
     * and add it into letters array
     * 
     * @param type $subject
     * @param type $view
     * @param type $params
     * @param type $recipient
     * @param type $sender
     * @return EmailLetter
     */
    public function createLetterFromView($subject, $view, $params=array(), $recipient = null, $sender = null) {
         $letter = $this->createLetterFromString($subject, false, $recipient, $sender);
         $letter->view = $view;
         $letter->viewVars = $params;
         return $letter;
    }
    
    /**
     * 
     * @param EmailLetter $letter письмо
     * @return \SmtpMailer
     */
    public function addLetter(EmailLetter $letter) {
        $this->letters[] = $letter;
        return $this;
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

    /**
     * Начать отправку
     */
    public function sendAll() {
        foreach ($this->letters as $letter) {
            $this->sendOne($letter);
        }
    }

    public function sendOne(EmailLetter $letter) {
        if ($letter->senderEmail and $letter->recipientEmail) {
            $headers = $this->buildHeaders($letter);
            $body = $this->buildBody($letter);

            $this->_transport->send($letter->senderEmail, $letter->recipientEmail, $headers, $body);
        } else {
            throw new Exception("Отправка не возможна, не указан отправитель или получатель !");
        }
    }

    protected function buildHeaders(EmailLetter $letter) {

        $headers = "From: $letter->sender\r\n";
        $headers .= "To: $letter->recipient\r\n";
        $headers .= "Subject: $letter->subject\r\n";
        $headers .= "Reply-To: $letter->sender\r\n";
        $headers .= "X-Mailer: THC Mailing System\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"$this->boundary\"\r\n";

        return $headers;
    }

    /**
     * Формируем аттачи
     * @return string
     */
    protected function buildAttaches(EmailLetter $letter) {
        $files = '';

        if (!empty($letter->attaches)) {
            foreach ($letter->attaches as $attach) {
                $files .= "\r\n--$this->boundary\r\n";
                $files .= "Content-Type: application/octet-stream;name=\"{$attach->fileName}\"\r\n";
                $files .= "Content-Transfer-Encoding:base64\r\n";
                $files .= "Content-ID: <{$attach->cid}>\r\n";
                $files .= "Content-Disposition:attachment; filename=\"{$attach->fileName}\"\r\n\r\n";
                $files .= chunk_split(base64_encode(file_get_contents($attach->filePath))) . "\n";
            }
        }

        return $files;
    }

    protected function buildText(EmailLetter $letter) {
        $text = "";

        if ($letter->plainText != "") {
            $text .= "--$this->boundary\r\n";
            $text .= "Content-Type: text/plain; charset=utf-8\n";
            $text .= "Content-transfer-encoding: 8bit\r\n\r\n";
            $text .= $letter->plainText;
            $text .= "\r\n";
        }

        $text .= "--$this->boundary\r\n";
        $text .= "Content-Type: text/html; charset=utf-8\n";
        $text .= "Content-transfer-encoding: 8bit\r\n\r\n";
        $text .= $letter->body . "\r\n";

        return $text;
    }

    protected function buildBody(EmailLetter $letter) {
        $body = '';
        $body .= $this->buildText($letter);
        $body .= $this->buildAttaches($letter);
        $body .= "\r\n--$this->boundary--\r\n";
        return $body;
    }

}

interface IMailerTransport {

    /**
     * 
     * @param string $from Sender email (only email!)
     * @param string $recipient recipient Email (only email!)
     * @param string $headers prepared headers
     * @param string $body letter body
     */
    public function send($from, $recipient, $headers, $body);
    
    public function init();
}