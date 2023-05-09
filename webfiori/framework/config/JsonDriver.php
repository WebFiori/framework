<?php

namespace webfiori\framework\config;

use webfiori\database\ConnectionInfo;
use webfiori\email\SMTPAccount;
use webfiori\file\File;
use webfiori\http\Uri;
use webfiori\json\Json;
use const APP_PATH;

/**
 * Description of JsonDriver
 *
 * @author i.binalshikh
 */
class JsonDriver implements ConfigurationDriver {
    const JSON_CONFIG_FILE_PATH = APP_PATH.'config'.DIRECTORY_SEPARATOR.'app-config.json';
    private $json;
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
        $this->json->get('database-connections')->add($dbConnectionsInfo->getName(), $dbConnectionsInfo);
    }

    public function addOrUpdateSMTPAccount(SMTPAccount $emailAccount) {
        $this->json->get('smtp-connections')->add($emailAccount->getAccountName(), $emailAccount);
    }

    public function getAppName(string $langCode) {
        return $this->json->get('app-names')->get($langCode);
    }

    public function getAppReleaseDate() {
        return $this->json->get('version-info')->get('release-date');
    }

    public function getAppVersion() {
        return $this->json->get('version-info')->get('version');
    }

    public function getAppVersionType() {
        return $this->json->get('version-info')->get('version-type');
    }

    public function getBaseURL(): string {
        return $this->json->get('base-url');
    }

    public function getDBConnection(string $conName) {
        return $this->json->get('database-connections')->get($conName);
    }

    public function getDBConnections(): array {
        
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

    public function getHomePage() {
        return $this->json->get('home-page');
    }

    public function getPrimaryLanguage(): string {
        return $this->json->get('primary-lang');
    }

    public function getSMTPAccount(string $name) {
        return $this->json->get('smtp-connections')->get($name);
    }

    public function getSMTPAccounts(): array {
        
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
    public function initialize() {
        $path = self::JSON_CONFIG_FILE_PATH;
        if (!file_exists($path)) {
            $this->writeJson();
        }
        $this->json = Json::fromJsonFile(self::JSON_CONFIG_FILE_PATH);
    }
    private function writeJson() {
        $file = new File(self::JSON_CONFIG_FILE_PATH);
        $file->setRawData($this->toJSON().'');
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
        $appNamesJson = $this->json->get('app-names');
        $appNamesJson->add($langCode, $name);
        $this->writeJson();
    }

    public function setHomePage(string $url) {
        $this->json->add('home-page', $url);
        $this->writeJson();
    }

    public function setPrimaryLanguage(string $langCode) {
        $this->json->add('primary-language', $langCode);
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

    public function setTitle(string $title, string $langCode): string {
        $appNamesJson = $this->json->get('titles');
        $appNamesJson->add($langCode, $title);
        $this->writeJson();
    }

}
