<?php

//Bootstrap file which is used to boot testing process.
use webfiori\framework\AutoLoader;
use webfiori\framework\WebFioriApp;

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
fprintf(STDOUT, "Bootstrap Path: '".__DIR__."'\n");
fprintf(STDOUT,"Tests Directory: '".TESTS_DIRECTORY."'.\n");
fprintf(STDOUT,'Include Path: \''.get_include_path().'\''."\n");
fprintf(STDOUT,"Tryning to load the class 'AutoLoader'...\n");
$isAutoloaderLoaded = false;

if (explode($DS, __DIR__)[0] == 'home') {
    fprintf(STDOUT,"Run Environment: Linux.\n");

    foreach ($WebFioriFrameworkDirs as $dir) {
        //linux 
        $file = $DS.$dir.'framework'.$DS.'AutoLoader.php';
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
        $file = $dir.$DS.'framework'.$DS.'AutoLoader.php';
        fprintf(STDOUT,"Checking if file '$file' is exist...\n");

        if (file_exists($file)) {
            require_once $file;
            $isAutoloaderLoaded = true;
            break;
        }
    }
}

if ($isAutoloaderLoaded === false) {
    fprintf(STDERR, "Error: Unable to find the class 'AutoLoader'.\n");
    exit(-1);
} else {
    fprintf(STDOUT,"Class 'AutoLoader' successfully loaded.\n");
}
fprintf(STDOUT,"Initializing autoload directories...\n");
AutoLoader::get([
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
fprintf(STDOUT,'Class Search Paths:'."\n");
$dirs = AutoLoader::getFolders();

foreach ($dirs as $dir) {
    fprintf(STDOUT, $dir."\n");
}
$themesPath = TESTS_DIRECTORY.DIRECTORY_SEPARATOR.'themes';
fprintf(STDOUT, 'Setting themes path to "'.$themesPath.'" ...');
define('THEMES_PATH', $themesPath);
fprintf(STDOUT,"Initializing application...\n");
WebFioriApp::start();
fprintf(STDOUT,'Done.'."\n");
fprintf(STDOUT,'Root Directory: \''.AutoLoader::get()->root().'\'.'."\n");


fprintf(STDOUT, "Registering shutdown function...\n");
//run code after tests completion.
register_shutdown_function(function()
{
});
fprintf(STDOUT, "Registering shutdown function completed.\n");
fprintf(STDOUT,"Starting to run tests...\n");
