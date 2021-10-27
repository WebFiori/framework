<?php

namespace webfiori\tests\entity\mail;

/**
 * Description of SMTPServer
 *
 * @author Ibrahim
 * 
 * @version 1.0
 */
class SMTPServer {
    private $lastCommand;
    const NL = "\r\n";
    private $serverOptions;
    /**
     * The resource that is used to fire commands.
     * 
     * @var resource 
     */
    private $conn;
    /**
     * The name of mail server host.
     * 
     * @var string 
     * 
     * @since 1.0
     */
    private $host;
    /**
     * The last message that was sent by email server.
     * 
     * @var string
     * 
     * @since 1.0
     */
    private $lastResponse;
    /**
     * Last received code from server after sending some command.
     * 
     * @var int 
     * 
     * @since 1.0
     */
    private $lastResponseCode;
    /**
     * The port number.
     * 
     * @var int 
     * 
     * @since 1.0
     */
    private $port;
    /**
     *
     * @var array
     * 
     * @since 1.0
     */
    private $responseLog;
    /**
     * Connection timeout (in minutes)
     * @var int 
     */
    private $timeout;
    public function __construct($serverAddress, $port) {
        $this->port = $port;
        $this->host = $serverAddress;
        $this->serverOptions = [];
        $this->timeout = 5;
    }
    /**
     * Returns the last command which was sent to SMTP server.
     * 
     * @return string The last command which was sent to SMTP server.
     * 
     * @since 1.0
     */
    public function getLastSentCommand() {
        return $this->lastCommand;
    }
    /**
     * Returns last response code that was sent by SMTP server after executing 
     * specific command.
     * 
     * @return int The last response code that was sent by SMTP server after executing 
     * specific command. Default return value is 0.
     * 
     * @since 1.0
     */
    public function getLastResponseCode() {
        return $this->lastResponseCode;
    }
    /**
     * Returns the last response message which was sent by the server.
     * 
     * @return string The last response message after executing some command. Default 
     * value is empty string.
     * 
     * @since 1.0
     */
    public function getLastLogMessage() {
        return $this->lastResponse;
    }
    public function getServerOptions() {
        return $this->serverOptions;
    }
    private function _parseHelloResponse($response) {
        $split = explode(self::NL, $response);
        foreach ($split as $part) {
            $xPart = substr($part, 4);
            $this->serverOptions[] = $xPart;
        }
    }
    private function _tryConnect($protocol) {
        $host = $this->getHost();
        $portNum = $this->getPort();
        $conn = null;
        $err = 0;
        $errStr = '';
        $timeout = $this->getTimeout();
        
        if (function_exists('stream_socket_client')) {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                    
                    'crypto_type' => STREAM_CRYPTO_METHOD_TLSv1_2_SERVER
                ]
            ]);
            var_dump(STREAM_CRYPTO_METHOD_TLSv1_2_SERVER);
            
            $this->_log('Connect', 0, 'Trying to connect to the server using "stream_socket_client"...');
            $conn = stream_socket_client($protocol.$host.':'.$portNum, $err, $errStr, $timeout * 60, STREAM_CLIENT_CONNECT, $context);
        } else {
            $this->_log('Connect', 0, 'Trying to connect to the server using "fsockopen"...');
            $conn = fsockopen($protocol.$this->host, $portNum, $err, $errStr, $timeout * 60);
        }
        if (!is_resource($conn)) {
            $this->_log('Connect', $err, 'Faild to connect: '.$errStr);
        }
        return $conn;
    }
    public function connect() {
        $retVal = true;

        if (!$this->isConnected()) {
            set_error_handler(function($errno, $errstr, $errfile, $errline)
            {
                echo 'ErrorNo: '.$errno."\n";;
                echo 'ErrorLine: '.$errline."\n";;
                echo 'ErrorStr: '.$errstr."\n";;
            });
            $portNum = $this->getPort();
            $protocol = '';

            if ($portNum == 465) {
                $protocol = "ssl://";
            } else  if ($portNum == 587) {
                $protocol = "tls://";
            }
            $err = 0;
            $errStr = '';
            
            $this->conn = $this->_tryConnect($protocol, $err, $errStr);
            
            if ($this->conn === false) {
                $this->conn = $this->_tryConnect($protocol, $err, $errStr);
            }
            
            set_error_handler(null);

            if (is_resource($this->conn)) {
                $this->_log('-', 0, $this->read());
                if ($this->sendCommand('EHLO '.$this->host)) {
                    $retVal = true;
                }
            } else {
                $retVal = false;
            }
        }

        return $retVal;
    }
    /**
     * Read server response after sending a command to the server.
     * @return string
     * @since 1.0
     */
    public function read() {
        $message = '';
        $lastComm = $this->getLastSentCommand();
        
        while (!feof($this->conn)) {
            $str = fgets($this->conn);
            
            if ($lastComm == 'EHLO' && strlen($message) != 0) {
                $this->_addServerOption($str);
            }
            $message .= $str;
            
            if (!isset($str[3]) || (isset($str[3]) && $str[3] == ' ')) {
                break;
            }
        }
        $this->_setLastResponseCode($message);

        return $message;
    }
    /**
     * Returns an array that contains log messages for different events or 
     * commands which was sent to the server.
     * 
     * @return array The array will hold sub-associative arrays. Each array 
     * will have 3 indices, '
     */
    public function getLog() {
        return $this->responseLog;
    }
    private function _addServerOption($str) {
        $option = trim(substr($str, 3),"- ".self::NL);
        $this->serverOptions[] = $option;
    }
    /**
     * Sets the code that was the result of executing SMTP command.
     * @param string $serverResponseMessage The last message which was sent by 
     * the server after executing specific command.
     * @since 1.4.7
     */
    private function _setLastResponseCode($serverResponseMessage) {
        $firstNum = $serverResponseMessage[0];
        $firstAsInt = intval($firstNum);

        if ($firstAsInt != 0) {
            $secNum = $serverResponseMessage[1];
            $thirdNum = $serverResponseMessage[2];
            $this->lastResponseCode = intval($firstNum) * 100 + (intval($secNum * 10)) + (intval($thirdNum));
        }
    }
    private function _log($command, $code, $message) {
        $this->responseLog[] = [
            'command' => $command,
            'response-code' => $code,
            'response-message' => $message
        ];
    }
    /**
     * Sends a command to the mail server.
     * 
     * @param string $command Any SMTP command which is supported by the server.
     * 
     * @return boolean The method will return always true if the command was 
     * sent. The only case that the method will return false is when it is not 
     * connected to the server.
     * 
     * @since 1.0
     */
    public function sendCommand($command) {
        $this->lastCommand = explode(' ', $command)[0];
        $logEntry = [
            'command' => $command,
            'response-code' => 0,
            'response-message' => ''
        ];

        if ($this->lastResponseCode >= 400) {
            throw new SMTPException('Unable to send SMTP commend "'.$command.'" due to '
                    .'error code '.$this->lastResponseCode.' caused by last command. '
                    .'Error message: "'.$this->lastResponse.'".');
        }

        if ($this->isConnected()) {
            fwrite($this->conn, $command.self::NL);
            $response = trim($this->read());
            $this->lastResponse = $response;
            $logEntry['response-message'] = $response;
            $logEntry['response-code'] = $this->getLastResponseCode();
            $this->responseLog[] = $logEntry;
            
            if (substr($command, 0, 4) == 'HELO' || substr($command, 0, 4) == 'EHLO') {
                $this->_parseHelloResponse($response);
            }
            return true;
        } else {
            $this->responseLog[] = $logEntry;

            return false;
        }
    }
    /**
     * Sets the timeout time of the connection.
     * 
     * @param int $val The value of timeout (in minutes). The timeout will be updated 
     * only if the connection is not yet established and the given value is grater 
     * than 0.
     * 
     * @since 1.0
     */
    public function setTimeout($val) {
        if ($val >= 1 && !$this->isConnected()) {
            $this->timeout = $val;
        }
    }
    /**
     * 
     * @return type
     * 
     * @since 1.0
     */
    public function getPort() {
        return $this->port;
    }
    /**
     * 
     * @return type
     * 
     * @since 1.0
     */
    public function getHost() {
        return $this->host;
    }
    /**
     * Returns the time at which the connection will timeout if no response 
     * was received in minutes.
     * 
     * @return int Timeout time in minutes.
     * 
     * @since 1.0
     */
    public function getTimeout() {
        return $this->timeout;
    }
    /**
     * Checks if the connection is still open or is it closed.
     * 
     * @return boolean true if the connection is open.
     * 
     * @since 1.0
     */
    public function isConnected() {
        return is_resource($this->conn);
    }
}
