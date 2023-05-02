<?php


namespace webfiori\framework\config;

/**
 * Default configuration driver.
 * 
 * The default configuration driver is class-based. This driver will
 * create a class called 'AppConfig' on the directory APP_DIR/config and
 * use it to read and write configurations.
 *
 * @author Ibrahim
 */
class DefaultDriver implements ConfigurationDriver {
    const NL = "\n";
    private $blockEnd;
    private $configVars;
    private $docEmptyLine;
    private $docEnd;
    private $docStart;
    public function __construct() {
        $this->docEnd = " */";
        $this->blockEnd = "}";
        $this->docStart = "/**";
        $this->docEmptyLine = " * ";
        $this->configVars = [
            'config-file-version' => '1.0',
            'smtp-connections' => [],
            'database-connections' => [],
            'scheduler-password' => 'NO_PASSWORD',
            'version-info' => [
                'version' => '1.0',
                'version-type' => 'Stable',
                'release-date' => '2021-01-10'
            ],
            'site' => [
                'base-url' => '',
                'primary-lang' => 'EN',
                'title-sep' => '|',
                'home-page' => null,
                'admin-theme' => '',
                'base-theme' => '',
                'descriptions' => [
                    'EN' => '',
                    'AR' => ''
                ],
                'website-names' => [
                    'EN' => 'WebFiori',
                    'AR' => 'ويب فيوري'
                ],
                'titles' => [
                    'EN' => 'Hello World',
                    'AR' => 'اهلا و سهلا'
                ],
            ]
        ];
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

    public function getSMTPAccount(string $name) {
        
    }

    public function getSMTPAccounts(): array {
        
    }

    public function getSchedulerPassword(): string {
        
    }

    public function getTheme(): string {
        
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
