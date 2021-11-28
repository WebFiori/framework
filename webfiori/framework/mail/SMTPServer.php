<?php
namespace webfiori\framework\mail;

use webfiori\framework\exceptions\SMTPException;
/**
 * A class which can be used to connect to SMTP server and execute commands on it.
 *
 * @author Ibrahim
 * 
 * @version 1.0
 */
class SMTPServer {
    const NL = "\r\n";
    private $isWriting;
    private $lastCommand;
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
     *
     * @var array
     * 
     * @since 1.0
     */
    private $responseLog;
    /**
     * Connection timeout (in minutes)
     * 
     * @var int 
     */
    private $responseTimeout;
    /**
     * The resource that is used to fire commands.
     * 
     * @var resource 
     */
    private $serverCon;
    /**
     * The name of mail server host.
     * 
     * @var string 
     * 
     * @since 1.0
     */
    private $serverHost;
    private $serverOptions;
    /**
     * The port number.
     * 
     * @var int 
     * 
     * @since 1.0
     */
    private $serverPort;
    /**
     * Initiates new instance of the class.
     * 
     * @param string $serverAddress SMTP Server address such as 'smtp.example.com'.
     * 
     * @param string $port SMTP server port such as 25, 465 or 587.
     */
    public function __construct($serverAddress, $port) {
        $this->serverPort = $port;
        $this->serverHost = $serverAddress;
        $this->serverOptions = [];
        $this->responseTimeout = 5;
        $this->lastResponse = '';
        $this->lastResponseCode = 0;
        $this->isWriting = false;
    }
    /**
     * Use plain authorization method to log in the user to SMTP server.
     * 
     * This method will attempt to establish a connection to SMTP server if 
     * the method 'SMTPServer::connect()' is called.
     * 
     * @param string $username The username of SMTP server user.
     * 
     * @param string $pass The password of the user.
     * 
     * @return boolean If the user is authenticated successfully, the method
     * will return true. Other than that, the method will return false.
     * 
     * @since 1.0
     */
    public function authLogin($username, $pass) {
        if (!$this->isConnected()) {
            $this->connect();

            if (!$this->isConnected()) {
                return false;
            }
        }
        $this->sendCommand('AUTH LOGIN');
        $this->sendCommand(base64_encode($username));
        $this->sendCommand(base64_encode($pass));

        if ($this->getLastResponseCode() == 535) {
            return false;
        }

        return true;
    }
    /**
     * Connects to SMTP server.
     * 
     * @return boolean If the connection was established and the 'EHLO' command 
     * was successfully sent, the method will return true. Other than that, the 
     * method will return false.
     * 
     * @since 1.0
     */
    public function connect() {
        $retVal = true;

        if (!$this->isConnected()) {
            set_error_handler(null);
            $transport = $this->_getTransport();
            $err = 0;
            $errStr = '';

            $this->serverCon = $this->_tryConnect($transport, $err, $errStr);

            if ($this->serverCon === false) {
                $this->serverCon = $this->_tryConnect('', $err, $errStr);
            }

            if (is_resource($this->serverCon)) {
                $this->_log('-', 0, $this->read());
                if ($this->getLastResponseCode() != 220) {
                    throw new SMTPException('Server did not respond with code 220 during initial connection.');
                }
                if ($this->sendHello()) {
                    //We might need to switch to secure connection.
                    $retVal = $this->_checkStartTls();
                } else {
                    $retVal = false;
                }
            } else {
                $retVal = false;
            }
            restore_error_handler();
        }

        return $retVal;
    }
    /**
     * Returns SMTP server host address.
     * 
     * @return string A string such as 'smtp.example.com'.
     * 
     * @since 1.0
     */
    public function getHost() {
        return $this->serverHost;
    }
    /**
     * Returns the last response message which was sent by the server.
     * 
     * @return string The last response message after executing some command. Default 
     * value is empty string.
     * 
     * @since 1.0
     */
    public function getLastResponse() {
        return $this->lastResponse;
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
     * Returns an array that contains log messages for different events or 
     * commands which was sent to the server.
     * 
     * @return array The array will hold sub-associative arrays. Each array 
     * will have 3 indices, 'command', 'response-code' and 'response-message'
     * 
     * @since 1.0
     */
    public function getLog() {
        return $this->responseLog;
    }
    /**
     * Returns SMTP server port number.
     * 
     * @return int Common values are : 25, 465 (SSL) and 586 (TLS).
     * 
     * @since 1.0
     */
    public function getPort() {
        return $this->serverPort;
    }
    /**
     * Returns an array that contains server supported commands.
     * 
     * The method will only be able to get the options after sending the 
     * command 'EHLO' to the server. The array will be empty if not 
     * connected to SMTP server.
     * 
     * @return array An array that holds supported SMTP server options.
     * 
     * @since 1.0
     */
    public function getServerOptions() {
        return $this->serverOptions;
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
        return $this->responseTimeout;
    }
    /**
     * Checks if the connection is still open or is it closed.
     * 
     * @return boolean true if the connection is open.
     * 
     * @since 1.0
     */
    public function isConnected() {
        return is_resource($this->serverCon);
    }
    /**
     * Checks if the server is in message writing mode.
     * 
     * The server will be in writing mode if the command 'DATA' was sent.
     * 
     * @return boolean If the server is in message writing mode, the method 
     * will return true. False otherwise.
     * 
     * @since 1.0
     */
    public function isInWritingMode() {
        return $this->isWriting;
    }
    /**
     * Read server response after sending a command to the server.
     * 
     * @return string
     * 
     * @since 1.0
     */
    public function read() {
        $message = '';
        
        while (!feof($this->serverCon)) {
            $str = fgets($this->serverCon);
            
            if ($str !== false) {
                $message .= $str;

                if (!isset($str[3]) || (isset($str[3]) && $str[3] == ' ')) {
                    break;
                }
            } else {
                $this->_log('-', '0', 'Unable to read server response.');
            }
        }
        $this->_setLastResponseCode($message);

        return $message;
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

        if ($this->lastResponseCode >= 400) {
            throw new SMTPException('Unable to send SMTP commend "'.$command.'" due to '
                    .'error code '.$this->lastResponseCode.' caused by last command. '
                    .'Error message: "'.$this->lastResponse.'".');
        }

        if ($this->isConnected()) {
            fwrite($this->serverCon, $command.self::NL);

            if (!$this->isInWritingMode()) {
                $response = trim($this->read());
                $this->lastResponse = $response;
                $this->_log($command, $this->getLastResponseCode(), $response);
            } else {
                $this->_log($command, 0, '-');
            }

            if ($command == 'DATA') {
                $this->isWriting = true;
            }

            if ($command == self::NL.'.') {
                $this->writeMode = false;
                $response = trim($this->read());
                $this->lastResponse = $response;
                $this->_log($command, $this->getLastResponseCode(), $response);
            }

            return true;
        } else {
            $this->_log($command, 0, '');

            return false;
        }
    }
    /**
     * Sends 'EHLO' command to SMTP server.
     * 
     * The developer does not have to call this method manually as its 
     * called when connecting to SMTP server.
     * 
     * @return boolean If the command was sent successfully, the method will 
     * return true. Other than that, the method will return false.
     * 
     * @since 1.0
     */
    public function sendHello() {
        if ($this->sendCommand('EHLO '.$this->getHost())) {
            $this->_parseHelloResponse($this->getLastResponse());

            return true;
        }

        return false;
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
            $this->responseTimeout = $val;
        }
    }
    private function _checkStartTls() {
        if (in_array('STARTTLS', $this->getServerOptions())) {
            if ($this->_switchToTls()) {
                $this->sendHello();

                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
    private function _getTransport() {
        $port = $this->getPort();

        if ($port == 465) {
            return "ssl://";
        } else {
            if ($port == 587) {
                return "tls://";
            }
        }

        return '';
    }
    private function _log($command, $code, $message) {
        $this->responseLog[] = [
            'command' => $command,
            'response-code' => $code,
            'response-message' => $message
        ];
    }
    private function _parseHelloResponse($response) {
        $split = explode(self::NL, $response);
        $index = 0;
        $this->serverOptions = [];

        foreach ($split as $part) {
            //Index 0 will hold server address
            if ($index != 0) {
                $xPart = substr($part, 4);
                $this->serverOptions[] = $xPart;
            }
            $index++;
        }
    }
    /**
     * Sets the code that was the result of executing SMTP command.
     * 
     * @param string $serverResponseMessage The last message which was sent by 
     * the server after executing specific command.
     * 
     * @since 1.0
     */
    private function _setLastResponseCode($serverResponseMessage) {
        if (strlen($serverResponseMessage) != 0) {
            $firstNum = $serverResponseMessage[0];
            $firstAsInt = intval($firstNum);

            if ($firstAsInt != 0) {
                $secNum = $serverResponseMessage[1];
                $thirdNum = $serverResponseMessage[2];
                $this->lastResponseCode = intval($firstNum) * 100 + (intval($secNum * 10)) + (intval($thirdNum));
            }
        }
    }
    private function _switchToTls() {
        $this->sendCommand('STARTTLS');
        $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;

        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
        }


        $success = stream_socket_enable_crypto(
            $this->serverCon,
            true,
            $cryptoMethod
        );

        return $success === true;
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


            $this->_log('Connect', 0, 'Trying to connect to the server using "stream_socket_client"...');
            $conn = stream_socket_client($protocol.$host.':'.$portNum, $err, $errStr, $timeout * 60, STREAM_CLIENT_CONNECT, $context);
        } else {
            $this->_log('Connect', 0, 'Trying to connect to the server using "fsockopen"...');
            $conn = fsockopen($protocol.$this->serverHost, $portNum, $err, $errStr, $timeout * 60);
        }

        if (!is_resource($conn)) {
            if (strlen($errStr) == 0) {
                $this->_log('Connect', $err, 'Faild to connect due to unspecified error.');
            } else {
                $this->_log('Connect', $err, 'Faild to connect: '.$errStr);
            }
        }

        return $conn;
    }
}
