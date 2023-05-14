<?php

namespace webfiori\framework\config;

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
        $this->driver = new JsonDriver();
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
