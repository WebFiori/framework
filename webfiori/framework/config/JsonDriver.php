<?php

namespace webfiori\framework\config;

/**
 * Description of JsonDriver
 *
 * @author i.binalshikh
 */
class JsonDriver implements ConfigurationDriver {
    private $filePath;
    public function __construct() {
        $this->filePath = APP_DIR.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'app-config.json';
        $this->initialize();
    }
    public function addEnvVar(string $name, $value, string $description = null) {
        
    }

    public function addOrUpdateDBConnection(ConnectionInfo $dbConnectionsInfo) {
        
    }

    public function addOrUpdateSMTPAccount(SMTPAccount $emailAccount) {
        
    }

    public function getAppName(string $langCode) {
        
    }

    public function getAppReleaseDate() {
        
    }

    public function getAppVersion() {
        
    }

    public function getAppVersionType() {
        
    }

    public function getBaseURL(): string {
        
    }

    public function getDBConnection(string $conName) {
        
    }

    public function getDBConnections(): array {
        
    }

    public function getDescription(string $langCode) {
        
    }

    public function getEnvVars(): array {
        
    }

    public function getHomePage() {
        
    }

    public function getPrimaryLanguage(): string {
        
    }

    public function getSMTPAccount(string $name) {
        
    }

    public function getSMTPAccounts(): array {
        
    }

    public function getSchedulerPassword(): string {
        
    }

    public function getTheme(): string {
        
    }

    public function getTitleSeparator(): string {
        
    }

    public function initialize() {
        
    }

    public function removeAllDBConnections() {
        
    }

    public function removeDBConnection(string $connectionName) {
        
    }

    public function removeSMTPAccount(string $accountName) {
        
    }

    public function setAppName(string $name, string $langCode) {
        
    }

    public function setAppVersion(string $vNum, string $vType, string $releaseDate) {
        
    }

    public function setBaseURL(string $url) {
        
    }

    public function setDescription(string $description, string $langCode) {
        
    }

    public function setHomePage(string $url) {
        
    }

    public function setPrimaryLanguage(string $langCode) {
        
    }

    public function setSchedulerPassword(string $newPass) {
        
    }

    public function setTheme(string $theme) {
        
    }

    public function setTitleSeparator(string $separator) {
        
    }

}
