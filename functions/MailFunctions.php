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
 * @version 1.3
 */
class MailFunctions extends Functions{
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
        $fh = new FileHandler(ROOT_DIR.'/MailConfig.php');
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
     * Sends a notification email to let the user know about password change. The 
     * change is done by super admin.
     * @param User $user An object of type <b>User</b>
     * @since 1.1
     */
    public function notifyOfPasswordChangeAdmin($user){
        $noReplayAcc = MailConfig::get()->getAccount('no-replay');
        if($noReplayAcc instanceof EmailAccount){
            $mailer = $this->getSocketMailer($noReplayAcc);
            $mailer->addReceiver($user->getUserName(), $user->getEmail());
            $mailer->setSubject('Password Reset By Admin');
            $msg = '<p>Dear,</p>';
            $msg .= '<p>We would like to inform you that system adminstrator has reset your account password.<p>';
            $msg .= '<p>Thank you for your time.</p>';
            $msg .= '<p><b>'.$noReplayAcc->getName().'</b></p>';
            $mailer->write($msg,TRUE);
        }
    }
    /**
     * 
     * @param User $user
     * @since 1.3
     */
    public function sendPasswordChangeConfirm($user) {
        $noReplayAcc = MailConfig::get()->getAccount('no-replay');
        if($noReplayAcc instanceof EmailAccount){
            $mailer = $this->getSocketMailer($noReplayAcc);
            $mailer->addReceiver($user->getUserName(), $user->getEmail());
            $mailer->setSubject('Password Reset');
            $msg = '<p>Dear,</p>';
            $msg .= '<p>We would like to inform you that you have requested to reset your account\'s password.<p>';
            $msg .= '<p>In order to complete the reset process, please click on <a href="'.SiteConfig::get()->getBaseURL().'pages/reset-password?token='.$user->getResetToken().'" target="_blank">this link</a> and update your password.<p>'
                    . '<p>If you did not request a password reset, please inform us as soon as possible.</p>';
            $msg .= '<p>Thank you for your time.</p>';
            $msg .= '<p><b>'.$noReplayAcc->getName().'</b></p>';
            $mailer->write($msg,TRUE);
        }
    }
    /**
     * Sends a notification email to let the user know about password change.
     * @param User $user An object of type <b>User</b>
     * @since 1.1
     */
    public function notifyOfPasswordChange($user){
        $noReplayAcc = MailConfig::get()->getAccount('no-replay');
        if($noReplayAcc instanceof EmailAccount){
            $mailer = $this->getSocketMailer($noReplayAcc);
            $mailer->addReceiver($user->getUserName(), $user->getEmail());
            $mailer->setSubject('Account Password has Changed');
            $msg = '<p>Dear,</p>';
            $msg .= '<p>We would like to inform you that your password has been changed. '
                    . 'if you are not the one who did the change, please contact us. '
                    . 'if you are the one who did the update, you can simply ignore this message.<p>';
            $msg .= '<p>Thank you for your time.</p>';
            $msg .= '<p><b>'.$noReplayAcc->getName().'</b></p>';
            $mailer->write($msg,TRUE);
        }
    }
    /**
     * Send an email message to the email of the first user was ever created.
     * @param User $adminAcc The first user account.
     * @since 1.2
     */
    public function sendFirstMail($adminAcc) {
        $noReplayAcc = MailConfig::get()->getAccount('no-replay');
        if($noReplayAcc instanceof EmailAccount){
            $mailer = $this->getSocketMailer($noReplayAcc);
            $mailer->addReceiver($adminAcc->getUserName(), $adminAcc->getEmail());
            $mailer->setSubject('Welcome');
            $msg = '<p>Dear,</p>';
            $msg .= '<p>This email is used to confirm your admin account information. '
                    . 'Please note that you should never forget your password and keep it '
                    . 'in a safe place.'
                    . '</p>'
                    . '<ul>'
                    . '<li><b>Username:</b> '.$adminAcc->getUserName().'</li>'
                    . '<li><b>Display name:</b> '.$adminAcc->getDisplayName().'</li>'
                    . '<li><b>Email Address:</b> '.$adminAcc->getEmail().'</li>'
                    . '</ul>'
                    . '<p>You can start using the system and manage it by logging in at '
                    . '<a target="_blank" href="'.SiteConfig::get()->getBaseURL().'/pages/login">'.SiteConfig::get()->getBaseURL().'/pages/login</a>.</p>';
            $msg .= '<p>Thank you for your time.</p>';
            $msg .= '<p><b>'.$noReplayAcc->getName().'</b></p>';
            $mailer->write($msg,TRUE);
        }
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
                    . 'the system, you must activate your account by loging in '
                    . '<a href="'.SiteConfig::get()->getBaseURL().'pages/activate-account?activation-token='.$user->getActivationTok().'" _target="_blank"><b>Here</b></a>.</p>'
                    . '<ul>'
                    . '<li><b>Username:</b> '.$user->getUserName().'</li>'
                    . '<li><b>Display name:</b> '.$user->getDisplayName().'</li>'
                    . '<li><b>Email Address:</b> '.$user->getEmail().'</li>'
                    . '</ul>'
                    . '';
            $msg .= '<p>Thank you for your time.</p>';
            $msg .= '<p><b>'.$noReplayAcc->getName().'</b></p>';
            $mailer->write($msg,TRUE);
        }
    }
    /**
     * Update the email address that is used to send system notifications.
     * @param EmailAccount $emailAccount An instance of <b>EmailAccount</b>.
     * @return boolean|string The function will return <b>TRUE</b> if the email 
     * account updated. If the email account contains wrong server information, 
     * the function will return <b>MailFunctions::INV_HOST_OR_PORT</b>. If the 
     * given email account contains wrong login info, the function will return 
     * <b>MailFunctions::INV_CREDENTIALS</b>. Other than that, the function 
     * will return <b>FALSE</b>.
     * @since 1.1
     */
    public function updateEmailAccount($emailAccount) {
        if($emailAccount instanceof EmailAccount){
            $sm = $this->getSocketMailer($emailAccount);
            if($sm instanceof SocketMailer){
                $arr = array($emailAccount);
                $this->writeMailConfig($arr);
                return TRUE;
            }
            return $sm;
        }
        return FALSE;
    }
    /**
     * Returns a new instance of the class <b>SocketMailer</b>.
     * @param EmailAccount $emailAcc An account that is used to initiate 
     * socket mailer.
     * @return SocketMailer|string The function will return an instance of <b>SocketMailer</b> 
     * on successful connection. If no connection is established, the function will 
     * return <b>MailFunctions::INV_HOST_OR_PORT</b>. If user authentication fails, 
     * the function will return <b></b>.
     * @since 1.0
     */
    private function getSocketMailer($emailAcc){
        $m = new SocketMailer();
        $m->setHost($emailAcc->getServerAddress());
        $m->setPort($emailAcc->getPort());
        if($m->connect()){
            $m->setSender($emailAcc->getName(), $emailAcc->getAddress());
            if($m->login($emailAcc->getUsername(), $emailAcc->getPassword())){
                return $m;
            }
            return MailFunctions::INV_CREDENTIALS;
        }
        return MailFunctions::INV_HOST_OR_PORT;
    }
}