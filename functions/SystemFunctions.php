<?php

/*
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
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
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 403 Forbidden");
    die(''
        . '<!DOCTYPE html>'
        . '<html>'
        . '<head>'
        . '<title>Forbidden</title>'
        . '</head>'
        . '<body>'
        . '<h1>403 - Forbidden</h1>'
        . '<hr>'
        . '<p>'
        . 'Direct access not allowed.'
        . '</p>'
        . '</body>'
        . '</html>');
}
/**
 * Description of SystemFunctions
 *
 * @author Ibrahim
 * @version 1.4
 */
class SystemFunctions extends Functions{
    /**
     * An array of possible user registration statuses.
     * @since 1.3
     */
    const USER_REG_STATS = array(
        'C'=>'Closed',
        'O'=>'Open',
        'AO'=>'Admin Only'
    );
    /**
     * An array of system setup steps
     * @var array
     * @since 1.3 
     */
    public static $SETUP_STAGES = array(
        'w'=>'welcome',
        'db'=>'database-setup',
        'smtp'=>'smtp-account-setup',
        'admin'=>'admin-account',
        'site'=>'site-configuration'
    );
    /**
     * A constant that indicates the selected database schema has tables.
     * @since 1.1
     */
    const DB_NOT_EMPTY = 'db_has_tables';
    /**
     * A constant that indicates the file Config.php was not found.
     * @since 1.2
     */
    const SYS_CONFIG_MISSING = 'config_file_missing';
    /**
     * A constant that indicates the file SiteConfig.php was not found.
     * @since 1.2
     */
    const SITE_CONFIG_MISSING = 'site_config_file_missing';
    /**
     * A constant that indicates the file MailConfig.php was not found.
     * @since 1.2
     */
    const MAIL_CONFIG_MISSING = 'mail_config_file_missing';
    /**
     * An array that contains initial system configuration variables.
     * @since 1.0
     */
    const INITIAL_CONFIG_VARS = array(
        'is-config'=>'FALSE',
        'lisks-date'=>'10-03-2018 (DD-MM-YYYY)',
        'lisks-version'=>'0.1.4',
        'lisks-version-type'=>'Beta',
        'config-file-version'=>'1.3',
        'database-host'=>'localhost',
        'database-username'=>'',
        'database-password'=>'',
        'database-name'=>'',
        'user-reg-status'=>'C',
        'user-reg-status-description'=>SystemFunctions::USER_REG_STATS['C'],
    );
    /**
     * An instance of SystemFunctions
     * @var SystemFunctions
     * @since 1.0 
     */
    private static $singleton;
    /**
     * Returns a single instance of the class.
     * @return SystemFunctions
     * @since 1.0
     */
    public static function get(){
        if(self::$singleton !== NULL){
            return self::$singleton;
        }
        self::$singleton = new SystemFunctions();
        return self::$singleton;
    }
    /**
     * A SessionManager used in case of application setup
     * @var SessionManager
     * @since 1.3 
     */
    private $setupSession;
    /**
     * Initiate the session manager that is used to manage setup steps.
     * @since 1.3
     */
    public function initSetupSession() {
        $this->setupSession = new SessionManager('setup-session');
        $this->setupSession->initSession(TRUE, TRUE);
        if(!isset($_SESSION['setup-step'])){
            $_SESSION['setup-step'] = self::$SETUP_STAGES['w'];
        }
    }
    /**
     * Updates the current setup step.
     * @param string $stageCode The code of the current stage. It 
     * must be a value from the constant SystemFunctions::SETUP_STAGES.
     * @since 1.3
     */
    public function setSetupStage($stageCode) {
        $this->initSetupSession();
        if(isset($this->SETUP_STAGES[$stageCode])){
            $_SESSION['setup-step'] = self::$SETUP_STAGES[$stageCode];
        }
    }
    /**
     * Returns the name of current setup step.
     * @return string The name of current setup step. It will be a value 
     * from the array SystemFunctions::SETUP_STAGES.
     * @since 1.3
     */
    public function getSetupStep() {
        $this->initSetupSession();
        return $_SESSION['setup-step'];
    }
    /**
     * Creates the file 'Config.php' if it does not exist.
     * @since 1.0
     */
    public function createConfigFile() {
        if(!class_exists('Config')){
            $cfg = $this->getConfigVars();
            $this->writeConfig($cfg);
        }
    }
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct();
    }
    /**
     * Update database attributes. 
     * @param string $dbHost The name of database host. It can be an IP address 
     * or a URL.
     * @param string $dbUser The username of database user.
     * @param string $dbPass The password of the database user.
     * @param string $dbName The name of database schema.
     * @return boolean|string The function will return <b>TRUE</b> in case 
     * of valid database attributes. Also the function will return 
     * <b>SessionManager::DB_CONNECTION_ERR</b> in case the connection was not 
     * established. If the connection is established and the database is not 
     * empty, the function will return <b>SystemFunctions::DB_NOT_EMPTY</b>.
     * @since 1.0
     */
    public function updateDBAttributes($dbHost,$dbUser,$dbPass,$dbName){
        $r = $this->getMainSession()->useDb(array(
            'user'=>$dbUser,
            'host'=>$dbHost,
            'pass'=>$dbPass,
            'db-name'=>$dbName
        ));
        if($r === TRUE){
            $tablesCount = $this->getSchemaTablesCount($dbName);
            if($tablesCount == 0){
                $config = $this->getConfigVars();
                $config['database-host'] = $dbHost;
                $config['database-username'] = $dbUser;
                $config['database-password'] = $dbPass;
                $config['database-name'] = $dbName;
                $this->writeConfig($config);
                return TRUE;
            }
            else if($tablesCount == MySQLQuery::QUERY_ERR){
                return MySQLQuery::QUERY_ERR;
            }
            return self::DB_NOT_EMPTY;
        }
        return $r;
    }
    /**
     * Updates system configuration status. Only 
     * a user that is logged in as super admin can perform that task.
     * @param boolean $isConfig <b>TRUE</b> to set system as configured. 
     * <b>FALSE</b> to make it not configured.
     * @return boolean The function will return <b>TRUE</b> if system configuration 
     * status updated.
     * @since 1.3
     */
    public function configured($isConfig=true){
        if($this->getAccessLevel() == 0){
            $confVars = $this->getConfigVars();
            $confVars['is-config'] = $isConfig === TRUE ? 'TRUE' : 'FALSE';
            $this->writeConfig($confVars);
            return TRUE;
        }
        return FALSE;
    }
    private function getSchemaTablesCount($schema){
        $q = new UserQuery();
        $q->schemaTablesCount($schema);
        if($this->excQ($q)){
            return intval($this->getRow()['tables_count']);
        }
        return MySQLQuery::QUERY_ERR;
    }
    
    
    /**
     * Returns an associative array that contains system configuration 
     * info.
     * @return array An associative array that contains system configuration 
     * info.
     * @since 1.0
     */
    public function getConfigVars(){
        $cfgArr = SystemFunctions::INITIAL_CONFIG_VARS;
        if(class_exists('Config')){
            $cfgs = Config::get();
            $cfgArr['is-config'] = $cfgs->isConfig() === TRUE ? 'TRUE' : 'FALSE';
            $cfgArr['database-host'] = $cfgs->getDBHost();
            $cfgArr['database-username'] = $cfgs->getDBUser();
            $cfgArr['database-password'] = $cfgs->getDBPassword();
            $cfgArr['database-name'] = $cfgs->getDBName();
            $cfgArr['user-reg-status'] = $cfgs->getUserRegStatus();
        }
        return $cfgArr;
    }
    /**
     * A function to save changes to configuration file.
     * @param type $configArr An array that contains system configuration 
     * variables.
     * @since 1.0
     */
    private function writeConfig($configArr){
        $fh = new FileHandler(ROOT_DIR.'/entity/Config.php');
        $fh->write('<?php', TRUE, TRUE);
        $fh->write('if(!defined(\'ROOT_DIR\')){
    header("HTTP/1.1 403 Forbidden");
    die(\'\'
        . \'<!DOCTYPE html>\'
        . \'<html>\'
        . \'<head>\'
        . \'<title>Forbidden</title>\'
        . \'</head>\'
        . \'<body>\'
        . \'<h1>403 - Forbidden</h1>\'
        . \'<hr>\'
        . \'<p>\'
        . \'Direct access not allowed.\'
        . \'</p>\'
        . \'</body>\'
        . \'</html>\');
}', TRUE, TRUE);
        $fh->write('/**
 * Global configuration class. Used by the server part and the presentation part.
 * Do not modify this file manually unless you know what you are doing.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.3
 */', TRUE, TRUE);
        $fh->write('class Config{', TRUE, TRUE);
        $fh->addTab();
        //stat here
        $fh->write('/**
     * The type framework version that is used to build the project.
     * @var string The framework version that is used to build the project.
     * @since 1.0 
     */
    private $lisksVersionType;
    /**
     * The version of the framework that is used to build the project.
     * @var string The version of the framework that is used to build the project.
     * @since 1.0 
     */
    private $lisksVersion;
    /**
     * The release date of the framework that is used to build the project.
     * @var string Release date of of the framework that is used to build the project.
     * @since 1.0 
     */
    private $lisksDate;
    /**
     * A boolean value. Set to true once system configuration is completed.
     * @var boolean 
     * @since 1.0
     */
    private $isConfigured;
    /**
     * The name of database host.
     * @var string 
     * @since 1.0
     */
    private $dbHost;
    /**
     * The name of database username. It must be a user with all privileges over the database.
     * @var string 
     * @since 1.0
     */
    private $dbUser;
    /**
     * The database user\'s password.
     * @var string 
     * @since 1.0
     */
    private $dbPass;
    /**
     * User resgistration status.
     * @var string 
     * @since 1.3
     */
    private $userRegStats;
    /**
     * The name of database schema.
     * @var string 
     * @since 1.0
     */
    private $dbName;
    /**
     * Configuration file version number.
     * @var string 
     * @since 1.2
     */
    private $configVision;',TRUE,TRUE);
        $fh->write('/**
     * Initialize configuration.
     */
    private function __construct() {
        $this->isConfigured = '.$configArr['is-config'].';
        $this->lisksDate = \''.$configArr['lisks-date'].'\';
        $this->lisksVersion = \''.$configArr['lisks-version'].'\';
        $this->lisksVersionType = \''.$configArr['lisks-version-type'].'\';
        $this->configVision = \''.$configArr['config-file-version'].'\';
        $this->dbHost = \''.$configArr['database-host'].'\';
        $this->dbUser = \''.$configArr['database-username'].'\';
        $this->dbPass = \''.$configArr['database-password'].'\';
        $this->dbName = \''.$configArr['database-name'].'\';
        $this->userRegStats = \''.$configArr['user-reg-status'].'\';
    }', TRUE, TRUE);
        $fh->write('/**
     * An instance of <b>Config</b>.
     * @var Config 
     * @since 1.0
     */
    private static $cfg;
    /**
     * Returns an instance of the configuration file.
     * @return Config An object of type <b>Config</b>.
     * @since 1.0
     */
    public static function get(){
        if(self::$cfg != NULL){
            return self::$cfg;
        }
        self::$cfg = new Config();
        return self::$cfg;
    }
    /**
     * Returns user registration status.
     * @return User registration status.
     * @since 1.3
     */
    public function getUserRegStatus(){
        return $this->userRegStats;
    }
    /**
     * Returns the version number of configuration file.
     * @return string The version number of configuration file.
     * @since 1.2
     */
    public function getConfigVersion(){
        return $this->configVision;
    }
    /**
     * Checks if the system is configured or not.
     * @return boolean <b>TRUE</b> if the system is configured.
     * @since 1.0
     */
    public function isConfig(){
        return $this->isConfigured;
    }
    /**
     * Returns the name of the database.
     * @return string Database name.
     * @since 1.0
     */
    public function getDBName(){
        return $this->dbName;
    }
    /**
     * Returns the name of database host.
     * @return string Database host.
     * @since 1.0
     */
    public function getDBHost(){
        return $this->dbHost;
    }
    /**
     * Returns the name of the database user.
     * @return string Database username.
     * @since 1.0
     */
    public function getDBUser(){
        return $this->dbUser;
    }
    /**
     * Returns the password of the database user.
     * @return string Database password.
     * @since 1.0
     */
    public function getDBPassword(){
        return $this->dbPass;
    }
    /**
     * Returns framework version number.
     * @return string Framework version number.
     * @since 1.2
     */
    public function getLisksVersion(){
        return $this->lisksVersion;
    }
    /**
     * Returns framework version type.
     * @return string framework version type.
     * @since 1.2
     */
    public function getLisksVersionType(){
        return $this->lisksVersionType;
    }
    /**
     * Returns the date at which the framework is released.
     * @return string The date at which the framework is released.
     * @since 1.0
     */
    public function getLisksDate(){
        return $this->lisksDate;
    }', TRUE, TRUE);
        $fh->reduceTab();
        $fh->write('}', TRUE, TRUE);
        $fh->close();
    }
    /**
     * Checks if the application is setup or not.
     * @return boolean If the system is configured, the function will return 
     * <b>TRUE</b>. If it is not configured, It will return <b>FALSE</b>. Note 
     * that the function will throw an exception in case one of the 3 main 
     * configuration files is missing.
     * @throws Exception
     * @since 1.0
     */
    public function isSetupFinished(){
        if(class_exists('Config')){
            if(class_exists('MailConfig')){
                if(class_exists('SiteConfig')){
                    return Config::get()->isConfig();
                }
                throw new Exception('SiteConfig.php is missing.');
            }
            throw new Exception('MailConfig.php is missing.');
        }
        throw new Exception('Config.php is missing.');
    }
}