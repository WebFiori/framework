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

/**
 * A class for the functions that is related to mailing.
 *
 * @author Ibrahim
 * @version 1.1
 */
class MailFunctions extends Functions{
    /**
     * A constant that indicates a mail server address or its port 
     * is invalid.
     * @since 1.1
     */
    const INV_HOST_OR_PORT = 'inv_mail_host_or_port';
    /**
     *
     * @var MailFunctions 
     * @since 1.0
     */
    private static $instance;
    /**
     * Returns a singleton of the class.
     * @return MailFunctions
     * @since 1.0
     */
    public static function get(){
        if(self::$instance != NULL){
            return self::$instance;
        }
        self::$instance = new MailFunctions();
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
     * type <b>EmailAccount</b>. 
     * @since 1.1
     */
    private function writeMailConfig($emailAccountsArr){
        $fh = new FileHandler(ROOT_DIR.'/MailConfig_test.php');
        $fh->write('<?php', TRUE, TRUE);
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
        foreach ($emailAccountsArr as $emailAcc){
            $fh->write('$acc1 = new EmailAccount();
        $acc1->setServerAddress(\''.$emailAcc->getServerAddress().'\');
        $acc1->setAddress(\''.$emailAcc->getAddress().'\');
        $acc1->setUsername(\''.$emailAcc->getUsername().'\');
        $acc1->setPassword(\''.$emailAcc->getPassword().'\');
        $acc1->setName(\''.$emailAcc->getName().'\');
        $acc1->setPort('.$emailAcc->getPort().');
        $this->addAccount($acc1, \'no-replay\');',TRUE,TRUE);
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
     * Sends a welcome email to a newly added account.
     * @param User $user An instance of the class <b>User</b>.
     * @since 1.0
     */
    public function sendWelcomeEmail($user) {
        $noReplayAcc = MailConfig::get()->getAccount('no-replay');
        if($noReplayAcc instanceof EmailAccount){
            $mailer = $this->getSocketMailer($noReplayAcc);
            $mailer->addReceiver($user->getUserName(), $user->getEmail());
            $mailer->setSubject('Activate Your Account');
            $msg = '<p>Dear Mr. '.$user->getUserName().', Welcome to <b>'.SiteConfig::get()->getWebsiteName().'</b>.</p>';
            $msg .= '<p>A new user account has been created for you. In order to start using '
                    . 'the system, you must activate your account by clicking on the '
                    . '<a href="'.SiteConfig::get()->getBaseURL().'apis/UserAPIs?action=activate-account&activation-token='.$user->getActivationTok().'" _target="_blank"><b>This Link</b></a> and logging in.</p>';
            $msg .= '<p>Thank you for your time.</p>';
            $msg .= '<p><b>'.$noReplayAcc->getName().'</b></p>';
            $mailer->write($msg,TRUE);
        }
    }
    public function updateEmailAccount($emailAccount) {
        if($emailAccount instanceof EmailAccount){
            $sm = $this->getSocketMailer($emailAccount);
            
        }
    }
    /**
     * Returns a new instance of the class <b>SocketMailer</b>.
     * @param EmailAccount $emailAcc An account that is used to initiate 
     * socket mailer.
     * @return SocketMailer|NULL The function will return an instance of <b>SocketMailer</b> 
     * on successful connection. If no connection is established, the function will 
     * return <b>NULL</b>.
     * @since 1.0
     */
    private function getSocketMailer($emailAcc){
        $m = new SocketMailer();
        $m->setHost($emailAcc->getServerAddress());
        $m->setPort($emailAcc->getPort());
        if($m->connect()){
            $m->sendC('EHLO');
            $m->sendC('AUTH LOGIN');
            $m->sendC(base64_encode($emailAcc->getUsername()));
            $m->sendC(base64_encode($emailAcc->getPassword()));
            $m->setSender($emailAcc->getName(), $emailAcc->getAddress());
            return $m;
        }
        return NULL;
    }
}
