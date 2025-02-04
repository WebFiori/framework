<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2019 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace webfiori\framework;

use const DS;
use Error;
use Exception;
use const ROOT_PATH;
use const THEMES_PATH;
use webfiori\file\File;
use webfiori\framework\exceptions\InitializationException;
use webfiori\framework\exceptions\NoSuchThemeException;
use webfiori\framework\router\Router;
use webfiori\http\Response;


/**
 * A class which has utility methods which are related to themes loading.
 *
 * @author Ibrahim
 *
 * @version 1.0.1
 */
class ThemeLoader {
    /**
     * The directory where themes are located in.
     *
     * @since 1.0
     */
    const THEMES_DIR = 'themes';
    /**
     * An array that contains all available themes.
     *
     * @var array
     *
     * @since 1.0
     */
    private static $AvailableThemes;
    /**
     * An array that contains all loaded themes.
     *
     * @var array
     *
     * @since 1.0
     */
    private static $loadedThemes = [];
    private function __construct() {
    }

    /**
     * Returns an array that contains the metadata of all available themes.
     *
     * This method will return an associative array. The key is the theme
     * name and the value is an object of type Theme that contains theme info.
     *
     * @param bool $updateCache If set to true, cached data of descovered themes
     * will reset and search will be performed again.
     *
     * @return array An associative array that contains all themes information. The name
     * of the theme will be the key and the value is an object of type 'Theme'.
     *
     * @throws Exception
     * @since 1.0
     */
    public static function getAvailableThemes(bool $updateCache = true): array {
        if (self::$AvailableThemes === null || $updateCache) {
            self::$AvailableThemes = [];

            if (Util::isDirectory(THEMES_PATH, true)) {
                $themesDirs = array_diff(scandir(THEMES_PATH.DS), ['..', '.']);

                foreach ($themesDirs as $dir) {
                    $pathToScan = THEMES_PATH.DS.$dir;
                    $filesInDir = array_diff(scandir($pathToScan), ['..', '.']);
                    self::scanDir($filesInDir, $pathToScan, $dir);
                }
            } else {
                throw new InitializationException(THEMES_PATH.' is not a path or does not exist.');
            }
        }

        return self::$AvailableThemes;
    }
    /**
     * Returns an array which contains all loaded themes.
     *
     * @return array An associative array which contains all loaded themes.
     * The index will be theme name and the value is an object of type 'Theme'
     * which contains theme info.
     *
     * @since 1.0
     */
    public static function getLoadedThemes(): array {
        return self::$loadedThemes;
    }
    /**
     * Checks if a theme is loaded or not given its name.
     *
     * @param string $themeName The name of the theme.
     *
     * @return boolean The method will return true if
     * the theme was found in the array of loaded themes. false
     * if not.
     *
     * @since 1.0
     */
    public static function isThemeLoaded(string $themeName): bool {
        return isset(self::$loadedThemes[$themeName]) === true;
    }

    /**
     * Adds routes to all themes resource files (JavaSecript, CSS and images).
     *
     * The method will check for themes resources directories if set or not.
     * If set, it will scan each directory and add a route to it. For CSS and
     * JavaScript files, the routes will depend on the directory at which the
     * files are placed on. Assuming that the domain is 'example.com' and the
     * name of theme directory is 'my-theme' and the directory at which CSS
     * files are placed on is CSS, then any CSS file can be accessed using
     * 'https://example.com/my-theme/css/my-file.css'. For any other resources,
     * they can be accessed directly. Assuming that we have an
     * image file somewhere in images directory of the theme. The
     * image can be accessed as follows: 'https://example.com/my-image.png'. Note that
     * CSS, JS and images directories of the theme must be set to correctly create
     * the routes.
     *
     * @throws Exception
     * @since 1.0.1
     */
    public static function registerResourcesRoutes() {
        $availableThemes = self::getAvailableThemes();

        foreach ($availableThemes as $themeObj) {
            $themeDir = THEMES_PATH.DS.$themeObj->getDirectoryName();
            self::createAssetsRoutes($themeObj->getDirectoryName(), $themeDir, $themeObj->getJsDirName());
            self::createAssetsRoutes($themeObj->getDirectoryName(), $themeDir, $themeObj->getCssDirName());
            self::createAssetsRoutes($themeObj->getDirectoryName(), $themeDir, $themeObj->getImagesDirName());
        }
    }
    /**
     * Reset the array which contains all loaded themes.
     *
     * By calling this method, all loaded themes will be unloaded.
     *
     * @since 1.0
     */
    public static function resetLoaded() {
        self::$loadedThemes = [];
    }

    /**
     * Loads a theme given its name or class name.
     *
     * If the given name is null or empty string, the method will load the default theme as
     * which is set by application configuration.
     *
     * @param string|null $themeName The name of the theme. This also can be the name of
     * theme class including its namespace (e.g. Theme::class).
     *
     * @return Theme The method will return an object of type Theme once the
     * theme is loaded. The object will contain all theme information. If provided
     * theme name is empty string, the method will return null.
     *
     * @throws NoSuchThemeException The method will throw
     * @throws Exception
     * an exception if no theme was found which has the given name.
     *
     * @since 1.0
     */
    public static function usingTheme(?string $themeName = null) {
        $trimmedName = trim((string)$themeName);

        if (strlen($trimmedName) != 0) {
            $themeName = $trimmedName;
        } else {
            $themeName = App::getConfig()->getTheme();

            if (strlen($themeName) == 0) {
                return null;
            }
        }

        $themeToLoad = null;
        $xName = '\\'.trim($themeName, '\\');

        if (class_exists($xName)) {
            $tmpTheme = new $xName();

            if ($tmpTheme instanceof Theme) {
                $themeToLoad = $tmpTheme;
                $themeName = $themeToLoad->getName();
            }
        }

        if (self::isThemeLoaded($themeName)) {
            $themeToLoad = self::$loadedThemes[$themeName];
        } else if ($themeToLoad === null) {
            $themes = self::getAvailableThemes();

            if (isset($themes[$themeName])) {
                $themeToLoad = $themes[$themeName];
            } else {
                throw new NoSuchThemeException('No such theme: \''.$themeName.'\'.');
            }
        }

        if (isset($themeToLoad)) {
            self::$loadedThemes[$themeToLoad->getName()] = $themeToLoad;
            $themeToLoad->invokeBeforeLoaded();

            return $themeToLoad;
        }
    }
    private static function createAssetsRoutes($themeDirName, $themeRootDir, $dir) {
        if (strlen($dir) != 0 && Util::isDirectory($themeRootDir.DS.$dir)) {
            Router::closure([
                'path' => $themeDirName.'/'.$dir.'/{file-name}',
                'route-to' => function ($fileDir, $themeDirName, $dir)
                {
                    $fileName = Router::getParameterValue('file-name');

                    if (file_exists($fileDir.DS.$dir.DS.$fileName)) {
                        $file = new File($fileDir.DS.$dir.DS.$fileName);
                        $file->view();
                    } else {
                        Response::write('Resource "'.$themeDirName.'/'.$dir.'/'.$fileName.'" was not found.');
                        Response::setCode(404);
                    }
                },
                'closure-params' => [
                    ROOT_PATH.DS.self::THEMES_DIR.DS.$themeDirName,
                    $themeDirName,
                    $dir
                ]
            ]);
        }
    }

    /**
     * @throws Exception
     */
    private static function scanDir($filesInDir, $pathToScan, $dirName) {
        foreach ($filesInDir as $fileName) {
            $fileExt = substr($fileName, -4);

            if ($fileExt == '.php') {
                $cName = str_replace('.php', '', $fileName);
                ob_start();
                $ns = require_once $pathToScan.DS.$fileName;
                ob_end_clean();
                $aNs = gettype($ns) == 'string' ? $ns.'\\' : '\\';
                $aCName = $aNs.$cName;

                if (!class_exists($aCName)) {
                    $aCName = '\\'.self::THEMES_DIR.'\\'.$dirName.'\\'.$cName;
                }

                if (class_exists($aCName)) {
                    try {
                        $instance = new $aCName();
                    } catch (Error $ex) {
                        $instance = null;
                    } catch (Exception $ex) {
                        $instance = null;
                    }

                    if ($instance instanceof Theme) {
                        self::$AvailableThemes[$instance->getName()] = $instance;
                    }
                }
            }
        }
    }
}
