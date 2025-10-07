<?php
namespace WebFiori\Framework\Config;

use Exception;
use WebFiori\Framework\App;
use WebFiori\Framework\Exceptions\InitializationException;

/**
 * A class which acts as an interface between the application and configuration
 * driver.
 *
 * @author Ibrahim
 */
class Controller {
    const NL = "\n";
    /**
     *
     * @var ConfigurationDriver
     */
    private $driver;
    private static $singleton;
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        $driverClazz = App::getConfigDriver();
        $this->driver = new $driverClazz();
        $this->init($this->driver);
    }
    /**
     * Adds new environment variable to the configuration of the app.
     *
     * @param string $name The name of the variable such as 'MY_VAR'.
     *
     * @param mixed $value The value of the variable.
     *
     * @param string|null $description An optional text that describes the variable.
     */
    public function addEnvVar(string $name, $value, ?string $description = null) {
        $this->getDriver()->addEnvVar($name, $value, $description);
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
        $this->init($new, true);
    }
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
     * Returns the driver that was set to read and write application configuration.
     *
     * @return ConfigurationDriver
     */
    public static function getDriver() : ConfigurationDriver {
        return self::get()->driver;
    }
    /**
     * Sets the driver that will be used to read and write configuration.
     *
     * @param ConfigurationDriver $driver Configuration driver.
     */
    public static function setDriver(ConfigurationDriver $driver) {
        self::get()->driver = $driver;
        self::init($driver);
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
                if (isset($envVar['value'])) {
                    define($name, $envVar['value']);
                    putenv($name.'='.$envVar['value']);
                } else {
                    define($name, null);
                    putenv($name.'=');
                }
            }
        }
    }
    private static function init(ConfigurationDriver $driver, bool $reCreate = false) {
        try {
            $driver->initialize($reCreate);
        } catch (Exception $ex) {
            throw new InitializationException('Unable to initialize configuration driver due to an error: '.$ex->getMessage(), $ex->getCode(), $ex);
        }
    }
}
