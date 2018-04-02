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
        $root = 'C:/Server/apache2/htdocs/y-project';
    }
    /**
     * Folders to search in for required classes. Modify as needed.
     */
    $searchFolders = array(
        '',
        '/entity',
        '/entity/queries',
        '/entity/rest-easy-1.1',
        '/entity/jsonx-1.3',
        '/entity/ph-mysql-1.1',
        '/entity/php-structures-1.1',
        '/entity/php-structures-1.1/html',
        '/publish',
        '/functions',
        '/apis'
    );
    foreach ($searchFolders as $value) {
        $f = $root.$value.'/'.$name.'.php';
        if(file_exists($f)){
            require $root.$value.'/'.$name.'.php';
            return;
        }
    }
});