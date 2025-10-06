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

use Error;
use Exception;
use webfiori\file\File;
use webfiori\framework\exceptions\NoSuchThemeException;
use webfiori\framework\router\RouteOption;
use webfiori\framework\router\Router;
use webfiori\http\Response;

/**
 * A class which manages theme registration and loading.
 *
 * @author Ibrahim
 *
 * @version 2.0.0
 */
class ThemeManager {
    /**
     * An array that contains all registered themes.
     *
     * @var array
     *
     * @since 2.0
     */
    private static $registeredThemes = [];

    private function __construct() {
    }

    /**
     * Returns an array which contains all registered themes.
     *
     * @return array An associative array which contains all registered themes.
     * The index will be theme name and the value is an object of type 'Theme'
     * which contains theme info.
     *
     * @since 2.0
     */
    public static function getRegisteredThemes(): array {
        return self::$registeredThemes;
    }

    /**
     * Checks if a theme is registered or not given its name.
     *
     * @param string $themeName The name of the theme.
     *
     * @return boolean The method will return true if
     * the theme was found in the array of registered themes. false
     * if not.
     *
     * @since 2.0
     */
    public static function isThemeRegistered(string $themeName): bool {
        return isset(self::$registeredThemes[$themeName]) === true;
    }

    /**
     * Registers a theme for lazy loading.
     *
     * @param Theme|string $theme Theme instance or class name/namespace
     *
     * @throws NoSuchThemeException If theme cannot be registered
     *
     * @since 2.0
     */
    public static function register(Theme|string $theme): void {
        if ($theme instanceof Theme) {
            $themeInstance = $theme;
        } else if (is_string($theme)) {
            $className = '\\' . trim($theme, '\\');
            
            if (!class_exists($className)) {
                throw new NoSuchThemeException("Theme class '$className' does not exist.");
            }

            try {
                $themeInstance = new $className();
            } catch (\Throwable $ex) {
                throw new NoSuchThemeException("Failed to instantiate theme class '$className': " . $ex->getMessage());
            }

            if (!($themeInstance instanceof Theme)) {
                throw new NoSuchThemeException("Class '$className' is not an instance of Theme.");
            }
        } else {
            throw new NoSuchThemeException("Theme must be an instance of Theme or a valid class name string.");
        }

        $themeName = $themeInstance->getName();
        
        if (empty($themeName)) {
            throw new NoSuchThemeException("Theme name cannot be empty.");
        }

        if (self::isThemeRegistered($themeName)) {
            throw new NoSuchThemeException("Theme '$themeName' is already registered.");
        }

        self::$registeredThemes[$themeName] = $themeInstance;
    }

    /**
     * Reset the array which contains all registered themes.
     *
     * By calling this method, all registered themes will be cleared.
     *
     * @since 2.0
     */
    public static function resetRegistered() {
        self::$registeredThemes = [];
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
    private static function registerResourcesRoutes(Theme $theme) {
        $assetsFolderName = 'assets';
        $publicPath = ROOT_PATH.DS.PUBLIC_FOLDER.DS.$assetsFolderName.DS;
        $themeResourcesPath = $theme->getAbsolutePath().DS.$assetsFolderName.DS;
        $themePublicPath = $publicPath.DS.$theme->getDirectoryName();

        if (!is_dir($themePublicPath)) {
            mkdir($themePublicPath, 0777);
            $jsDir = $themeResourcesPath.$theme->getJsDirName();
            if (is_dir($jsDir)) {
                mkdir($themePublicPath.DS.$theme->getJsDirName());
                copy($jsDir, $themePublicPath.DS.$theme->getJsDirName());
            }
            $cssDir = $themeResourcesPath.$theme->getCssDirName();
            if (is_dir($cssDir)) {
                mkdir($themePublicPath.DS.$theme->getCssDirName());
                copy($cssDir, $themePublicPath.DS.$theme->getCssDirName());
            }
            $ImagesDir = $themeResourcesPath.$theme->getImagesDirName();
            if (is_dir($ImagesDir)) {
                mkdir($themePublicPath.DS.$theme->getImagesDirName());
                copy($ImagesDir, $themePublicPath.DS.$theme->getImagesDirName());
            }
        }

        self::createAssetsRoutes($theme->getDirectoryName(), $theme->getJsDirName());
        self::createAssetsRoutes($theme->getDirectoryName(), $theme->getCssDirName());
        self::createAssetsRoutes($theme->getDirectoryName(), $theme->getImagesDirName());
    }
    private static function createAssetsRoutes($themeDirName, $dir) {
        Router::closure([
            RouteOption::PATH => $themeDirName.'/'.$dir.'/{file-name}',
            RouteOption::TO => function ($fileDir, $themeDirName, $dir) {
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
                ROOT_PATH.DS.PUBLIC_FOLDER.DS.$themeDirName,
                $themeDirName,
                $dir
            ]
        ]);
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

        if (class_exists($xName, false)) {
            
            foreach (self::$registeredThemes as $theme) {
                $clazz = '\\'.get_class($theme);
                
                if ($clazz == $xName) {
                    return $theme;
                }
            }
        }

        
        $themes = self::getRegisteredThemes();

        if (isset($themes[$themeName])) {
            $themeToLoad = $themes[$themeName];
        } else {
            throw new NoSuchThemeException('No such theme: \''.$themeName.'\'.');
        }

        if (isset($themeToLoad)) {
            self::registerResourcesRoutes($themeToLoad);
            $themeToLoad->invokeBeforeLoaded();

            return $themeToLoad;
        }
    }
}
