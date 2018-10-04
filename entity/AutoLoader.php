<?php
/**
 * An autoloader class to load classes as needed during runtime.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.1.1
 */
class AutoLoader{
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
     *
     * @var Logger 
     * @ssince 1.1
     */
    private static $logger;
    /**
     * Returns a single instance of the class 'AutoLoader'.
     * @param $options [Optional] An associative array of options that is used to initialize 
     * the autoloader. The available options are:
     * <ul>
     * <li><b>root</b>: A directory that can be used as a base search folder. 
     * Default is empty string. Ignored if the constant ROOT_DIR is defined.</li>
     * <li><b>search-folders</b>: An array which contains a set of folders to search 
     * on. Default is an empty array.</li>
     * <li><b>define-root</b>: If set to TRUE, The autoloader will try to define 
     * the constant 'ROOT_DIR' based on the autoload folders. 
     * Default is FALSE. Ignored if the constant ROOT_DIR is defined.</li>
     * </ul>
     * @return AutoLoader
     * @throws Exception 
     */
    public static function &get($options=array(
        'define-root'=>false,
        'search-folders'=>array(),
        'root'=>''
    )) {
        //Logger::logFuncCall(__METHOD__);
        if(self::$loader === NULL){
            $frameworkSearchFoldres = array(
                '',
                '/entity',
                '/entity/cron',
                '/entity/queries',
                '/entity/rest-easy',
                '/entity/jsonx',
                '/entity/ph-mysql',
                '/entity/html-php-structs/structs',
                '/entity/html-php-structs/html',
                '/entity/router',
                '/entity/mail',
                '/publish',
                '/publish/themes',
                '/functions',
                '/apis',
                '/pages',
            );
            if(isset($options['search-folders'])){
                foreach ($options['search-folders'] as $folder){
                    $frameworkSearchFoldres[] = '/'.trim($folder,'/');
                }
            }
            $defineRoot = isset($options['define-root']) && $options['define-root'] === TRUE ? TRUE : FALSE;
            $root = isset($options['define-root']) ? $options['define-root'] : '';
            self::$loader = new AutoLoader($root, $frameworkSearchFoldres, $defineRoot);
        }
        //Logger::logFuncReturn(__METHOD__);
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
    private function __construct($root='',$searchFolders=array(),$defineRoot=false) {
        //Logger::logFuncCall(__METHOD__);
        //Logger::log('$root = \''.$root.'\'', 'debug');
        //Logger::log('$defineRoot = \''.$defineRoot.'\'', 'debug');
        if(defined('ROOT_DIR')){
            //Logger::log('Setting root search directory to ROOT_DIR.');
            $this->rootDir = ROOT_DIR;
        }
        else{
            //Logger::log('ROOT_DIR is not defined.', 'warning');
            if(strlen($root) != 0 && is_dir($root)){
                $this->rootDir = $root;
                if($defineRoot === TRUE){
                    //Logger::log('Defining ROOT_DIR.');
                    define('ROOT_DIR', $this->rootDir);
                    //Logger::log('ROOT_DIR = \''.ROOT_DIR.'\'.','debug');
                }
            }
            else if($defineRoot === TRUE){
                //Logger::log('ROOT_DIR is not defined.', 'warning');
                //Logger::log('Defining ROOT_DIR.');
                $this->rootDir = __DIR__;
                foreach ($searchFolders as $folder){
                    $this->rootDir = str_replace($folder, '', $this->rootDir);
                }
                define('ROOT_DIR', $this->rootDir);
                //Logger::log('ROOT_DIR = \''.ROOT_DIR.'\'.','debug');
            }
            else{
                //Logger::log('Unable to set root search folder. An exception is thrown.','error');
                throw new Exception('Unable to set root search folder.');
            }
        }
        //Logger::log('Root search folder was set to \''.$this->rootDir.'\'.', 'debug');
        if(gettype($searchFolders) == 'array'){
            $this->searchFolders = $searchFolders;
        }
        spl_autoload_register(function($className){
            AutoLoader::get()->loadClass($className);
        });
    }
    /**
     * Adds new search directory to the array of search 
     * folders.
     * @param string $dir A new directory (such as '/entity/html-php-structs-1.6/html').
     * @since 1.0
     */
    public function addSearchDirectory($dir) {
        //Logger::logFuncCall(__METHOD__);
        //Logger::log('Passed value = \''.$dir.'\'', 'debug');
        if(strlen($dir) != 0){
            array_push($this->searchFolders, '/'. trim($dir, '/'));
            //Logger::log('Folder added.');
        }
        //Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Tries to load a class given its name.
     * @param string $className The name of the class.
     * @since 1.0
     */
    private function loadClass($className){
        //Logger::logFuncCall(__METHOD__);
        //Logger::log('Trying to load the class \''.$className.'\'.');
        foreach ($this->searchFolders as $value) {
            $f = $this->getRoot().$value.'/'.$className.'.php';
            //Logger::log('Checking if file \''.$f.'\' exist...', 'debug');
            if(file_exists($f)){
                //Logger::log('Class \''.$className.'\' found. Loading the class...');
                require $f;
                //Logger::log('Class \''.$className.'\' loaded.');
                //Logger::logFuncReturn(__METHOD__);
                return;
            }
        }
        //Logger::log('Class \''.$className.'\' was not found.', 'error');
        //Logger::logFuncReturn(__METHOD__);
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
        return self::get()->searchFolders;
    }
}