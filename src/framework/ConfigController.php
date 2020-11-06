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
        'release-date' => '2020-07-05',
        'version' => '1.1.0',
        'version-type' => 'Beta 3',
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
                ."<?php\n"
                ."namespace webfiori\\conf;\n"
                ."\n"
                ."use webfiori\\framework\\mail\\SMTPAccount;\n"
                ."/**\n"
                ." * SMTP configuration class.\n"
                ." * The developer can create multiple SMTP accounts and add\n"
                ." * Connection information inside the body of this class.\n"
                ." * @author Ibrahim\n"
                ." * @version 1.0.1\n"
                ." */\n"
                ."class MailConfig {\n"
                ."    private \$emailAccounts;\n"
                ."    /**\n"
                ."     *\n"
                ."     * @var MailConfig\n"
                ."     * @since 1.0\n"
                ."     */\n"
                ."    private static \$inst;\n"
                ."    private function __construct() {\n"
                . "        \$this->emailAccounts = [];\n";
        $index = 0;

        foreach ($emailAccountsArr as $emailAcc) {
            $fileData .= ""
                    ."        \$acc$index = new SMTPAccount([\n"
                    ."            'server-address' => '".$emailAcc->getServerAddress()."',\n"
                    ."            'port' => ".$emailAcc->getPort().",\n"
                    ."            'user' => '".$emailAcc->getUsername()."',\n"
                    ."            'pass' => '".$emailAcc->getPassword()."',\n"
                    ."            'sender-name' => '".$emailAcc->getSenderName()."',\n"
                    ."            'sender-address' => '".$emailAcc->getAddress()."',\n"
                    ."            'account-name' => '".$emailAcc->getAccountName()."'\n"
                    ."        ]);\n"
                    ."        \$this->addAccount(\$acc$index, '".$emailAcc->getAccountName()."');\n"
                    ."        \n";
            $index++;
        }
        $fileData .= "    }\n"
                ."    /**\n"
                ."     * Adds new SMTP connection information or updates an existing one.\n"
                ."     * @param string \$accName The name of the account that will be added or updated.\n"
                ."     * @param SMTPAccount \$smtpConnInfo An object of type 'SMTPAccount' that\n"
                ."     * will contain SMTP account information.\n"
                ."     * @since 1.0.1\n"
                ."     */\n"
                ."    public static function addSMTPAccount(\$accName, \$smtpConnInfo) {\n"
                ."        if (\$smtpConnInfo instanceof SMTPAccount) {\n"
                ."            \$trimmedName = trim(\$accName);\n"
                ."            \n"
                ."            if (strlen(\$trimmedName) != 0) {\n"
                ."                self::get()->addAccount(\$smtpConnInfo, \$trimmedName);\n"
                ."            }\n"
                ."        }\n"
                ."    }\n"
                .""
                ."    /**\n"
                ."     * Return a single instance of the class.\n"
                ."     * Calling this method multiple times will result in returning\n"
                ."     * the same instance every time.\n"
                ."     * @return MailConfig\n"
                ."     * @since 1.0\n"
                ."     */\n"
                ."    public static function get() {\n"
                ."        if (self::\$inst === null) {\n"
                ."            self::\$inst = new MailConfig();\n"
                ."        }\n"
                ."        \n"
                ."        return self::\$inst;\n"
                ."    }\n"
                .""
                ."    /**\n"
                ."     * Returns an email account given its name.\n"
                ."     * The method will search for an account with the given name in the set\n"
                ."     * of added accounts. If no account was found, null is returned.v"
                ."     * @param string \$name The name of the account.\n"
                ."     * @return SMTPAccount|null If the account is found, The method\n"
                ."     * will return an object of type SMTPAccount. Else, the\n"
                ."     * method will return null.\n"
                ."     * @since 1.0\n"
                ."     */\n"
                ."    public static function getAccount(\$name) {\n"
                ."        return self::get()->_getAccount(\$name);\n"
                ."    }\n"
                .""
                ."    /**\n"
                ."     * Returns an associative array that contains all email accounts.\n"
                ."     * The indices of the array will act as the names of the accounts.\n"
                ."     * The value of the index will be an object of type EmailAccount.\n"
                ."     * @return array An associative array that contains all email accounts.\n"
                ."     * @since 1.0\n"
                ."     */\n"
                ."    public static function getAccounts() {\n"
                ."        return self::get()->_getAccounts();\n"
                ."    }\n"
                ."    private function _getAccount(\$name) {\n"
                ."        if (isset(\$this->emailAccounts[\$name])) {\n"
                ."            return \$this->emailAccounts[\$name];\n"
                ."        }\n"
                ."        \n"
                ."        return null;\n"
                ."    }\n"
                ."    private function _getAccounts() {\n"
                ."        return \$this->emailAccounts;\n"
                ."    }\n"
                .""
                ."    /**\n"
                ."     * Adds an email account.\n"
                ."     * The developer can use this method to add new account during runtime.\n"
                ."     * The account will be removed once the program finishes.\n"
                ."     * @param SMTPAccount \$acc an object of type SMTPAccount.\n"
                ."     * @param string \$name A name to associate with the email account.\n"
                ."     * @since 1.0\n"
                ."     */\n"
                ."    private function addAccount(\$acc,\$name) {\n"
                ."        \$this->emailAccounts[\$name] = \$acc;\n"
                ."    }\n";
        //End of class
        $fileData .= "}\n";
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
        $names = "[\n";
        foreach ($configArr['website-names'] as $k => $v) {
            $names .= '            \''.$k.'\'=>\''.$v.'\','."\n";
        }
        $names .= '        ]';
        $descriptions = "[\n";

        foreach ($configArr['site-descriptions'] as $k => $v) {
            $descriptions .= '            \''.$k.'\'=>\''.$v.'\','."\n";
        }
        $descriptions .= '        ]';
        
        $fileAsStr = "<?php\n"
            . "namespace webfiori\conf;\n"
            . "\n"
            . "use webfiori\\framework\Util;\n"
            . "/**\n"
            . "  * Website configuration class.\n"
            . "  * This class is used to control the following settings:\n"
            . "  * <ul>\n"
            . "  * <li>The base URL of the website.</li>\n"
            . "  * <li>The primary language of the website.</li>\n"
            . "  * <li>The name of the website in different languages.</li>\n"
            . "  * <li>The general description of the website in different languages.</li>\n"
            . "  * <li>The character that is used to separate the name of the website from page title.</li>\n"
            . "  * <li>The theme of the website.</li>\n"
            . "  * <li>Admin theme of the website (if uses one).</li>\n"
            . "  * <li>The home page of the website.</li>\n"
            . "  * </ul>\n"
            . "  */\n"
            . "class SiteConfig {\n"
            . "    /**\n"
            . "     * The name of admin control pages Theme.\n"
            . "     * @var string\n"
            . "     * @since 1.3\n"
            . "     */\n"
            . "    private \$adminThemeName;\n"
            . "    /**\n"
            . "     * The name of base website UI Theme.\n"
            . "     * @var string\n"
            . "     * @since 1.3\n"
            . "     */\n"
            . "    private \$baseThemeName;\n"
            . "    /**\n"
            . "     * The base URL that is used by all web site pages to fetch resource files.\n"
            . "     * @var string\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    private \$baseUrl;\n"
            . "    /**\n"
            . "     * Configuration file version number.\n"
            . "     * @var string\n"
            . "     * @since 1.2\n"
            . "     */\n"
            . "    private \$configVision;\n"
            . "    /**\n"
            . "     * An array which contains different descriptions in different languages.\n"
            . "     * @var string\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    private \$descriptions;\n"
            . "    /**\n"
            . "     * The URL of the home page.\n"
            . "     * @var string\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    private \$homePage;\n"
            . "    /**\n"
            . "     * The primary language of the website.\n"
            . "     */\n"
            . "    private \$primaryLang;\n"
            . "    /**\n"
            . "     * A singleton instance of the class.\n"
            . "     * @var SiteConfig\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    private static \$siteCfg;\n"
            . "    /**\n"
            . "     *\n"
            . "     * @var string\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    private \$titleSep;\n"
            . "    /**\n"
            . "     * An array which contains all website names in different languages.\n"
            . "     * @var string\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    private \$webSiteNames;\n"
            . "    private function __construct() {\n"
            . "        \$this->configVision = '".$configArr['config-file-version']."';\n"
            . "        \$this->webSiteNames = ".$names.";\n"
            . "        \$this->baseUrl = Util::getBaseURL();\n"
            . "        \$this->titleSep = '".trim($configArr['title-separator'])."';\n"
            . "        \$this->primaryLang = '".trim($configArr['primary-language'])."';\n"
            . "        \$this->baseThemeName = '".$configArr['theme-name']."';\n"
            . "        \$this->adminThemeName = '".$configArr['admin-theme-name']."';\n"
            . "        \$this->homePage = Util::getBaseURL();\n"
            . "        \$this->descriptions = ".$descriptions.";\n"
            . "    }\n"
            . "    /**\n"
            . "     * Returns an instance of the configuration file.\n"
            . "     * @return SiteConfig\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    public static function get() {\n"
            . "        if (self::\$siteCfg != null) {\n"
            . "            return self::\$siteCfg;\n"
            . "        }\n"
            . "        self::\$siteCfg = new SiteConfig();\n"
            . "        \n"
            . "        return self::\$siteCfg;\n"
            . "    }\n"
            . "    /**\n"
            . "     * Returns the name of the theme that is used in admin control pages.\n"
            . "     * @return string The name of the theme that is used in admin control pages.\n"
            . "     * @since 1.3\n"
            . "     */\n"
            . "    public static function getAdminThemeName() {\n"
            . "        return self::get()->_getAdminThemeName();\n"
            . "    }\n"
            . "    /**\n"
            . "     * Returns the name of base theme that is used in website pages.\n"
            . "     * Usually, this theme is used for the normall visitors of the web site.\n"
            . "     * @return string The name of base theme that is used in website pages.\n"
            . "     * @since 1.3\n"
            . "     */\n"
            . "    public static function getBaseThemeName() {\n"
            . "        return self::get()->_getBaseThemeName();\n"
            . "    }\n"
            . "    /**\n"
            . "     * Returns the base URL that is used to fetch resources.\n"
            . "     * The return value of this method is usually used by the tag 'base'\n"
            . "     * of web site pages.\n"
            . "     * @return string the base URL.\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    public static function getBaseURL() {\n"
            . "        return self::get()->_getBaseURL();\n"
            . "    }\n"
            . "    /**\n"
            . "     * Returns version number of the configuration file.\n"
            . "     * This value can be used to check for the compatability of configuration\n"
            . "     * file\n"
            . "     * @return string The version number of the configuration file.\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    public static function getConfigVersion() {\n"
            . "        return self::get()->_getConfigVersion();\n"
            . "    }\n"
            . "    /**\n"
            . "     * Returns an associative array which contains different website descriptions\n"
            . "     * in different languages.\n"
            . "     * Each index will contain a language code and the value will be the description\n"
            . "     * of the website in the given language.\n"
            . "     * @return string An associative array which contains different website descriptions\n"
            . "     * in different languages.\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    public static function getDescriptions() {\n"
            . "        return self::get()->_getDescriptions();\n"
            . "    }\n"
            . "    /**\n"
            . "     * Returns the home page URL of the website.\n"
            . "     * @return string The home page URL of the website.\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    public static function getHomePage() {\n"
            . "        return self::get()->_getHomePage();\n"
            . "    }\n"
            . "    /**\n"
            . "     * Returns the primary language of the website.\n"
            . "     * This function will return a language code such as 'EN'.\n"
            . "     * @return string Language code of the primary language.\n"
            . "     * @since 1.3\n"
            . "     */\n"
            . "    public static function getPrimaryLanguage() {\n"
            . "        return self::get()->_getPrimaryLanguage();\n"
            . "    }\n"
            . "    /**\n"
            . "     * Returns the character (or string) that is used to separate page title from website name.\n"
            . "     * @return string A string such as ' - ' or ' | '. Note that the method\n"
            . "     * will add the two spaces by default.\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    public static function getTitleSep() {\n"
            . "        return self::get()->_getTitleSep();\n"
            . "    }\n"
            . "    /**\n"
            . "     * Returns an array which contains diffrent website names in different languages.\n"
            . "     * Each index will contain a language code and the value will be the name\n"
            . "     * of the website in the given language.\n"
            . "     * @return array An array which contains diffrent website names in different languages.\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    public static function getWebsiteNames() {\n"
            . "        return self::get()->_getWebsiteNames();\n"
            . "    }\n"
            . "    private function _getAdminThemeName() {\n"
            . "        return \$this->adminThemeName;\n"
            . "    }\n"
            . "    private function _getBaseThemeName() {\n"
            . "        return \$this->baseThemeName;\n"
            . "    }\n"
            . "    private function _getBaseURL() {\n"
            . "        return \$this->baseUrl;\n"
            . "    }\n"
            . "    private function _getConfigVersion() {\n"
            . "        return \$this->configVision;\n"
            . "    }\n"
            . "    private function _getDescriptions() {\n"
            . "        return \$this->descriptions;\n"
            . "    }\n"
            . "    private function _getHomePage() {\n"
            . "        return \$this->homePage;\n"
            . "    }\n"
            . "    \n"
            . "    private function _getPrimaryLanguage() {\n"
            . "        return \$this->primaryLang;\n"
            . "    }\n"
            . "    private function _getTitleSep() {\n"
            . "        return \$this->titleSep;\n"
            . "    }\n"
            . "    private function _getWebsiteNames() {\n"
            . "        return \$this->webSiteNames;\n"
            . "    }\n"
            . "}\n";
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
     * Checks if the application setup is completed or not.
     * 
     * Note that the method will throw an exception in case one of the 3 main 
     * configuration files is missing.
     * 
     * @return boolean If the system is configured, the method will return 
     * true. If it is not configured, It will return false.
     * 
     * @throws InitializationException If one of configuration files is missing. The format 
     * of exception message will be 'XX.php is missing.' where XX is the name 
     * of the configuration file.
     * 
     * @since 1.0
     */
    public function isSetupFinished() {
        if (class_exists('webfiori\conf\Config')) {
            if (class_exists('webfiori\conf\MailConfig')) {
                if (class_exists('webfiori\conf\SiteConfig')) {
                    return Config::isConfig();
                }
                throw new InitializationException('SiteConfig.php is missing.');
            }
            throw new InitializationException('MailConfig.php is missing.');
        }
        throw new InitializationException('Config.php is missing.');
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
        
        $fileAsStr = "<?php\n"
                . "namespace webfiori\conf;\n"
                . "\n"
                . "use webfiori\\database\ConnectionInfo;\n"
                . "/**\n"
                . " * Global configuration class.\n"
                . " * \n"
                . " * Used by the server part and the presentation part. It contains framework version\n"
                . " * information and database connection settings.\n"
                . " * \n"
                . " * @author Ibrahim\n"
                . " * \n"
                . " * @version 1.3.5\n"
                . " */\n"
                . "class Config {\n"
                . "    /**\n"
                . "     * An instance of Config.\n"
                . "     * \n"
                . "     * @var Config\n"
                . "     * \n"
                . "     * @since 1.0\n"
                . "     */\n"
                . "    private static \$cfg;\n"
                . "    /**\n"
                . "     * An associative array that will contain database connections.\n"
                . "     * \n"
                . "     * @var type\n"
                . "     */\n"
                . "    private \$dbConnections;\n"
                . "    /**\n"
                . "     * A boolean value. Set to true once system configuration is completed.\n"
                . "     * \n"
                . "     * @var boolean\n"
                . "     * \n"
                . "     * @since 1.0\n"
                . "     */\n"
                . "    private \$isConfigured;\n"
                . "    /**\n"
                . "     * The release date of the framework that is used to build the system.\n"
                . "     * \n"
                . "     * @var string Release date of of the framework that is used to build the system.\n"
                . "     * \n"
                . "     * @since 1.0\n"
                . "     */\n"
                . "    private \$releaseDate;\n"
                . "    /**\n"
                . "     * The version of the framework that is used to build the system.\n"
                . "     * \n"
                . "     * @var string The version of the framework that is used to build the system.\n"
                . "     * \n"
                . "     * @since 1.0\n"
                . "     */\n"
                . "    private \$version;\n"
                . "    /**\n"
                . "     * The type framework version that is used to build the system.\n"
                . "     * \n"
                . "     * @var string The framework version that is used to build the system.\n"
                . "     * \n"
                . "     * @since 1.0\n"
                . "     */\n"
                . "    private \$versionType;\n"
                . "    \n"
                . "    /**\n"
                . "     * Initialize configuration.\n"
                . "     */\n"
                . "    private function __construct() {\n"
                . "        \$this->releaseDate = '".$configArr['release-date']."';\n"
                . "        \$this->version = '".$configArr['version']."';\n"
                . "        \$this->versionType = '".$configArr['version-type']."';\n"
                . "        \$this->configVision = '".$configArr['config-file-version']."';\n"
                . "        \$this->dbConnections = [\n"
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
                        . "".$dbConn->getPort()."),\n";
            }
            $i++;
        }
        $fileAsStr .= "\n"
                   . "        ];\n";
        foreach ($configArr['databases'] as $dbConn) {
            $fileAsStr .= '        $this->dbConnections[\''.$dbConn->getName().'\']->setName(\''.$dbConn->getName().'\');'."\n";
        }
                $fileAsStr .= ""
                . "    }\n"
                . "    /**\n"
                . "     * Adds new database connection or updates an existing one.\n"
                . "     * \n"
                . "     * @param ConnectionInfo \$connectionInfo an object of type 'ConnectionInfo'\n"
                . "     * that will contain connection information.\n"
                . "     * \n"
                . "     * @since 1.3.4\n"
                . "     */\n"
                . "    public static function addDbConnection(\$connectionInfo) {\n"
                . "        if (\$connectionInfo instanceof ConnectionInfo) {\n"
                . "            self::get()->dbConnections[\$connectionInfo->getName()] = \$connectionInfo;\n"
                . "        }\n"
                . "    }\n"
                . "    /**\n"
                . "     * Returns an object that can be used to access configuration information.\n"
                . "     * \n"
                . "     * @return Config An object of type Config.\n"
                . "     * \n"
                . "     * @since 1.0\n"
                . "     */\n"
                . "    public static function get() {\n"
                . "        if (self::\$cfg != null) {\n"
                . "            return self::\$cfg;\n"
                . "        }\n"
                . "        self::\$cfg = new Config();\n"
                . "        \n"
                . "        return self::\$cfg;\n"
                . "    }\n"
                . "    /**\n"
                . "     * Returns the version number of configuration file.\n"
                . "     * \n"
                . "     * The value is used to check for configuration compatibility since the\n"
                . "     * framework is updated and more features are added.\n"
                . "     * \n"
                . "     * @return string The version number of configuration file.\n"
                . "     * \n"
                . "     * @since 1.2\n"
                . "     */\n"
                . "    public static function getConfigVersion() {\n"
                . "        return self::get()->_getConfigVersion();\n"
                . "    }\n"
                . "    /**\n"
                . "     * Returns database connection information given connection name.\n"
                . "     * \n"
                . "     * @param string \$conName The name of the connection.\n"
                . "     * \n"
                . "     * @return ConnectionInfo|null The method will return an object of type\n"
                . "     * ConnectionInfo if a connection info was found for the given connection name.\n"
                . "     * Other than that, the method will return null.\n"
                . "     * \n"
                . "     * @since 1.3.3\n"
                . "     */\n"
                . "    public static function getDBConnection(\$conName) {\n"
                . "        \$conns = self::getDBConnections();\n"
                . "        \$trimmed = trim(\$conName);\n"
                . "        \n"
                . "        if (isset(\$conns[\$trimmed])) {\n"
                . "            return \$conns[\$trimmed];\n"
                . "        }\n"
                . "        \n"
                . "        return null;\n"
                . "    }\n"
                . "    /**\n"
                . "     * Returns an associative array that contain the information of database connections.\n"
                . "     * \n"
                . "     * The keys of the array will be the name of database connection and the value of\n"
                . "     * each key will be an object of type ConnectionInfo.\n"
                . "     * \n"
                . "     * @return array An associative array.\n"
                . "     * \n"
                . "     * @since 1.3.3\n"
                . "     */\n"
                . "    public static function getDBConnections() {\n"
                . "        return self::get()->dbConnections;\n"
                . "    }\n"
                . "    /**\n"
                . "     * Returns the date at which the current version of the framework is released.\n"
                . "     * \n"
                . "     * The format of the date will be YYYY-MM-DD.\n"
                . "     * \n"
                . "     * @return string The date at which the current version of the framework is released.\n"
                . "     * \n"
                . "     * @since 1.0\n"
                . "     */\n"
                . "    public static function getReleaseDate() {\n"
                . "        return self::get()->_getReleaseDate();\n"
                . "    }\n"
                . "    /**\n"
                . "     * Returns WebFiori Framework version number.\n"
                . "     * \n"
                . "     * @return string WebFiori Framework version number. The version number will\n"
                . "     * have the following format: x.x.x\n"
                . "     * \n"
                . "     * @since 1.2\n"
                . "     */\n"
                . "    public static function getVersion() {\n"
                . "        return self::get()->_getVersion();\n"
                . "    }\n"
                . "    /**\n"
                . "     * Returns WebFiori Framework version type.\n"
                . "     * \n"
                . "     * @return string WebFiori Framework version type (e.g. 'Beta', 'Alpha', 'Preview').\n"
                . "     * \n"
                . "     * @since 1.2\n"
                . "     */\n"
                . "    public static function getVersionType() {\n"
                . "        return self::get()->_getVersionType();\n"
                . "    }\n"
                . "    /**\n"
                . "     * Checks if the system is configured or not.\n"
                . "     * \n"
                . "     * This method is helpful in case the developer would like to create some\n"
                . "     * kind of a setup wizard for the web application.\n"
                . "     * \n"
                . "     * @return boolean true if the system is configured.\n"
                . "     * \n"
                . "     * @since 1.0\n"
                . "     */\n"
                . "    public static function isConfig() {\n"
                . "        return self::get()->_isConfig();\n"
                . "    }\n"
                . "    private function _getConfigVersion() {\n"
                . "        return \$this->configVision;\n"
                . "    }\n"
                . "    private function _getReleaseDate() {\n"
                . "        return \$this->releaseDate;\n"
                . "    }\n"
                . "    \n"
                . "    private function _getVersion() {\n"
                . "        return \$this->version;\n"
                . "    }\n"
                . "    private function _getVersionType() {\n"
                . "        return \$this->versionType;\n"
                . "    }\n"
                . "    private function _isConfig() {\n"
                . "        return \$this->isConfigured;\n"
                . "    }\n"
                . "\n"
                . "}\n"
                . "";
        
        $mailConfigFile = new File('Config.php', ROOT_DIR.DS.'conf');
        $mailConfigFile->remove();
        $mailConfigFile->setRawData($fileAsStr);
        $mailConfigFile->write(false, true);
    }
}
