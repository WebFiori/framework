<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework;

use Exception;
use WebFiori\Framework\Autoload\ClassLoader;
use WebFiori\Framework\Exceptions\InitializationException;

/**
 * Handles application bootstrapping: path constants, encoding, autoloader, and version info.
 *
 * @author Ibrahim
 */
class AppBootstrapper {
    /**
     * Boot the application environment.
     *
     * Defines path constants, sets encoding, initializes the autoloader,
     * checks standard libraries, and sets up I/O streams.
     *
     * @param string $appFolder The name of the application folder.
     * @param string $publicFolder The name of the public folder.
     * @param string $indexDir The directory where index.php exists.
     */
    public static function boot(string $appFolder = 'App', string $publicFolder = 'public', string $indexDir = __DIR__): void {
        self::initEncoding();
        self::defineConstants($appFolder, $publicFolder, $indexDir);
        self::initAutoLoader();
        self::checkStandardLibs();
        self::checkStdInOut();
        self::initFrameworkVersionInfo();
    }
    /**
     * Defines framework version constants.
     */
    public static function initFrameworkVersionInfo(): void {
        if (!defined('WF_VERSION')) {
            define('WF_VERSION', '3.0.0-RC.5');
        }

        if (!defined('WF_VERSION_TYPE')) {
            define('WF_VERSION_TYPE', 'RC');
        }

        if (!defined('WF_RELEASE_DATE')) {
            define('WF_RELEASE_DATE', '2026-05-31');
        }
    }
    /**
     * Sets MB encoding to UTF-8.
     */
    private static function initEncoding(): void {
        if (function_exists('mb_internal_encoding')) {
            $encoding = 'UTF-8';
            mb_internal_encoding($encoding);
            mb_http_output($encoding);
            mb_regex_encoding($encoding);
        }
    }
    /**
     * Defines path and directory constants.
     *
     * @param string $appFolder Application folder name.
     * @param string $publicFolder Public folder name.
     * @param string $indexDir Index directory path.
     */
    private static function defineConstants(string $appFolder, string $publicFolder, string $indexDir): void {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }

        if (!defined('ROOT_PATH')) {
            if ($indexDir == __DIR__) {
                $indexDir = self::getRoot().DS.$publicFolder;
            }

            define('ROOT_PATH', substr($indexDir, 0, strlen($indexDir) - strlen(DS.$publicFolder)));
        }

        if (!defined('APP_DIR')) {
            define('APP_DIR', $appFolder);
        }

        if (!defined('APP_PATH')) {
            define('APP_PATH', ROOT_PATH.DIRECTORY_SEPARATOR.APP_DIR.DS);
        }

        if (!defined('PUBLIC_FOLDER')) {
            define('PUBLIC_FOLDER', $publicFolder);
        }

        if (!defined('WF_CORE_PATHS')) {
            define('WF_CORE_PATHS', [
                ROOT_PATH.DS.'vendor'.DS.'webfiori'.DS.'framework'.DS.'WebFiori'.DS.'Framework',
                ROOT_PATH.DS.'WebFiori'.DS.'Framework'
            ]);
        }
    }
    /**
     * Verifies that all required standard libraries are available.
     *
     * @throws InitializationException If a required library is missing.
     */
    private static function checkStandardLibs(): void {
        $standardLibsClasses = [
            'WebFiori/collections' => 'WebFiori\\Collections\\Node',
            'WebFiori/ui' => 'WebFiori\\Ui\\HTMLNode',
            'WebFiori/jsonx' => 'WebFiori\\Json\\Json',
            'WebFiori/database' => 'WebFiori\\Database\\ResultSet',
            'WebFiori/http' => 'WebFiori\\Http\\Response',
            'WebFiori/file' => 'WebFiori\\File\\File',
            'WebFiori/mailer' => 'WebFiori\\Mail\\SMTPAccount',
            'WebFiori/cli' => 'WebFiori\\Cli\\Command',
            'WebFiori/cache' => 'WebFiori\\Cache\\Cache'
        ];

        foreach ($standardLibsClasses as $lib => $class) {
            if (!class_exists($class)) {
                throw new InitializationException("The standard library '$lib' is missing.");
            }
        }
    }
    /**
     * Checks and initializes standard input and output streams.
     */
    private static function checkStdInOut(): void {
        if (!defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'r'));
        }

        if (!defined('STDOUT')) {
            define('STDOUT', fopen('php://stdout', 'w'));
        }

        if (!defined('STDERR')) {
            define('STDERR', fopen('php://stderr', 'w'));
        }
    }
    /**
     * Calculates application root path.
     *
     * @return string The application root path.
     */
    private static function getRoot(): string {
        $DS = DIRECTORY_SEPARATOR;
        $vendorPath = $DS.'vendor'.$DS.'webFiori'.$DS.'framework'.$DS.'WebFiori'.$DS.'Framework';

        return substr(__DIR__, 0, strlen(__DIR__) - strlen($vendorPath));
    }
    /**
     * Initializes the class autoloader.
     *
     * @throws Exception If autoloader class cannot be found.
     */
    private static function initAutoLoader(): void {
        Ini::createAppDirs();

        if (class_exists('WebFiori\Framework\Autoload\ClassLoader', false)) {
            return;
        }
        $isLoaded = false;

        foreach (WF_CORE_PATHS as $path) {
            $autoloader = $path.DIRECTORY_SEPARATOR.'Autoload'.DIRECTORY_SEPARATOR.'ClassLoader.php';

            if (file_exists($autoloader)) {
                require_once $autoloader;
                ClassLoader::get();
                $isLoaded = true;
            }

            if (!class_exists(APP_DIR.'\\Ini\\AutoLoad')) {
                Ini::get()->createIniClass('AutoLoad', 'Add user-defined directories to the set of directories at which the framework will search for classes.');
            }
            App::call(APP_DIR.'\\Ini\\AutoLoad::initialize');
        }

        if (!$isLoaded) {
            throw new Exception('Unable to locate the autoloader class.');
        }
    }
}
