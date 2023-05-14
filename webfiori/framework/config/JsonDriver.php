<?php

namespace webfiori\framework\config;

use webfiori\database\ConnectionInfo;
use webfiori\email\SMTPAccount;
use webfiori\file\File;
use webfiori\http\Uri;
use webfiori\json\Json;
use const APP_PATH;

/**
 * Application configuration driver which is used to read and write application
 * configuration from JSON file.
 * 
 * The driver will create a JSON file in the path 'APP_PATH/config' with
 * the name 'app-config.json'. The developer can use the file to
 * modify application configuration.
 *
 * @author Ibrahim
 */
class JsonDriver implements ConfigurationDriver {
    const JSON_CONFIG_FILE_PATH = APP_PATH.'config'.DIRECTORY_SEPARATOR.'app-config.json';
    private $json;
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        $this->json = new Json([
            'base-url' => Uri::getBaseURL(),
            'theme' => null,
            'home-page' => null,
            'primary-lang' => 'EN',
            'titles' => new Json([
                'AR' => 'افتراضي',
                'EN' => 'Default'
            ]),
            'name-separator' => '|',
            'scheduler-password' => 'NO_PASSWORD',
            'app-names' => new Json([
                'AR' => 'تطبيق',
                'EN' => 'Application'
            ]),
            'app-descriptions' => new Json([
                'AR' => '',
                'EN' => ''
            ]),
            'version-info' => new Json([
                'version' => '1.0',
                'version-type' => 'Stable',
                'release-date' => date('Y-m-d')
            ]),
            'env-vars' => new Json([
                "CLI_HTTP_HOST" => new Json([
                    "value" => "example.com",
                    "description" => ""
                ])
            ]), 
            'smtp-connections' => new Json(),
            'database-connections' => new Json(),
        ]);
        $this->json->setIsFormatted(true);
    }
    public function addEnvVar(string $name, $value, string $description = null) {
        $this->json->get('env-vars')->add($name, new Json([
            'value' => $value,
            'description' => $description
        ]));
    }

    public function addOrUpdateDBConnection(ConnectionInfo $dbConnectionsInfo) {
        $json = new Json([
            'type' => $dbConnectionsInfo->getDatabaseType(),
            'host' => $dbConnectionsInfo->getHost(),
            'port' => $dbConnectionsInfo->getPort(),
            'username' => $dbConnectionsInfo->getUsername(),
            'database' => $dbConnectionsInfo->getDBName(),
            'password' => $dbConnectionsInfo->getPassword(),
            'extars' => $dbConnectionsInfo->getExtars(),
        ]);
        $this->json->get('database-connections')->add($dbConnectionsInfo->getName(), $json);
        $this->writeJson();
    }

    public function addOrUpdateSMTPAccount(SMTPAccount $emailAccount) {
        $json = new Json([
            'host' => $emailAccount->getServerAddress(),
            'port' => $emailAccount->getPort(),
            'username' => $emailAccount->getSenderName(),
            'password' => $emailAccount->getPassword(),
            'address' => $emailAccount->getAddress(),
            'sender-name' => $emailAccount->getSenderName(),
            
        ]);
        $this->json->get('smtp-connections')->add($emailAccount->getAccountName(), $json);
        $this->writeJson();
    }

    public function getAppName(string $langCode) {
        return $this->json->get('app-names')->get($langCode);
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

    public function getBaseURL(): string {
        return $this->json->get('base-url');
    }

    public function getDBConnection(string $conName) {
        $jsonObj = $this->json->get('database-connections')->get($conName);
        
        if ($jsonObj !== null) {
            return new ConnectionInfo(
                    $jsonObj->get('type'), 
                    $jsonObj->get('username'), 
                    $jsonObj->get('password'), 
                    $jsonObj->get('database'), 
                    $jsonObj->get('host'), 
                    $jsonObj->get('port'), 
                    $jsonObj->get('extras'));
        }
    }

    public function getDBConnections(): array {
                
        $accountsInfo = $this->json->get('database-connections');
        $retVal = [];
        foreach ($accountsInfo->getProperties() as $name => $jsonObj) {
            
            $acc = new ConnectionInfo();
            $acc->setDBName($jsonObj->get('database'));
            $acc->setDatabaseType($jsonObj->get('type'));
            $acc->setExtras($jsonObj->get('extras'));
            $acc->setHost($jsonObj->get('host'));
            $acc->setName($name);
            $acc->setPassword($jsonObj->get('password'));
            $acc->setPort($jsonObj->get('port'));
            $acc->setUsername($jsonObj->get('username'));
            $retVal[] = $acc;
        }
        return $retVal;
    }

    public function getDescription(string $langCode) {
        return $this->json->get('app-descriptions')->get($langCode);
    }

    public function getEnvVars(): array {
        $retVal = [];
        $vars = $this->json->get('env-vars');
        foreach ($vars->getPropsNames() as $name) {
            $retVal[$name] = [
                'value' => $this->json->get('env-vars')->get($name)->get('value'),
                'description' => $this->json->get('env-vars')->get($name)->get('value')
            ];
        }
        return $retVal;
    }

    public function getHomePage() : string {
        return $this->json->get('home-page');
    }

    public function getPrimaryLanguage(): string {
        return $this->json->get('primary-lang');
    }

    public function getSMTPConnection(string $name) {
        $jsonObj = $this->json->get('smtp-connections')->get($name);
        
        if ($jsonObj !== null) {
            return new SMTPAccount([
                'sender-address' => $jsonObj->get('address'),
                'password' => $jsonObj->get('password'),
                'port' => $jsonObj->get('port'),
                'sender-name' => $jsonObj->get('sender-name'),
                'server-address' => $jsonObj->get('host'),
                'username' => $jsonObj->get('username'),
            ]);
        }
    }

    public function getSMTPConnections(): array {
        $accountsInfo = $this->json->get('smtp-connections');
        $retVal = [];
        foreach ($accountsInfo->getProperties() as $name => $jsonObj) {
            
            $acc = new SMTPAccount();
            $acc->setAccountName($name);
            $acc->setAddress($jsonObj->get('address'));
            $acc->setPassword($jsonObj->get('password'));
            $acc->setPort($jsonObj->get('port'));
            $acc->setSenderName($jsonObj->get('sender-name'));
            $acc->setServerAddress($jsonObj->get('host'));
            $acc->setUsername($jsonObj->get('username'));
            $retVal[] = $acc;
        }
        return $retVal;
    }

    public function getSchedulerPassword(): string {
        return $this->json->get('scheduler-password');
    }

    public function getTheme(): string {
        return $this->json->get('theme');
    }

    public function getTitleSeparator(): string {
        return $this->json->get('name-separator');
    }
    public function getTitle(string $lang) : string {
        $titles = $this->json->get('titles');
        foreach ($titles->getProperties() as $prob) {
            if ($prob->getName() == $lang) {
                return $prob->getValue();
            }
        }
        return '';
    }
    public function initialize(bool $reCreate = false) {
        $path = self::JSON_CONFIG_FILE_PATH;
        if (!file_exists($path) || $reCreate) {
            $this->writeJson();
        }
        $this->json = Json::fromJsonFile(self::JSON_CONFIG_FILE_PATH);
    }
    public function remove() {
        $f = new File(self::JSON_CONFIG_FILE_PATH);
        $f->remove();
    }
    private function writeJson() {
        $file = new File(self::JSON_CONFIG_FILE_PATH);
        $file->remove();
        $json = $this->toJSON();
        $json->setIsFormatted(true);
        $file->setRawData($json.'');
        $file->write(false, true);
    }
    public function toJSON() : Json {
        return $this->json;
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

    public function setAppName(string $name, string $langCode) {
        $appNamesJson = $this->json->get('app-names');
        $appNamesJson->add($langCode, $name);
        $this->writeJson();
    }


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

    public function setDescription(string $description, string $langCode) {
        $appNamesJson = $this->json->get('app-descriptions');
        $appNamesJson->add($langCode, $description);
        $this->writeJson();
    }

    public function setHomePage(string $url) {
        $this->json->add('home-page', $url);
        $this->writeJson();
    }

    public function setPrimaryLanguage(string $langCode) {
        $this->json->add('primary-lang', $langCode);
        $this->writeJson();
    }

    public function setSchedulerPassword(string $newPass) {
        $this->json->add('scheduler-password', $newPass);
        $this->writeJson();
    }

    public function setTheme(string $theme) {
        $this->json->add('theme', $theme);
        $this->writeJson();
    }

    public function setTitleSeparator(string $separator) {
        $this->json->add('name-separator', $separator);
        $this->writeJson();
    }

    public function getAppNames(): array {
        $appNamesJson = $this->json->get('app-names');
        $retVal = [];
        foreach ($appNamesJson->getProperties() as $prob) {
            $retVal[$prob->getName()] = $prob->getValue();
        }
        return $retVal;
    }

    public function getDescriptions(): array {
        $descriptions = $this->json->get('app-descriptions');
        $retVal = [];
        foreach ($descriptions->getProperties() as $prob) {
            $retVal[$prob->getName()] = $prob->getValue();
        }
        return $retVal;
    }

    public function getTitles(): array {
        $titles = $this->json->get('titles');
        $retVal = [];
        foreach ($titles->getProperties() as $prob) {
            $retVal[$prob->getName()] = $prob->getValue();
        }
        return $retVal;
    }

    public function setTitle(string $title, string $langCode) {
        $appNamesJson = $this->json->get('titles');
        $appNamesJson->add($langCode, $title);
        $this->writeJson();
    }

}
