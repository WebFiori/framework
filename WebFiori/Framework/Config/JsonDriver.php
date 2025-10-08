<?php
namespace WebFiori\Framework\Config;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Mail\SMTPAccount;
use WebFiori\File\File;
use WebFiori\Framework\Exceptions\InitializationException;
use WebFiori\Http\Uri;
use WebFiori\Json\Json;

/**
 * Application configuration driver which is used to read and write application
 * configuration from JSON file.
 *
 * The driver will create a JSON file in the path 'APP_PATH/Config' with
 * the name 'app-config.json'. The developer can use the file to
 * modify application configuration. The name of the file can be changed as needed.
 *
 * @author Ibrahim
 */
class JsonDriver implements ConfigurationDriver {
    
    /**
     * Returns the path to JSON configuration files.
     */
    public static function getConfigPath(): string {
        return APP_PATH.'Config'.DIRECTORY_SEPARATOR;
    }
    /**
     * The name of JSON configuration file.
     */
    private static $configFileName = 'app-config';
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
                    "value" => "127.0.0.1",
                    "description" => "Host name that will be used when runing the application as command line utility."
                ], 'none', 'same')
            ], 'none', 'same'),
            'smtp-connections' => new Json([], 'none', 'same'),
            'database-connections' => new Json([], 'none', 'same'),
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
    public function addEnvVar(string $name, mixed $value = null, ?string $description = null) {
        $this->json->get('env-vars')->add($name, new Json([
            'value' => $value,
            'description' => $description
        ], 'none', 'same'));
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
        ], 'none', 'same');
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

        ], 'none', 'same');
        $this->json->get('smtp-connections')->add($emailAccount->getAccountName(), $connectionAsJson);
        $this->writeJson();
    }
    /**
     * Returns the name of the application in specific display language.
     *
     * @param string $langCode Language code such as 'AR'.
     *
     * @return string|null Application name or null if language code does not
     * exist.
     */
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
    /**
     * Returns the name of the file at which the application is using to
     * read configuration.
     *
     * @return string
     */
    public static function getConfigFileName() : string {
        return self::$configFileName;
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
            } else if (gettype($extras) == 'array') {
                $extrasArr = $extras;
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

            if ($extrasObj !== null) {
                $extrasArr = [];
                if ($extrasObj instanceof Json) {
                    

                    foreach ($extrasObj->getProperties() as $prop) {
                        $extrasArr[$prop->getName()] = $prop->getValue();
                    }
                } else if (gettype($extrasObj) == 'array') {
                    $extrasArr = $extrasObj;
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
    /**
     * Returns an array that holds the information of defined application environment
     * variables.
     * 
     * @return array The returned array will be associative. The key will represent
     * the name of the variable and its value is a sub-associative array with
     * two indices, 'description' and 'value'. The description index is a text that describes
     * the variable and the value index will hold its value.
     */
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
        $pass = $this->json->get('scheduler-password') ?? 'NO_PASSWORD';

        if (strlen($pass.'') == 0 || $pass == 'NO_PASSWORD') {
            return 'NO_PASSWORD';
        }

        return $pass;
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

        foreach ($accountsInfo->getProperties() as $prop) {
            $name = $prop->getName();
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
    /**
     * Returns the name or the namespace of default theme that the application
     * will use in case a page does not have specific theme.
     *
     * @return string
     */
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
    /**
     * Creates application configuration file.
     *
     * This method will attempt to create a JSON configuration file in the folder
     * 'config' of the application.
     *
     * @param bool $reCreate If this parameter is set to true and there already a configuration
     * file with same name, it will be overriden.
     */
    public function initialize(bool $reCreate = false) {
        $path = self::getConfigPath().self::getConfigFileName().'.json';

        if (!file_exists($path) || $reCreate) {
            $this->writeJson();
        }
        $this->json = Json::fromJsonFile($path);
    }
    /**
     * Deletes configuration file.
     *
     * Note that in order to remove specific configuration file, its name must
     * be set using the method JsonDriver::setConfigFileName()
     */
    public function remove() {
        $f = new File(self::getConfigPath().self::getConfigFileName().'.json');
        $f->remove();
    }
    public function removeAllDBConnections() {
        $this->json->add('database-connections', new Json([], 'none', 'same'));
        $this->writeJson();
    }
    /**
     * Removes all added SMTP connections.
     */
    public function removeAllSMTPAccounts() {
        $this->json->add('smtp-connections', new Json([], 'none', 'same'));
        $this->writeJson();
    }

    public function removeDBConnection(string $connectionName) {
        $connections = $this->getDBConnections();
        $accountNameTrimmed = trim($connectionName);
        $toAdd = [];

        foreach ($connections as $connection) {
            if ($connection->getName() != $accountNameTrimmed) {
                $toAdd[] = $connection;
            }
        }
        $this->removeAllDBConnections();

        foreach ($toAdd as $account) {
            $this->addOrUpdateDBConnection($account);
        }
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
    /**
     * Removes specific SMTP connection from the configuration given its name.
     *
     * @param string $accountName The name of the connection.
     */
    public function removeSMTPAccount(string $accountName) {
        $connections = $this->getSMTPConnections();
        $accountNameTrimmed = trim($accountName);
        $toAdd = [];

        foreach ($connections as $connection) {
            if ($connection->getAccountName() != $accountNameTrimmed) {
                $toAdd[] = $connection;
            }
        }
        $this->removeAllSMTPAccounts();

        foreach ($toAdd as $account) {
            $this->addOrUpdateSMTPAccount($account);
        }
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
        ], 'none', 'same'));
        $this->writeJson();
    }
    /**
     * Sets the base URL of the application.
     *
     * This is usually used in fetching resources.
     *
     * @param string $url
     */
    public function setBaseURL(string $url) {
        $trim = trim($url);

        if (strlen($trim) == 0) {
            $this->json->add('base-url', 'DYNAMIC');
        } else {
            $this->json->add('base-url', $trim);
        }
    }
    /**
     * Sets the name of the file that configuration values will be taken from.
     *
     * The file must exist on the directory [APP_PATH]/Config/ .
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
        $trim = trim($url);

        if (strlen($trim) == 0) {
            $this->json->add('home-page', 'BASE_URL');
        } else {
            $this->json->add('home-page', $trim);
        }
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
        $this->json->add('theme', trim($theme));
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
    private function getProp(Json $j, $name, string $connName) {
        $val = $j->get($name);

        if ($val === null) {
            throw new InitializationException('The property "'.$name.'" of the connection "'.$connName.'" is missing.');
        }

        return $val;
    }
    private function isValidLangCode($langCode) {
        $code = strtoupper(trim($langCode));

        if (strlen($code) != 2) {
            return false;
        }

        return $code;
    }
    private function writeJson() {
        $file = new File(self::getConfigPath().self::getConfigFileName().'.json');
        $file->remove();
        $json = $this->toJSON();
        $json->setIsFormatted(true);
        $file->setRawData($json.'');
        $file->write(false, true);
    }
}
