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
namespace WebFiori\Framework;

use WebFiori\File\exceptions\FileException;
use WebFiori\File\File;
use WebFiori\Framework\Config\ClassDriver;
use WebFiori\Json\Json;
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
    private static $DIR_TO_CREATE;
    /**
     * An instance of the class.
     *
     * @var Ini
     *
     */
    private static $singleton;
    private function __construct() {
        $this->docStart = '/**';
        $this->docEnd = ' **/';
        $this->docEmptyLine = " *";
        $this->blockEnd = '}';
    }


    /**
     * Creates all directories at which the application needs to run.
     */
    public static function createAppDirs() {
        $DS = DIRECTORY_SEPARATOR;
        self::mkdir(ROOT_PATH.$DS.APP_DIR);
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'Init');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'Init'.$DS.'Routes');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'Pages');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'Commands');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'Tasks');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'Middleware');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'Langs');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'Apis');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'Config');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'Storage');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'Storage'.$DS.'Uploads');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'Storage'.$DS.'Logs');
        self::mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'Storage'.$DS.'Sessions');
        self::mkdir(ROOT_PATH.$DS.'public');
        self::mkdir(ROOT_PATH.$DS.'tests');
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
        $cFile = new File("$className.php", APP_PATH.'Init');
        $cFile->remove();
        $cFile->create();
        ClassDriver::a($cFile, [
            "<?php",
            '',
            "namespace ".APP_DIR."\\Init;",
            '',
            "class $className {",

        ]);
        ClassDriver::a($cFile, [
            $this->docStart,
            " * $comment",
            $this->docEmptyLine,
            $this->docEnd,
            'public static function init() {'
        ], 1);
        ClassDriver::a($cFile, "", 3);
        ClassDriver::a($cFile, "}", 1);
        ClassDriver::a($cFile, "}");
        $cFile->create(true);
        $cFile->write();
        require_once APP_PATH.'Init'.DS."$className.php";
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
        $cFile = new File("$className.php", APP_PATH.'Init'.DS.'Routes');
        $cFile->remove();
        ClassDriver::a($cFile, "<?php");
        ClassDriver::a($cFile, "");
        ClassDriver::a($cFile, "namespace ".APP_DIR."\\Init\\Routes;");
        ClassDriver::a($cFile, "");
        ClassDriver::a($cFile, "use WebFiori\\Framework\\Router\\Router;");
        ClassDriver::a($cFile, "");
        ClassDriver::a($cFile, "class $className {");
        ClassDriver::a($cFile, $this->docStart, 1);
        ClassDriver::a($cFile, "     * Initialize system routes.");
        ClassDriver::a($cFile, $this->docEmptyLine, 1);
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
    public static function mkdir($dir) {
        self::$DIR_TO_CREATE = $dir;
        if (!is_dir($dir)) {
            set_error_handler(function (int $errno, string $errstr) {
                http_response_code(500);
                header('content-type:application/json');
                die('{'
                    . '"message":"Unable to create application directory due to an error: '.$errstr.'",'
                    . '"code":'.$errno.','
                    . '"dir":"'.Json::escapeJSONSpecialChars(self::$DIR_TO_CREATE).'"'
                    . '}');
            });
            mkdir($dir);
            restore_error_handler();
        }
    }
}
