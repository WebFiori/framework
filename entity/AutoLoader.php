<?php
/**
 * PHP autoloader. It loads php classes as needed. First file to include.
 */
spl_autoload_register(function ($name) {
    /**
     * The root include directory.
     */
    if(defined('ROOT_DIR')){
        $root = ROOT_DIR;
    }
    else{
        throw new Exception('Root Directory is not defined.');
    }
    /**
     * Folders to search in for required classes. Modify as needed.
     */
    $searchFolders = array(
        '',
        '/entity',
        '/entity/queries',
        '/entity/rest-easy-1.3',
        '/entity/jsonx-1.3',
        '/entity/ph-mysql-1.1.2',
        '/entity/html-php-structs-1.5/structs',
        '/entity/html-php-structs-1.5/html',
        '/publish',
        '/functions',
        '/apis'
    );
    foreach ($searchFolders as $value) {
        $f = $root.$value.'/'.$name.'.php';
        if(file_exists($f)){
            require $f;
            return;
        }
    }
});