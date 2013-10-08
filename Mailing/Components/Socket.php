<?php


/**
 * Wrapper for Socket functions
 *
 * @author t.yacenko
 */
class Socket{
    
    protected $socket;
    
    /**
     * @see socket_create()
     * @param int $domain
     * @param int $type
     * @param int $protocol
     */
    public function __construct($domain , $type , $protocol) {
        $this->socket = socket_create($domain , $type , $protocol);
    }

    /**
     * @see socket_connect()
     * @param string $address
     * @param int $port
     * @return \Socket
     */
    public function connect($address, $port = 0) {
        $result = socket_connect($this->socket, $address, $port);
        
         if (!$result) {
                $error = socket_strerror($this->getLastError());
                throw new Exception($error);
         }
            
        return $this;
    }
    
    /**
     * @see socket_last_error()
     * @return int
     */
    public function getLastError(){
        return socket_last_error($this->socket);
    }
    
    /**
     * @see socket_read()
     * @param int $length
     * @param int $type
     * @return string
     */
    public function read($length = 2048, $type=PHP_BINARY_READ){
       return socket_read($this->socket, $length, $type);
    }
    
    /**
     * @param string $data
      * @return \Socket
      */
    public function write($data){
        socket_write($this->socket, $data, strlen($data));
        return $this;
    }
    
    public function close(){
        socket_close($this->socket);
    }
    
    public function __destruct() {
        //close socket in a desctructor is a very bad idea, because php doesn't guarantee sequence of calling desctructors.
        //this may cause an error, if a socket desctructor will be closed before component wich used a socket will be desctructed.
    }
}