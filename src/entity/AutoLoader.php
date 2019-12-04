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
/**
 * An autoloader class to load classes as needed during runtime.
 *
 * @author Ibrahim
 * @version 1.1.5
 */
class AutoLoader{
    /**
     * A string or callback that indicates what will happen if the loader 
     * is unable to load a class.
     * @var string|callable
     * @since 1.1.3 
     */
    private $onFail;
    /**
     * An array of folders to search on.
     * @var array
     * @since 1.0 
     */
    private $searchFolders;
    /**
     * The relative root directory that is used to search on.
     * @var string
     * @since 1.0 
     */
    private $rootDir;
    /**
     * An array that contains the names of all loaded class.
     * @var array
     * @since 1.1.4
     */
    private $loadedClasses;
    /**
     * A single instance of the class 'AutoLoader'.
     * @var AutoLoader
     * @since 1.0 
     */
    private static $loader;
    /**
     * Returns a single instance of the class 'AutoLoader'.
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
     * @return AutoLoader
     * @throws Exception 
     */
    public static function get($options=[
        'define-root'=>false,
        'search-folders'=>[],
        'root'=>'',
        'on-load-failure'=>'do-nothing'
    ]) {
        $DS = DIRECTORY_SEPARATOR;
        if(self::$loader === null){
            $frameworkSearchFoldres = [
                '',
                $DS.'entity',
                $DS.'themes',
                $DS.'logic',
                $DS.'apis',
                $DS.'pages',
                $DS.'ini',
                $DS.'conf'
            ];
            
            if(isset($options['search-folders'])){
                foreach ($options['search-folders'] as $folder){
                    $frameworkSearchFoldres[] = $DS.trim(str_replace('\\', $DS, str_replace('/', $DS, $folder)),'/\\');
                }
            }
            $defineRoot = isset($options['define-root']) && $options['define-root'] === true ? true : false;
            $root = isset($options['root']) ? trim($options['root'],'\\/') : trim(substr(__DIR__, 0, strlen(__DIR__) - strlen('\entity')),'\\/');
            if(strlen($root) != 0 && explode($DS, $root)[0] == 'home'){
                //linux 
                $root = $DS.$root;
            }
            $onFail = isset($options['on-load-failure']) ? $options['on-load-failure'] : 'throw-exception';
            self::$loader = new AutoLoader($root, $frameworkSearchFoldres, $defineRoot,$onFail);
        }
        return self::$loader;
    }
    /**
     * 
     * @param type $root
     * @param type $searchFolders
     * @param type $defineRoot
     * @throws Exception
     * @since 1.0
     */
    private function __construct($root='',$searchFolders=[],$defineRoot=false,$onFail='throw-exception') {
        $this->searchFolders = [];
        $this->loadedClasses = [];
        if(defined('ROOT_DIR')){
            $this->rootDir = ROOT_DIR;
        }
        else{
            if(strlen($root) != 0 && is_dir($root)){
                $this->rootDir = $root;
                if($defineRoot === true){
                    define('ROOT_DIR', $this->rootDir);
                }
            }
            else{
                throw new Exception('Unable to set root search folder.');
            }
        }
        if(gettype($searchFolders) == 'array'){
            foreach ($searchFolders as $folder){
                $this->addSearchDirectory($folder);
            }
        }
        spl_autoload_register(function($className){
            AutoLoader::get()->loadClass($className);
        });
        if(gettype($onFail) == 'string'){
            $this->onFail = strtolower($onFail);
            if($this->onFail != 'do-nothing'){
                if($this->onFail != 'throw-exception'){
                    $this->onFail = 'throw-exception';
                }
            }
        }
        else if(is_callable($onFail)){
            $this->onFail = $onFail;
        }
        else{
            $this->onFail = 'throw-exception';
        }
        $this->loadedClasses[] = [
            'class-name'=>'AutoLoader',
            'namespace'=>'webfiori\\entity',
            'path'=>__DIR__
        ];
    }
    /**
     * Sets what will happen in case a class was failed to load.
     * @param Closure|string $onFail It can be a PHP function or one of 
     * the following values:
     * <ul>
     * <li>do-nothing</li>
     * <li>throw-exception</li>
     * </ul>
     * @since 1.1.5
     */
    public static function setOnFail($onFail) {
        if(is_callable($onFail)){
            self::get()->onFail = $onFail;
        }
        else{
            $lower = strtolower(trim($onFail));
            if($lower == 'throw-exception' || $lower == 'do-nothing'){
                self::get()->onFail = $lower;
            }
        }
    }
    /**
     * Adds new search directory to the array of search 
     * folders.
     * @param string $dir A new directory (such as '/entity/html-php-structs-1.6/html').
     * @since 1.0
     * @deprecated since version 1.1.2
     */
    private function addSearchDirectory($dir,$incSubFolders=true) {
        $DS = DIRECTORY_SEPARATOR;
        if(strlen($dir) != 0){
            $cleanDir = $DS. trim(str_replace('\\', $DS, str_replace('/', $DS, $dir)), '\\/');
            if($incSubFolders){
                $dirsStack = [];
                $dirsStack[] = $cleanDir;
                while($xDir = array_pop($dirsStack)){
                    $fullPath =  $this->getRoot().$xDir;
                    if(is_dir($fullPath)){
                        $subDirs = scandir($fullPath);
                        foreach ($subDirs as $subDir){
                            if($subDir != '.' && $subDir != '..'){
                                $dirsStack[] = $xDir.$DS.$subDir;
                            }
                        }
                        $this->searchFolders[] = $xDir;
                    }
                    else{
                        if(is_dir($fullPath)){
                            $subDirs = scandir($fullPath);
                            foreach ($subDirs as $subDir){
                                if($subDir != '.' && $subDir != '..'){
                                    $dirsStack[] = $xDir.$DS.$subDir;
                                }
                            }
                            $this->searchFolders[] = $xDir;
                        }
                    }
                }
            }
            else{
                $this->searchFolders[] = $cleanDir;
            }
        }
    }
    /**
     * Adds new folder to the set folder at which the autoloader will try to search 
     * on for classes.
     * @param string $dir A string that represents a dirictory. The directory 
     * must be inside the scope of the framework.
     * @param boolean $incSubFolders If set to true, even sub-directories which 
     * are inside the given directory will be included in the search.
     * @since 1.1.2
     */
    public static function newSearchFolder($dir,$incSubFolders=true){
        self::get()->addSearchDirectory($dir,$incSubFolders);
    }
    /**
     * Checks if a class is loaded or not.
     * @param string $class The name of the class. Note that it must have 
     * the namespace.
     * @return boolean If the class was already loaded, the method will return true. 
     * Else, it will return false.
     * @since 1.1.5
     */
    public  static function isLoaded($class) {
        foreach (self::getLoadedClasses() as $classArr){
            if($class == $classArr['namespace'].'\\'.$classArr['class-name']){
                return true;
            }
        }
        return false;
    }
    /**
     * Tries to load a class given its name.
     * @param string $classPath The name of the class alongside its namespace.
     * @since 1.0
     */
    private function loadClass($classPath){
        if(self::isLoaded($classPath)){
            return;
        }
        $DS = DIRECTORY_SEPARATOR;
        $cArr = explode('\\', $classPath);
        $className = $cArr[count($cArr) - 1];
        $loaded = false;
        $root = $this->getRoot();
        $allPaths = self::getClassPath($className);
        foreach ($this->searchFolders as $value) {
            $f = $root.$value.$DS.$className.'.php';
            if(file_exists($f) && !in_array($f, $allPaths)){
                require_once $f;
                $this->loadedClasses[] = [
                    'class-name'=>$className,
                    'namespace'=> substr($classPath, 0, strlen($classPath) - strlen($className) - 1),
                    'path'=>$f
                ];
                $loaded = true;
                if(PHP_MAJOR_VERSION < 7 || (PHP_MAJOR_VERSION == 7 && PHP_MINOR_VERSION < 3)){
                    //in php 7.2 and lower, if same class is loaded 
                    //from two namespaces with same name, it will 
                    //rise a fatal error with message 
                    // 'Cannot redeclare class'
                    break;
                }
            }
            else{
                //lower case class name to support loading of old-style classes.
                $f = $root.$value.$DS. strtolower($className).'.php';
                if(file_exists($f) && !in_array($f, $allPaths)){
                    require_once $f;
                    $this->loadedClasses[] = [
                        'class-name'=>$className,
                        'namespace'=> substr($classPath, 0, strlen($classPath) - strlen($className) - 1),
                        'path'=>$f
                    ];
                    $loaded = true;
                    if(PHP_MAJOR_VERSION < 7 || (PHP_MAJOR_VERSION == 7 && PHP_MINOR_VERSION < 3)){
                        break;
                    }
                }
            }
        }
        if(!$loaded){
            if(is_callable($this->onFail)){
                call_user_func($this->onFail);
            }
            else if($this->onFail == 'throw-exception'){
                throw new Exception('Class \''.$classPath.'\' not found in any include directory. '
                    . 'Make sure that class path is included in auto-load directories and its namespace is correct.');
            }
            else if($this->onFail == 'do-nothing'){
                //do nothing
            }
        }
    }
    /**
     * Returns an array that contains the paths to all files which has a class 
     * with the given name.
     * @param string $className The name of the class.
     * @param string|null $namespace If specified, the search will only be specific 
     * to the given namespace. This means the array will have one path most 
     * probably. Default is null.
     * @param boolean $load If the class is not loaded and this parameter is set 
     * to true, the method will attempt to load the class. Default is false.
     * @return array An array that contains all paths to the files which have 
     * a definition for the given class.
     */
    public static function getClassPath($className,$namespace=null,$load=false) {
        $retVal = [];
        if($load === true){
            try {
                self::get()->loadClass($namespace.'\\'.$className);
            } catch (Exception $ex) {
                
            }
        }
        $loadedClasses = self::getLoadedClasses();
        foreach ($loadedClasses as $classArr){
            if($namespace !== null){
                if($classArr['namespace'] == $namespace && $classArr['class-name'] == $className){
                    $retVal[] = $classArr['path'];
                }
            }
            else if($classArr['class-name'] == $className){
                $retVal[] = $classArr['path'];
            }
        }
        return $retVal;
    }
    /**
     * Returns an indexed array of all loaded classes.
     * At each index, there will be an associative array. 
     * Each sub array will have the following indices:
     * <ul>
     * <li><b>class-name</b>: The actual name of the class.</li>
     * <li><b>namespace</b>: The namespace at which the class belongs to.</li>
     * <li><b>path</b>: The location of the file that represents the class.</li>
     * </ul>
     * @return array An associative array that contains loaded classes info.
     * @since 1.1.4
     */
    public static function getLoadedClasses() {
        return self::get()->loadedClasses;
    }
    /**
     * Returns the root directory that is used to search inside.
     * @return string The root directory that is used to search inside.
     * @since 1.0
     */
    private function getRoot(){
        return $this->rootDir;
    }
    /**
     * Returns the root directory that is used to search inside.
     * @return string The root directory that is used to search inside.
     * @since 1.1.5
     */
    public static function root() {
        return self::get()->getRoot();
    }
    /**
     * Returns an array of all added search folders.
     * @return array An array of all added search folders.
     * @since 1.1.1
     */
    public static function getFolders() {
        $folders = array();
        foreach(self::get()->searchFolders as $f){
            $folders[] = self::get()->getRoot().$f;
        }
        return $folders;
    }
}