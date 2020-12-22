<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
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
namespace webfiori\framework;

use Exception;
use webfiori\conf\Config;
use webfiori\conf\MailConfig;
use webfiori\database\ConnectionInfo;
use webfiori\framework\exceptions\InitializationException;
use webfiori\framework\File;
use webfiori\framework\mail\SMTPAccount;
use webfiori\framework\mail\SocketMailer;
use webfiori\framework\exceptions\SMTPException;
/**
 * A class that can be used to modify basic configuration settings of 
 * the web application. 
 *
 * @author Ibrahim
 * 
 * @version 1.4.4
 */
class ConfigController {
    const NL = "\n";
    /**
     * A constant that indicates the given username or password  
     * is invalid.
     * 
     * @since 1.1
     */
    const INV_CREDENTIALS = 'inv_username_or_pass';
    /**
     * A constant that indicates a mail server address or its port 
     * is invalid.
     * 
     * @since 1.1
     */
    const INV_HOST_OR_PORT = 'inv_mail_host_or_port';
    /**
     * A constant that indicates the selected database schema has tables.
     * 
     * @since 1.1
     */
    const DB_NOT_EMPTY = 'db_has_tables';
    /**
     * An array that contains initial system configuration variables.
     * This array has the following indices and values:
     * <ul>
     * <li>is-config = 'false'</li>
     * <li>release-date</li>
     * <li>version</li>
     * <li>version-type</li>
     * <li>config-file-version</li>
     * <li>databases</li>
     * </ul>
     * @since 1.0
     */
    const INITIAL_CONFIG_VARS = [
        'release-date' => '2020-12-20',
        'version' => '2.0.0',
        'version-type' => 'Beta 5',
        'config-file-version' => '1.3.5',
        'databases' => []
    ];
    /**
     * An associative array that contains initial system configuration variables.
     * 
     * The array has the following values:
     * <ul>
     * <li>site-descriptions = array(<ul>
     * <li>EN = 'WebFiori'</li>
     * <li>AR = 'ويب فيوري'</li>
     * </ul>)</li>
     * <li>base-url = ''</li>
     * <li>primary-language = 'EN'</li>
     * <li>title-separator = ' | '</li>
     * <li>home-page = 'index'</li>
     * <li>admin-theme-name = 'WebFiori Theme'</li>
     * <li>theme-name = 'WebFiori Theme'</li>
     * <li>site-descriptions = array(<ul>
     * <li>EN = ''</li>
     * <li>AR = ''</li>
     * </ul>)</li>
     * <li>config-file-version => 1.2.1</li>
     * </ul>
     * 
     * @since 1.0
     */
    const INITIAL_WEBSITE_CONFIG_VARS = [
        'website-names' => [
            'EN' => 'WebFiori',
            'AR' => 'ويب فيوري'
        ],
        'base-url' => '',
        'primary-language' => 'EN',
        'title-separator' => ' | ',
        'home-page' => 'index',
        'admin-theme-name' => 'WebFiori Theme',
        'theme-name' => 'WebFiori Theme',
        'site-descriptions' => [
            'EN' => '',
            'AR' => ''
        ],
        'config-file-version' => '1.2.1',
    ];
    
    /**
     * A constant that indicates the file MailConfig.php was not found.
     * 
     * @since 1.2
     */
    const MAIL_CONFIG_MISSING = 'mail_config_file_missing';
    /**
     * A constant that indicates the file SiteConfig.php was not found.
     * 
     * @since 1.2
     */
    const SITE_CONFIG_MISSING = 'site_config_file_missing';
    /**
     * A constant that indicates the file Config.php was not found.
     * 
     * @since 1.2
     */
    const SYS_CONFIG_MISSING = 'config_file_missing';
    /**
     * An instance of the class.
     * 
     * @var ConfigController
     * 
     * @since 1.0 
     */
    private static $singleton;
    /**
     * Adds new database connections information or update existing connections.
     * 
     * The information of the connections will be stored in the file 'Config.php'.
     * 
     * @param array $dbConnectionsInfo An array that contains objects of type ConnectionInfo. 
     * 
     * @since 1.4.3
     */
    public function addOrUpdateDBConnections($dbConnectionsInfo) {
        if (gettype($dbConnectionsInfo) == 'array') {
            $confVars = $this->getConfigVars();

            foreach ($dbConnectionsInfo as $con) {
                if ($con instanceof ConnectionInfo && strlen($con->getHost()) > 0 && 
                    strlen($con->getPort()) > 0 &&
                    strlen($con->getUsername()) > 0 && 
                    strlen($con->getPassword()) > 0 && 
                    strlen($con->getDBName()) > 0) {
                    $confVars['databases'][$con->getName()] = $con;
                }
            }
            $this->writeConfig($confVars);
        }
    }
    /**
     * Creates the file 'SiteConfig.php' if it does not exist.
     * 
     * @since 1.0
     */
    public function createSiteConfigFile() {
        if (!class_exists('webfiori\conf\SiteConfig')) {
            $initCfg = $this->getSiteConfigVars();
            $this->writeSiteConfig($initCfg);
        }
    }
    /**
     * Creates the file 'MailConfig.php' if it does not exist.
     * 
     * @since 1.0
     */
    public function createEmailConfigFile() {
        if (!class_exists('webfiori\conf\MailConfig')) {
            $this->writeMailConfig([]);
        }
    }
    /**
     * Returns a new instance of the class SocketMailer.
     * 
     * The method will try to establish a connection to SMTP server using 
     * the given SMTP account.
     * 
     * @param SMTPAccount $emailAcc An account that is used to initiate 
     * socket mailer.
     * 
     * @return SocketMailer|string The method will return an instance of SocketMailer
     * on successful connection. If no connection is established, the method will 
     * return MailFunctions::INV_HOST_OR_PORT. If user authentication fails, 
     * the method will return 'MailFunctions::INV_CREDENTIALS'.
     * 
     * @since 1.0
     */
    public function getSocketMailer($emailAcc) {
        if ($emailAcc instanceof SMTPAccount) {
            $retVal = self::INV_HOST_OR_PORT;
            $m = new SocketMailer();

            $m->setHost($emailAcc->getServerAddress());
            $m->setPort($emailAcc->getPort());

            if ($m->connect()) {
                try {
                    $m->setSender($emailAcc->getSenderName(), $emailAcc->getAddress());

                    if ($m->login($emailAcc->getUsername(), $emailAcc->getPassword())) {
                        $retVal = $m;
                    } else {
                        $retVal = self::INV_CREDENTIALS;
                    }
                } catch (\Exception $ex) {
                    throw new SMTPException($ex->getMessage());
                }
            }

            return $retVal;
        }

        return false;
    }
    /**
     * Removes SMTP email account if it is exist.
     * 
     * @param string $accountName The name of the email account (such as 'no-reply').
     * 
     * @return boolean If the account is not exist or the class 'MailConfig' 
     * does not exist, the method will return false. If the account was removed, 
     * The method will return true.
     * 
     * @since 1.3
     */
    public function removeAccount($accountName) {
        $retVal = false;

        if (class_exists('webfiori\conf\MailConfig')) {
            $account = MailConfig::getAccount($accountName);

            if ($account instanceof SMTPAccount) {
                $accountsArr = MailConfig::getAccounts();
                unset($accountsArr[$accountName]);
                $toSave = [];

                foreach ($accountsArr as $account) {
                    $toSave[] = $account;
                }
                $this->writeMailConfig($toSave);
                $retVal = true;
            }
        }

        return $retVal;
    }
    /**
     * Adds new SMTP account or Updates an existing one.
     * 
     * Note that the connection will be added or updated only if it 
     * has correct information.
     * 
     * @param SMTPAccount $emailAccount An instance of 'SMTPAccount'.
     * 
     * @return boolean|string The method will return true if the email 
     * account was updated or added. If the email account contains wrong server
     *  information, the method will return MailFunctions::INV_HOST_OR_PORT. 
     * If the given email account contains wrong login info, the method will 
     * return MailFunctions::INV_CREDENTIALS. Other than that, the method 
     * will return false.
     * 
     * @since 1.1
     */
    public function updateOrAddEmailAccount($emailAccount) {
        $retVal = false;

        if ($emailAccount instanceof SMTPAccount) {
            $sm = $this->getSocketMailer($emailAccount);

            if ($sm instanceof SocketMailer) {
                if (class_exists('webfiori\conf\MailConfig')) {
                    $accountsArr = MailConfig::getAccounts();
                    $accountsArr[$emailAccount->getSenderName()] = $emailAccount;
                    $toSave = [];

                    foreach ($accountsArr as $account) {
                        $toSave[] = $account;
                    }
                    $this->writeMailConfig($toSave);
                } else {
                    $arr = [$emailAccount];
                    $this->writeMailConfig($arr);
                }
                $retVal = true;
            }
            $retVal = $sm;
        }

        return $retVal;
    }
    /**
     * A method to save changes to mail configuration file.
     * 
     * @param array $emailAccountsArr An associative array that contains an objects of 
     * type 'SMTPAccount'. The indices of the array are the names of the accounts.
     * 
     * @since 1.1
     */
    private function writeMailConfig($emailAccountsArr) {
        $fileData = ""
                ."<?php".self::NL
                ."namespace webfiori\\conf;".self::NL
                ."".self::NL
                ."use webfiori\\framework\\mail\\SMTPAccount;".self::NL
                ."/**".self::NL
                ." * SMTP configuration class.".self::NL
                ." * ".self::NL
                ." * The developer can create multiple SMTP accounts and add".self::NL
                ." * Connection information inside the body of this class.".self::NL
                ." * ".self::NL
                ." * @author Ibrahim".self::NL
                ." * ".self::NL
                ." * @version 1.0.1".self::NL
                ." */".self::NL
                ."class MailConfig {".self::NL
                ."    private \$emailAccounts;".self::NL
                ."    /**".self::NL
                ."     *".self::NL
                ."     * @var MailConfig".self::NL
                ."     * @since 1.0".self::NL
                ."     */".self::NL
                ."    private static \$inst;".self::NL
                ."    private function __construct() {".self::NL
                . "        \$this->emailAccounts = [];".self::NL;
        $index = 0;

        foreach ($emailAccountsArr as $emailAcc) {
            $fileData .= ""
                    ."        \$acc$index = new SMTPAccount([".self::NL
                    ."            'server-address' => '".$emailAcc->getServerAddress()."',".self::NL
                    ."            'port' => ".$emailAcc->getPort().",".self::NL
                    ."            'user' => '".$emailAcc->getUsername()."',".self::NL
                    ."            'pass' => '".$emailAcc->getPassword()."',".self::NL
                    ."            'sender-name' => '".$emailAcc->getSenderName()."',".self::NL
                    ."            'sender-address' => '".$emailAcc->getAddress()."',".self::NL
                    ."            'account-name' => '".$emailAcc->getAccountName()."'".self::NL
                    ."        ]);".self::NL
                    ."        \$this->addAccount(\$acc$index, '".$emailAcc->getAccountName()."');".self::NL
                    ."        ".self::NL;
            $index++;
        }
        $fileData .= "    }".self::NL
                ."    /**".self::NL
                ."     * Adds new SMTP connection information or updates an existing one.".self::NL
                ."     * ".self::NL
                ."     * @param string \$accName The name of the account that will be added or updated.".self::NL
                ."     * ".self::NL
                ."     * @param SMTPAccount \$smtpConnInfo An object of type 'SMTPAccount' that".self::NL
                ."     * will contain SMTP account information.".self::NL
                ."     * ".self::NL
                ."     * @since 1.0.1".self::NL
                ."     */".self::NL
                ."    public static function addSMTPAccount(\$accName, \$smtpConnInfo) {".self::NL
                ."        if (\$smtpConnInfo instanceof SMTPAccount) {".self::NL
                ."            \$trimmedName = trim(\$accName);".self::NL
                ."            ".self::NL
                ."            if (strlen(\$trimmedName) != 0) {".self::NL
                ."                self::get()->addAccount(\$smtpConnInfo, \$trimmedName);".self::NL
                ."            }".self::NL
                ."        }".self::NL
                ."    }".self::NL
                .""
                ."    /**".self::NL
                ."     * Return a single instance of the class.".self::NL
                ."     * ".self::NL
                ."     * Calling this method multiple times will result in returning".self::NL
                ."     * the same instance every time.".self::NL
                ."     * ".self::NL
                ."     * @return MailConfig".self::NL
                ."     * ".self::NL
                ."     * @since 1.0".self::NL
                ."     */".self::NL
                ."    public static function get() {".self::NL
                ."        if (self::\$inst === null) {".self::NL
                ."            self::\$inst = new MailConfig();".self::NL
                ."        }".self::NL
                ."        ".self::NL
                ."        return self::\$inst;".self::NL
                ."    }".self::NL
                .""
                ."    /**".self::NL
                ."     * Returns an email account given its name.".self::NL
                ."     * ".self::NL
                ."     * The method will search for an account with the given name in the set".self::NL
                ."     * of added accounts. If no account was found, null is returned.v"
                ."     * ".self::NL
                ."     * @param string \$name The name of the account.".self::NL
                ."     * ".self::NL
                ."     * @return SMTPAccount|null If the account is found, The method".self::NL
                ."     * will return an object of type SMTPAccount. Else, the".self::NL
                ."     * method will return null.".self::NL
                ."     * ".self::NL
                ."     * @since 1.0".self::NL
                ."     */".self::NL
                ."    public static function getAccount(\$name) {".self::NL
                ."        return self::get()->_getAccount(\$name);".self::NL
                ."    }".self::NL
                .""
                ."    /**".self::NL
                ."     * Returns an associative array that contains all email accounts.".self::NL
                ."     * ".self::NL
                ."     * The indices of the array will act as the names of the accounts.".self::NL
                ."     * The value of the index will be an object of type EmailAccount.".self::NL
                ."     * ".self::NL
                ."     * @return array An associative array that contains all email accounts.".self::NL
                ."     * ".self::NL
                ."     * @since 1.0".self::NL
                ."     */".self::NL
                ."    public static function getAccounts() {".self::NL
                ."        return self::get()->_getAccounts();".self::NL
                ."    }".self::NL
                ."    private function _getAccount(\$name) {".self::NL
                ."        if (isset(\$this->emailAccounts[\$name])) {".self::NL
                ."            return \$this->emailAccounts[\$name];".self::NL
                ."        }".self::NL
                ."        ".self::NL
                ."        return null;".self::NL
                ."    }".self::NL
                ."    private function _getAccounts() {".self::NL
                ."        return \$this->emailAccounts;".self::NL
                ."    }".self::NL
                .""
                ."    /**".self::NL
                ."     * Adds an email account.".self::NL
                ."     * ".self::NL
                ."     * The developer can use this method to add new account during runtime.".self::NL
                ."     * The account will be removed once the program finishes.".self::NL
                ."     * ".self::NL
                ."     * @param SMTPAccount \$acc an object of type SMTPAccount.".self::NL
                ."     * ".self::NL
                ."     * @param string \$name A name to associate with the email account.".self::NL
                ."     * ".self::NL
                ."     * @since 1.0".self::NL
                ."     */".self::NL
                ."    private function addAccount(\$acc,\$name) {".self::NL
                ."        \$this->emailAccounts[\$name] = \$acc;".self::NL
                ."    }".self::NL;
        //End of class
        $fileData .= "}".self::NL;
        $mailConfigFile = new File('MailConfig.php', ROOT_DIR.DS.'conf');
        $mailConfigFile->remove();
        $mailConfigFile->setRawData($fileData);
        $mailConfigFile->write(false, true);
    }
    /**
     * Returns an associative array that contains web site configuration 
     * info.
     * 
     * The returned array will have the following indices: 
     * <ul>
     * <li><b>website-names</b>: A sub associative array. The index of the 
     * array will be language code (such as 'EN') and the value 
     * will be the name of the web site in the given language.</li>
     * <li><b>base-url</b>: The URL at which system pages will be served from. 
     * usually, this URL is used in the tag 'base' of the web page.</li>
     * <li><b>title-separator</b>: A character or a string that is used 
     * to separate web site name from web page title.</li>
     * <li><b>home-page</b>: The URL of the home page of the web site.</li>
     * <li><b>theme-name</b>: The name of the theme that will be used to style 
     * web site UI.</li>
     * <li><b>primary-language</b>: Primary language of the website.
     * <li><b>admin-theme-name</b>: The name of the theme that is used to style 
     * admin web pages.</li>
     * <li><b>site-descriptions</b>: A sub associative array. The index of the 
     * array will be language code (such as 'EN') and the value 
     * will be the general web site description in the given language.</li></li>
     * </ul> 
     * @return array An associative array that contains web site configuration 
     * info.
     * 
     * @since 1.0
     */
    public function getSiteConfigVars() {
        $cfgArr = self::INITIAL_WEBSITE_CONFIG_VARS;

        if (class_exists('webfiori\conf\SiteConfig')) {
            $SC = SiteConfig::get();
            $cfgArr['website-names'] = $SC->getWebsiteNames();
            $cfgArr['base-url'] = $SC->getBaseURL();
            $cfgArr['title-separator'] = $SC->getTitleSep();
            $cfgArr['home-page'] = $SC->getHomePage();
            $cfgArr['primary-language'] = $SC->getPrimaryLanguage();
            $cfgArr['site-descriptions'] = $SC->getDescriptions();
            $cfgArr['theme-name'] = $SC->getBaseThemeName();
            $cfgArr['admin-theme-name'] = $SC->getAdminThemeName();
        }

        return $cfgArr;
    }
    /**
     * Updates web site configuration based on some attributes.
     * 
     * @param array $websiteInfoArr an associative array. The array can 
     * have the following indices: 
     * <ul>
     * <li><b>primary-language</b>: The main display language of the website.
     * <li><b>website-names</b>: A sub associative array. The index of the 
     * array should be language code (such as 'EN') and the value 
     * should be the name of the web site in the given language.</li>
     * <li><b>title-separator</b>: A character or a string that is used 
     * to separate web site name from web page title. Two common 
     * values are '-' and '|'.</li>
     * <li><b>home-page</b>: The URL of the home page of the web site. For example, 
     * If root URL of the web site is 'https://www.example.com', This page is served 
     * when the user visits this URL.</li>
     * <li><b>theme-name</b>: The name of the theme that will be used to style 
     * web site UI.</li>
     * <li><b>admin-theme-name</b>: If the web site has two UIs (One for normal 
     * users and another for admins), this one 
     * can be used to serve the UI for web site admins.</li>
     * <li><b>site-descriptions</b>: A sub associative array. The index of the 
     * array should be language code (such as 'EN') and the value 
     * should be the general web site description in the given language.</li></li>
     * </ul> 
     * 
     * @since 1.0
     */
    public function updateSiteInfo($websiteInfoArr) {
        $confArr = $this->getSiteConfigVars();

        foreach ($confArr as $k => $v) {
            if (isset($websiteInfoArr[$k])) {
                $confArr[$k] = $websiteInfoArr[$k];
            }
        }
        $this->writeSiteConfig($confArr);
    }
    /**
     * A method to save changes to web site configuration file.
     * 
     * @param array $configArr An array that contains system configuration 
     * variables.
     * 
     * @since 1.0
     */
    private function writeSiteConfig($configArr) {
        $names = "[".self::NL;
        foreach ($configArr['website-names'] as $k => $v) {
            $names .= '            \''.$k.'\'=>\''.$v.'\','."".self::NL;
        }
        $names .= '        ]';
        $descriptions = "[".self::NL;

        foreach ($configArr['site-descriptions'] as $k => $v) {
            $descriptions .= '            \''.$k.'\'=>\''.$v.'\','."".self::NL;
        }
        $descriptions .= '        ]';
        
        $fileAsStr = "<?php".self::NL
            . "namespace webfiori\conf;".self::NL
            . "".self::NL
            . "use webfiori\\framework\Util;".self::NL
            . "/**".self::NL
            . "  * Website configuration class.".self::NL
            . "  * ".self::NL
            . "  * This class is used to control the following settings:".self::NL
            . "  * <ul>".self::NL
            . "  * <li>The base URL of the website.</li>".self::NL
            . "  * <li>The primary language of the website.</li>".self::NL
            . "  * <li>The name of the website in different languages.</li>".self::NL
            . "  * <li>The general description of the website in different languages.</li>".self::NL
            . "  * <li>The character that is used to separate the name of the website from page title.</li>".self::NL
            . "  * <li>The theme of the website.</li>".self::NL
            . "  * <li>Admin theme of the website (if uses one).</li>".self::NL
            . "  * <li>The home page of the website.</li>".self::NL
            . "  * </ul>".self::NL
            . "  */".self::NL
            . "class SiteConfig {".self::NL
            . "    /**".self::NL
            . "     * The name of admin control pages Theme.".self::NL
            . "     * ".self::NL
            . "     * @var string".self::NL
            . "     * ".self::NL
            . "     * @since 1.3".self::NL
            . "     */".self::NL
            . "    private \$adminThemeName;".self::NL
            . "    /**".self::NL
            . "     * The name of base website UI Theme.".self::NL
            . "     * ".self::NL
            . "     * @var string".self::NL
            . "     * ".self::NL
            . "     * @since 1.3".self::NL
            . "     */".self::NL
            . "    private \$baseThemeName;".self::NL
            . "    /**".self::NL
            . "     * The base URL that is used by all web site pages to fetch resource files.".self::NL
            . "     * ".self::NL
            . "     * @var string".self::NL
            . "     * ".self::NL
            . "     * @since 1.0".self::NL
            . "     */".self::NL
            . "    private \$baseUrl;".self::NL
            . "    /**".self::NL
            . "     * Configuration file version number.".self::NL
            . "     * ".self::NL
            . "     * @var string".self::NL
            . "     * ".self::NL
            . "     * @since 1.2".self::NL
            . "     */".self::NL
            . "    private \$configVision;".self::NL
            . "    /**".self::NL
            . "     * An array which contains different descriptions in different languages.".self::NL
            . "     * ".self::NL
            . "     * @var string".self::NL
            . "     * ".self::NL
            . "     * @since 1.0".self::NL
            . "     */".self::NL
            . "    private \$descriptions;".self::NL
            . "    /**".self::NL
            . "     * The URL of the home page.".self::NL
            . "     * ".self::NL
            . "     * @var string".self::NL
            . "     * ".self::NL
            . "     * @since 1.0".self::NL
            . "     */".self::NL
            . "    private \$homePage;".self::NL
            . "    /**".self::NL
            . "     * The primary language of the website.".self::NL
            . "     */".self::NL
            . "    private \$primaryLang;".self::NL
            . "    /**".self::NL
            . "     * A singleton instance of the class.".self::NL
            . "     * ".self::NL
            . "     * @var SiteConfig".self::NL
            . "     * ".self::NL
            . "     * @since 1.0".self::NL
            . "     */".self::NL
            . "    private static \$siteCfg;".self::NL
            . "    /**".self::NL
            . "     *".self::NL
            . "     * @var string".self::NL
            . "     * ".self::NL
            . "     * @since 1.0".self::NL
            . "     */".self::NL
            . "    private \$titleSep;".self::NL
            . "    /**".self::NL
            . "     * An array which contains all website names in different languages.".self::NL
            . "     * ".self::NL
            . "     * @var string".self::NL
            . "     * ".self::NL
            . "     * @since 1.0".self::NL
            . "     */".self::NL
            . "    private \$webSiteNames;".self::NL
            . "    private function __construct() {".self::NL
            . "        \$this->configVision = '".$configArr['config-file-version']."';".self::NL
            . "        \$this->webSiteNames = ".$names.";".self::NL
            . "        \$this->baseUrl = Util::getBaseURL();".self::NL
            . "        \$this->titleSep = '".trim($configArr['title-separator'])."';".self::NL
            . "        \$this->primaryLang = '".trim($configArr['primary-language'])."';".self::NL
            . "        \$this->baseThemeName = '".$configArr['theme-name']."';".self::NL
            . "        \$this->adminThemeName = '".$configArr['admin-theme-name']."';".self::NL
            . "        \$this->homePage = Util::getBaseURL();".self::NL
            . "        \$this->descriptions = ".$descriptions.";".self::NL
            . "    }".self::NL
            . "    /**".self::NL
            . "     * Returns an instance of the configuration file.".self::NL
            . "     * ".self::NL
            . "     * @return SiteConfig".self::NL
            . "     * ".self::NL
            . "     * @since 1.0".self::NL
            . "     */".self::NL
            . "    public static function get() {".self::NL
            . "        if (self::\$siteCfg != null) {".self::NL
            . "            return self::\$siteCfg;".self::NL
            . "        }".self::NL
            . "        self::\$siteCfg = new SiteConfig();".self::NL
            . "        ".self::NL
            . "        return self::\$siteCfg;".self::NL
            . "    }".self::NL
            . "    /**".self::NL
            . "     * Returns the name of the theme that is used in admin control pages.".self::NL
            . "     * ".self::NL
            . "     * @return string The name of the theme that is used in admin control pages.".self::NL
            . "     * ".self::NL
            . "     * @since 1.3".self::NL
            . "     */".self::NL
            . "    public static function getAdminThemeName() {".self::NL
            . "        return self::get()->_getAdminThemeName();".self::NL
            . "    }".self::NL
            . "    /**".self::NL
            . "     * Returns the name of base theme that is used in website pages.".self::NL
            . "     * ".self::NL
            . "     * Usually, this theme is used for the normall visitors of the web site.".self::NL
            . "     * ".self::NL
            . "     * @return string The name of base theme that is used in website pages.".self::NL
            . "     * ".self::NL
            . "     * @since 1.3".self::NL
            . "     */".self::NL
            . "    public static function getBaseThemeName() {".self::NL
            . "        return self::get()->_getBaseThemeName();".self::NL
            . "    }".self::NL
            . "    /**".self::NL
            . "     * Returns the base URL that is used to fetch resources.".self::NL
            . "     * ".self::NL
            . "     * The return value of this method is usually used by the tag 'base'".self::NL
            . "     * of web site pages.".self::NL
            . "     * ".self::NL
            . "     * @return string the base URL.".self::NL
            . "     * ".self::NL
            . "     * @since 1.0".self::NL
            . "     */".self::NL
            . "    public static function getBaseURL() {".self::NL
            . "        return self::get()->_getBaseURL();".self::NL
            . "    }".self::NL
            . "    /**".self::NL
            . "     * Returns version number of the configuration file.".self::NL
            . "     * ".self::NL
            . "     * This value can be used to check for the compatability of configuration".self::NL
            . "     * file".self::NL
            . "     * ".self::NL
            . "     * @return string The version number of the configuration file.".self::NL
            . "     * ".self::NL
            . "     * @since 1.0".self::NL
            . "     */".self::NL
            . "    public static function getConfigVersion() {".self::NL
            . "        return self::get()->_getConfigVersion();".self::NL
            . "    }".self::NL
            . "    /**".self::NL
            . "     * Returns an associative array which contains different website descriptions".self::NL
            . "     * in different languages.".self::NL
            . "     * ".self::NL
            . "     * Each index will contain a language code and the value will be the description".self::NL
            . "     * of the website in the given language.".self::NL
            . "     * ".self::NL
            . "     * @return string An associative array which contains different website descriptions".self::NL
            . "     * in different languages.".self::NL
            . "     * ".self::NL
            . "     * @since 1.0".self::NL
            . "     */".self::NL
            . "    public static function getDescriptions() {".self::NL
            . "        return self::get()->_getDescriptions();".self::NL
            . "    }".self::NL
            . "    /**".self::NL
            . "     * Returns the home page URL of the website.".self::NL
            . "     * ".self::NL
            . "     * @return string The home page URL of the website.".self::NL
            . "     * ".self::NL
            . "     * @since 1.0".self::NL
            . "     */".self::NL
            . "    public static function getHomePage() {".self::NL
            . "        return self::get()->_getHomePage();".self::NL
            . "    }".self::NL
            . "    /**".self::NL
            . "     * Returns the primary language of the website.".self::NL
            . "     * ".self::NL
            . "     * This function will return a language code such as 'EN'.".self::NL
            . "     * ".self::NL
            . "     * @return string Language code of the primary language.".self::NL
            . "     * ".self::NL
            . "     * @since 1.3".self::NL
            . "     */".self::NL
            . "    public static function getPrimaryLanguage() {".self::NL
            . "        return self::get()->_getPrimaryLanguage();".self::NL
            . "    }".self::NL
            . "    /**".self::NL
            . "     * Returns the character (or string) that is used to separate page title from website name.".self::NL
            . "     * ".self::NL
            . "     * @return string A string such as ' - ' or ' | '. Note that the method".self::NL
            . "     * will add the two spaces by default.".self::NL
            . "     * ".self::NL
            . "     * @since 1.0".self::NL
            . "     */".self::NL
            . "    public static function getTitleSep() {".self::NL
            . "        return self::get()->_getTitleSep();".self::NL
            . "    }".self::NL
            . "    /**".self::NL
            . "     * Returns an array which contains diffrent website names in different languages.".self::NL
            . "     * ".self::NL
            . "     * Each index will contain a language code and the value will be the name".self::NL
            . "     * of the website in the given language.".self::NL
            . "     * ".self::NL
            . "     * @return array An array which contains diffrent website names in different languages.".self::NL
            . "     * ".self::NL
            . "     * @since 1.0".self::NL
            . "     */".self::NL
            . "    public static function getWebsiteNames() {".self::NL
            . "        return self::get()->_getWebsiteNames();".self::NL
            . "    }".self::NL
            . "    private function _getAdminThemeName() {".self::NL
            . "        return \$this->adminThemeName;".self::NL
            . "    }".self::NL
            . "    private function _getBaseThemeName() {".self::NL
            . "        return \$this->baseThemeName;".self::NL
            . "    }".self::NL
            . "    private function _getBaseURL() {".self::NL
            . "        return \$this->baseUrl;".self::NL
            . "    }".self::NL
            . "    private function _getConfigVersion() {".self::NL
            . "        return \$this->configVision;".self::NL
            . "    }".self::NL
            . "    private function _getDescriptions() {".self::NL
            . "        return \$this->descriptions;".self::NL
            . "    }".self::NL
            . "    private function _getHomePage() {".self::NL
            . "        return \$this->homePage;".self::NL
            . "    }".self::NL
            . "    ".self::NL
            . "    private function _getPrimaryLanguage() {".self::NL
            . "        return \$this->primaryLang;".self::NL
            . "    }".self::NL
            . "    private function _getTitleSep() {".self::NL
            . "        return \$this->titleSep;".self::NL
            . "    }".self::NL
            . "    private function _getWebsiteNames() {".self::NL
            . "        return \$this->webSiteNames;".self::NL
            . "    }".self::NL
            . "}".self::NL;
        $mailConfigFile = new File('SiteConfig.php', ROOT_DIR.DS.'conf');
        $mailConfigFile->remove();
        $mailConfigFile->setRawData($fileAsStr);
        $mailConfigFile->write(false, true);
    }
    /**
     * Creates the file 'Config.php' if it does not exist.
     * 
     * @since 1.0
     */
    public function createConfigFile() {
        if (!class_exists('webfiori\conf\Config')) {
            $cfg = $this->getConfigVars();
            $this->writeConfig($cfg);
        }
    }
    /**
     * Returns a single instance of the class.
     * 
     * @return ConfigController
     * 
     * @since 1.0
     */
    public static function get() {
        if (self::$singleton === null) {
            self::$singleton = new ConfigController();
        }

        return self::$singleton;
    }
    /**
     * Returns an associative array that contains system configuration 
     * info.
     * 
     * The array that will be returned will have the following information: 
     * <ul>
     * <li>release-date: The release date of WebFiori Framework.</li>
     * <li>version: Version number of WebFiori Framework.</li>
     * <li>version-type: Type of WebFiori Framework version.</li>
     * <li>config-file-version: Configuration file version number.</li>
     * <li>databases: A sub associative array that contains multiple 
     * database connections information. The key will be the name of the database 
     * and the value is an object of type ConnectionInfo.</li>
     * </ul>
     * 
     * @return array An associative array that contains system configuration 
     * info.
     * 
     * @since 1.0
     */
    public function getConfigVars() {
        $cfgArr = ConfigController::INITIAL_CONFIG_VARS;

        if (class_exists('webfiori\conf\Config')) {
            $cfgArr['databases'] = Config::getDBConnections();
        }

        return $cfgArr;
    }
    /**
     * Removes a set of database connections.
     * 
     * This method will search for a connection which has the given database 
     * name. Once it found, it will remove the connection and save the updated 
     * information to the file 'Config.php'.
     * 
     * @param array $connectionsNames An array that contains the names of database connections.
     * 
     * @since 1.4.3
     */
    public function removeDBConnections($connectionsNames) {
        if (gettype($connectionsNames) == 'array') {
            $confVars = $this->getConfigVars();

            foreach ($connectionsNames as $dbName) {
                unset($confVars['databases'][$dbName]);
            }
            $this->writeConfig($confVars);
        }
    }
    /**
     * A method to save changes to configuration file.
     * 
     * @param type $configArr An array that contains system configuration 
     * variables.
     * 
     * @since 1.0
     */
    private function writeConfig($configArr) {
        
        $fileAsStr = "<?php".self::NL
                . "namespace webfiori\conf;".self::NL
                . "".self::NL
                . "use webfiori\\database\ConnectionInfo;".self::NL
                . "/**".self::NL
                . " * Global configuration class.".self::NL
                . " * ".self::NL
                . " * Used by the server part and the presentation part. It contains framework version".self::NL
                . " * information and database connection settings.".self::NL
                . " * ".self::NL
                . " * @author Ibrahim".self::NL
                . " * ".self::NL
                . " * @version 1.3.5".self::NL
                . " */".self::NL
                . "class Config {".self::NL
                . "    /**".self::NL
                . "     * An instance of Config.".self::NL
                . "     * ".self::NL
                . "     * @var Config".self::NL
                . "     * ".self::NL
                . "     * @since 1.0".self::NL
                . "     */".self::NL
                . "    private static \$cfg;".self::NL
                . "    /**".self::NL
                . "     * An associative array that will contain database connections.".self::NL
                . "     * ".self::NL
                . "     * @var type".self::NL
                . "     */".self::NL
                . "    private \$dbConnections;".self::NL
                . "    /**".self::NL
                . "     * The release date of the framework that is used to build the system.".self::NL
                . "     * ".self::NL
                . "     * @var string Release date of of the framework that is used to build the system.".self::NL
                . "     * ".self::NL
                . "     * @since 1.0".self::NL
                . "     */".self::NL
                . "    private \$releaseDate;".self::NL
                . "    /**".self::NL
                . "     * The version of the framework that is used to build the system.".self::NL
                . "     * ".self::NL
                . "     * @var string The version of the framework that is used to build the system.".self::NL
                . "     * ".self::NL
                . "     * @since 1.0".self::NL
                . "     */".self::NL
                . "    private \$version;".self::NL
                . "    /**".self::NL
                . "     * The type framework version that is used to build the system.".self::NL
                . "     * ".self::NL
                . "     * @var string The framework version that is used to build the system.".self::NL
                . "     * ".self::NL
                . "     * @since 1.0".self::NL
                . "     */".self::NL
                . "    private \$versionType;".self::NL
                . "    ".self::NL
                . "    /**".self::NL
                . "     * Initialize configuration.".self::NL
                . "     */".self::NL
                . "    private function __construct() {".self::NL
                . "        \$this->releaseDate = '".$configArr['release-date']."';".self::NL
                . "        \$this->version = '".$configArr['version']."';".self::NL
                . "        \$this->versionType = '".$configArr['version-type']."';".self::NL
                . "        \$this->configVision = '".$configArr['config-file-version']."';".self::NL
                . "        \$this->dbConnections = [".self::NL
                . "";
        $count = count($configArr['databases']);
        $i = 0;

        foreach ($configArr['databases'] as $dbConn) {
            if ($i + 1 == $count) {
                $fileAsStr .= "            '".$dbConn->getName()."' => new ConnectionInfo("
                        . "'".$dbConn->getDatabaseType()."', "
                        . "'".$dbConn->getUsername()."', "
                        . "'".$dbConn->getPassword()."', "
                        . "'".$dbConn->getDBName()."', "
                        . "'".$dbConn->getHost()."', "
                        . "".$dbConn->getPort().")";
            } else {
                $fileAsStr .= "            '".$dbConn->getName()."' => new ConnectionInfo("
                        . "'".$dbConn->getDatabaseType()."', "
                        . "'".$dbConn->getUsername()."', "
                        . "'".$dbConn->getPassword()."', "
                        . "'".$dbConn->getDBName()."', "
                        . "'".$dbConn->getHost()."', "
                        . "".$dbConn->getPort()."),".self::NL;
            }
            $i++;
        }
        $fileAsStr .= "".self::NL
                   . "        ];".self::NL;
        foreach ($configArr['databases'] as $dbConn) {
            $fileAsStr .= '        $this->dbConnections[\''.$dbConn->getName().'\']->setName(\''.$dbConn->getName().'\');'."".self::NL;
        }
                $fileAsStr .= ""
                . "    }".self::NL
                . "    /**".self::NL
                . "     * Adds new database connection or updates an existing one.".self::NL
                . "     * ".self::NL
                . "     * @param ConnectionInfo \$connectionInfo an object of type 'ConnectionInfo'".self::NL
                . "     * that will contain connection information.".self::NL
                . "     * ".self::NL
                . "     * @since 1.3.4".self::NL
                . "     */".self::NL
                . "    public static function addDbConnection(\$connectionInfo) {".self::NL
                . "        if (\$connectionInfo instanceof ConnectionInfo) {".self::NL
                . "            self::get()->dbConnections[\$connectionInfo->getName()] = \$connectionInfo;".self::NL
                . "        }".self::NL
                . "    }".self::NL
                . "    /**".self::NL
                . "     * Returns an object that can be used to access configuration information.".self::NL
                . "     * ".self::NL
                . "     * @return Config An object of type Config.".self::NL
                . "     * ".self::NL
                . "     * @since 1.0".self::NL
                . "     */".self::NL
                . "    public static function get() {".self::NL
                . "        if (self::\$cfg != null) {".self::NL
                . "            return self::\$cfg;".self::NL
                . "        }".self::NL
                . "        self::\$cfg = new Config();".self::NL
                . "        ".self::NL
                . "        return self::\$cfg;".self::NL
                . "    }".self::NL
                . "    /**".self::NL
                . "     * Returns the version number of configuration file.".self::NL
                . "     * ".self::NL
                . "     * The value is used to check for configuration compatibility since the".self::NL
                . "     * framework is updated and more features are added.".self::NL
                . "     * ".self::NL
                . "     * @return string The version number of configuration file.".self::NL
                . "     * ".self::NL
                . "     * @since 1.2".self::NL
                . "     */".self::NL
                . "    public static function getConfigVersion() {".self::NL
                . "        return self::get()->_getConfigVersion();".self::NL
                . "    }".self::NL
                . "    /**".self::NL
                . "     * Returns database connection information given connection name.".self::NL
                . "     * ".self::NL
                . "     * @param string \$conName The name of the connection.".self::NL
                . "     * ".self::NL
                . "     * @return ConnectionInfo|null The method will return an object of type".self::NL
                . "     * ConnectionInfo if a connection info was found for the given connection name.".self::NL
                . "     * Other than that, the method will return null.".self::NL
                . "     * ".self::NL
                . "     * @since 1.3.3".self::NL
                . "     */".self::NL
                . "    public static function getDBConnection(\$conName) {".self::NL
                . "        \$conns = self::getDBConnections();".self::NL
                . "        \$trimmed = trim(\$conName);".self::NL
                . "        ".self::NL
                . "        if (isset(\$conns[\$trimmed])) {".self::NL
                . "            return \$conns[\$trimmed];".self::NL
                . "        }".self::NL
                . "        ".self::NL
                . "        return null;".self::NL
                . "    }".self::NL
                . "    /**".self::NL
                . "     * Returns an associative array that contain the information of database connections.".self::NL
                . "     * ".self::NL
                . "     * The keys of the array will be the name of database connection and the value of".self::NL
                . "     * each key will be an object of type ConnectionInfo.".self::NL
                . "     * ".self::NL
                . "     * @return array An associative array.".self::NL
                . "     * ".self::NL
                . "     * @since 1.3.3".self::NL
                . "     */".self::NL
                . "    public static function getDBConnections() {".self::NL
                . "        return self::get()->dbConnections;".self::NL
                . "    }".self::NL
                . "    /**".self::NL
                . "     * Returns the date at which the current version of the framework is released.".self::NL
                . "     * ".self::NL
                . "     * The format of the date will be YYYY-MM-DD.".self::NL
                . "     * ".self::NL
                . "     * @return string The date at which the current version of the framework is released.".self::NL
                . "     * ".self::NL
                . "     * @since 1.0".self::NL
                . "     */".self::NL
                . "    public static function getReleaseDate() {".self::NL
                . "        return self::get()->_getReleaseDate();".self::NL
                . "    }".self::NL
                . "    /**".self::NL
                . "     * Returns WebFiori Framework version number.".self::NL
                . "     * ".self::NL
                . "     * @return string WebFiori Framework version number. The version number will".self::NL
                . "     * have the following format: x.x.x".self::NL
                . "     * ".self::NL
                . "     * @since 1.2".self::NL
                . "     */".self::NL
                . "    public static function getVersion() {".self::NL
                . "        return self::get()->_getVersion();".self::NL
                . "    }".self::NL
                . "    /**".self::NL
                . "     * Returns WebFiori Framework version type.".self::NL
                . "     * ".self::NL
                . "     * @return string WebFiori Framework version type (e.g. 'Beta', 'Alpha', 'Preview').".self::NL
                . "     * ".self::NL
                . "     * @since 1.2".self::NL
                . "     */".self::NL
                . "    public static function getVersionType() {".self::NL
                . "        return self::get()->_getVersionType();".self::NL
                . "    }".self::NL
                . "    /**".self::NL
                . "     * Checks if the system is configured or not.".self::NL
                . "     * ".self::NL
                . "     * This method is helpful in case the developer would like to create some".self::NL
                . "     * kind of a setup wizard for the web application.".self::NL
                . "     * ".self::NL
                . "     * @return boolean true if the system is configured.".self::NL
                . "     * ".self::NL
                . "     * @since 1.0".self::NL
                . "     */".self::NL
                . "    private function _getConfigVersion() {".self::NL
                . "        return \$this->configVision;".self::NL
                . "    }".self::NL
                . "    private function _getReleaseDate() {".self::NL
                . "        return \$this->releaseDate;".self::NL
                . "    }".self::NL
                . "    ".self::NL
                . "    private function _getVersion() {".self::NL
                . "        return \$this->version;".self::NL
                . "    }".self::NL
                . "    private function _getVersionType() {".self::NL
                . "        return \$this->versionType;".self::NL
                . "    }".self::NL
                . "".self::NL
                . "}".self::NL
                . "";
        
        $mailConfigFile = new File('Config.php', ROOT_DIR.DS.'conf');
        $mailConfigFile->remove();
        $mailConfigFile->setRawData($fileAsStr);
        $mailConfigFile->write(false, true);
    }
}
