<?php
/**
 * A file that contains system email addresses configurations.
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
    public static function get(){
        if(self::$inst !== NULL){
            return self::$inst;
        }
        self::$inst = new MailConfig();
        return self::$inst;
    }
    private function __construct() {
    $acc1 = new EmailAccount();
        $acc1->setServerAddress('mail.programmingacademia.com');
        $acc1->setAddress('no-replay@programmingacademia.com');
        $acc1->setUsername('no-replay@programmingacademia.com');
        $acc1->setPassword('132970');
        $acc1->setName('Programming Academia Team');
        $acc1->setPort(25);
        $this->addAccount($acc1, 'no-replay');
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
    }
}
