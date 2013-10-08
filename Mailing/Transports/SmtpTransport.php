<?php

/**
 * Send Emailes via SMTP server threw socket
 */
class SmtpTransport implements IMailerTransport{
 
    /**
     * Smtp Server Adress
     * @var string 
     */
    public $server = "127.0.0.1";
    
    /**
     * SMTP Server Port
     * @var int 
     */
    public $serverPort = 25;
    
    /**
     * Enable authenticate ? 
     * @var type 
     */
    public $authenticate = false;
    
    public $username = '';
    
    public $password = '';
    
    
    /**
     *
     * @var SmtpClient 
     */
    protected $smtp;
    
    public function init() {
        $this->smtp = new SmtpClient($this->server, $this->serverPort);
        
        if ($this->authenticate) {
            $this->smtp->authenticate($this->username, $this->password);
        }
    }
    
    /**
     * 
     * @param string $from Sender email (only email!)
     * @param string $recipient recipient Email (only email!)
     * @param string $headers prepared headers
     * @param string $body letter body
     */
    public function send($from, $recipient, $headers, $body) {
        $this->smtp->sendFrom($from);
        $this->smtp->sendRecipient($recipient);

        $this->smtp->sendData($headers . $body);
        $this->smtp->reset();
    }
    
}

class SmtpClient {
    
    const RESPONSE_CODE_OK = 250;
 
    /**
     *
     * @var Socket 
     */
    protected $socket;
    
    public function __construct($server, $port) {
        $this->socket = new Socket(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->socket->connect($server, $port);
       
        
        $response = $this->socket->read();
         
         if ($this->parseCode($response) != 220) {
             throw new Exception("Unable connect to SMTP server. Server response: $response");
         };
         
         $this->sendHelo();
    }
    
    /**
     * 
     * @throws Exception
     */
    public function authenticate($username, $password)
    {
        $response = '';
        
        if($this->execCommand("AUTH LOGIN", $response) != self::RESPONSE_CODE_OK)
            throw new Exception($response);

        $code = $this->execCommand(base64_encode($username));
        if($code != self::RESPONSE_CODE_OK && $code != 334)
            throw new Exception($response);

        $code = $this->execCommand(base64_encode($password));
        if($code != self::RESPONSE_CODE_OK && $code != 334)
            throw new Exception($response);
    }
    
    protected function sendHelo($name = "") {
        
        if ($name == "") {
            $name = gethostname();
        }
        
        $response = "";
        if ($this->execCommand("HELO $name", $response) != self::RESPONSE_CODE_OK) {
            throw new Exception("Failed to execute HELO command, server response: $response");
        }
    }
    
     public function reset() {
        $this->execCommand("RSET");
    }
    
    public function sendFrom($value) {
        $this->execCommand("MAIL FROM: " . $value . "");
    }
    
    public function sendRecipient($value) {
        $this->execCommand("RCPT TO: <" . $value . ">");
    }
    
    public function sendData($data) {
        $this->execCommand("DATA ");
        
        $this->socket->write($data . "\r\n");
        
        Yii::log($data, CLogger::LEVEL_TRACE, 'SMTP Mailer Transport');
        
        $this->execCommand(".");
    }
    
    public function closeConnection() {
        $this->execCommand("QUIT"); 
        $this->socket->close();
    }
            
    /**
     * Exec command to SMTP server. Return result from socket.
     * @param string $command
     * @param string $response - variable by reference. Here will be response from socket.
     * @return string SMTP response code
     */
    public function execCommand($command, &$response= ''){
        $this->socket->write($command . "\r\n");
        $response = $this->socket->read();
        
         Yii::log(">>> $command". "\r\n", CLogger::LEVEL_TRACE, 'SMTP Mailer Transport');
         Yii::log("<<< $response", CLogger::LEVEL_TRACE, 'SMTP Mailer Transport');
         
        return $this->parseCode($response);
    }
    
        
    /**
     * 
     * @param string $response response from socket
     * @return string response code
     */
    protected function parseCode($response){
        return substr($response,0,3);
    }
    
    public function __destruct() {
        $this->closeConnection();
    }
}