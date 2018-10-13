<?php
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 403 Forbidden");
    die(''
        . '<!DOCTYPE html>'
        . '<html>'
        . '<head>'
        . '<title>Forbidden</title>'
        . '</head>'
        . '<body>'
        . '<h1>403 - Forbidden</h1>'
        . '<hr>'
        . '<p>'
        . 'Direct access not allowed.'
        . '</p>'
        . '</body>'
        . '</html>');
}
/**
 * A file that contains SMTP accounts information.
 *
 * @author Ibrahim
 * @version 1.0
 */
class MailConfig{
    private $emailAccounts;
    /**
     *
     * @var MailConfig 
     * @since 1.0
     */
    private static $inst;
    /**
     * 
     * @return MailConfig
     * @since 1.0
     */
    public static function &get(){
        if(self::$inst !== NULL){
            return self::$inst;
        }
        self::$inst = new MailConfig();
        return self::$inst;
    }
    private function __construct() {
    }
/**
     * Adds an email account.
     * @param EmailAccount $acc an object of type <b>EmailAccount</b>.
     * @param string $name A name to associate with the email account.
     * @since 1.0
     */
    private function addAccount($acc,$name){
        $this->emailAccounts[$name] = $acc;
    }
    private function &_getAccount($name){
        if(isset($this->emailAccounts[$name])){
            return $this->emailAccounts[$name];
        }
        $null = NULL;
        return $null;
    }
    /**
     * Returns an email account given its name.
     * @param string $name The name of the account.
     * @return EmailAccount|null If the account is found, The function 
     * will return an object of type <b>EmailAccount</b>. Else, the 
     * function will return <b>NULL</b>.
     * @since 1.0
     */
    public static function &getAccount($name){
        return self::get()->_getAccount($name);
    }
    private function _getAccounts(){
        return $this->emailAccounts;
    }
    /**
     * Returns an array that contains all email accounts.
     * @return array An array that contains all email accounts.
     * @since 1.0
     */
    public static function getAccounts(){
        return self::get()->_getAccounts();
    }
}
