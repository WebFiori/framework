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
namespace webfiori\framework\autoload;

use Exception;
/**
 * An autoloader class to load classes as needed during runtime.
 *
 * The class aims to provide all needed utilities to autoload any classes
 * which are within the scope of the framework. In addition, the developer
 * can add his own custom folders to the autoloader.
 *
 * @author Ibrahim
 *
 */
class ClassLoader {
    /**
     * The name of the file that represents autoloader's cache.
     * @var string
     */
    const CACHE_NAME = 'class-loader.cache';
    /**
     * An array that contains the possible things that can be performed
     * if a class has failed to load.
     * The array has the following values:
     * <ul>
     * <li>throw-exception</li>,
     * <li>do-nothing</li>
     * </ul>
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
     */
    private $cacheArr;
    /**
     * An array that contains the names of all loaded class.
     *
     * @var array
     *
     */
    private $loadedClasses;
    /**
     * A single instance of the class 'ClassLoader'.
     *
     * @var ClassLoader
     *
     */
    private static $loader;
    /**
     * A string or callback that indicates what will happen if the loader
     * is unable to load a class.
     *
     * @var string|callable
     *
     */
    private $onFail;
    /**
     * The relative root directory that is used to search on.
     *
     * @var string
     *
     */
    private $rootDir;
    /**
     * An array of folders to search on.
     *
     * @var array
     *
     */
    private $searchFolders;

    /**
     *
     * @param string $root
     * @param array $searchFolders
     * @param bool $defineRoot
     * @param string $onFail
     * @throws ClassLoaderException
     * @throws Exception
     */
    private function __construct(string $root = '', array $searchFolders = [], bool $defineRoot = false, string $onFail = self::ON_FAIL_ACTIONS[0]) {
        $this->searchFolders = [];
        $this->cacheArr = [];
        $this->loadedClasses = [];
        require_once 'ClassLoaderException.php';
        require_once 'ClassInfo.php';

        if (defined('ROOT_PATH')) {
            $this->rootDir = ROOT_PATH;
        } else if (strlen($root) != 0 && is_dir($root)) {
            $this->rootDir = $root;

            if ($defineRoot === true) {
                define('ROOT_PATH', $this->rootDir);
            }
        } else {
            throw new ClassLoaderException('Unable to set root search folder.');
        }
        //Read Cache after setting root dir as it depends on it.
        $this->readCache();

        if (gettype($searchFolders) == 'array') {
            foreach ($searchFolders as $folder) {
                $this->addSearchDirectory($folder);
            }
        }
        spl_autoload_register(function($className)
        {
            ClassLoader::get()->loadClass($className);
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
            ClassInfo::NAME => 'ClassLoader',
            ClassInfo::NS => substr(self::class, 0, strlen(self::class) - strlen('ClassLoader') - 1),
            ClassInfo::PATH => __DIR__,
            ClassInfo::CACHED => false
        ];
        $this->loadedClasses[] = [
            ClassInfo::NAME => 'ClassInfo',
            ClassInfo::NS => substr(ClassInfo::class, 0, strlen(ClassInfo::class) - strlen('ClassInfo') - 1),
            ClassInfo::PATH => __DIR__,
            ClassInfo::CACHED => false
        ];
        $this->loadedClasses[] = [
            ClassInfo::NAME => 'ClassLoaderException',
            ClassInfo::NS => substr(ClassLoaderException::class, 0, strlen(ClassLoaderException::class) - strlen('ClassLoaderException') - 1),
            ClassInfo::PATH => __DIR__,
            ClassInfo::CACHED => false
        ];
    }
    /**
     * Load a class using its specified path.
     *
     * This method can be used in case the class that the user tries to load does
     * not comply with PSR-4 standard for placing classes in correct folder
     * with correct namespace. Once loaded, it will be added to the cache.
     *
     * @param string $className The name of the class that will be loaded.
     *
     * @param string $classWithNs The full name of the class including its namespace.
     *
     * @param string $path The path to PHP file that has class implementation.
     *
     * @return bool If file is exist and class is loaded, true is returned. False
     * otherwise.
     */
    public function addClassMap(string $className, string $classWithNs, string $path) : bool {
        $ns = count(explode('\\', $classWithNs)) == 1 ? '\\' : substr($classWithNs, 0, strlen($classWithNs) - strlen($className) - 1);

        if ($this->loadFromCache($ns, $className)) {
            return true;
        }

        if (!file_exists($path)) {
            return false;
        }
        require_once $path;

        $this->loadedClasses[] = [
            ClassInfo::NAME => $className,
            ClassInfo::NS => $ns,
            ClassInfo::PATH => $path,
            ClassInfo::CACHED => false
        ];

        return true;
    }
    /**
     * Returns a single instance of the class 'ClassLoader'.
     *
     * @param $options array An associative array of options that is used to initialize
     * the autoloader. The available options are:
     * <ul>
     * <li><b>root</b>: A directory that can be used as a base search folder.
     * Default is empty string. Ignored if the constant ROOT_PATH is defined.</li>
     * <li><b>search-folders</b>: An array which contains a set of folders to search
     * on. Default is an empty array.</li>
     * <li><b>define-root</b>: If set to true, The autoloader will try to define
     * the constant 'ROOT_PATH' based on autoload folders.
     * Default is false. Ignored if the constant ROOT_PATH is defined.</li>,
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
     * @return ClassLoader
     *
     * @throws Exception
     */
    public static function get(array $options = [
        'define-root' => false,
        'search-folders' => [],
        'root' => '',
        'on-load-failure' => self::ON_FAIL_ACTIONS[1]
    ]): ClassLoader {
        $DS = DIRECTORY_SEPARATOR;

        if (self::$loader === null) {
            if (!defined('APP_DIR')) {
                define('APP_DIR', 'app');
            }
            $appFolder = APP_DIR;
            $frameworkSearchFolders = [
                '',
                $DS.'webfiori'.$DS.'framework',
                $DS.'framework'.$DS.'exceptions',
                $DS.'framework'.$DS.'cli',
                $DS.'framework'.$DS.'ui',
                $DS.'framework',
                $DS.'themes',
                $DS.'logic',
                $DS.'apis',
                $DS.'pages',
                $DS.'ini',
                $DS.'libs',
                $DS.'config',
                $DS.$appFolder
            ];

            if (isset($options['search-folders'])) {
                foreach ($options['search-folders'] as $folder) {
                    $frameworkSearchFolders[] = $DS.trim(str_replace('\\', $DS, str_replace('/', $DS, $folder)),'/\\');
                }
            }
            $defineRoot = isset($options['define-root']) && $options['define-root'] === true;
            $root = isset($options['root']) ? trim($options['root'],'\\/') : trim(substr(__DIR__, 0, strlen(__DIR__) - strlen('\entity')),'\\/');

            if (strlen($root) != 0 && explode($DS, $root)[0] == 'home') {
                //linux
                $root = $DS.$root;
            }
            $onFail = $options['on-load-failure'] ?? self::ON_FAIL_ACTIONS[0];
            self::$loader = new ClassLoader($root, $frameworkSearchFolders, $defineRoot,$onFail);
            self::checkComposer();
        }

        return self::$loader;
    }
    /**
     * Returns an array that contains all cached classes information.
     *
     * The returned array will be associative. The keys of the array are the
     * names of the classes and the value of each key is a sub-indexed array.
     * The indexed array will contain the paths at which the class was found in.
     *
     * @return array An array that contains all cached classes information.
     * @throws Exception
     */
    public static function getCacheArray(): array {
        return self::get()->cacheArr;
    }

    /**
     * Returns the directory at which autoload cache file will be created at.
     *
     * @return string The directory at which autoload cache file will be created at.
     * @throws Exception
     */
    public static function getCachePath() : string {
        return self::get()->getRoot().DIRECTORY_SEPARATOR.APP_DIR.DIRECTORY_SEPARATOR.'sto'.DIRECTORY_SEPARATOR.self::CACHE_NAME;
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
     * @param bool $load If the class is not loaded and this parameter is set
     * to true, the method will attempt to load the class. Default is false.
     *
     * @return array An array that contains all paths to the files which have
     * a definition for the given class.
     *
     * @throws ClassLoaderException If $load is set to true and the class was not
     * @throws Exception
     * loaded.
     *
     */
    public static function getClassPath(string $className, string $namespace = null, bool $load = false): array {
        $retVal = [];

        if ($load === true) {
            try {
                self::get()->loadClass($namespace.'\\'.$className);
            } catch (Exception $ex) {
                throw new ClassLoaderException($ex->getMessage());
            }
        }
        $loadedClasses = self::getLoadedClasses();

        foreach ($loadedClasses as $classArr) {
            if ($namespace !== null) {
                if ($classArr[ClassInfo::NS] == $namespace && $classArr[ClassInfo::NAME] == $className) {
                    $retVal[] = $classArr[ClassInfo::PATH];
                }
            } else if ($classArr[ClassInfo::NAME] == $className) {
                $retVal[] = $classArr[ClassInfo::PATH];
            }
        }

        return $retVal;
    }

    /**
     * Returns an array of all added search folders.
     *
     * @return array An array of all added search folders.
     *
     * @throws Exception
     */
    public static function getFolders(): array {
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
     * @throws Exception
     */
    public static function getLoadedClasses(): array {
        return self::get()->loadedClasses;
    }

    /**
     * Checks if a class is loaded or not.
     *
     * @param string $class The name of the class. Note that it must not have
     * the namespace.
     *
     * @param string|null $ns An optional namespace to check if the class
     * exist in.
     *
     * @return bool If the class was already loaded, the method will return true.
     * Else, it will return false.
     *
     * @throws Exception
     */
    public  static function isLoaded(string $class, string $ns = null): bool {
        foreach (self::getLoadedClasses() as $classArr) {
            if ($ns !== null) {
                if ($class == $classArr[ClassInfo::NAME]
                        && $ns == $classArr[ClassInfo::NS]) {
                    return true;
                }
            } else if ($class == $classArr[ClassInfo::NAME]) {
                return true;
            }
        }

        return false;
    }
    /**
     * Checks if provided string represents a valid namespace or not.
     *
     * @param string $ns A string to be validated.
     *
     * @return bool If the provided string represents a valid namespace, the
     * method will return true. False if it does not represent a valid namespace.
     */
    public static function isValidNamespace(string $ns) {
        if ($ns == '\\') {
            return true;
        }
        $split = explode('\\', $ns);

        foreach ($split as $subNs) {
            $len = strlen($subNs);

            for ($x = 0 ; $x < $len ; $x++) {
                $char = $subNs[$x];

                if ($x == 0 && $char >= '0' && $char <= '9') {
                    return false;
                }

                if (!(($char <= 'Z' && $char >= 'A') || ($char <= 'z' && $char >= 'a') || ($char >= '0' && $char <= '9') || $char == '_')) {
                    return false;
                }
            }
        }

        return true;
    }
    /**
     * Load a class using its specified path.
     *
     * This method can be used in case the class that the user tries to load does
     * not comply with PSR-4 standard for placing classes in correct folder
     * with correct namespace. Once loaded, it will be added to the cache.
     *
     * @param string $className The name of the class that will be loaded.
     *
     * @param string $classWithNs The full name of the class including its namespace.
     *
     * @param string $filePath The path to PHP file that has class implementation.
     *
     * @return bool If file is exist and class is loaded, true is returned. False
     * otherwise.
     */
    public static function map(string $className, string $classWithNs, string $filePath) {
        self::get()->addClassMap($className, $classWithNs, $filePath);
    }
    /**
     * Load multiple classes from same path which belongs to same namespace.
     *
     * This helper method can be used to autoload classes which are non-PSR-4 compliant.
     *
     * @param string $ns The namespace at which classes belongs to.
     *
     * @param string $path The location at which all classes stored at.
     *
     * @param array $classes An array that holds the names of the classes.
     */
    public static function mapAll(string $ns, string $path, array $classes) {
        foreach ($classes as $className) {
            self::map($className, $ns.'\\'.$className, $path.DIRECTORY_SEPARATOR.$className.'.php');
        }
    }

    /**
     * Adds new folder to the set folder at which the autoloader will try to search
     * on for classes.
     *
     * @param string $dir A string that represents a directory. The directory
     * must be inside the scope of the framework.
     *
     * @param bool $incSubFolders If set to true, even sub-directories which
     * are inside the given directory will be included in the search.
     *
     * @throws Exception
     */
    public static function newSearchFolder(string $dir, bool $incSubFolders = true) {
        self::get()->addSearchDirectory($dir,$incSubFolders);
    }

    /**
     * Returns the root directory that is used to search inside.
     *
     * @return string The root directory that is used to search inside.
     *
     * @throws Exception
     */
    public static function root(): string {
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
     * @throws Exception
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
     *
     * @deprecated since version 1.1.2
     */
    private function addSearchDirectory(string $dir, $incSubFolders = true, $appendRoot = true) {
        $DS = DIRECTORY_SEPARATOR;

        if (strlen($dir) != 0) {
            if ($appendRoot === true) {
                $cleanDir = $DS.trim(str_replace('\\', $DS, str_replace('/', $DS, $dir)), '\\/');
            } else {
                $cleanDir = $dir;
            }

            if ($incSubFolders) {
                $this->addSearchDirectoryHelper($cleanDir, $appendRoot);
            } else {
                $this->searchFolders[$cleanDir] = $appendRoot;
            }
        }
    }
    private function addSearchDirectoryHelper($cleanDir, $appendRoot) {
        $dirsStack = [$cleanDir];
        $root = $this->getRoot();

        while ($xDir = array_pop($dirsStack)) {
            if ($appendRoot === true) {
                $fullPath = $root.$xDir;
            } else {
                $fullPath = $xDir;
            }

            if (is_dir($fullPath)) {
                $dirsStack = $this->addSearchDirectoryHelper2($xDir, $fullPath, $dirsStack, $appendRoot);
            }
        }
    }
    private function addSearchDirectoryHelper2($xDir, $fullPath, $dirsStack, $appendRoot) {
        $subDirs = scandir($fullPath);

        if (gettype($subDirs) == 'array') {
            foreach ($subDirs as $subDir) {
                if ($subDir != '.' && $subDir != '..') {
                    $dirsStack[] = $xDir.DIRECTORY_SEPARATOR.$subDir;
                }
            }
        }
        $this->searchFolders[$xDir] = $appendRoot;

        return $dirsStack;
    }
    private function attemptCreateCache($autoloadCachePath, $autoloadCache) {
        if (!file_exists($autoloadCachePath)) {
            mkdir($autoloadCachePath, 0777, true);
        }

        if (!file_exists($autoloadCache) && is_writable($autoloadCachePath)) {
            $h = fopen($autoloadCache, 'w');

            if (is_resource($h)) {
                fclose($h);
            }
        }
    }
    private static function checkComposer() {
        $composerVendors = self::getComposerVendorDirs();

        foreach ($composerVendors as $vendorFolder) {
            self::$loader->addSearchDirectory($vendorFolder, true, false);
        }
    }
    private function createNSFromPath(string $filePath, string $className) {
        $split = explode(DIRECTORY_SEPARATOR, $filePath);
        $nsArr = ['\\'.$className];
        $currentNs = '';

        foreach ($split as $str) {
            if (self::isValidNamespace($str)) {
                if (strlen($currentNs) == 0) {
                    $currentNs = '\\'.$str;
                } else {
                    $currentNs = $currentNs.'\\'.$str;
                }
                $nsArr[] = $currentNs.'\\'.$className;
            }
        }
        $currentNs = '';

        for ($x = count($split) - 1 ; $x > -1 ; $x--) {
            $str = $split[$x];

            if (self::isValidNamespace($str)) {
                if (strlen($currentNs) == 0) {
                    $currentNs = '\\'.$str;
                } else {
                    $currentNs = '\\'.$str.$currentNs;
                }
                $nsArr[] = $currentNs.'\\'.$className;
            }
        }

        return $nsArr;
    }
    /**
     * Returns an array string that contains all possible paths for the folder
     * 'vendor'.
     *
     * @return array
     *
     */
    private static function getComposerVendorDirs(): array {
        $DS = DIRECTORY_SEPARATOR;
        $split = explode($DS, ROOT_PATH);
        $vendorPath = $split[0].$DS;
        $pathsCount = count($split);
        $vendorFound = false;
        $vendorFolderName = 'vendor';
        $vendorDirs = [];

        for ($x = 1 ; $x < $pathsCount; $x++) {
            $xDir = $vendorPath.$vendorFolderName;

            if (is_dir($xDir)) {
                $vendorFound = true;
                $vendorDirs[] = $xDir;
            }

            $vendorPath .= $split[$x].$DS;
        }


        if (!$vendorFound && is_dir($vendorPath.$vendorFolderName)) {
            $vendorDirs[] = $vendorPath.$vendorFolderName;
        }

        return array_reverse($vendorDirs);
    }
    /**
     * Returns the root directory that is used to search inside.
     *
     * @return string The root directory that is used to search inside.
     *
     */
    private function getRoot(): string {
        return $this->rootDir;
    }

    /**
     * Tries to load a class given its name.
     *
     * @param string $classWithNs The name of the class alongside its namespace.
     *
     * @throws ClassLoaderException
     * @throws Exception
     */
    private function loadClass(string $classWithNs) {
        $cArr = explode('\\', $classWithNs);
        $className = $cArr[count($cArr) - 1];
        $classNs = implode('\\', array_slice($cArr, 0, count($cArr) - 1));

        if (self::isLoaded($className, $classNs)) {
            return;
        }

        $loaded = false;
        //checks if the class is cached or not.
        if ($this->loadFromCache($classWithNs, $className)) {
            return;
        }

        $allPaths = self::getClassPath($className);

        foreach ($this->searchFolders as $value => $appendRoot) {
            $loaded = $this->loadClassHelper($className, $classWithNs, $value, $appendRoot, $allPaths) || $loaded;
        }

        if ($loaded === false) {
            if (is_callable($this->onFail)) {
                call_user_func($this->onFail);
            } else if ($this->onFail == self::ON_FAIL_ACTIONS[0]) {
                throw new ClassLoaderException('Class \''.$classWithNs.'\' not found in any include directory. '
                .'Make sure that class path is included in auto-load directories and its namespace is correct.');
            }
        } else {
            $this->updateCacheHelper();
        }
    }
    /**
     *
     * @param string $className The name of the class to load.
     * @param string $classWithNs Class name including its namespace.
     * @param string $value A path to directory to check in.
     * @param bool $appendRoot  If set to true, root directory will be
     * appended to file path.
     * @param array $allPaths An array that holds pathes to classes which has
     * same name.
     *
     * @return bool True if loaded. False if not.
     */
    private function loadClassHelper(string $className, string $classWithNs, string $value, bool $appendRoot, array $allPaths): bool {
        $loaded = false;
        $DS = DIRECTORY_SEPARATOR;

        if ($appendRoot === true) {
            $f = $this->getRoot().$value.$DS.$className.'.php';
        } else {
            $f = $value.$DS.$className.'.php';
        }
        $isFileLoaded = in_array($f, $allPaths);

        if (!$isFileLoaded && file_exists($f)) {
            $nsFromPath = $this->createNSFromPath($f, $className);

            if (in_array('\\'.$classWithNs, $nsFromPath)) {
                require_once $f;
                $ns = count(explode('\\', $classWithNs)) == 1 ? '\\' : substr($classWithNs, 0, strlen($classWithNs) - strlen($className) - 1);
                $this->loadedClasses[] = [
                    ClassInfo::NAME => $className,
                    ClassInfo::NS => $ns,
                    ClassInfo::PATH => $f,
                    ClassInfo::CACHED => false
                ];
                $loaded = true;
            }
        }

        return $loaded;
    }
    private function loadFromCache($classNS, $className): bool {
        $loaded = false;

        if (isset($this->cacheArr[$classNS])) {
            foreach ($this->cacheArr[$classNS] as $location) {
                if (file_exists($location)) {
                    require_once $location;
                    $ns = count(explode('\\', $classNS)) == 1 ? '\\' : substr($classNS, 0, strlen($classNS) - strlen($className) - 1);
                    $this->loadedClasses[] = [
                        ClassInfo::NAME => $className,
                        ClassInfo::NS => $ns,
                        ClassInfo::PATH => $location,
                        ClassInfo::CACHED => true
                    ];
                    $loaded = true;
                }
            }
        }

        return $loaded;
    }
    private function parseCacheString($str) {
        $cacheArr = explode("\n", $str);

        foreach ($cacheArr as $ca) {
            if (strlen(trim($ca)) !== 0) {
                $exploded = explode('=>', $ca);
                //Index 0 of the explode will contain the path to PHP class.
                //Index 1 of the explode will contain class namespace.
                if (isset($this->cacheArr[$exploded[1]])) {
                    if (!in_array($exploded[0], $this->cacheArr[$exploded[1]])) {
                        $this->cacheArr[$exploded[1]][] = $this->getRoot().$exploded[0];
                    }
                } else {
                    //The cache array hold namespace as index and a set of
                    //Pathes to the same class.
                    $this->cacheArr[$exploded[1]] = [
                        $this->getRoot().$exploded[0]
                    ];
                }
            }
        }
    }
    /**
     * Read the file which contains autoloader cached content.
     *
     */
    private function readCache() {
        $autoloadCachePath = $this->getRoot().DIRECTORY_SEPARATOR.APP_DIR.DIRECTORY_SEPARATOR.'sto';
        $autoloadCache = $autoloadCachePath.DIRECTORY_SEPARATOR.self::CACHE_NAME;
        //For first run, the cache file might not exist.
        if (file_exists($autoloadCache)) {
            $cacheStr = file_get_contents($autoloadCache);
            $this->parseCacheString($cacheStr);
        } else {
            $this->attemptCreateCache($autoloadCachePath, $autoloadCache);
        }
    }

    /**
     * Updates autoloder's cache file content.
     *
     * This method is called every time a new class is loaded to update the cache.
     *
     * @throws Exception
     */
    private function updateCacheHelper() {
        $autoloadCache = self::getCachePath();

        if (file_exists($autoloadCache)) {
            $h = @fopen($autoloadCache, 'w');
            $root = $this->getRoot();

            if (is_resource($h)) {
                foreach ($this->loadedClasses as $classArr) {
                    $path = substr($classArr[ClassInfo::PATH], strlen($root)).'=>';

                    if ($classArr[ClassInfo::NS] == '\\') {
                        //A class without a namespace
                        fwrite($h, $path.$classArr[ClassInfo::NAME]."\n");
                    } else {
                        fwrite($h, $path.$classArr[ClassInfo::NS].'\\'.$classArr[ClassInfo::NAME]."\n");
                    }
                }
                fclose($h);
            }
        }
    }
}
