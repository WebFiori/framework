<?php
namespace webfiori\entity;
use webfiori\entity\cli\CLI;
use webfiori\entity\session\SessionsManager;
/**
 * Description of Response
 *
 * @author Ibrahim
 */
class Response {
    private static $inst;
    private $headers;
    private $body;
    private $responseCode;
    public function hasHeader($headerName, $headerVal = null) {
        $headerValFromObj = $this->getHeader($headerName);
        if ($headerValFromObj !== null) {
            return $headerVal === null ? true : $headerVal == $headerValFromObj;
        }
        return false;
    }
    public static function send() {
        SessionsManager::validateStorage();
        if (!CLI::isCLI()) {
            http_response_code(self::getResponseCode());
            foreach (self::getHeaders() as $headerName => $headerVal) {
                header($headerName.': '.$headerVal, false);
            }
            echo self::getBody();
            die();
        }
    }
    public static function getResponseCode() {
        return self::get()->responseCode;
    }
    public static function setResponseCode($code) {
        $asInt = intval($code);
        if ($asInt >= 100 && $asInt <= 599) {
            self::get()->responseCode = $code;
        }
    }
    public function __construct() {
        $this->headers = [];
        $this->body = '';
        $this->responseCode = 200;
    }
    public static function getBody() {
        return self::get()->body;
    }
    public static function append($str) {
        self::get()->body .= $str;
    }
    public static function getHeader($headerName) {
        $trimmed = trim($headerName);
        if (isset(self::get()->headers[$trimmed])) {
            return self::get()->headers[$trimmed];
        }
    }
    public static function addHeader($headerName, $headerVal) {
        $trimmedHeader = trim($headerName);
        if (self::_validateheaderName($trimmedHeader) && strlen($headerVal) != 0) {
            self::get()->headers[$trimmedHeader] = $headerVal;
        }
    }
    public static function getHeaders() {
        return self::get()->headers;
    }
    private static function _validateheaderName($name) {
        $len = strlen($name);
        if ($len == 0) {
            return false;
        }
        for ($x = 0 ; $x < $len ; $x++) {
            $char = $name[$x];
            if (!(($char >= 'a' && $char <= 'z') || ($char >= 'A' && $char <= 'Z') || $char == '_' || $char == '-')) {
                return false;
            }
        }
        return true;
    }
    /**
     * 
     * @return Response
     */
    private static function get() {
        if (self::$inst === null) {
            self::$inst = new Response();
        }
        return self::$inst;
    }
}
