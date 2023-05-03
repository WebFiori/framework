<?php

namespace webfiori\framework\config;

/**
 * A class which acts as an interface between the application and configuration
 * driver.
 *
 * @author Ibrahim
 */
class Controller {
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
    public function __construct() {
        require_once './ConfigurationDriver.php';
        require_once './DefaultDriver.php';
        $this->setDriver(new DefaultDriver());
    }
    public function setDriver(ConfigurationDriver $driver) {
        $this->driver = $driver;
    }
    public function updateEnv() {
        $DS = DIRECTORY_SEPARATOR;
        //The class GlobalConstants must exist before autoloader.
        //For this reason, use the 'resource' instead of the class 'File'. 
        $path = ROOT_PATH.$DS.APP_DIR.$DS.'config'.$DS."Env.php";
        $resource = fopen($path, 'w');

        if (!is_resource($resource)) {
            require_once ROOT_PATH.$DS.'vendor'.$DS.'webfiori'.$DS.'framework'.$DS.'webfiori'.$DS.'framework'.$DS.'exceptions'.$DS.'InitializationException.php';
            throw new InitializationException('Unable to create the file "'.$path.'"');
        }
        
        $this->a($resource, [
            "<?php",
            '',
            "namespace ".APP_DIR."\\config;",
            '',
            "/**",
            " * A class which is used to initialize application environment variables as global constants.",
            " *",
            " * This class has one static method which is used to define environment variables.",
            " * The class can be used to initialize any constant that the application depends",
            " * on. The constants that this class will initialize are the constants which",
            " * uses the function <code>define()</code>.",
            " * Also, the developer can modify existing ones as needed to change some of the",
            " * default settings of application environment.",
            " *",
            " */",
            "class Env {"
        ]);
        $this->a($resource, [
            "/**",
            " * Initialize application environment variables.",
            " *",
            " * Include your own in the body of this method or modify existing ones",
            " * to suite your configuration. It is recommended to check if the global",
            " * constant is defined or not before defining it using the function",
            " * <code>defined</code>.",
            " */",
            "public static function defineEnvVars() {"
        ], 1);
        
        foreach ($this->getDriver()->getEnvVars() as $envVar) {
            $this->a($resource, "define('".$envVar['name']."', ".$envVar['value'].");", 2);
        }
        $this->a($resource, '}', 1);
        $this->a($resource, '}');
    }
    public function getDriver() : ConfigurationDriver {
        return $this->driver;
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
}
