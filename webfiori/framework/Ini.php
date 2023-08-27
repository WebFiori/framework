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
        $this->a($cFile, [
            "<?php",
            '',
            "namespace ".APP_DIR."\\ini;",
            '',
            "class $className {",

        ]);
        $this->a($cFile, [
            $this->docStart,
            " * $comment",
            $this->docEmptyLine,
            $this->since10,
            $this->docEnd,
            'public static function init() {'
        ], 1);
        $this->a($cFile, "", 3);
        $this->a($cFile, "}", 1);
        $this->a($cFile, "}");
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
        $this->a($cFile, "<?php");
        $this->a($cFile, "");
        $this->a($cFile, "namespace ".APP_DIR."\\ini\\routes;");
        $this->a($cFile, "");
        $this->a($cFile, "use webfiori\\framework\\router\\Router;");
        $this->a($cFile, "");
        $this->a($cFile, "class $className {");
        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Initialize system routes.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public static function create() {");
        $this->a($cFile, "        //TODO: Add your own routes here.");
        $this->a($cFile, $this->blockEnd, 1);
        $this->a($cFile, "}");
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

    
    /**
     * Creates all directories at which the application needs to run.
     */
    public static function createAppDirs() {
        $DS = DIRECTORY_SEPARATOR;
        $this->mkdir(ROOT_PATH.$DS.APP_DIR);
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'ini');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'ini'.$DS.'routes');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'pages');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'commands');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'tasks');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'middleware');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'langs');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'apis');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'config');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'sto');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'sto'.$DS.'uploads');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'sto'.$DS.'logs');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'sto'.$DS.'sessions');
        $this->mkdir(ROOT_PATH.$DS.'public');
    }
    private function mkdir($dir) {
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
