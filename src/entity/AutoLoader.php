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

use Exception;
use webfiori\entity\exceptions\ClassLoaderException;
/**
 * An autoloader class to load classes as needed during runtime.
 * 
 * The class aims to provide all needed utilities to autoload any classes 
 * which are within the scope of the framework. In addition, the developer 
 * can add his own custom folders to the autoloader. More to that, the autoloder 
 * will load any class which exist in the folder 'vendor' if composer 
 * is used to collect the dependencies. To activate this feature, the constant 
 * 'LOAD_COMPOSER_PACKAGES' must be defined and set to true. The class can be used independent of 
 * any other component to load classes.
 * 
 * @author Ibrahim
 * 
 * @version 1.1.7
 */
class AutoLoader {
    /**
     * The name of the file that represents autoloder's cache.
     * @var string 
     * @since 1.1.6
     */
    const CACHE_NAME = 'autoload.cache';
    /**
     * An array that contains the possible things that can be performed 
     * if a class has failed to load.
     * The array has the following values:
     * <ul>
     * <li>throw-exception</li>,
     * <li>do-nothing</li>
     * </ul>
     * @since 1.1.6
     */
    const ON_FAIL_ACTIONS = [
        'throw-exception',
        'do-nothing'
    ];
    /**
     * An associative array that contains the info which was taken 
     * from autoloader's cache file.
     * This one is used to fasten the process of loading classes.
     * 
     * @var array 
     * 
     * @since 1.1.6
     */
    private $casheArr;
    /**
     * An array that contains the names of indices that are used by loaded class 
     * info array.
     * 
     * The array have the following indices:
     * <ul>
     * <li>class-name</li>
     * <li>namespace</li>
     * <li>path</li>
     * <li>loaded-from-cache</li>
     * </ul>
     * 
     * @var array 
     */
    private static $CLASS_INDICES = [
        'class-name',
        'namespace',
        'path',
        'loaded-from-cache'
    ];
    /**
     * An array that contains the names of all loaded class.
     * 
     * @var array
     * 
     * @since 1.1.4
     */
    private $loadedClasses;
    /**
     * A single instance of the class 'AutoLoader'.
     * 
     * @var AutoLoader
     * 
     * @since 1.0 
     */
    private static $loader;
    /**
     * A string or callback that indicates what will happen if the loader 
     * is unable to load a class.
     * 
     * @var string|callable
     * 
     * @since 1.1.3 
     */
    private $onFail;
    /**
     * The relative root directory that is used to search on.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private $rootDir;
    /**
     * An array of folders to search on.
     * 
     * @var array
     * 
     * @since 1.0 
     */
    private $searchFolders;
    /**
     * 
     * @param type $root
     * @param type $searchFolders
     * @param type $defineRoot
     * @throws Exception
     * @since 1.0
     */
    private function __construct($root = '',$searchFolders = [],$defineRoot = false,$onFail = self::ON_FAIL_ACTIONS[0]) {
        $this->searchFolders = [];
        $this->casheArr = [];
        $this->loadedClasses = [];
        require_once 'exceptions'.DIRECTORY_SEPARATOR.'ClassLoaderException.php';

        if (defined('ROOT_DIR')) {
            $this->rootDir = ROOT_DIR;
        } else if (strlen($root) != 0 && is_dir($root)) {
            $this->rootDir = $root;

            if ($defineRoot === true) {
                define('ROOT_DIR', $this->rootDir);
            }
        } else {
            throw new ClassLoaderException('Unable to set root search folder.');
        }
        //Read cashe after setting root dir as it depends on it.
        $this->_readCache();
        if (gettype($searchFolders) == 'array') {
            foreach ($searchFolders as $folder) {
                $this->addSearchDirectory($folder, true);
            }
        }
        spl_autoload_register(function($className)
        {
            AutoLoader::get()->loadClass($className);
        });

        if (gettype($onFail) == 'string') {
            $this->onFail = strtolower($onFail);

            if ($this->onFail != self::ON_FAIL_ACTIONS[1] && $this->onFail != self::ON_FAIL_ACTIONS[0]) {
                $this->onFail = self::ON_FAIL_ACTIONS[0];
            }
        } else if (is_callable($onFail)) {
                $this->onFail = $onFail;
            } else {
                $this->onFail = self::ON_FAIL_ACTIONS[0];
            }
        $this->loadedClasses[] = [
            self::$CLASS_INDICES[0] => 'AutoLoader',
            self::$CLASS_INDICES[1] => 'webfiori\\entity',
            self::$CLASS_INDICES[2] => __DIR__,
            self::$CLASS_INDICES[3] => false
        ];
    }
    /**
     * Returns a single instance of the class 'AutoLoader'.
     * 
     * @param $options An associative array of options that is used to initialize 
     * the autoloader. The available options are:
     * <ul>
     * <li><b>root</b>: A directory that can be used as a base search folder. 
     * Default is empty string. Ignored if the constant ROOT_DIR is defined.</li>
     * <li><b>search-folders</b>: An array which contains a set of folders to search 
     * on. Default is an empty array.</li>
     * <li><b>define-root</b>: If set to true, The autoloader will try to define 
     * the constant 'ROOT_DIR' based on the autoload folders. 
     * Default is false. Ignored if the constant ROOT_DIR is defined.</li>,
     * <li>
     * <b>on-load-failure</b>: An attribute that will be used if the 
     * loader is unable to load the class. Possible values are:
     * <ul>
     * <li>'do-nothing'</li>
     * <li>'throw-exception'</li>
     * <li>A callable that will be called when the class loader is unable 
     * to load the class.</li>
     * </ul>
     * </li>
     * </ul>
     * 
     * @return AutoLoader
     * 
     * @throws Exception 
     */
    public static function get($options = [
        'define-root' => false,
        'search-folders' => [],
        'root' => '',
        'on-load-failure' => self::ON_FAIL_ACTIONS[1]
    ]) {
        $DS = DIRECTORY_SEPARATOR;

        if (self::$loader === null) {
            $frameworkSearchFoldres = [
                '',
                $DS.'entity'.$DS.'exceptions',
                $DS.'entity'.$DS.'cli',
                $DS.'entity'.$DS.'ui',
                $DS.'entity',
                $DS.'themes',
                $DS.'logic',
                $DS.'apis',
                $DS.'pages',
                $DS.'ini',
                $DS.'conf',
                $DS.'app'
            ];

            if (isset($options['search-folders'])) {
                foreach ($options['search-folders'] as $folder) {
                    $frameworkSearchFoldres[] = $DS.trim(str_replace('\\', $DS, str_replace('/', $DS, $folder)),'/\\');
                }
            }
            $defineRoot = isset($options['define-root']) && $options['define-root'] === true ? true : false;
            $root = isset($options['root']) ? trim($options['root'],'\\/') : trim(substr(__DIR__, 0, strlen(__DIR__) - strlen('\entity')),'\\/');

            if (strlen($root) != 0 && explode($DS, $root)[0] == 'home') {
                //linux 
                $root = $DS.$root;
            }
            $onFail = isset($options['on-load-failure']) ? $options['on-load-failure'] : self::ON_FAIL_ACTIONS[0];
            self::$loader = new AutoLoader($root, $frameworkSearchFoldres, $defineRoot,$onFail);
            self::_checkComposer();
        }

        return self::$loader;
    }
    /**
     * Returns an array that contains all cashed classes information.
     * 
     * The returned array will be associative. The keys of the array are the 
     * names of the classes and the value of each key is a sub-indexed array. 
     * The indexed array will contains the paths at which the class was found in.
     * 
     * @return array An array that contains all cashed classes information.
     * @since 1.1.7
     */
    public static function getCasheArray() {
        return self::get()->casheArr;
    }
    /**
     * Returns an array that contains the paths to all files which has a class 
     * with the given name.
     * 
     * Note that the method will only return the path to a loaded class only.
     * 
     * @param string $className The name of the class.
     * 
     * @param string|null $namespace If specified, the search will only be specific 
     * to the given namespace. This means the array will have one path most 
     * probably. Default is null.
     * 
     * @param boolean $load If the class is not loaded and this parameter is set 
     * to true, the method will attempt to load the class. Default is false.
     * 
     * @return array An array that contains all paths to the files which have 
     * a definition for the given class.
     * 
     * @since 1.0
     */
    public static function getClassPath($className,$namespace = null,$load = false) {
        $retVal = [];

        if ($load === true) {
            try {
                self::get()->loadClass($namespace.'\\'.$className);
            } catch (Exception $ex) {
            }
        }
        $loadedClasses = self::getLoadedClasses();

        foreach ($loadedClasses as $classArr) {
            if ($namespace !== null) {
                if ($classArr[self::$CLASS_INDICES[1]] == $namespace && $classArr[self::$CLASS_INDICES[0]] == $className) {
                    $retVal[] = $classArr[self::$CLASS_INDICES[2]];
                }
            } else {
                if ($classArr[self::$CLASS_INDICES[0]] == $className) {
                    $retVal[] = $classArr[self::$CLASS_INDICES[2]];
                }
            }
        }

        return $retVal;
    }
    /**
     * Returns an array of all added search folders.
     * 
     * @return array An array of all added search folders.
     * 
     * @since 1.1.1
     */
    public static function getFolders() {
        $folders = [];

        foreach (self::get()->searchFolders as $f => $appendRoot) {
            if ($appendRoot === true) {
                $folders[] = self::get()->getRoot().$f;
            } else {
                $folders[] = $f;
            }
        }

        return $folders;
    }
    /**
     * Returns an indexed array of all loaded classes.
     * 
     * At each index, there will be an associative array. 
     * Each sub array will have the following indices:
     * <ul>
     * <li><b>class-name</b>: The actual name of the class.</li>
     * <li><b>namespace</b>: The namespace at which the class belongs to.</li>
     * <li><b>path</b>: The location of the file that represents the class.</li>
     * </ul>
     * 
     * @return array An associative array that contains loaded classes info.
     * 
     * @since 1.1.4
     */
    public static function getLoadedClasses() {
        return self::get()->loadedClasses;
    }
    /**
     * Checks if a class is loaded or not.
     * 
     * @param string $class The name of the class. Note that it must have 
     * the namespace.
     * 
     * @return boolean If the class was already loaded, the method will return true. 
     * Else, it will return false.
     * 
     * @since 1.1.5
     */
    public  static function isLoaded($class) {
        foreach (self::getLoadedClasses() as $classArr) {
            if ($class == $classArr[self::$CLASS_INDICES[1]].'\\'.$classArr[self::$CLASS_INDICES[0]]) {
                return true;
            }
        }

        return false;
    }
    /**
     * Adds new folder to the set folder at which the autoloader will try to search 
     * on for classes.
     * 
     * @param string $dir A string that represents a directory. The directory 
     * must be inside the scope of the framework.
     * 
     * @param boolean $incSubFolders If set to true, even sub-directories which 
     * are inside the given directory will be included in the search.
     * 
     * @since 1.1.2
     */
    public static function newSearchFolder($dir,$incSubFolders = true) {
        self::get()->addSearchDirectory($dir,$incSubFolders);
    }
    /**
     * Returns the root directory that is used to search inside.
     * 
     * @return string The root directory that is used to search inside.
     * 
     * @since 1.1.5
     */
    public static function root() {
        return self::get()->getRoot();
    }
    /**
     * Sets what will happen in case a class was failed to load.
     * 
     * @param Closure|string $onFail It can be a PHP function or one of 
     * the following values:
     * <ul>
     * <li>do-nothing</li>
     * <li>throw-exception</li>
     * </ul>
     * 
     * @since 1.1.5
     */
    public static function setOnFail($onFail) {
        if (is_callable($onFail)) {
            self::get()->onFail = $onFail;
        } else {
            $lower = strtolower(trim($onFail));

            if ($lower == self::ON_FAIL_ACTIONS[0] || self::ON_FAIL_ACTIONS[1]) {
                self::get()->onFail = $lower;
            }
        }
    }
    private function _addSearchDirectoryHelper($cleanDir, $appendRoot) {
        $dirsStack = [$cleanDir];

        while ($xDir = array_pop($dirsStack)) {
            if ($appendRoot === true) {
                $fullPath = $this->getRoot().$xDir;
            } else {
                $fullPath = $xDir;
            }

            if (is_dir($fullPath)) {
                $dirsStack = $this->_addSrachDirectoryHelper2($xDir, $fullPath, $dirsStack, $appendRoot);
            }
        }
    }
    private function _addSrachDirectoryHelper2($xDir, $fullPath, $dirsStack, $appendRoot) {
        $subDirs = scandir($fullPath);

        foreach ($subDirs as $subDir) {
            if ($subDir != '.' && $subDir != '..') {
                $dirsStack[] = $xDir.DIRECTORY_SEPARATOR.$subDir;
            }
        }
        $this->searchFolders[$xDir] = $appendRoot;

        return $dirsStack;
    }
    private static function _checkComposer() {
        if (defined('LOAD_COMPOSER_PACKAGES') && LOAD_COMPOSER_PACKAGES === true) {
            $composerVendors = self::_getComposerVendorDirs();

            foreach ($composerVendors as $vendorFolder) {
                self::$loader->addSearchDirectory($vendorFolder, true, false);
            }
        }
    }
    /**
     * Returns an array string that contains all possible paths for the folder 
     * 'vendor'.
     * 
     * @return array
     * 
     * @since 1.1.6
     */
    private static function _getComposerVendorDirs() {
        $DS = DIRECTORY_SEPARATOR;
        $split = explode($DS, ROOT_DIR);
        $vendorPath = '';
        $pathsCount = count($split);
        $vendorFound = false;
        $vendorFolderName = 'vendor';
        $vendorDirs = [];

        for ($x = 0 ; $x < $pathsCount; $x++) {
            if (is_dir($vendorPath.$vendorFolderName)) {
                $vendorFound = true;
                $vendorDirs[] = $vendorPath.$vendorFolderName;
            }

            if ($x + 1 == $pathsCount) {
                $vendorPath .= $split[$x];
            } else {
                $vendorPath .= $split[$x].$DS;
            }
        }

        if (!$vendorFound && is_dir($vendorPath.$vendorFolderName)) {
            $vendorDirs[] = $vendorPath.$vendorFolderName;
        }
        
        return array_reverse($vendorDirs);
    }
    private function _loadClassHelper($className, $classWithNs, $value, $appendRoot, $allPaths) {
        $loaded = false;
        $DS = DIRECTORY_SEPARATOR;
        $root = $this->getRoot();
        
        if ($appendRoot === true) {
            $f = $root.$value.$DS.$className.'.php';
        } else {
            $f = $value.$DS.$className.'.php';
        }

        if (file_exists($f) && !in_array($f, $allPaths)) {
            require_once $f;
            $ns = count(explode('\\', $classWithNs)) == 1 ? '\\' : substr($classWithNs, 0, strlen($classWithNs) - strlen($className) - 1);
            $this->loadedClasses[] = [
                self::$CLASS_INDICES[0] => $className,
                self::$CLASS_INDICES[1] => $ns,
                self::$CLASS_INDICES[2] => $f,
                self::$CLASS_INDICES[3] => false
            ];
            $loaded = true;
        }
        return $loaded;
    }
    private function _loadFromCache($classNS, $className) {
        $loaded = false;
        
        if (isset($this->casheArr[$classNS])) {
            foreach ($this->casheArr[$classNS] as $location) {
                if (file_exists($location)) {
                    require_once $location;
                    $ns = count(explode('\\', $classNS)) == 1 ? '\\' : substr($classNS, 0, strlen($classNS) - strlen($className) - 1);
                    $this->loadedClasses[] = [
                        self::$CLASS_INDICES[0] => $className,
                        self::$CLASS_INDICES[1] => $ns,
                        self::$CLASS_INDICES[2] => $location,
                        self::$CLASS_INDICES[3] => true
                    ];
                    $loaded = true;
                }
            }
        }
        
        return $loaded;
    }
    /**
     * Read the file which contains autoloader cached content.
     * 
     * @since 1.1.6
     */
    private function _readCache() {
        $autoloadCache = $this->getRoot().DIRECTORY_SEPARATOR.self::CACHE_NAME;
        //For first run, the cache file might not exist.
        if (file_exists($autoloadCache)) {
            $casheStr = file_get_contents($autoloadCache);

            $cacheArr = explode("\n", $casheStr);

            foreach ($cacheArr as $ca) {
                if (strlen(trim($ca)) !== 0) {
                    $exploded = explode('=>', $ca);
                    //Index 0 of the explode will contain the path to PHP class.
                    //Index 1 of the explode will contain class namespace.
                    if (isset($this->casheArr[$exploded[1]])) {
                        if (!in_array($exploded[0], $this->casheArr[$exploded[1]])) {
                            $this->casheArr[$exploded[1]][] = $exploded[0];
                        }
                    } else {
                        //The cashe array hold namespace as index and a set of 
                        //Pathes to the same class.
                        $this->casheArr[$exploded[1]] = [
                            $exploded[0]
                        ];
                    }
                }
            }
        }
    }
    /**
     * Updates autoloder's cache file content.
     * 
     * This method is called every time a new class is loaded to update the cache.
     * 
     * @since 1.1.6
     */
    private function _updateCache() {
        $autoloadCache = $this->getRoot().DIRECTORY_SEPARATOR.self::CACHE_NAME;
        $h = fopen($autoloadCache, 'w');

        foreach ($this->loadedClasses as $classArr) {
            if ($classArr[self::$CLASS_INDICES[1]] == '\\') {
                //A class without a namespace
                fwrite($h, $classArr[self::$CLASS_INDICES[2]].'=>'.$classArr[self::$CLASS_INDICES[0]]."\n");
            } else {
                fwrite($h, $classArr[self::$CLASS_INDICES[2]].'=>'.$classArr[self::$CLASS_INDICES[1]].'\\'.$classArr[self::$CLASS_INDICES[0]]."\n");
            }
        }
        fclose($h);
    }
    /**
     * Adds new search directory to the array of search 
     * folders.
     * 
     * @param string $dir A new directory (such as '/entity/html-php-structs-1.6/html').
     * 
     * @param  string $incSubFolders If set to true, even sub-folders will 
     * be included in the search.
     * 
     * @param string $appendRoot If set to true, Root directory of the search will 
     * be added as a prefix to the path.
     * 
     * @since 1.0
     * 
     * @deprecated since version 1.1.2
     */
    private function addSearchDirectory($dir,$incSubFolders = true,$appendRoot = true) {
        $DS = DIRECTORY_SEPARATOR;

        if (strlen($dir) != 0) {
            if ($appendRoot === true) {
                $cleanDir = $DS.trim(str_replace('\\', $DS, str_replace('/', $DS, $dir)), '\\/');
            } else {
                $cleanDir = $dir;
            }

            if ($incSubFolders) {
                $this->_addSearchDirectoryHelper($cleanDir, $appendRoot);
            } else {
                $this->searchFolders[$cleanDir] = $appendRoot;
            }
        }
    }
    /**
     * Returns the root directory that is used to search inside.
     * 
     * @return string The root directory that is used to search inside.
     * 
     * @since 1.0
     */
    private function getRoot() {
        return $this->rootDir;
    }
    /**
     * Tries to load a class given its name.
     * 
     * @param string $classWithNs The name of the class alongside its namespace.
     * 
     * @since 1.0
     */
    private function loadClass($classWithNs) {
        
        if (self::isLoaded($classWithNs)) {
            return;
        }
        $cArr = explode('\\', $classWithNs);
        $className = $cArr[count($cArr) - 1];
        $loaded = false;
        //checks if the class is cached or not.
        if ($this->_loadFromCache($classWithNs, $className)) {
            return;
        }
        
        $allPaths = self::getClassPath($className);

        foreach ($this->searchFolders as $value => $appendRoot) {
            $loaded = $this->_loadClassHelper($className, $classWithNs, $value, $appendRoot, $allPaths);

            if (!$loaded) {
                $loaded = $this->_loadClassHelper(strtolower($className), $classWithNs, $value, $appendRoot, $allPaths);
            }

            if ($loaded && (PHP_MAJOR_VERSION < 7 || (PHP_MAJOR_VERSION == 7 && PHP_MINOR_VERSION <= 3))) {
                //in php 7.2 and lower, if same class is loaded 
                //from two namespaces with same name, it will 
                //rise a fatal error with message 
                // 'Cannot redeclare class'
                break;
            }
        }

        if ($loaded === false) {
            if (is_callable($this->onFail)) {
                call_user_func($this->onFail);
            } else if ($this->onFail == self::ON_FAIL_ACTIONS[0]) {
                throw new ClassLoaderException('Class \''.$classWithNs.'\' not found in any include directory. '
                .'Make sure that class path is included in auto-load directories and its namespace is correct.');
            }
        } else {
            $this->_updateCache();
        }
    }
}
