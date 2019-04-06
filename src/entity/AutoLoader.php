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
 * @version 1.1.3
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
    public static function &get($options=array(
        'define-root'=>false,
        'search-folders'=>array(),
        'root'=>'',
        'on-load-failure'=>'do-nothing'
    )) {
        if(self::$loader === null){
            $frameworkSearchFoldres = array(
                '',
                '/entity',
                '/themes',
                '/functions',
                '/apis',
                '/pages',
                '/ini',
                '/conf'
            );
            $DS = DIRECTORY_SEPARATOR;
            if(isset($options['search-folders'])){
                foreach ($options['search-folders'] as $folder){
                    $frameworkSearchFoldres[] = $DS.trim(str_replace('\\', $DS, str_replace('/', $DS, $folder)),'/\\');
                }
            }
            $defineRoot = isset($options['define-root']) && $options['define-root'] === true ? true : false;
            $root = isset($options['root']) ? trim($options['root'],'\\/') : '';
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
    private function __construct($root='',$searchFolders=array(),$defineRoot=false,$onFail='throw-exception') {
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
            else if($defineRoot === true){
                $this->rootDir = __DIR__;
                foreach ($searchFolders as $folder){
                    $this->rootDir = str_replace($folder, '', $this->rootDir);
                }
                define('ROOT_DIR', $this->rootDir);
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
    }
    /**
     * Adds new search directory to the array of search 
     * folders.
     * @param string $dir A new directory (such as '/entity/html-php-structs-1.6/html').
     * @since 1.0
     * @deprecated since version 1.1.2
     */
    public function addSearchDirectory($dir,$incSubFolders=true) {
        $DS = DIRECTORY_SEPARATOR;
        if(strlen($dir) != 0){
            $cleanDir = $DS. trim(str_replace('\\', $DS, str_replace('/', $DS, $dir)), '\\/');
            if($incSubFolders){
                $dirsStack = array();
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
     * 
     * @param type $dir
     * @param type $incSubFolders
     * @since 1.1.2
     */
    public static function newSearchFolder($dir,$incSubFolders=true){
        self::get()->addSearchDirectory($dir,$incSubFolders);
    }
    /**
     * Tries to load a class given its name.
     * @param string $classPath The name of the class.
     * @since 1.0
     */
    private function loadClass($classPath){
        $DS = DIRECTORY_SEPARATOR;
        $cArr = explode('\\', $classPath);
        $className = $cArr[count($cArr) - 1];
        $loaded = false;
        foreach ($this->searchFolders as $value) {
            $f = $this->getRoot().$value.$DS.$className.'.php';
            //lower case class name to support loading of old-style classes.
            $f2 = $this->getRoot().$value.$DS. strtolower($className).'.php';
            if(file_exists($f)){
                require_once $f;
                $loaded = true;
                break;
            }
            else if(file_exists($f2)){
                require_once $f2;
                $loaded = true;
                break;
            }
        }
        if(!$loaded){
            if(is_callable($this->onFail)){
                call_user_func($this->onFail);
            }
            else if($this->onFail == 'throw-exception'){
                throw new Exception('Class \''.$classPath.'\' not found in any include directory. '
                    . 'Make sure that class path is included in auto-load directories.');
            }
            else if($this->onFail == 'do-nothing'){
                //do nothing
            }
        }
    }
    /**
     * Returns the root directory that is used to search inside.
     * @return string The root directory that is used to search inside.
     * @since 1.0
     */
    public function getRoot(){
        return $this->rootDir;
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