<?php
namespace WebFiori\Framework\Config;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Mail\SMTPAccount;
use WebFiori\File\exceptions\FileException;
use WebFiori\File\File;
use WebFiori\Framework\Writers\LangClassWriter;
use WebFiori\Http\Uri;

/**
 * A configuration driver which is used to store configuration on PHP class.
 *
 * This driver will
 * create a class called 'AppConfig' on the directory APP_DIR/Config and
 * use it to read and write configurations.
 *
 * @author Ibrahim
 */
class ClassDriver implements ConfigurationDriver {
    const CONFIG_FILE_PATH = APP_PATH.'Config'.DIRECTORY_SEPARATOR.'AppConfig.php';
    const CONFIG_NS = APP_DIR.'\\Config\\AppConfig';
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
        $this->initDefaultConfig();
    }
    public static function a($file, $str, $tabSize = 0) {
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
    /**
     * Adds application environment variable to the configuration.
     *
     * The variables which are added using this method will be defined as
     * a named constant at run time using the function 'define'. This means
     * the constant will be accesaable anywhere within the appllication's environment.
     *
     * @param string $name The name of the named constant such as 'MY_CONSTANT'.
     *
     * @param mixed $value The value of the constant.
     *
     * @param string $description An optional description to describe the porpuse
     * of the constant.
     */
    public function addEnvVar(string $name, mixed $value = null, ?string $description = null) {
        $this->configVars['env-vars'][$name] = [
            'value' => $value,
            'description' => $description
        ];
        $this->writeAppConfig();
    }
    /**
     * Adds new database connections information or update existing connection.
     *
     * @param ConnectionInfo $dbConnectionsInfo An object which holds connection information.
     */
    public function addOrUpdateDBConnection(ConnectionInfo $dbConnectionsInfo) {
        $this->configVars['database-connections'][$dbConnectionsInfo->getName()] = $dbConnectionsInfo;
        $this->writeAppConfig();
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
     * Returns an associative array of application constants.
     *
     * @return array The indices of the array are names of the constants and
     * values are sub-associative arrays. Each sub-array will have two indices,
     * 'value' and 'description'.
     */
    public function getEnvVars(): array {
        return $this->configVars['env-vars'];
    }
    /**
     * Returns a string that represents the URL of home page of the application.
     *
     * @return string
     */
    public function getHomePage() : string {
        return $this->configVars['site']['home-page'];
    }
    /**
     * Returns a two-letters string that represents primary language of the application.
     *
     * @return string A two-letters string that represents primary language of the application.
     */
    public function getPrimaryLanguage() : string {
        return $this->configVars['site']['primary-lang'];
    }

    public function getSchedulerPassword(): string {
        return $this->configVars['scheduler-password'];
    }

    public function getSMTPAccount(string $name) {
        foreach ($this->getSMTPConnections() as $connName => $connObj) {
            if ($connName == $name || $connObj->getAccountName() == $name) {
                return $connObj;
            }
        }
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

    public function getSMTPConnections(): array {
        return $this->configVars['smtp-connections'];
    }

    public function getTheme(): string {
        return $this->configVars['site']['base-theme'];
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

    public function getTitleSeparator() : string {
        return $this->configVars['site']['title-sep'];
    }
    /**
     * Initialize configuration driver.
     *
     * This method should be used to create application configuration and
     * pubulate it with default values if needed.
     *
     * @param bool $reCreate If the configuration is exist and this one is set
     * to true, the method should remove existing configuration and re-create it
     * using default values.
     */
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
            //$cfg is of type [APP_DIR]\config\AppConfig
            $cfg = new $cfgNs();
            $this->configVars = [
                'smtp-connections' => $cfg->getAccounts(),
                'database-connections' => $cfg->getDBConnections(),
                'scheduler-password' => $cfg->getSchedulerPassword(),
                'version-info' => [
                    'version' => $cfg->getVersion(),
                    'version-type' => $cfg->getVersionType(),
                    'release-date' => $cfg->getReleaseDate()
                ],
                'env-vars' => $cfg->getConstants(),
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
    /**
     * Removes all configuration variables.
     */
    public function remove() {
        $f = new File(self::CONFIG_FILE_PATH);
        $f->remove();
    }
    /**
     * Removes all stored database connections.
     */
    public function removeAllDBConnections() {
        $this->configVars['database-connections'] = [];
        $this->writeAppConfig();
    }
    /**
     * Removes database connection given its name.
     *
     * This method will search for a connection which has the given
     * name. Once it found, it will remove the connection.
     *
     * @param string $connectionName The name of the connection.
     *
     */
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
    /**
     * Removes specific application environment variable given its name.
     *
     * @param string $name The name of the variable.
     */
    public function removeEnvVar(string $name) {
        unset($this->configVars['env-vars'][$name]);
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
    public function setTitleSeparator(string $separator) {
        $this->configVars['site']['title-sep'] = $separator;
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
            'public function initConstants()',
            'Initialize application environment constants.');
        self::a($cFile, "        \$this->globalConst = [");

        foreach ($this->getEnvVars() as $varName => $varProbs) {
            $valType = gettype($varProbs['value']);

            if (!in_array($valType, ['string', 'integer', 'double', 'boolean'])) {
                continue;
            }
            self::a($cFile, "            '$varName' => [");
            $valType = gettype($varProbs['value']);

            if ($valType == 'boolean') {
                self::a($cFile, "                'value' => ".($varProbs['value'] === true ? 'true' : 'false').',');
            } else if ($valType == 'integer' || $valType == 'double') {
                self::a($cFile, "                'value' => ".$varProbs['value'].',');
            } else if ($valType == 'string') {
                self::a($cFile, "                'value' => ".$varProbs['value'].',');
            }
            self::a($cFile, "                'description' => '".$varProbs['description']."',");
            self::a($cFile, "             ],");
        }
        self::a($cFile, "        ];");
        self::a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile,
            'public function getConstants() : array ',
            'Returns an array that contains application environment constants.');
        self::a($cFile, "        return \$this->globalConst;");
        self::a($cFile, $this->blockEnd, 1);

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
        self::a($cFile, "        if (isset(\$this->emailAccounts[\$name])) {");
        self::a($cFile, "            return \$this->emailAccounts[\$name];");
        self::a($cFile, "        }");
        self::a($cFile, $this->blockEnd, 1);

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
        self::a($cFile, "        return \$this->emailAccounts;");
        self::a($cFile, $this->blockEnd, 1);


        $this->writeFuncHeader($cFile,
            'public function getBaseThemeName() : string',
            'Returns the name of base theme that is used in website pages.',
            'Usually, this theme is used for the normally visitors of the website.',
            [],
            [
                'type' => 'string',
                'description' => 'The name of base theme that is used in website pages.'
            ]);
        self::a($cFile, "        return \$this->baseThemeName;");
        self::a($cFile, $this->blockEnd, 1);

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
        self::a($cFile, "        return \$this->baseUrl;");
        self::a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile,
            'public function getConfigVersion() : string',
            'Returns version number of the configuration file.',
            'This value can be used to check for the compatability of configuration file',
            [],
            [
                'type' => 'string',
                'description' => 'The version number of the configuration file.'
            ]);
        self::a($cFile, "        return \$this->configVision;");
        self::a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile,
            'public function getSchedulerPassword() : string',
            'Returns sha256 hash of the password which is used to prevent unauthorized access to run the tasks or access scheduler web interface.',
            '',
            [],
            [
                'type' => 'string',
                'description' => "Password hash or the string 'NO_PASSWORD' if there is no password."
            ]);
        self::a($cFile, "        return \$this->schedulerPass;");
        self::a($cFile, $this->blockEnd, 1);

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
        self::a($cFile, "        \$conns = \$this->getDBConnections();");
        self::a($cFile, "        \$trimmed = trim(\$conName);");
        self::a($cFile, "        ");
        self::a($cFile, "        if (isset(\$conns[\$trimmed])) {");
        self::a($cFile, "            return \$conns[\$trimmed];");
        self::a($cFile, "        }");
        self::a($cFile, $this->blockEnd, 1);

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
        self::a($cFile, "        return \$this->dbConnections;");
        self::a($cFile, $this->blockEnd, 1);

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
        self::a($cFile, "        \$langs = \$this->getTitles();");
        self::a($cFile, "        \$langCodeF = strtoupper(trim(\$langCode));");
        self::a($cFile, "        ");
        self::a($cFile, "        if (isset(\$langs[\$langCodeF])) {");
        self::a($cFile, "            return \$langs[\$langCode];");
        self::a($cFile, "        }");
        self::a($cFile, $this->blockEnd, 1);


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
        self::a($cFile, "        \$langs = \$this->getDescriptions();");
        self::a($cFile, "        \$langCodeF = strtoupper(trim(\$langCode));");
        self::a($cFile, "        if (isset(\$langs[\$langCodeF])) {");
        self::a($cFile, "            return \$langs[\$langCode];");
        self::a($cFile, "        }");
        self::a($cFile, $this->blockEnd, 1);

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
        self::a($cFile, "        return \$this->descriptions;");
        self::a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile,
            'public function getHomePage() : string',
            'Returns the home page URL of the website.',
            '',
            [],
            [
                'type' => 'string',
                'description' => 'The home page URL of the website.'
            ]);
        self::a($cFile, "        return \$this->homePage;");
        self::a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile,
            'public function getPrimaryLanguage() : string',
            'Returns the primary language of the website.',
            '',
            [],
            [
                'type' => 'string',
                'description' => "Language code of the primary language such as 'EN'."
            ]);
        self::a($cFile, "        return \$this->primaryLang;");
        self::a($cFile, $this->blockEnd, 1);

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
        self::a($cFile, "        return \$this->appReleaseDate;");
        self::a($cFile, $this->blockEnd, 1);

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
        self::a($cFile, "        return \$this->defaultPageTitles;");
        self::a($cFile, $this->blockEnd, 1);

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
        self::a($cFile, "        return \$this->titleSep;");
        self::a($cFile, $this->blockEnd, 1);

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
        self::a($cFile, "        if (isset(\$this->defaultPageTitles[\$langCode])) {");
        self::a($cFile, "            return \$this->defaultPageTitles[\$langCode];");
        self::a($cFile, "        }");
        self::a($cFile, $this->blockEnd, 1);

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
        self::a($cFile, "        return \$this->appVersion;");
        self::a($cFile, $this->blockEnd, 1);

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
        self::a($cFile, "        return \$this->appVersionType;");
        self::a($cFile, $this->blockEnd, 1);

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
        self::a($cFile, "        \$langs = \$this->getWebsiteNames();");
        self::a($cFile, "        \$langCodeF = strtoupper(trim(\$langCode));");
        self::a($cFile, "        ");
        self::a($cFile, "        if (isset(\$langs[\$langCodeF])) {");
        self::a($cFile, "            return \$langs[\$langCode];");
        self::a($cFile, "        }");
        self::a($cFile, $this->blockEnd, 1);

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
        self::a($cFile, "        return \$this->webSiteNames;");
        self::a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile,
            'public function removeDBConnections()',
            'Removes all stored database connections.');
        self::a($cFile, "        \$this->dbConnections = [];");
        self::a($cFile, $this->blockEnd, 1);

        $this->writeDbCon($cFile);
        $this->writeSiteInfo($cFile);
        $this->writeSmtpConn($cFile);
        $this->writeAppVersionInfo($cFile);

        self::a($cFile, "}");
        $cFile->write(false, true);
    }
    private function initDefaultConfig() {
        $this->configVars = [
            'smtp-connections' => [],
            'database-connections' => [],
            'scheduler-password' => 'NO_PASSWORD',
            'version-info' => [
                'version' => '1.0',
                'version-type' => 'Stable',
                'release-date' => date('Y-m-d')
            ],
            'env-vars' => [
                'WF_VERBOSE' => [
                    'value' => false,
                    'description' => 'Configure the verbosity of error messsages at run-time. This should be set to true in testing and false in production.'
                ],
                "CLI_HTTP_HOST" => [
                    "value" => "127.0.0.1",
                    "description" => "Host name that will be used when runing the application as command line utility."
                ]
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
        self::a($cFile, "        \$this->emailAccounts[\$acc->getAccountName()] = \$acc;");
        self::a($cFile, $this->blockEnd, 1);

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
        self::a($cFile, "        \$this->dbConnections[\$connectionInfo->getName()] = \$connectionInfo;");
        self::a($cFile, $this->blockEnd, 1);
    }
    private function writeAppConfigAttrs($cFile) {
        self::a($cFile, "<?php");
        self::a($cFile, "");
        self::a($cFile, "namespace ".APP_DIR."\\config;");
        self::a($cFile, "");
        self::a($cFile, "use WebFiori\\database\\ConnectionInfo;");
        self::a($cFile, "use WebFiori\\email\\SMTPAccount;");
        self::a($cFile, "use WebFiori\\http\\Uri;");
        self::a($cFile, "/**");
        self::a($cFile, " * Configuration class of the application");
        self::a($cFile, " *");
        self::a($cFile, " * @author Ibrahim");
        self::a($cFile, " *");
        self::a($cFile, " * @version 1.0.1");
        self::a($cFile, " *");
        self::a($cFile, " * @since 2.1.0");
        self::a($cFile, " */");
        self::a($cFile, "class AppConfig {");

        self::a($cFile, $this->docStart, 1);
        self::a($cFile, "     * An array that holds global constants of the application");
        self::a($cFile, $this->docEmptyLine, 1);
        self::a($cFile, "     * @var array");
        self::a($cFile, $this->docEmptyLine, 1);

        self::a($cFile, $this->docEnd, 1);
        self::a($cFile, "    private \$globalConst;");

        self::a($cFile, $this->docStart, 1);
        self::a($cFile, "     * The date at which the application was released.");
        self::a($cFile, $this->docEmptyLine, 1);
        self::a($cFile, "     * @var string");
        self::a($cFile, $this->docEmptyLine, 1);

        self::a($cFile, $this->docEnd, 1);
        self::a($cFile, "    private \$appReleaseDate;");

        self::a($cFile, $this->docStart, 1);
        self::a($cFile, "     * A string that represents the type of the release.");
        self::a($cFile, $this->docEmptyLine, 1);
        self::a($cFile, "     * @var string");
        self::a($cFile, $this->docEmptyLine, 1);

        self::a($cFile, $this->docEnd, 1);
        self::a($cFile, "    private \$appVersionType;");

        self::a($cFile, $this->docStart, 1);
        self::a($cFile, "     * Version of the web application.");
        self::a($cFile, $this->docEmptyLine, 1);
        self::a($cFile, "     * @var string");
        self::a($cFile, $this->docEmptyLine, 1);

        self::a($cFile, $this->docEnd, 1);
        self::a($cFile, "    private \$appVersion;");

        self::a($cFile, $this->docStart, 1);
        self::a($cFile, "     * The name of base website UI Theme.");
        self::a($cFile, $this->docEmptyLine, 1);
        self::a($cFile, "     * @var string");
        self::a($cFile, $this->docEmptyLine, 1);

        self::a($cFile, $this->docEnd, 1);
        self::a($cFile, "    private \$baseThemeName;");

        self::a($cFile, $this->docStart, 1);
        self::a($cFile, "     * The base URL that is used by all website pages to fetch resource files.");
        self::a($cFile, $this->docEmptyLine, 1);
        self::a($cFile, "     * @var string");
        self::a($cFile, $this->docEmptyLine, 1);

        self::a($cFile, $this->docEnd, 1);
        self::a($cFile, "    private \$baseUrl;");

        self::a($cFile, $this->docStart, 1);
        self::a($cFile, "     * Configuration file version number.");
        self::a($cFile, $this->docEmptyLine, 1);
        self::a($cFile, "     * @var string");
        self::a($cFile, $this->docEmptyLine, 1);

        self::a($cFile, $this->docEnd, 1);
        self::a($cFile, "    private \$configVision;");

        self::a($cFile, $this->docStart, 1);
        self::a($cFile, "     * Password hash of scheduler sub-system.");
        self::a($cFile, $this->docEmptyLine, 1);
        self::a($cFile, "     * @var string");
        self::a($cFile, $this->docEmptyLine, 1);

        self::a($cFile, $this->docEnd, 1);
        self::a($cFile, "    private \$schedulerPass;");

        self::a($cFile, $this->docStart, 1);
        self::a($cFile, "     * An associative array that will contain database connections.");
        self::a($cFile, $this->docEmptyLine, 1);
        self::a($cFile, "     * @var array");
        self::a($cFile, $this->docEmptyLine, 1);

        self::a($cFile, $this->docEnd, 1);
        self::a($cFile, "    private \$dbConnections;");

        self::a($cFile, $this->docStart, 1);
        self::a($cFile, "     * An array that is used to hold default page titles for different languages.");
        self::a($cFile, $this->docEmptyLine, 1);
        self::a($cFile, "     * @var array");
        self::a($cFile, $this->docEmptyLine, 1);

        self::a($cFile, $this->docEnd, 1);
        self::a($cFile, "    private \$defaultPageTitles;");

        self::a($cFile, $this->docStart, 1);
        self::a($cFile, "     * An array that is used to hold default page descriptions for different languages.");
        self::a($cFile, $this->docEmptyLine, 1);
        self::a($cFile, "     * @var array");
        self::a($cFile, $this->docEmptyLine, 1);

        self::a($cFile, $this->docEnd, 1);
        self::a($cFile, "    private \$descriptions;");

        self::a($cFile, $this->docStart, 1);
        self::a($cFile, "     * An array that holds SMTP connections information.");
        self::a($cFile, $this->docEmptyLine, 1);
        self::a($cFile, "     * @var string");
        self::a($cFile, $this->docEmptyLine, 1);

        self::a($cFile, $this->docEnd, 1);
        self::a($cFile, "    private \$emailAccounts;");

        self::a($cFile, $this->docStart, 1);
        self::a($cFile, "     * The URL of the home page.");
        self::a($cFile, $this->docEmptyLine, 1);
        self::a($cFile, "     * @var string");
        self::a($cFile, $this->docEmptyLine, 1);

        self::a($cFile, $this->docEnd, 1);
        self::a($cFile, "    private \$homePage;");

        self::a($cFile, $this->docStart, 1);
        self::a($cFile, "     * The primary language of the website.");
        self::a($cFile, $this->docEmptyLine, 1);
        self::a($cFile, "     * @var string");
        self::a($cFile, $this->docEmptyLine, 1);

        self::a($cFile, $this->docEnd, 1);
        self::a($cFile, "    private \$primaryLang;");

        self::a($cFile, $this->docStart, 1);
        self::a($cFile, "     * The character which is used to separate site name from page title.");
        self::a($cFile, $this->docEmptyLine, 1);
        self::a($cFile, "     * @var string");
        self::a($cFile, $this->docEmptyLine, 1);

        self::a($cFile, $this->docEnd, 1);
        self::a($cFile, "    private \$titleSep;");

        self::a($cFile, $this->docStart, 1);
        self::a($cFile, "     * An array which contains all website names in different languages.");
        self::a($cFile, $this->docEmptyLine, 1);
        self::a($cFile, "     * @var string");
        self::a($cFile, $this->docEmptyLine, 1);

        self::a($cFile, $this->docEnd, 1);
        self::a($cFile, "    private \$webSiteNames;");
    }
    private function writeAppConfigConstructor(File $cFile) {
        self::a($cFile, $this->docStart, 1);
        self::a($cFile, "     * Creates new instance of the class.");
        self::a($cFile, $this->docEmptyLine, 1);
        self::a($cFile, $this->docEnd, 1);
        self::a($cFile, "    public function __construct() {");
        self::a($cFile, "        \$this->configVision = '1.0.1';");
        self::a($cFile, "        \$this->initVersionInfo();");
        self::a($cFile, "        \$this->initSiteInfo();");
        self::a($cFile, "        \$this->initDbConnections();");
        self::a($cFile, "        \$this->initSmtpConnections();");
        self::a($cFile, "        \$this->initConstants();");


        $this->writeSchedulerPass($cFile);

        self::a($cFile, $this->blockEnd, 1);
    }
    private function writeAppVersionInfo($cFile) {
        self::a($cFile, [
            $this->docStart,
            $this->docEnd
        ], 1);

        self::a($cFile, "private function initVersionInfo() {", 1);


        self::a($cFile, [
            "\$this->appVersion = '".$this->getAppVersion()."';",
            "\$this->appVersionType = '".$this->getAppVersionType()."';",
            "\$this->appReleaseDate = '".$this->getAppReleaseDate()."';"
        ], 2);

        self::a($cFile, $this->blockEnd, 1);
    }
    private function writeDbCon($cFile) {
        self::a($cFile, $this->docStart, 1);

        self::a($cFile, $this->docEnd, 1);
        self::a($cFile, "    private function initDbConnections() {");
        self::a($cFile, "        \$this->dbConnections = [");


        $dbCons = $this->getDBConnections();

        foreach ($dbCons as $connObj) {
            if ($connObj instanceof ConnectionInfo) {
                $cName = $connObj->getName();
                self::a($cFile, "            '$cName' => new ConnectionInfo('".$connObj->getDatabaseType()."',"
                        ."'".$connObj->getUsername()."', "
                        ."'".$connObj->getPassword()."', "
                        ."'".$connObj->getDBName()."', "
                        ."'".$connObj->getHost()."', ".$connObj->getPort().", [");
                self::a($cFile, "                'connection-name' => '".str_replace("'", "\'", $cName)."'");
                self::a($cFile, "            ]),");
            }
        }
        self::a($cFile, "        ];");
        self::a($cFile, $this->blockEnd, 1);
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
        self::a($cFile, $phpDocArr, 1);
    }
    private function writeSchedulerPass($cFile) {
        $password = $this->getSchedulerPassword();
        self::a($cFile, "        \$this->schedulerPass = '".$password."';");
    }
    private function writeSiteDescriptions($cFile) {
        $descArr = $this->getDescriptions();
        self::a($cFile, "        \$this->descriptions = [");

        foreach ($descArr as $langCode => $desc) {
            $desc = str_replace("'", "\'", $desc);
            self::a($cFile, "            '$langCode' => '$desc',");
        }
        self::a($cFile, "        ];");
    }
    private function writeSiteInfo($cFile) {
        self::a($cFile, $this->docStart, 1);
        self::a($cFile, $this->docEnd, 1);
        self::a($cFile, "    private function initSiteInfo() {");

        $this->writeSiteNames($cFile);
        $this->writeSiteTitles($cFile);
        $this->writeSiteDescriptions($cFile);

        self::a($cFile, "        \$this->baseUrl = Uri::getBaseURL();");

        $sep = $this->getTitleSeparator();
        self::a($cFile, "        \$this->titleSep = '$sep';");

        $lang = $this->getPrimaryLanguage();
        self::a($cFile, "        \$this->primaryLang = '$lang';");


        $baseTheme = $this->getTheme();

        if (class_exists($baseTheme)) {
            self::a($cFile, "        \$this->baseThemeName = \\".trim($baseTheme, '\\')."::class;");
        } else {
            self::a($cFile, "        \$this->baseThemeName = '$baseTheme';");
        }




        $home = $this->getHomePage();

        if ($home === null || strlen($home) == 0) {
            self::a($cFile, "        \$this->homePage = Uri::getBaseURL();");
        } else {
            self::a($cFile, "        \$this->homePage = '$home';");
        }


        self::a($cFile, $this->blockEnd, 1);
    }
    private function writeSiteNames($cFile) {
        $wNamesArr = $this->getAppNames();
        self::a($cFile, "        \$this->webSiteNames = [");

        foreach ($wNamesArr as $langCode => $name) {
            $name = str_replace("'", "\'", $name);
            self::a($cFile, "            '$langCode' => '$name',");
        }
        self::a($cFile, "        ];");
        self::a($cFile, "    ");
    }
    private function writeSiteTitles($cFile) {
        $titlesArr = $this->getTitles();
        self::a($cFile, "        \$this->defaultPageTitles = [");

        foreach ($titlesArr as $langCode => $title) {
            $title = str_replace("'", "\'", $title);
            self::a($cFile, "            '$langCode' => '$title',");

            if (!class_exists(APP_DIR.'\\Langs\\Lang'.$langCode)) {
                //This requires a fix in the future
                $dir = $langCode == 'AR' ? 'rtl' : 'ltr';

                $writer = new LangClassWriter($langCode, $dir);
                $writer->writeClass();
                require_once $writer->getAbsolutePath();
            }
        }
        self::a($cFile, "        ];");
    }
    private function writeSmtpConn($cFile) {
        self::a($cFile, $this->docStart, 1);
        self::a($cFile, $this->docEnd, 1);
        self::a($cFile, "    private function initSmtpConnections() {");
        self::a($cFile, "        \$this->emailAccounts = [");

        $smtpAccArr = $this->getSMTPConnections();

        foreach ($smtpAccArr as $smtpAcc) {
            if ($smtpAcc instanceof SMTPAccount) {
                self::a($cFile, "            '".$smtpAcc->getAccountName()."' => new SMTPAccount([");
                self::a($cFile, "                'port' => ".$smtpAcc->getPort().",");
                self::a($cFile, "                'server-address' => '".$smtpAcc->getServerAddress()."',");
                self::a($cFile, "                'user' => '".$smtpAcc->getUsername()."',");
                self::a($cFile, "                'pass' => '".$smtpAcc->getPassword()."',");
                self::a($cFile, "                'sender-name' => '".str_replace("'", "\'", $smtpAcc->getSenderName())."',");
                self::a($cFile, "                'sender-address' => '".$smtpAcc->getAddress()."',");
                self::a($cFile, "                'account-name' => '".str_replace("'", "\'", $smtpAcc->getAccountName())."'");
                self::a($cFile, "            ]),");
            }
        }
        self::a($cFile, "        ];");
        self::a($cFile, $this->blockEnd, 1);
    }
}
