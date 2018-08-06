<?php

/*
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
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
 * A class for the functions that is related to mailing.
 *
 * @author Ibrahim
 * @version 1.3
 */
class BasicMailFunctions extends Functions{
    /**
     * A constant that indicates a mail server address or its port 
     * is invalid.
     * @since 1.1
     */
    const INV_HOST_OR_PORT = 'inv_mail_host_or_port';
    /**
     * A constant that indicates the given username or password  
     * is invalid.
     * @since 1.1
     */
    const INV_CREDENTIALS = 'inv_username_or_pass';
    /**
     *
     * @var MailFunctions 
     * @since 1.0
     */
    private static $instance;
    /**
     * Returns a singleton of the class.
     * @return BasicMailFunctions
     * @since 1.0
     */
    public static function get(){
        if(self::$instance != NULL){
            return self::$instance;
        }
        self::$instance = new BasicMailFunctions();
        return self::$instance;
    }
    public function __construct() {
        parent::__construct();
    }
    /**
     * Creates the file 'MailConfig.php' if it does not exist.
     * @since 1.0
     */
    public function createEmailConfigFile(){
        if(!class_exists('MailConfig')){
            $this->writeMailConfig(array());
        }
    }
    /**
     * A function to save changes to mail configuration file.
     * @param type $emailAccountsArr An array that contains an objects of 
     * type 'EmailAccount'. 
     * @since 1.1
     */
    private function writeMailConfig($emailAccountsArr){
        $fh = new FileHandler(ROOT_DIR.'/entity/MailConfig.php');
        $fh->write('<?php', TRUE, TRUE);
        $fh->write('if(!defined(\'ROOT_DIR\')){
    header("HTTP/1.1 403 Forbidden");
    die(\'\'
        . \'<!DOCTYPE html>\'
        . \'<html>\'
        . \'<head>\'
        . \'<title>Forbidden</title>\'
        . \'</head>\'
        . \'<body>\'
        . \'<h1>403 - Forbidden</h1>\'
        . \'<hr>\'
        . \'<p>\'
        . \'Direct access not allowed.\'
        . \'</p>\'
        . \'</body>\'
        . \'</html>\');
}', TRUE, TRUE);
        $fh->write('/**
 * A file that contains system email addresses configurations.
 *
 * @author Ibrahim
 * @version 1.0
 */', TRUE, TRUE);
        $fh->write('class MailConfig{', TRUE, TRUE);
        $fh->addTab();
        //stat here
        $fh->write('private $emailAccounts;
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
    public static function get(){
        if(self::$inst !== NULL){
            return self::$inst;
        }
        self::$inst = new MailConfig();
        return self::$inst;
    }', TRUE, TRUE);
        $fh->write('private function __construct() {', TRUE, TRUE);
        $fh->addTab();
        $fh->reduceTab();
        //adding email accounts
        $index=0;
        foreach ($emailAccountsArr as $emailAcc){
            $fh->write('$acc'.$index.' = new EmailAccount();
        $acc'.$index.'->setServerAddress(\''.$emailAcc->getServerAddress().'\');
        $acc'.$index.'->setAddress(\''.$emailAcc->getAddress().'\');
        $acc'.$index.'->setUsername(\''.$emailAcc->getUsername().'\');
        $acc'.$index.'->setPassword(\''.$emailAcc->getPassword().'\');
        $acc'.$index.'->setName(\''.$emailAcc->getName().'\');
        $acc'.$index.'->setPort('.$emailAcc->getPort().');
        $this->addAccount($acc'.$index.', \'no-replay\');',TRUE,TRUE);
            $index++;
        }
        $fh->write('}', TRUE, TRUE);
        $fh->reduceTab();
        $fh->write('/**
     * Adds an email account.
     * @param EmailAccount $acc an object of type <b>EmailAccount</b>.
     * @param string $name A name to associate with the email account.
     * @since 1.0
     */
    private function addAccount($acc,$name){
        $this->emailAccounts[$name] = $acc;
    }
    /**
     * Returns an email account given its name.
     * @param string $name The name of the account.
     * @return EmailAccount|null If the account is found, The function 
     * will return an object of type <b>EmailAccount</b>. Else, the 
     * function will return <b>NULL</b>.
     * @since 1.0
     */
    public function getAccount($name){
        if(isset($this->emailAccounts[$name])){
            return $this->emailAccounts[$name];
        }
        return NULL;
    }
    /**
     * Returns an array that contains all email accounts.
     * @return array An array that contains all email accounts.
     * @since 1.0
     */
    public function getAccounts(){
        return $this->emailAccounts;
    }', TRUE, TRUE);
        $fh->write('}', TRUE, TRUE);
        $fh->close();
    }
    /**
     * Removes SMTP email account if it is exist.
     * @param string $accountName The name of the email account (such as 'no-replay').
     * @return boolean If the account is not exist or the class 'MailConfig' 
     * does not exist, the function will return FALSE. If the account was removed, 
     * The function will return TRUE.
     * @since 1.3
     */
    public function removeAccount($accountName) {
        if(class_exists('MailConfig')){
            $account = MailConfig::get()->getAccount($accountName);
            if($account instanceof EmailAccount){
                $accountsArr = MailConfig::get()->getAccounts();
                unset($accountsArr[$accountName]);
                $toSave = array();
                foreach ($accountsArr as $account){
                    $toSave[] = $account;
                }
                $this->writeMailConfig($toSave);
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * Updates an existing SMTP email account or adds new one.
     * @param EmailAccount $emailAccount An instance of 'EmailAccount'.
     * @return boolean|string The function will return 'TRUE' if the email 
     * account updated or added. If the email account contains wrong server information, 
     * the function will return 'MailFunctions::INV_HOST_OR_PORT'. If the 
     * given email account contains wrong login info, the function will return 
     * 'MailFunctions::INV_CREDENTIALS'. Other than that, the function 
     * will return 'FALSE'.
     * @since 1.1
     */
    public function updateOrAddEmailAccount($emailAccount) {
        if($emailAccount instanceof EmailAccount){
            $sm = $this->getSocketMailer($emailAccount);
            if($sm instanceof SocketMailer){
                if(class_exists('MailConfig')){
                    $accountsArr = MailConfig::get()->getAccounts();
                    $accountsArr[$emailAccount->getName()] = $emailAccount;
                    $toSave = array();
                    foreach ($accountsArr as $account){
                        $toSave[] = $account;
                    }
                    $this->writeMailConfig($toSave);
                }
                else{
                    $arr = array($emailAccount);
                    $this->writeMailConfig($arr);
                }
                return TRUE;
            }
            return $sm;
        }
        return FALSE;
    }
    /**
     * Returns a new instance of the class 'SocketMailer'.
     * @param EmailAccount $emailAcc An account that is used to initiate 
     * socket mailer.
     * @return SocketMailer|string The function will return an instance of 'SocketMailer'
     * on successful connection. If no connection is established, the function will 
     * return 'MailFunctions::INV_HOST_OR_PORT'. If user authentication fails, 
     * the function will return 'MailFunctions::INV_CREDENTIALS'.
     * @since 1.0
     */
    public function getSocketMailer($emailAcc){
        $m = new SocketMailer();
        $m->setHost($emailAcc->getServerAddress());
        $m->setPort($emailAcc->getPort());
        if($m->connect()){
            $m->setSender($emailAcc->getName(), $emailAcc->getAddress());
            if($m->login($emailAcc->getUsername(), $emailAcc->getPassword())){
                return $m;
            }
            return BasicMailFunctions::INV_CREDENTIALS;
        }
        return BasicMailFunctions::INV_HOST_OR_PORT;
    }
}