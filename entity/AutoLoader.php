<?php
/**
 * An autoloader class to load classes as needed during runtime.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
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
     * A single instance of the class <b>AutoLoader</b>.
     * @var AutoLoader
     * @since 1.0 
     */
    private static $loader;
    /**
     * Returns an instance of the class <b>AutoLoader</b>.
     * @return AutoLoader
     * @throws Exception An exception will be thrown if the constant 
     * <b>ROOT_DIR</b> is undefined.
     */
    public static function get() {
        if(self::$loader != NULL){
            return self::$loader;
        }
        if(defined('ROOT_DIR')){
            self::$loader = new AutoLoader(ROOT_DIR, array(
                '',
                '/entity',
                '/entity/queries',
                '/entity/rest-easy-1.4.1',
                '/entity/jsonx-1.3',
                '/entity/ph-mysql-1.1.2',
                '/entity/html-php-structs-1.6/structs',
                '/entity/html-php-structs-1.6/html',
                '/publish',
                '/functions',
                '/apis'
            ));
            return self::$loader;
        }
        else{
            throw new Exception('Root Directory is not defined.');
        }
    }
    
    private function __construct($root='',$searchFolders=array()) {
        if(defined('ROOT_DIR')){
            $this->rootDir = ROOT_DIR;
        }
        else{
            if(strlen($root) != 0 && is_dir($root)){
                $this->rootDir = $root;
            }
            else{
                throw new Exception('Invalid Root Directory: '.$root);
            }
        }
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
        if(strlen($dir) != 0){
            array_push($this->searchFolders, $dir);
        }
    }
    /**
     * Tries to load a class given its name.
     * @param string $className The name of the class.
     * @since 1.0
     */
    private function loadClass($className){
        foreach ($this->searchFolders as $value) {
            $f = $this->getRoot().$value.'/'.$className.'.php';
            if(file_exists($f)){
                require $f;
                return;
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
}