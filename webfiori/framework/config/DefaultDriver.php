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
        $this->configVars['database-connections'][$dbConnectionsInfo->getName()] = $dbConnectionsInfo;
        $this->writeAppConfig();
    }

    public function getPrimaryLanguage() : string {
        return $this->configVars['site']['primary-lang'];
    }
    public function addOrUpdateSMTPAccount(SMTPAccount $emailAccount) {
        $this->configVars[$emailAccount->getAccountName()] = $emailAccount;
        $this->writeAppConfig();
    }

    public function getAppName(string $langCode) {
        if (isset($this->configVars['site']['website-names'][$langCode])) {
            return $this->configVars['site']['website-names'][$langCode];
        }
    }

    public function getAppReleaseDate() {
        return $this->configVars['version-info']['release-date'];
    }

    public function getAppVersion() {
        return $this->configVars['version-info']['version'];
    }

    public function getAppVersionType() {
        return $this->configVars['version-info']['version-type'];
    }

    public function getBaseURL(): string {
        return $this->configVars['site']['base-url'];
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
        return $this->configVars['site']['home-page'];
    }

    public function getSMTPAccount(string $name) {
        
    }

    public function getSMTPConnections(): array {
        
    }

    public function getSchedulerPassword(): string {
        return $this->configVars['scheduler-password'];
    }

    public function getTheme(): string {
        return $this->configVars['site']['base-theme'];
    }

    public function initialize() {
        if (!class_exists(APP_DIR.'\\config\\AppConfig')) {
            $this->writeAppConfig();
        }
    }

    public function removeAllDBConnections() {
        $this->configVars['database-connections'] = [];
        $this->writeAppConfig();
    }

    public function removeDBConnection(string $connectionName) {
        if (isset($this->configVars['database-connections'][$connectionName])) {
            unset($this->configVars['database-connections'][$connectionName]);
        }
        $connections = $this->getDatabaseConnections();
        $updated = [];

        foreach ($connections as $name => $conObj) {
            if ($name != $connectionName) {
                $updated[] = $conObj;
            }
        }
        $this->configVars['database-connections'] = $updated;
        $this->writeAppConfig();
    }

    public function removeSMTPAccount(string $accountName) {
        if (isset($this->configVars['smtp-connections'][$accountName])) {
            unset($this->configVars['smtp-connections'][$accountName]);
        }
        $this->writeAppConfig();
    }

    public function setAppName(string $name, string $langCode) {
        $this->configVars['site']['website-names'][$langCode] = $name;
    }

    public function setAppVersion(string $vNum, string $vType, string $releaseDate) {
        $this->configVars['version-info'] = [
            'version' => $vNum,
            'version-type' => $vType,
            'release-date' => $releaseDate
        ];
        $this->writeAppConfig();
    }

    public function setBaseURL(string $url) {
        $this->configVars['site']['base-url'] = $url;
    }

    public function setDescription(string $description, string $langCode) {
        
    }

    public function setHomePage(string $url) {
        
    }

    public function setPrimaryLanguage(string $langCode) {
        
    }

    public function setSchedulerPassword(string $newPass) {
        $this->configVars['scheduler-password'] = hash('sha256', $newPass);
        $this->writeAppConfig();
    }

    public function setTheme(string $theme) {
        
    }
    
    public function getTitleSeparator() : string {
        return $this->configVars['site']['title-sep'];
    }
    public function setTitleSeparator(string $separator) {
        
    }

    public function addEnvVar(string $name, $value, string $description = null) {
        
    }
    /**
     * Stores configuration variables into the application configuration class.
     *
     * @throws FileException
     * @since 1.5
     */
    public function writeAppConfig() {
        $cFile = new File('AppConfig.php', APP_PATH.'config');
        $cFile->remove();

        $this->writeAppConfigAttrs($cFile);

        $this->writeAppConfigConstructor($cFile);

        $this->writeAppConfigAddMethods($cFile);

        $this->writeFuncHeader($cFile, 
            'public function getAccount(string $name)', 
            'Returns SMTP account given its name.', 
            [
                'The method will search for an account with the given name in the set',
                'of added accounts. If no account was found, null is returned.'
            ], 
            [
                '$name' => [
                    'type' => 'string',
                    'description' => 'The name of the account.'
                ]
            ], 
            [
                'type' => 'SMTPAccount|null',
                'description' => [
                    'If the account is found, The method',
                    'will return an object of type SMTPAccount. Else, the',
                    'method will return null.'
                ]
            ]);
        $this->a($cFile, "        if (isset(\$this->emailAccounts[\$name])) {");
        $this->a($cFile, "            return \$this->emailAccounts[\$name];");
        $this->a($cFile, "        }");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getAccounts() : array', 
            'Returns an associative array that contains all email accounts.', 
            [
                'The indices of the array will act as the names of the accounts.',
                'The value of the index will be an object of type SMTPAccount.'
            ], 
            [], 
            [
                'type' => 'array',
                'description' => 'An associative array that contains all email accounts.'
            ]);
        $this->a($cFile, "        return \$this->emailAccounts;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getAdminThemeName() : string', 
            'Returns the name of the theme that is used in admin control pages.', 
            '', 
            [], 
            [
                'type' => 'string',
                'description' => 'The name of the theme that is used in admin control pages.'
            ]);
        $this->a($cFile, "        return \$this->adminThemeName;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getBaseThemeName() : string', 
            'Returns the name of base theme that is used in website pages.', 
            'Usually, this theme is used for the normally visitors of the website.', 
            [], 
            [
                'type' => 'string',
                'description' => 'The name of base theme that is used in website pages.'
            ]);
        $this->a($cFile, "        return \$this->baseThemeName;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getBaseURL() : string', 
            'Returns the base URL that is used to fetch resources.', 
            [
                "The return value of this method is usually used by the tag 'base'",
                'of website pages.'
            ], 
            [], 
            [
                'type' => 'string',
                'description' => 'The base URL.'
            ]);
        $this->a($cFile, "        return \$this->baseUrl;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getConfigVersion() : string', 
            'Returns version number of the configuration file.', 
            'This value can be used to check for the compatability of configuration file', 
            [], 
            [
                'type' => 'string',
                'description' => 'The version number of the configuration file.'
            ]);
        $this->a($cFile, "        return \$this->configVision;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getSchedulerPassword() : string', 
            'Returns sha256 hash of the password which is used to prevent unauthorized access to run the tasks or access scheduler web interface.', 
            '', 
            [], 
            [
                'type' => 'string',
                'description' => "Password hash or the string 'NO_PASSWORD' if there is no password."
            ]);
        $this->a($cFile, "        return \$this->schedulerPass;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getDBConnection(string $conName)', 
            'Returns database connection information given connection name.', 
            '', 
            [
                '$conName' => [
                    'type' => 'string',
                    'description' => 'The name of the connection.'
                ]
            ], 
            [
                'type' => 'ConnectionInfo|null',
                'description' => [
                    'The method will return an object of type',
                    'ConnectionInfo if a connection info was found for the given connection name.',
                    'Other than that, the method will return null.'
                ]
            ]);
        $this->a($cFile, "        \$conns = \$this->getDBConnections();");
        $this->a($cFile, "        \$trimmed = trim(\$conName);");
        $this->a($cFile, "        ");
        $this->a($cFile, "        if (isset(\$conns[\$trimmed])) {");
        $this->a($cFile, "            return \$conns[\$trimmed];");
        $this->a($cFile, "        }");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getDBConnections() : array', 
            'Returns an associative array that contain the information of database connections.', 
            [
                'The keys of the array will be the name of database connection and the',
                'value of each key will be an object of type ConnectionInfo.'
            ], 
            [], 
            [
                'type' => 'array',
                'description' => 'An associative array.'
            ]);
        $this->a($cFile, "        return \$this->dbConnections;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getDefaultTitle(string $langCode)', 
            'Returns the global title of the website that will be used as default page title.', 
            '', 
            [
                '$langCode' => [
                    'type' => 'string',
                    'description' => "Language code such as 'AR' or 'EN'."
                ]
            ], 
            [
                'type' => 'string|null',
                'description' => [
                    'If the title of the page',
                    'does exist in the given language, the method will return it.',
                    'If no such title, the method will return null.'
                ]
            ]);
        $this->a($cFile, "        \$langs = \$this->getTitles();");
        $this->a($cFile, "        \$langCodeF = strtoupper(trim(\$langCode));");
        $this->a($cFile, "        ");
        $this->a($cFile, "        if (isset(\$langs[\$langCodeF])) {");
        $this->a($cFile, "            return \$langs[\$langCode];");
        $this->a($cFile, "        }");
        $this->a($cFile, $this->blockEnd, 1);


        $this->writeFuncHeader($cFile, 
            'public function getDescription(string $langCode)', 
            'Returns the global description of the website that will be used as default page description.', 
            '', 
            [
                '$langCode' => [
                    'type' => 'string',
                    'description' => "Language code such as 'AR' or 'EN'."
                ]
            ], 
            [
                'type' => 'string|null',
                'description' => [
                    'If the description for the given language',
                    'does exist, the method will return it. If no such description, the',
                    'method will return null.'
                ]
            ]);
        $this->a($cFile, "        \$langs = \$this->getDescriptions();");
        $this->a($cFile, "        \$langCodeF = strtoupper(trim(\$langCode));");
        $this->a($cFile, "        if (isset(\$langs[\$langCodeF])) {");
        $this->a($cFile, "            return \$langs[\$langCode];");
        $this->a($cFile, "        }");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getDescriptions() : array', 
            'Returns an associative array which contains different website descriptions in different languages.', 
            [
                'Each index will contain a language code and the value will be the description',
                'of the website in the given language.'
            ], 
            [], 
            [
                'type' => 'array',
                'description' => [
                    'An associative array which contains different website descriptions',
                    'in different languages.'
                ]
            ]);
        $this->a($cFile, "        return \$this->descriptions;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getHomePage() : string', 
            'Returns the home page URL of the website.', 
            '', 
            [], 
            [
                'type' => 'string',
                'description' => 'The home page URL of the website.'
            ]);
        $this->a($cFile, "        return \$this->homePage;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getPrimaryLanguage() : string', 
            'Returns the primary language of the website.', 
            '', 
            [], 
            [
                'type' => 'string',
                'description' => "Language code of the primary language such as 'EN'."
            ]);
        $this->a($cFile, "        return \$this->primaryLang;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getReleaseDate() : string', 
            'Returns the date at which the application was released at.', 
            '', 
            [], 
            [
                'type' => 'string',
                'description' => [
                    'The method will return a string in the format',
                    "YYYY-MM-DD' that represents application release date."
                ]
            ]);
        $this->a($cFile, "        return \$this->appReleaseDate;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getTitles() : array', 
            'Returns an array that holds the default page title for different display languages.', 
            '', 
            [], 
            [
                'type' => 'array',
                'description' => [
                    'An associative array. The indices of the array are language codes',
                    'and the values are pages titles.'
                ]
            ]);
        $this->a($cFile, "        return \$this->defaultPageTitles;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getTitleSep() : string', 
            'Returns the character (or string) that is used to separate page title from website name.', 
            '', 
            [], 
            [
                'type' => 'string',
                'description' => [
                    "A string such as ' - ' or ' | '. Note that the method",
                    'will add the two spaces by default.'
                ]
            ]);
        $this->a($cFile, "        return \$this->titleSep;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getVersion() : string', 
            'Returns version number of the application.', 
            '', 
            [], 
            [
                'type' => 'string',
                'description' => [
                    'The method should return a string in the',
                    "form 'x.x.x.x'."
                ]
            ]);
        $this->a($cFile, "        return \$this->appVersion;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getVersionType() : string', 
            'Returns a string that represents application release type.', 
            '', 
            [], 
            [
                'type' => 'string',
                'description' => [
                    'The method will return a string such as',
                    "'Stable', 'Alpha', 'Beta' and so on."
                ]
            ]);
        $this->a($cFile, "        return \$this->appVersionType;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getWebsiteName(string $langCode)', 
            'Returns the global website name.', 
            '', 
            [
                '$langCode' => [
                    'type' => 'string',
                    'description' => "Language code such as 'AR' or 'EN'."
                ]
            ], 
            [
                'type' => 'string|null',
                'description' => [
                    'If the name of the website for the given language',
                    'does exist, the method will return it. If no such name, the',
                    'method will return null.'
                ]
            ]);
        $this->a($cFile, "        \$langs = \$this->getWebsiteNames();");
        $this->a($cFile, "        \$langCodeF = strtoupper(trim(\$langCode));");
        $this->a($cFile, "        ");
        $this->a($cFile, "        if (isset(\$langs[\$langCodeF])) {");
        $this->a($cFile, "            return \$langs[\$langCode];");
        $this->a($cFile, "        }");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getWebsiteNames() : array', 
            'Returns an array which contains different website names in different languages.', 
            [
                'Each index will contain a language code and the value will be the name',
                'of the website in the given language.'
            ], 
            [], 
            [
                'type' => 'array',
                'description' => [
                    'An array which contains different website names in different languages.'
                ]
            ]);
        $this->a($cFile, "        return \$this->webSiteNames;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function removeDBConnections()', 
            'Removes all stored database connections.');
        $this->a($cFile, "        \$this->dbConnections = [];");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeDbCon($cFile);
        $this->writeSiteInfo($cFile);
        $this->writeSmtpConn($cFile);
        $this->writeAppVersionInfo($cFile);

        $this->a($cFile, "}");
        $cFile->create(true);
        $cFile->write();
        require_once APP_PATH.'config'.DS.'AppConfig.php';
    }
    private function writeAppConfigAttrs($cFile) {
        $this->a($cFile, "<?php");
        $this->a($cFile, "");
        $this->a($cFile, "namespace ".APP_DIR."\\config;");
        $this->a($cFile, "");
        $this->a($cFile, "use webfiori\\database\\ConnectionInfo;");
        $this->a($cFile, "use webfiori\\email\\SMTPAccount;");
        $this->a($cFile, "use webfiori\\framework\\Config;");
        $this->a($cFile, "use webfiori\\http\\Uri;");
        $this->a($cFile, "/**");
        $this->a($cFile, " * Configuration class of the application");
        $this->a($cFile, " *");
        $this->a($cFile, " * @author Ibrahim");
        $this->a($cFile, " *");
        $this->a($cFile, " * @version 1.0.1");
        $this->a($cFile, " *");
        $this->a($cFile, " * @since 2.1.0");
        $this->a($cFile, " */");
        $this->a($cFile, "class AppConfig implements Config {");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The name of admin control pages Theme.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$adminThemeName;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The date at which the application was released.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$appReleaseDate;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * A string that represents the type of the release.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$appVersionType;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Version of the web application.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$appVersion;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The name of base website UI Theme.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$baseThemeName;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The base URL that is used by all website pages to fetch resource files.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$baseUrl;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Configuration file version number.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$configVision;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Password hash of scheduler sub-system.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$schedulerPass;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * An associative array that will contain database connections.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var array");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$dbConnections;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * An array that is used to hold default page titles for different languages.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var array");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$defaultPageTitles;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * An array that is used to hold default page descriptions for different languages.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var array");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$descriptions;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * An array that holds SMTP connections information.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$emailAccounts;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The URL of the home page.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$homePage;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The primary language of the website.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$primaryLang;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The character which is used to separate site name from page title.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$titleSep;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * An array which contains all website names in different languages.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$webSiteNames;");
    }
    private function writeAppVersionInfo($cFile) {
        $this->a($cFile, [
            $this->docStart,
            $this->since10,
            $this->docEnd
        ], 1);

        $this->a($cFile, "private function initVersionInfo() {", 1);

        $versionInfo = $this->getAppVersionInfo();

        $this->a($cFile, [
            "\$this->appVersion = '".$versionInfo['version']."';",
            "\$this->appVersionType = '".$versionInfo['version-type']."';",
            "\$this->appReleaseDate = '".$versionInfo['release-date']."';"
        ], 2);

        $this->a($cFile, $this->blockEnd, 1);
    }
    private function writeSchedulerPass($cFile) {
        $password = $this->getSchedulerPassword();
        $this->a($cFile, "        \$this->schedulerPass = '".$password."';");
    }
    private function writeDbCon($cFile) {
        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private function initDbConnections() {");
        $this->a($cFile, "        \$this->dbConnections = [");


        $dbCons = $this->getDatabaseConnections();

        foreach ($dbCons as $connObj) {
            if ($connObj instanceof ConnectionInfo) {
                $cName = $connObj->getName();
                $this->a($cFile, "            '$cName' => new ConnectionInfo('".$connObj->getDatabaseType()."',"
                        ."'".$connObj->getUsername()."', "
                        ."'".$connObj->getPassword()."', "
                        ."'".$connObj->getDBName()."', "
                        ."'".$connObj->getHost()."', ".$connObj->getPort().", [");
                $this->a($cFile, "                'connection-name' => '".str_replace("'", "\'", $cName)."'");
                $this->a($cFile, "            ]),");
            }
        }
        $this->a($cFile, "        ];");
        $this->a($cFile, $this->blockEnd, 1);
    }
    private function writeSiteTitles($cFile) {
        $titlesArr = $this->getTitles();
        $this->a($cFile, "        \$this->defaultPageTitles = [");

        foreach ($titlesArr as $langCode => $title) {
            $title = str_replace("'", "\'", $title);
            $this->a($cFile, "            '$langCode' => '$title',");

            if (!class_exists(APP_DIR.'\\langs\\Language'.$langCode)) {
                //This requires a fix in the future
                $dir = $langCode == 'AR' ? 'rtl' : 'ltr';

                $writer = new LangClassWriter($langCode, $dir);
                $writer->writeClass();
                require_once $writer->getAbsolutePath();
            }
        }
        $this->a($cFile, "        ];");
    }
}
