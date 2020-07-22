<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
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
namespace webfiori\entity;

use webfiori\entity\exceptions\NoSuchThemeException;
use webfiori\entity\router\Router;
use webfiori\WebFiori;


/**
 * A class which has utility methods which are related to themes loading.
 *
 * @author Ibrahim
 * @version 1.0.1
 */
class ThemeLoader {
    /**
     * The directory where themes are located in.
     * @since 1.0
     */
    const THEMES_DIR = 'themes';
    /**
     * An array that contains all available themes.
     * @var array 
     * @since 1.0
     */
    private static $AvailableThemes;
    /**
     * An array that contains all loaded themes.
     * @var array
     * @since 1.0 
     */
    private static $loadedThemes = [];
    private function __construct() {
    }
    /**
     * Returns an array that contains the meta data of all available themes. 
     * This method will return an associative array. The key is the theme 
     * name and the value is an object of type Theme that contains theme info.
     * @return array An associative array that contains all themes information. The name 
     * of the theme will be the key and the value is an object of type 'Theme'.
     * @since 1.0
     */
    public static function getAvailableThemes() {
        if (self::$AvailableThemes === null) {
            self::$AvailableThemes = [];
            $DS = DIRECTORY_SEPARATOR;
            $themesDirs = array_diff(scandir(THEMES_PATH), ['..', '.']);
            
            foreach ($themesDirs as $dir) {
                $pathToScan = THEMES_PATH.$DS.$dir;
                $filesInDir = array_diff(scandir($pathToScan), ['..', '.']);
                self::_scanDir($filesInDir, $pathToScan);
            }
        }

        return self::$AvailableThemes;
    }
    /**
     * Returns an array which contains all loaded themes.
     * @return array An associative array which contains all loaded themes. 
     * The index will be theme name and the value is an object of type 'Theme' 
     * which contains theme info.
     * @since 1.0
     */
    public static function getLoadedThemes() {
        return self::$loadedThemes;
    }
    /**
     * Checks if a theme is loaded or not given its name.
     * @param string $themeName The name of the theme.
     * @return boolean The method will return true if 
     * the theme was found in the array of loaded themes. false
     * if not.
     * @since 1.0
     */
    public static function isThemeLoaded($themeName) {
        return isset(self::$loadedThemes[$themeName]) === true;
    }
    /**
     * Reset the array which contains all loaded themes.
     * By calling this method, all loaded themes will be unloaded.
     * @since 1.0
     */
    public static function resetLoaded() {
        self::$loadedThemes = [];
    }
    /**
     * Loads a theme given its name.
     * If the given name is null, the method will load the default theme as 
     * specified by the method SiteConfig::getBaseThemeName().
     * @param string $themeName The name of the theme. 
     * @return Theme The method will return an object of type Theme once the 
     * theme is loaded. The object will contain all theme information.
     * @throws NoSuchThemeException The method will throw 
     * an exception if no theme was found which has the given name.
     * @since 1.0
     */
    public static function usingTheme($themeName = null) {
        if ($themeName === null) {
            $themeName = WebFiori::getSiteConfig()->getBaseThemeName();
        }
        $themeToLoad = null;

        if (self::isThemeLoaded($themeName)) {
            $themeToLoad = self::$loadedThemes[$themeName];
        } else {
            $themes = self::getAvailableThemes();

            if (isset($themes[$themeName])) {
                $themeToLoad = $themes[$themeName];
            } else {
                throw new NoSuchThemeException('No such theme: \''.$themeName.'\'.');
            }
        }
        if (isset($themeToLoad)) {
            $themeToLoad->invokeBeforeLoaded();

            $themeDir = THEMES_PATH.DS.$themeToLoad->getDirectoryName();
            foreach ($themeToLoad->getComponents() as $component) {
                if (file_exists($themeDir.DS.$component)) {
                    require_once $themeDir.DS.$component;
                }
            }
            
            return $themeToLoad;
        }
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
    private static function createAssetsRoutes($themeDirName, $themeRootDir, $dir) {
        if (strlen($dir) != 0 && Util::isDirectory($themeRootDir.DS.$dir)) {
            $filesInDir = array_diff(scandir($themeRootDir.DS.$dir), ['.','..']);
            foreach ($filesInDir as $fileName) {
                $fileExp = explode('.', $fileName);
                if (count($fileExp) == 2 && (strtolower($fileExp[1]) == 'js' || strtolower($fileExp[1]) == 'css')) {
                    Router::addRoute([
                        'path' => $themeDirName.DS.$fileName,
                        'route-to' => self::THEMES_DIR.DS.$themeDirName.DS.$dir.DS.$fileName
                    ]);
                } else {
                    Router::addRoute([
                        'path' => $fileName,
                        'route-to' => self::THEMES_DIR.DS.$themeDirName.DS.$dir.DS.$fileName
                    ]);
                }
            }
        }
    }
    private static function _scanDir($filesInDir, $pathToScan) {
        foreach ($filesInDir as $fileName) {
            $fileExt = substr($fileName, -4);

            if ($fileExt == '.php') {
                $cName = str_replace('.php', '', $fileName);
                $ns = require_once $pathToScan.DIRECTORY_SEPARATOR.$fileName;
                $aNs = gettype($ns) == 'string' ? $ns.'\\' : '\\';
                $aCName = $aNs.$cName;
                if (!AutoLoader::isLoaded($aCName) && class_exists($aCName)) {
                    $instance = new $aCName();

                    if ($instance instanceof Theme) {
                        self::$AvailableThemes[$instance->getName()] = $instance;
                    }
                }
            }
        }
    }
}
