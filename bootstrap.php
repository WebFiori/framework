<?php

//Bootstrap file which is used to boot testing process.


use webfiori\framework\App;
use webfiori\framework\autoload\ClassLoader;

$DS = DIRECTORY_SEPARATOR;

//the name of tests directory. Update as needed.
define('TESTS_DIRECTORY', 'tests');

//an array that contains possible locations at which
//WebFiori Framework might exist.
//Add and remove directories as needed.
$WebFioriFrameworkDirs = [
    __DIR__.$DS.'webfiori',
    __DIR__.$DS.'vendor'.$DS.'webfiori'.$DS.'webfiori'
];
fprintf(STDOUT, "PHP Version: '".PHP_VERSION."'\n");
fprintf(STDOUT, "Version ID: '".PHP_VERSION_ID."'\n");
fprintf(STDOUT, "Bootstrap Path: '".__DIR__."'\n");
fprintf(STDOUT,"Tests Directory: '".TESTS_DIRECTORY."'.\n");
fprintf(STDOUT,'Include Path: \''.get_include_path().'\''."\n");
fprintf(STDOUT,"Tryning to load the class 'ClassLoader'...\n");
$isAutoloaderLoaded = false;

if (explode($DS, __DIR__)[0] == 'home') {
    fprintf(STDOUT,"Run Environment: Linux.\n");

    foreach ($WebFioriFrameworkDirs as $dir) {
        //linux
        $file = $DS.$dir.'framework'.$DS.'autoload'.$DS.'ClassLoader.php';
        fprintf(STDOUT,"Checking if file '$file' is exist...\n");

        if (file_exists($file)) {
            require_once $file;
            $isAutoloaderLoaded = true;
            break;
        }
    }
} else {
    fprintf(STDOUT,"Run Environment: Other.\n");

    foreach ($WebFioriFrameworkDirs as $dir) {
        //other
        $file = $dir.$DS.'framework'.$DS.'autoload'.$DS.'ClassLoader.php';
        fprintf(STDOUT,"Checking if file '$file' is exist...\n");

        if (file_exists($file)) {
            require_once $file;
            $isAutoloaderLoaded = true;
            break;
        }
    }
}

if ($isAutoloaderLoaded === false) {
    fprintf(STDERR, "Error: Unable to find the class 'ClassLoader'.\n");
    exit(-1);
} else {
    fprintf(STDOUT,"Class 'ClassLoader' successfully loaded.\n");
}
fprintf(STDOUT,"Initializing autoload directories...\n");
ClassLoader::get([
    'search-folders' => [
        'tests',
        'webfiori',
        'vendor',
        'app'
    ],
    'define-root' => true,
    'root' => __DIR__,
    'on-load-failure' => 'do-nothing'
]);
fprintf(STDOUT,'Autoloader Initialized.'."\n");
fprintf(STDOUT,"---------------------------------\n");
fprintf(STDOUT,"Initializing application...\n");
App::start();
fprintf(STDOUT,'Done.'."\n");
fprintf(STDOUT,'Root Directory: \''.ClassLoader::get()->root().'\'.'."\n");
define('TESTS_PATH', ClassLoader::get()->root().$DS.TESTS_DIRECTORY);
fprintf(STDOUT,'Tests Path: '.TESTS_PATH."\n");
fprintf(STDOUT,'App Path: '.APP_PATH."\n");
fprintf(STDOUT,"---------------------------------\n");

fprintf(STDOUT, "Registering shutdown function...\n");
//run code after tests completion.
register_shutdown_function(function()
{
    App::getConfig()->remove();
});
fprintf(STDOUT, "Registering shutdown function completed.\n");
fprintf(STDOUT,"---------------------------------\n");
fprintf(STDOUT,"Starting to run tests...\n");
