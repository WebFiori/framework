<?php


namespace webfiori\framework\config;

use webfiori\database\ConnectionInfo;
use webfiori\email\SMTPAccount;
use webfiori\file\exceptions\FileException;
use webfiori\file\File;
use webfiori\framework\writers\LangClassWriter;
use webfiori\http\Uri;

/**
 * A configuration driver which is used to store configuration on PHP class.
 * 
 * This driver will
 * create a class called 'AppConfig' on the directory APP_DIR/config and
 * use it to read and write configurations.
 *
 * @author Ibrahim
 */
class ClassDriver implements ConfigurationDriver {
    const NL = "\n";
    const CONFIG_FILE_PATH = APP_PATH.'config'.DIRECTORY_SEPARATOR.'AppConfig.php';
    const CONFIG_NS = APP_DIR.'\\config\\AppConfig';
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
        $this->initDefaultConfig();
    }
    private function initDefaultConfig() {
        $this->configVars = [
            'smtp-connections' => [],
            'database-connections' => [],
            'scheduler-password' => 'NO_PASSWORD',
            'version-info' => [
                'version' => '1.0',
                'version-type' => 'Stable',
                'release-date' => '2021-01-10'
            ],
            'env-vars' => [
                
            ],
            'site' => [
                'base-url' => Uri::getBaseURL(),
                'primary-lang' => 'EN',
                'title-sep' => '|',
                'home-page' => '',
                'base-theme' => '',
                'descriptions' => [
                    'AR' => '',
                    'EN' => '',
                ],
                'website-names' => [
                    'AR' => 'تطبيق',
                    'EN' => 'Application',
                ],
                'titles' => [
                    'AR' => 'افتراضي',
                    'EN' => 'Default',
                ],
            ]
        ];
    }
    private function writeFuncHeader($cFile, $methSig, $methodSummary = '', $description = [], $params = [], $returns = null) {
        $phpDocArr = [
            $this->docStart,
            ' * '.$methodSummary,
            $this->docEmptyLine,
        ];

        if (gettype($description) == 'array') {
            foreach ($description as $line) {
                $phpDocArr[] = ' * '.$line;
            }
            $phpDocArr[] = $this->docEmptyLine;
        } else if (strlen($description) != 0) {
            $phpDocArr[] = ' * '.$description;
            $phpDocArr[] = $this->docEmptyLine;
        }


        foreach ($params as $paramName => $paramArr) {
            $currentDescLine = ' * @param '.$paramArr['type'].' '.$paramName.' ';

            if (gettype($paramArr['description']) == 'array') {
                $currentDescLine .= $paramArr['description'][0];
                $phpDocArr[] = $currentDescLine;

                for ($x = 1 ; $x < count($paramArr['description']) ; $x++) {
                    $phpDocArr[] = ' * '.$paramArr['description'][$x];
                }
            } else {
                $phpDocArr[] = $currentDescLine.$paramArr['description'];
            }
            $phpDocArr[] = $this->docEmptyLine;
        }

        if ($returns !== null && gettype($returns) == 'array') {
            $phpDocArr[] = ' * @return '.$returns['type'].' ';

            if (gettype($returns['description']) == 'array') {
                $phpDocArr[count($phpDocArr) - 1] .= $returns['description'][0];

                for ($x = 1 ; $x < count($returns['description']) ; $x++) {
                    $phpDocArr[] = ' * '.$returns['description'][$x];
                }
            } else {
                $phpDocArr[count($phpDocArr) - 1] .= $returns['description'];
            }
        }
        $phpDocArr[] = $this->docEnd;
        $phpDocArr[] = $methSig.' {';
        $this->a($cFile, $phpDocArr, 1);
    }
    private function a($file, $str, $tabSize = 0) {
        $isResource = is_resource($file);
        $tabStr = $tabSize > 0 ? '    ' : '';

        if (gettype($str) == 'array') {
            foreach ($str as $subStr) {
                if ($isResource) {
                    fwrite($file, str_repeat($tabStr, $tabSize).$subStr.self::NL);
                } else {
                    $file->append(str_repeat($tabStr, $tabSize).$subStr.self::NL);
                }
            }
        } else {
            if ($isResource) {
                fwrite($file, str_repeat($tabStr, $tabSize).$str.self::NL);
            } else {
                $file->append(str_repeat($tabStr, $tabSize).$str.self::NL);
            }
        }
    }
    public function addOrUpdateDBConnection(ConnectionInfo $dbConnectionsInfo) {
        $this->configVars['database-connections'][$dbConnectionsInfo->getName()] = $dbConnectionsInfo;
        $this->writeAppConfig();
    }
    /**
     * Returns a two-letters string that represents primary language of the application.
     * 
     * @return string A two-letters string that represents primary language of the application.
     */
    public function getPrimaryLanguage() : string {
        return $this->configVars['site']['primary-lang'];
    }
    /**
     * Adds new SMTP account or Updates an existing one.
     * 
     * @param SMTPAccount $emailAccount An instance of 'SMTPAccount'.
     */
    public function addOrUpdateSMTPAccount(SMTPAccount $emailAccount) {
        $this->configVars['smtp-connections'][$emailAccount->getAccountName()] = $emailAccount;
        $this->writeAppConfig();
    }
    /**
     * Returns application name.
     * 
     * @param string $langCode Language code such as 'AR' or 'EN'.
     * 
     * @return string|null If the name of the application
     * does exist in the given language, the method should return it.
     * If no such name, the method should return null.
     */
    public function getAppName(string $langCode) {
        if (isset($this->configVars['site']['website-names'][$langCode])) {
            return $this->configVars['site']['website-names'][$langCode];
        }
    }
    /**
     * Returns a string that represents the date at which the version of
     * the application was released at.
     * 
     * @return string A string in the format 'YYYY-MM-DD'.
     */
    public function getAppReleaseDate() : string {
        return $this->configVars['version-info']['release-date'];
    }
    /**
     * Returns version number of the application.
     * 
     * @return string The method should return a string in the format 'x.x.x' if
     * semantic versioning is used.
     */
    public function getAppVersion() : string {
        return $this->configVars['version-info']['version'];
    }
    /**
     * Returns a string that represents the type of application version.
     * 
     * @return string A string such as 'alpha', 'beta' or 'rc'.
     */
    public function getAppVersionType() : string {
        return $this->configVars['version-info']['version-type'];
    }
    /**
     * Returns the base URL that is used to fetch resources.
     * 
     * The return value of this method is usually used by the tag 'base'
     * of website pages.
     * 
     * @return string the base URL.
     */
    public function getBaseURL(): string {
        return $this->configVars['site']['base-url'];
    }
    /**
     * Returns database connection information given connection name.
     * 
     * @param string $conName The name of the connection.
     * 
     * @return ConnectionInfo|null The method will return an object of type
     * ConnectionInfo if a connection info was found for the given connection name.
     * Other than that, the method will return null.
     * 
     */
    public function getDBConnection(string $conName) {
        foreach ($this->getDBConnections() as $connNameStored => $connObj) {
            
            if ($connNameStored == $conName || $connObj->getName() == $conName) {
                return $connObj;
            }
        }
    }
    /**
     * Returns an associative array that contain the information of database connections.
     * 
     * @return array An associative array of objects of type ConnectionInfo.
     */
    public function getDBConnections(): array {
        return $this->configVars['database-connections'];
    }

    public function getDescription(string $langCode) {
        if (isset($this->getDescriptions()[$langCode])) {
            return $this->getDescriptions()[$langCode];
        }
    }

    public function getEnvVars(): array {
        return $this->configVars['env-vars'];
    }

    public function getHomePage() : string {
        return $this->configVars['site']['home-page'];
    }

    public function getSMTPAccount(string $name) {
        foreach ($this->getSMTPConnections() as $connName => $connObj) {
            
            if ($connName == $name || $connObj->getAccountName() == $name) {
                return $connObj;
            }
        }
    }

    public function getSMTPConnections(): array {
        return $this->configVars['smtp-connections'];
    }

    public function getSchedulerPassword(): string {
        return $this->configVars['scheduler-password'];
    }

    public function getTheme(): string {
        return $this->configVars['site']['base-theme'];
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
        $this->configVars['site']['descriptions'][$langCode] = $description;
        $this->writeAppConfig();
    }

    public function setHomePage(string $url) {
        $this->configVars['site']['home-page'] = $url;
        $this->writeAppConfig();
    }

    public function setPrimaryLanguage(string $langCode) {
        $this->configVars['site']['primary-lang'] = $langCode;
        $this->writeAppConfig();
    }

    public function setSchedulerPassword(string $newPass) {
        $this->configVars['scheduler-password'] = $newPass;
        $this->writeAppConfig();
    }

    public function setTheme(string $theme) {
        $this->configVars['site']['base-theme'] = $theme;
        $this->writeAppConfig();
    }
    
    public function getTitleSeparator() : string {
        return $this->configVars['site']['title-sep'];
    }
    public function setTitleSeparator(string $separator) {
        $this->configVars['site']['title-sep'] = $separator;
        $this->writeAppConfig();
    }

    public function addEnvVar(string $name, $value, string $description = null) {
        $this->configVars['env-vars'][$name] = [
            'value' => $value,
            'description' => $description
        ];
        $this->writeAppConfig();
    }
    /**
     * Stores configuration variables into the application configuration class.
     *
     * @throws FileException
     */
    public function writeAppConfig() {
        $cFile = new File(self::CONFIG_FILE_PATH);
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
            'public function getTitle(string $langCode)', 
            'Returns default title to use for specific display language.', 
            [
                
            ], 
            [
                '$langCode' => [
                    'type' => 'string',
                    'description' => 'The code of display language.'
                ]
            ], 
            [
                'type' => 'string',
                'description' => [
                    'If the provided language is found, The method',
                    'will return the title as string. Other than that,',
                    'method will return empty string.'
                ]
            ]);
        $this->a($cFile, "        if (isset(\$this->defaultPageTitles[\$langCode])) {");
        $this->a($cFile, "            return \$this->defaultPageTitles[\$langCode];");
        $this->a($cFile, "        }");
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
        $cFile->write(false, true);
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
        $this->a($cFile, "class AppConfig {");


        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The date at which the application was released.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$appReleaseDate;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * A string that represents the type of the release.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$appVersionType;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Version of the web application.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$appVersion;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The name of base website UI Theme.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$baseThemeName;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The base URL that is used by all website pages to fetch resource files.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$baseUrl;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Configuration file version number.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$configVision;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Password hash of scheduler sub-system.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$schedulerPass;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * An associative array that will contain database connections.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var array");
        $this->a($cFile, $this->docEmptyLine, 1);
        
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$dbConnections;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * An array that is used to hold default page titles for different languages.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var array");
        $this->a($cFile, $this->docEmptyLine, 1);
        
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$defaultPageTitles;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * An array that is used to hold default page descriptions for different languages.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var array");
        $this->a($cFile, $this->docEmptyLine, 1);
        
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$descriptions;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * An array that holds SMTP connections information.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$emailAccounts;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The URL of the home page.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$homePage;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The primary language of the website.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$primaryLang;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The character which is used to separate site name from page title.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$titleSep;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * An array which contains all website names in different languages.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$webSiteNames;");
    }
    private function writeAppConfigConstructor(File $cFile) {
        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Creates new instance of the class.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function __construct() {");
        $this->a($cFile, "        \$this->configVision = '1.0.1';");
        $this->a($cFile, "        \$this->initVersionInfo();");
        $this->a($cFile, "        \$this->initSiteInfo();");
        $this->a($cFile, "        \$this->initDbConnections();");
        $this->a($cFile, "        \$this->initSmtpConnections();");


        $this->writeSchedulerPass($cFile);

        $this->a($cFile, $this->blockEnd, 1);
    }
    private function writeAppConfigAddMethods(File $cFile) {
        $this->writeFuncHeader($cFile, 
            'public function addAccount(SMTPAccount $acc)', 
            'Adds SMTP account.', 
            [
                'The developer can use this method to add new account during runtime.',
                'The account will be removed once the program finishes.'
            ], [
                '$acc' => [
                    'type' => 'SMTPAccount',
                    'description' => [
                        'An object of type SMTPAccount.'
                    ]
                ]
            ]);
        $this->a($cFile, "        \$this->emailAccounts[\$acc->getAccountName()] = \$acc;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function addDbConnection(ConnectionInfo $connectionInfo)', 
            'Adds new database connection or updates an existing one.', 
            '', 
            [
                '$connectionInfo' => [
                    'type' => 'ConnectionInfo',
                    'description' => [
                        "An object of type 'ConnectionInfo' that will contain connection information."
                    ]
                ]
            ]);
        $this->a($cFile, "        \$this->dbConnections[\$connectionInfo->getName()] = \$connectionInfo;");
        $this->a($cFile, $this->blockEnd, 1);
    }
    private function writeAppVersionInfo($cFile) {
        $this->a($cFile, [
            $this->docStart,
            $this->docEnd
        ], 1);

        $this->a($cFile, "private function initVersionInfo() {", 1);


        $this->a($cFile, [
            "\$this->appVersion = '".$this->getAppVersion()."';",
            "\$this->appVersionType = '".$this->getAppVersionType()."';",
            "\$this->appReleaseDate = '".$this->getAppReleaseDate()."';"
        ], 2);

        $this->a($cFile, $this->blockEnd, 1);
    }
    private function writeSchedulerPass($cFile) {
        $password = $this->getSchedulerPassword();
        $this->a($cFile, "        \$this->schedulerPass = '".$password."';");
    }
    private function writeSiteDescriptions($cFile) {
        $descArr = $this->getDescriptions();
        $this->a($cFile, "        \$this->descriptions = [");

        foreach ($descArr as $langCode => $desc) {
            $desc = str_replace("'", "\'", $desc);
            $this->a($cFile, "            '$langCode' => '$desc',");
        }
        $this->a($cFile, "        ];");
    }
    private function writeSiteNames($cFile) {
        $wNamesArr = $this->getAppNames();
        $this->a($cFile, "        \$this->webSiteNames = [");

        foreach ($wNamesArr as $langCode => $name) {
            $name = str_replace("'", "\'", $name);
            $this->a($cFile, "            '$langCode' => '$name',");
        }
        $this->a($cFile, "        ];");
        $this->a($cFile, "    ");
    }
    private function writeSmtpConn($cFile) {
        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private function initSmtpConnections() {");
        $this->a($cFile, "        \$this->emailAccounts = [");

        $smtpAccArr = $this->getSMTPConnections();

        foreach ($smtpAccArr as $smtpAcc) {
            if ($smtpAcc instanceof SMTPAccount) {
                $this->a($cFile, "            '".$smtpAcc->getAccountName()."' => new SMTPAccount([");
                $this->a($cFile, "                'port' => ".$smtpAcc->getPort().",");
                $this->a($cFile, "                'server-address' => '".$smtpAcc->getServerAddress()."',");
                $this->a($cFile, "                'user' => '".$smtpAcc->getUsername()."',");
                $this->a($cFile, "                'pass' => '".$smtpAcc->getPassword()."',");
                $this->a($cFile, "                'sender-name' => '".str_replace("'", "\'", $smtpAcc->getSenderName())."',");
                $this->a($cFile, "                'sender-address' => '".$smtpAcc->getAddress()."',");
                $this->a($cFile, "                'account-name' => '".str_replace("'", "\'", $smtpAcc->getAccountName())."'");
                $this->a($cFile, "            ]),");
            }
        }
        $this->a($cFile, "        ];");
        $this->a($cFile, $this->blockEnd, 1);
    }
    private function writeSiteInfo($cFile) {
        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private function initSiteInfo() {");

        $this->writeSiteNames($cFile);
        $this->writeSiteTitles($cFile);
        $this->writeSiteDescriptions($cFile);

        $this->a($cFile, "        \$this->baseUrl = Uri::getBaseURL();");

        $sep = $this->getTitleSeparator();
        $this->a($cFile, "        \$this->titleSep = '$sep';");

        $lang = $this->getPrimaryLanguage();
        $this->a($cFile, "        \$this->primaryLang = '$lang';");


        $baseTheme = $this->getTheme();

        if (class_exists($baseTheme)) {
            $this->a($cFile, "        \$this->baseThemeName = \\".trim($baseTheme, '\\')."::class;");
        } else {
            $this->a($cFile, "        \$this->baseThemeName = '$baseTheme';");
        }

        


        $home = $this->getHomePage();

        if ($home === null || strlen($home) == 0) {
            $this->a($cFile, "        \$this->homePage = Uri::getBaseURL();");
        } else {
            $this->a($cFile, "        \$this->homePage = '$home';");
        }


        $this->a($cFile, $this->blockEnd, 1);
    }
    private function writeDbCon($cFile) {
        $this->a($cFile, $this->docStart, 1);
        
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private function initDbConnections() {");
        $this->a($cFile, "        \$this->dbConnections = [");


        $dbCons = $this->getDBConnections();

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
    /**
     * Returns an array that holds different names for the web application 
     * on different languages.
     * 
     * @return array The indices of the array are language codes such as 'AR' and 
     * the value of the index is the name.
     * 
     */
    public function getAppNames(): array {
        return $this->configVars['site']['website-names'];
    }
    /**
     * Returns an array that holds different descriptions for the web application 
     * on different languages.
     * 
     * @return array The indices of the array are language codes such as 'AR' and 
     * the value of the index is the description.
     */
    public function getDescriptions(): array {
        return $this->configVars['site']['descriptions'];
    }
    /**
     * Returns SMTP connection given its name.
     * 
     * @param string $name The name of the account.
     * 
     * @return SMTPAccount|null If the account is found, The method
     * will return an object of type SMTPAccount. Else, the
     * method will return null.
     * 
     */
    public function getSMTPConnection(string $name) {
        if (isset($this->getSMTPConnections()[$name])) {
            return $this->getSMTPConnections()[$name];
        }
    }

    public function getTitle(string $lang): string {
        if (isset($this->getTitles()[$lang])) {
            return $this->getTitles()[$lang];
        }
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
    public function getTitles(): array {
        return $this->configVars['site']['titles'];
    }
    /**
     * Removes all configuration variables.
     */
    public function remove() {
        $f = new File(self::CONFIG_FILE_PATH);
        $f->remove();
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
        $this->configVars['site']['titles'][$langCode] = $title;
        $this->writeAppConfig();
    }

    public function initialize(bool $reCreate = false) {
        $cfgNs = self::CONFIG_NS;
        if ($reCreate) {
            $cFile = new File(self::CONFIG_FILE_PATH);
            $cFile->remove();
            $this->initDefaultConfig();
        }
        if (!class_exists($cfgNs)) {
            $this->writeAppConfig();
        } else {
            $cfg = new $cfgNs();
            $cfg instanceof \app\config\AppConfig;
            $this->configVars = [
                'smtp-connections' => $cfg->getAccounts(),
                'database-connections' => $cfg->getDBConnections(),
                'scheduler-password' => $cfg->getSchedulerPassword(),
                'version-info' => [
                    'version' => $cfg->getVersion(),
                    'version-type' => $cfg->getVersionType(),
                    'release-date' => $cfg->getReleaseDate()
                ],
                'env-vars' => [

                ],
                'site' => [
                    'base-url' => $cfg->getBaseURL(),
                    'primary-lang' => $cfg->getPrimaryLanguage(),
                    'title-sep' => $cfg->getTitleSep(),
                    'home-page' => $cfg->getHomePage(),
                    'base-theme' => $cfg->getBaseThemeName(),
                    'descriptions' => [
                        'AR' => $cfg->getDescription('AR'),
                        'EN' => $cfg->getDescription('EN')
                    ],
                    'website-names' => [
                        'AR' => $cfg->getWebsiteName('AR'),
                        'EN' => $cfg->getWebsiteName('EN')
                    ],
                    'titles' => [
                        'AR' => $cfg->getTitle('AR'),
                        'EN' => $cfg->getTitle('EN')
                    ],
                ]
            ];
        }
    }

}
