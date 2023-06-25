<?php

namespace webfiori\framework\config;

use webfiori\framework\App;
use webfiori\framework\exceptions\InitializationException;

/**
 * A class which acts as an interface between the application and configuration
 * driver.
 *
 * @author Ibrahim
 */
class Controller {
    const NL = "\n";
    private $driver;
    private static $singleton;
    /**
     * Returns a single instance of the class.
     * 
     * @return Controller
     * 
     */
    public static function get(): Controller {
        if (self::$singleton === null) {
            self::$singleton = new Controller();
        }

        return self::$singleton;
    }
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        $driverClazz = App::getConfigDriver();
        $this->driver = new $driverClazz();
        $this->driver->initialize();
    }
    /**
     * Sets the driver that will be used to read and write configuration.
     * 
     * @param ConfigurationDriver $driver Configuration driver.
     */
    public static function setDriver(ConfigurationDriver $driver) {
        self::get()->driver = $driver;
        $driver->initialize();
    }
    /**
     * Creates a copy of current configuration driver to another one.
     * 
     * @param ConfigurationDriver $new An instance of the driver at which the
     * active configuration will be copied to. Note that if the driver is
     * same as active one, nothing will be copied.
     */
    public function copy(ConfigurationDriver $new) {
        $current = $this->getDriver();
        if (get_class($current) == get_class($new)) {
            return;
        }
        foreach ($current->getDBConnections() as $connObj) {
            $new->addOrUpdateDBConnection($connObj);
        }
        foreach ($current->getSMTPConnections() as $connObj) {
            $new->addOrUpdateSMTPAccount($connObj);
        }
        foreach ($current->getAppNames() as $langCode => $name) {
            $new->setAppName($name, $langCode);
        }
        foreach ($current->getDescriptions() as $langCode => $desc) {
            $new->setDescription($desc, $langCode);
        }
        foreach ($current->getEnvVars() as $name => $probs) {
            $new->addEnvVar($name, $probs['value'], $probs['description']);
        }
        $new->setPrimaryLanguage($current->getPrimaryLanguage());
        $new->setTheme($current->getTheme());
        $new->setSchedulerPassword($current->getSchedulerPassword());
        $new->setHomePage($current->getHomePage());
        $new->setTitleSeparator($current->getTitleSeparator());
        $new->initialize(true);
    }
    /**
     * Reads application environment variables and updates the class which holds
     * application environment variables.
     * 
     * @throws InitializationException
     */
    public static function updateEnv() {
        
        foreach (self::getDriver()->getEnvVars() as $name => $envVar) {
            if (!defined($name)) {
                define($name, $envVar['value']);
            }
        }
    }
    public function addEnvVar(string $name, $value, string $description = null) {
        $this->getDriver()->addEnvVar($name, $value, $description);
    }
    /**
     * Returns the driver that was set to read and write application configuration.
     * 
     * @return ConfigurationDriver
     */
    public static function getDriver() : ConfigurationDriver {
        return self::get()->driver;
    }
    
}
