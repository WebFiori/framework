<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2023 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace webfiori\framework;

use webfiori\file\exceptions\FileException;
use webfiori\file\File;
use webfiori\framework\config\ClassDriver;
/**
 * A class which is used to create application initialization classes.
 *
 * @author Ibrahim
 *
 */
class Ini {
    const NL = "\n";
    private $blockEnd;
    private $docEmptyLine;
    private $docEnd;
    private $docStart;
    private $since10;
    /**
     * An instance of the class.
     *
     * @var Ini
     *
     */
    private static $singleton;
    private function __construct() {
        
    }

    

    /**
     * Creates initialization class.
     *
     * Note that if routes class already exist, this method will override
     * existing file.
     *
     * @param string $className The name of the class.
     *
     * @param string $comment A PHPDoc comment for class method.
     *
     * @throws FileException
     */
    public function createIniClass(string $className, string $comment) {
        $cFile = new File("$className.php", APP_PATH.'ini');
        $cFile->remove();
        $cFile->create();
        ClassDriver::a($cFile, [
            "<?php",
            '',
            "namespace ".APP_DIR."\\ini;",
            '',
            "class $className {",

        ]);
        ClassDriver::a($cFile, [
            $this->docStart,
            " * $comment",
            $this->docEmptyLine,
            $this->since10,
            $this->docEnd,
            'public static function init() {'
        ], 1);
        ClassDriver::a($cFile, "", 3);
        ClassDriver::a($cFile, "}", 1);
        ClassDriver::a($cFile, "}");
        $cFile->create(true);
        $cFile->write();
        require_once APP_PATH.'ini'.DS."$className.php";
    }

    /**
     * Creates a file that holds class information which is used to create
     * routes.
     *
     * Note that if routes class already exist, this method will override
     * existing file.
     *
     * @param string $className The name of the class.
     *
     * @throws FileException
     */
    public function createRoutesClass(string $className) {
        $cFile = new File("$className.php", APP_PATH.'ini'.DS.'routes');
        $cFile->remove();
        ClassDriver::a($cFile, "<?php");
        ClassDriver::a($cFile, "");
        ClassDriver::a($cFile, "namespace ".APP_DIR."\\ini\\routes;");
        ClassDriver::a($cFile, "");
        ClassDriver::a($cFile, "use webfiori\\framework\\router\\Router;");
        ClassDriver::a($cFile, "");
        ClassDriver::a($cFile, "class $className {");
        ClassDriver::a($cFile, $this->docStart, 1);
        ClassDriver::a($cFile, "     * Initialize system routes.");
        ClassDriver::a($cFile, $this->docEmptyLine, 1);
        ClassDriver::a($cFile, $this->since10, 1);
        ClassDriver::a($cFile, $this->docEnd, 1);
        ClassDriver::a($cFile, "    public static function create() {");
        ClassDriver::a($cFile, "        //TODO: Add your own routes here.");
        ClassDriver::a($cFile, $this->blockEnd, 1);
        ClassDriver::a($cFile, "}");
        $cFile->create(true);
        $cFile->write();
        require_once $cFile->getAbsolutePath();
    }
    /**
     * Returns a single instance of the class.
     *
     * @return Ini
     *
     */
    public static function get(): Ini {
        if (self::$singleton === null) {
            self::$singleton = new Ini();
        }

        return self::$singleton;
    }

    
    /**
     * Creates all directories at which the application needs to run.
     */
    public static function createAppDirs() {
        $DS = DIRECTORY_SEPARATOR;
        self::mkdir(ROOT_PATH.$DS.APP_DIR);
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'ini');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'ini'.$DS.'routes');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'pages');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'commands');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'tasks');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'middleware');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'langs');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'apis');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'config');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'sto');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'sto'.$DS.'uploads');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'sto'.$DS.'logs');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'sto'.$DS.'sessions');
        self::mkdir(ROOT_PATH.$DS.'public');
    }
    public static function mkdir($dir) {
        if (!is_dir($dir)) {
            set_error_handler(function (int $errno, string $errstr)
            {
                http_response_code(500);
                die('Unable to create one or more of application directories due to an error: "Code: '.$errno.', Message: '.$errstr.'"');
            });
            mkdir($dir);
            restore_error_handler();
        }
    }
}
