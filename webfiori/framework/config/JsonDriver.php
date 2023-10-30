<?php
namespace webfiori\framework\config;

use Exception;
use webfiori\database\ConnectionInfo;
use webfiori\email\SMTPAccount;
use webfiori\file\File;
use webfiori\http\Uri;
use webfiori\json\Json;

/**
 * Application configuration driver which is used to read and write application
 * configuration from JSON file.
 *
 * The driver will create a JSON file in the path 'APP_PATH/config' with
 * the name 'app-config.json'. The developer can use the file to
 * modify application configuration. The name of the file can be changed as needed.
 *
 * @author Ibrahim
 */
class JsonDriver implements ConfigurationDriver {
    /**
     * The name of JSON configuration file.
     */
    private static $configFileName = 'app-config';
    /**
     * The location at which the configuration file will be kept at.
     *
     * @var string The file will be stored at [APP_PATH]/config/';
     */
    const JSON_CONFIG_FILE_PATH = APP_PATH.'config'.DIRECTORY_SEPARATOR;
    /**
     * Sets the name of the file that configuration values will be taken from.
     * 
     * The file must exist on the directory [APP_PATH]/config/ .
     * 
     * @param string $name
     */
    public static function setConfigFileName(string $name) {
        $split = explode('.', trim($name));
        if (count($split) == 2) {
            self::$configFileName = $split[0];
        } else if (count($split) == 1) {
            self::$configFileName = trim($name); 
        }
    }
    /**
     * Returns the name of the file at which the application is using to
     * read configuration.
     * 
     * @return string
     */
    public static function getConfigFileName() : string {
        return self::$configFileName;
    }
    private $json;
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        $this->json = new Json([
            'base-url' => 'DYNAMIC',
            'theme' => null,
            'home-page' => 'BASE_URL',
            'primary-lang' => 'EN',
            'titles' => new Json([
                'AR' => 'افتراضي',
                'EN' => 'Default'
            ], 'none', 'same'),
            'name-separator' => '|',
            'scheduler-password' => 'NO_PASSWORD',
            'app-names' => new Json([
                'AR' => 'تطبيق',
                'EN' => 'Application'
            ], 'none', 'same'),
            'app-descriptions' => new Json([
                'AR' => '',
                'EN' => ''
            ]),
            'version-info' => new Json([
                'version' => '1.0',
                'version-type' => 'Stable',
                'release-date' => date('Y-m-d')
            ], 'none', 'same'),
            'env-vars' => new Json([
                'WF_VERBOSE' => new Json([
                    'value' => false,
                    'description' => 'Configure the verbosity of error messsages at run-time. This should be set to true in testing and false in production.'
                ], 'none', 'same'),
                "CLI_HTTP_HOST" => new Json([
                    "value" => "example.com",
                    "description" => "Host name that will be used when runing the application as command line utility."
                ], 'none', 'same')
            ], 'none', 'same'),
            'smtp-connections' => new Json(),
            'database-connections' => new Json(),
        ], 'none', 'same');
        $this->json->setIsFormatted(true);
    }
    /**
     * Adds application environment variable to the configuration.
     *
     * The variables which are added using this method will be defined as
     * a named constant at run time using the function 'define'. This means
     * the constant will be accessable anywhere within the application's environment.
     *
     * @param string $name The name of the named constant such as 'MY_CONSTANT'.
     *
     * @param mixed $value The value of the constant.
     *
     * @param string $description An optional description to describe the porpuse
     * of the constant.
     */
    public function addEnvVar(string $name, $value, string $description = null) {
        $this->json->get('env-vars')->add($name, new Json([
            'value' => $value,
            'description' => $description
        ]));
        $this->writeJson();
    }
    /**
     * Removes specific application environment variable given its name.
     * 
     * @param string $name The name of the variable.
     */
    public function removeEnvVar(string $name) {
        $this->json->get('env-vars')->remove($name);
        $this->writeJson();
    }
    public function addOrUpdateDBConnection(ConnectionInfo $dbConnectionsInfo) {
        $connectionJAsJson = new Json([
            'type' => $dbConnectionsInfo->getDatabaseType(),
            'host' => $dbConnectionsInfo->getHost(),
            'port' => $dbConnectionsInfo->getPort(),
            'username' => $dbConnectionsInfo->getUsername(),
            'database' => $dbConnectionsInfo->getDBName(),
            'password' => $dbConnectionsInfo->getPassword(),
        ]);
        $connectionJAsJson->addArray('extras', $dbConnectionsInfo->getExtars(), true);
        $this->json->get('database-connections')->add($dbConnectionsInfo->getName(), $connectionJAsJson);
        $this->writeJson();
    }

    public function addOrUpdateSMTPAccount(SMTPAccount $emailAccount) {
        $connectionAsJson = new Json([
            'host' => $emailAccount->getServerAddress(),
            'port' => $emailAccount->getPort(),
            'username' => $emailAccount->getUsername(),
            'password' => $emailAccount->getPassword(),
            'address' => $emailAccount->getAddress(),
            'sender-name' => $emailAccount->getSenderName(),

        ]);
        $this->json->get('smtp-connections')->add($emailAccount->getAccountName(), $connectionAsJson);
        $this->writeJson();
    }

    public function getAppName(string $langCode) {
        return $this->json->get('app-names')->get(strtoupper(trim($langCode)));
    }
    /**
     * Returns an array that holds different names for the web application
     * on different languages.
     *
     * @return array The indices of the array are language codes such as 'AR' and
     * the value of the index is the name.
     *
     */
    public function getAppNames(): array {
        $appNamesJson = $this->json->get('app-names');
        $retVal = [];

        foreach ($appNamesJson->getProperties() as $prob) {
            $retVal[$prob->getName()] = $prob->getValue();
        }

        return $retVal;
    }

    public function getAppReleaseDate() : string {
        return $this->json->get('version-info')->get('release-date');
    }

    public function getAppVersion() : string {
        return $this->json->get('version-info')->get('version');
    }

    public function getAppVersionType() : string {
        return $this->json->get('version-info')->get('version-type');
    }
    /**
     * Returns the base URL of the application.
     * 
     * Note that if the base is set so 'DYNAMIC' in the configuration, it will
     * be auto-generated at run time.
     * 
     * @return string A string such as 'http://example.com:8989'.
     */
    public function getBaseURL(): string {
        $val = $this->json->get('base-url');
        if ($val == '' || $val == 'DYNAMIC') {
            return Uri::getBaseURL();
        }
        return $val;
    }

    public function getDBConnection(string $conName) {
        $jsonObj = $this->json->get('database-connections')->get($conName);

        if ($jsonObj !== null) {
            $extras = $jsonObj->get('extras');
            $extrasArr = [];
            if ($extras instanceof Json) {
                foreach ($extras->getProperties() as $prop) {
                    $extrasArr[$prop->getName()] = $prop->getValue();
                }
            }
            return new ConnectionInfo(
                $jsonObj->get('type'),
                $jsonObj->get('username'),
                $jsonObj->get('password'),
                $jsonObj->get('database'),
                $jsonObj->get('host'),
                $jsonObj->get('port'),
                $extrasArr);
        }
    }
    /**
     * Returns an associative array that contain the information of database connections.
     *
     * @return array An associative array. The indices are connections names and
     * values are objects of type 'ConnectionInfo'.
     */
    public function getDBConnections(): array {
        $accountsInfo = $this->json->get('database-connections');
        $retVal = [];

        foreach ($accountsInfo->getProperties() as $propObj) {
            $name = $propObj->getName();
            $jsonObj = $propObj->getValue();
            $acc = new ConnectionInfo(
                    $this->getProp($jsonObj, 'type', $name), 
                    $this->getProp($jsonObj, 'username', $name), 
                    $this->getProp($jsonObj, 'password', $name), 
                    $this->getProp($jsonObj, 'database', $name));
            $extrasObj = $jsonObj->get('extras');
            
            if ($extrasObj !== null && $extrasObj instanceof Json) {
                $extrasArr = [];
                
                foreach ($extrasObj->getProperties() as $prop) {
                    $extrasArr[$prop->getName()] = $prop->getValue();
                }
                $acc->setExtras($extrasArr);
            }
            $acc->setHost($this->getProp($jsonObj, 'host', $name));
            $acc->setName($propObj->getName());
            $acc->setPort($this->getProp($jsonObj, 'port', $name));
            $retVal[$propObj->getName()] = $acc;
        }

        return $retVal;
    }
    private function getProp(Json $j, $name, string $connName) {
        $val = $j->get($name);
        if ($val === null) {
            throw new Exception('The property "'.$name.'" of the connection "'.$connName.'" is missing.');
        }
        return $val;
    }

    public function getDescription(string $langCode) {
        return $this->json->get('app-descriptions')->get(strtoupper(trim($langCode)));
    }
    /**
     * Returns an array that holds different descriptions for the web application
     * on different languages.
     *
     * @return array The indices of the array are language codes such as 'AR' and
     * the value of the index is the description.
     */
    public function getDescriptions(): array {
        $descriptions = $this->json->get('app-descriptions');
        $retVal = [];

        foreach ($descriptions->getProperties() as $prob) {
            $retVal[$prob->getName()] = $prob->getValue();
        }

        return $retVal;
    }

    public function getEnvVars(): array {
        $retVal = [];
        $vars = $this->json->get('env-vars');

        foreach ($vars->getPropsNames() as $name) {
            $retVal[$name] = [
                'value' => $this->json->get('env-vars')->get($name)->get('value'),
                'description' => $this->json->get('env-vars')->get($name)->get('description')
            ];
        }

        return $retVal;
    }
    /**
     * Returns a string that represent the home page of the application.
     * 
     * Note that if home page is set to 'BASE_URL' in configuration, the
     * method will use the base URL as the home page.
     * 
     * @return string
     */
    public function getHomePage() : string {
        $home = $this->json->get('home-page') ?? '';
        if ($home == 'BASE_URL') {
            return $this->getBaseURL();
        }
        return $home;
    }

    public function getPrimaryLanguage(): string {
        return $this->json->get('primary-lang');
    }
    /**
     * Returns sha256 hash of the password which is used to prevent unauthorized
     * access to run background tasks or access scheduler web interface.
     *
     * The password should be hashed before using this method as this one should
     * return the hashed value. If no password is set, this method will return the
     * string 'NO_PASSWORD'.
     *
     * @return string Password hash or the string 'NO_PASSWORD' if there is no
     * password.
     */
    public function getSchedulerPassword(): string {
        return $this->json->get('scheduler-password') ?? 'NO_PASSWORD';
    }
    /**
     * Returns SMTP connection given its name.
     *
     * The method will search
     * for an account with the given name in the set
     * of added accounts. If no account was found, null is returned.
     *
     * @param string $name The name of the account.
     *
     * @return SMTPAccount|null If the account is found, The method
     * will return an object of type SMTPAccount. Else, the
     * method will return null.
     *
     */
    public function getSMTPConnection(string $name) {
        $jsonObj = $this->json->get('smtp-connections')->get($name);

        if ($jsonObj !== null) {
            return new SMTPAccount([
                'sender-address' => $this->getProp($jsonObj, 'address', $name),
                'pass' => $this->getProp($jsonObj, 'password', $name),
                'port' => $this->getProp($jsonObj, 'port', $name),
                'sender-name' => $this->getProp($jsonObj, 'sender-name', $name),
                'server-address' => $this->getProp($jsonObj, 'host', $name),
                'user' => $this->getProp($jsonObj, 'username', $name),
                'account-name' => $name
            ]);
        }
    }
    /**
     * Returns an array that contains all added SMTP accounts.
     * 
     * @return array An array that contains all added SMTP accounts.
     */
    public function getSMTPConnections(): array {
        $accountsInfo = $this->json->get('smtp-connections');
        $retVal = [];

        foreach ($accountsInfo->getProperties() as $name => $prop) {
            $jsonObj = $prop->getValue();
            $acc = new SMTPAccount();
            $acc->setAccountName($name);
            $acc->setAddress($this->getProp($jsonObj, 'address', $name));
            $acc->setPassword($this->getProp($jsonObj, 'password', $name));
            $acc->setPort($this->getProp($jsonObj, 'port', $name));
            $acc->setSenderName($this->getProp($jsonObj, 'sender-name', $name));
            $acc->setServerAddress($this->getProp($jsonObj, 'host', $name));
            $acc->setUsername($this->getProp($jsonObj, 'username', $name));
            $retVal[$name] = $acc;
        }

        return $retVal;
    }

    public function getTheme(): string {
        return $this->json->get('theme') ?? '';
    }
    /**
     * Returns the default title at which a web page will use in case no title
     * is specified.
     *
     * @param string $lang A two-letter string that represents language code.
     * The returned value will be specific to selected language.
     *
     * @return string The default title at which a web page will use in case no title
     * is specified.
     */
    public function getTitle(string $lang) : string {
        $titles = $this->json->get('titles');
        $langU = strtoupper(trim($lang));
        
        if (strlen($langU) == 0) {
            return '';
        }
        
        foreach ($titles->getProperties() as $prob) {
            if ($prob->getName() == $langU) {
                return $prob->getValue();
            }
        }

        return '';
    }
    /**
     * Returns an array that holds different page titles for the web application
     * on different languages.
     *
     * @return array The indices of the array are language codes such as 'AR' and
     * the value of the index is the title.
     *
     */
    public function getTitles(): array {
        $titles = $this->json->get('titles');
        $retVal = [];

        foreach ($titles->getProperties() as $prob) {
            $retVal[$prob->getName()] = $prob->getValue();
        }

        return $retVal;
    }
    /**
     * Returns a string that represents the value which is used to separate the
     * title of a web page from the name of the application.
     *
     * @return string
     */
    public function getTitleSeparator(): string {
        return $this->json->get('name-separator');
    }
    public function initialize(bool $reCreate = false) {
        $path = self::JSON_CONFIG_FILE_PATH.self::getConfigFileName().'.json';

        if (!file_exists($path) || $reCreate) {
            $this->writeJson();
        }
        $this->json = Json::fromJsonFile($path);
    }
    public function remove() {
        $f = new File(self::JSON_CONFIG_FILE_PATH);
        $f->remove();
    }
    public function removeAllDBConnections() {
        $this->json->add('database-connections', new Json());
        $this->writeJson();
    }

    public function removeDBConnection(string $connectionName) {
    }

    public function removeSMTPAccount(string $accountName) {
        $this->json->add('smtp-connections', new Json());
        $this->writeJson();
    }
    /**
     * Sets or updates the name of the application for specific display language.
     *
     * @param string $name The name of the application.
     *
     * @param string $langCode The language code at which the name of the application will
     * be updated for.
     */
    public function setAppName(string $name, string $langCode) {
        $code = $this->isValidLangCode($langCode);

        if ($code === false) {
            return;
        }
        $appNamesJson = $this->json->get('app-names');
        $appNamesJson->add($code, $name);
        $this->writeJson();
    }
    /**
     * Update application version information.
     *
     * @param string $vNum Version number such as 1.0.0.
     *
     * @param string $vType Version type such as 'Beta', 'Alpha' or 'RC'.
     *
     * @param string $releaseDate The date at which the version was released on.
     *
     */
    public function setAppVersion(string $vNum, string $vType, string $releaseDate) {
        $this->json->add('version-info', new Json([
            'version' => $vNum,
            'version-type' => $vType,
            'release-date' => $releaseDate
        ]));
        $this->writeJson();
    }

    public function setBaseURL(string $url) {
    }
    /**
     * Sets or update default description of the application that will be used
     * by web pages.
     *
     * @param string $description The default description.
     *
     * @param string $langCode The code of the language at which the description
     * will be updated for.
     */
    public function setDescription(string $description, string $langCode) {
        $code = $this->isValidLangCode($langCode);

        if ($code === false) {
            return;
        }
        $appNamesJson = $this->json->get('app-descriptions');
        $appNamesJson->add($code, $description);
        $this->writeJson();
    }
    /**
     * Sets the home page of the application.
     *
     *
     * @param string $url The URL of the home page of the website. For example,
     * This page is served when the user visits the domain without specifying a path.
     */
    public function setHomePage(string $url) {
        $this->json->add('home-page', $url);
        $this->writeJson();
    }
    /**
     * Update application version information.
     *
     * @param string $vNum Version number such as 1.0.0.
     *
     * @param string $vType Version type such as 'Beta', 'Alpha' or 'RC'.
     *
     * @param string $releaseDate The date at which the version was released on.
     *
     */
    public function setPrimaryLanguage(string $langCode) {
        $code = $this->isValidLangCode($langCode);

        if ($code === false) {
            return;
        }
        $this->json->add('primary-lang', $code);
        $this->writeJson();
    }
    /**
     * Updates the password which is used to protect tasks from unauthorized
     * execution.
     *
     * @param string $newPass The new password. Note that provided value
     * must be hashed using SHA256 algorithm.
     *
     */
    public function setSchedulerPassword(string $newPass) {
        $this->json->add('scheduler-password', $newPass);
        $this->writeJson();
    }
    /**
     * Sets the default theme which will be used to style web pages.
     *
     * @param string $theme The name of the theme that will be used to style
     * website UI. This can also be class name of the theme.
     */
    public function setTheme(string $theme) {
        $this->json->add('theme', $theme);
        $this->writeJson();
    }
    /**
     * Sets or updates default web page title for a specific display language.
     *
     * @param string $title The title that will be set.
     *
     * @param string $langCode The display language at which the title will be
     * set or updated for.
     */
    public function setTitle(string $title, string $langCode) {
        $code = $this->isValidLangCode($langCode);

        if ($code === false) {
            return;
        }
        $trimmedTitle = trim($title);
        
        if (strlen($trimmedTitle) == 0) {
            return;
        }
        $appNamesJson = $this->json->get('titles');
        $appNamesJson->add($code, $trimmedTitle);
        $this->writeJson();
    }
    /**
     * Sets the string which is used to separate application name from page name.
     *
     * @param string $separator A character or a string that is used
     * to separate application name from web page title. Two common
     * values are '-' and '|'.
     */
    public function setTitleSeparator(string $separator) {
        $trimmed = trim($separator);
        
        if (strlen($trimmed) != 0) {
            $this->json->add('name-separator', $separator);
            $this->writeJson();
        }
    }
    public function toJSON() : Json {
        return $this->json;
    }
    private function isValidLangCode($langCode) {
        $code = strtoupper(trim($langCode));

        if (strlen($code) != 2) {
            return false;
        }

        return $code;
    }
    private function writeJson() {
        $file = new File(self::JSON_CONFIG_FILE_PATH.self::getConfigFileName().'.json');
        $file->remove();
        $json = $this->toJSON();
        $json->setIsFormatted(true);
        $file->setRawData($json.'');
        $file->write(false, true);
    }

    
}
