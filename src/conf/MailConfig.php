<?php
namespace webfiori\conf;

use webfiori\entity\mail\SMTPAccount;
/**
 * SMTP configuration class.
 * The developer can create multiple SMTP accounts and add
 * Connection information inside the body of this class.
 * @author Ibrahim
 * @version 1.0.1
 */
class MailConfig {
    private $emailAccounts;
    /**
     *
     * @var MailConfig
     * @since 1.0
     */
    private static $inst;
    private function __construct() {
    }
    /**
     * Adds new SMTP connection information or updates an existing one.
     * @param string $accName The name of the account that will be added or updated.
     * @param SMTPAccount $smtpConnInfo An object of type 'SMTPAccount' that
     * will contain SMTP account information.
     * @since 1.0.1
     */
    public static function addSMTPAccount($accName, $smtpConnInfo) {
        if ($smtpConnInfo instanceof SMTPAccount) {
            $trimmedName = trim($accName);

            if (strlen($trimmedName) != 0) {
                self::get()->addAccount($smtpConnInfo, $trimmedName);
            }
        }
    }
    /**
     * Return a single instance of the class.
     * Calling this method multiple times will result in returning
     * the same instance every time.
     * @return MailConfig
     * @since 1.0
     */
    public static function get() {
        if (self::$inst === null) {
            self::$inst = new MailConfig();
        }

        return self::$inst;
    }
    /**
     * Returns an email account given its name.
     * The method will search for an account with the given name in the set
     * of added accounts. If no account was found, null is returned.v     * @param string $name The name of the account.
     * @return SMTPAccount|null If the account is found, The method
     * will return an object of type SMTPAccount. Else, the
     * method will return null.
     * @since 1.0
     */
    public static function getAccount($name) {
        return self::get()->_getAccount($name);
    }
    /**
     * Returns an associative array that contains all email accounts.
     * The indices of the array will act as the names of the accounts.
     * The value of the index will be an object of type EmailAccount.
     * @return array An associative array that contains all email accounts.
     * @since 1.0
     */
    public static function getAccounts() {
        return self::get()->_getAccounts();
    }
    private function _getAccount($name) {
        if (isset($this->emailAccounts[$name])) {
            return $this->emailAccounts[$name];
        }

        return null;
    }
    private function _getAccounts() {
        return $this->emailAccounts;
    }
    /**
     * Adds an email account.
     * The developer can use this method to add new account during runtime.
     * The account will be removed once the program finishes.
     * @param SMTPAccount $acc an object of type SMTPAccount.
     * @param string $name A name to associate with the email account.
     * @since 1.0
     */
    private function addAccount($acc,$name) {
        $this->emailAccounts[$name] = $acc;
    }
}
