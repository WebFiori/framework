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
 * @version 1.0
 */
class MailFunctions extends Functions{
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
     * Sends a welcome email to a newly added account.
     * @param User $user An instance of the class <b>User</b>.
     * @since 1.0
     */
    public function sendWelcomeEmail($user) {
        $noReplayAcc = MailConfig::get()->getAccount('no-replay');
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
