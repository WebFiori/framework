<?php
if (function_exists('xdebug_break')) {
    xdebug_break(); // Pause here so VS Code can catch up and bind other breakpoints
}

//Bootstrap file which is used to boot testing process.

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\Schema\SchemaRunner;
use WebFiori\Framework\App;
use WebFiori\Framework\Autoload\ClassLoader;
use WebFiori\Framework\Config\JsonDriver;
use WebFiori\Framework\ThemeManager;
use Themes\FioriTheme\NewFTestTheme;
use Themes\FioriTheme2\NewTestTheme2;

$DS = DIRECTORY_SEPARATOR;

//the name of tests directory. Update as needed.
define('TESTS_DIRECTORY', 'tests');
define('MYSQL_ROOT_PASSWORD', getenv('MYSQL_ROOT_PASSWORD') ?: '123456');
define('SQL_SERVER_HOST', getenv('SQL_SERVER_HOST') ?: 'localhost');
define('SQL_SERVER_USER', getenv('SQL_SERVER_USER') ?: 'sa');
define('SQL_SERVER_PASS', getenv('SA_SQL_SERVER_PASSWORD') ?: '1234567890@Eu');
define('SQL_SERVER_DB', getenv('SQL_SERVER_DB') ?: 'testing_db');
define('ODBC_VERSION', 17);
//an array that contains possible locations at which
//WebFiori Framework might exist.
//Add and remove directories as needed.
$WebFioriFrameworkDirs = [
    __DIR__.$DS.'..'.$DS.'WebFiori',
    __DIR__.$DS.'..'.$DS.'vendor'.$DS.'WebFiori'.$DS.'WebFiori'
];
fprintf(STDOUT, "PHP Version: '".PHP_VERSION."'\n");
fprintf(STDOUT, "Version ID: '".PHP_VERSION_ID."'\n");
fprintf(STDOUT, "Bootstrap Path: '".__DIR__."'\n");
fprintf(STDOUT,"Tests Directory: '".TESTS_DIRECTORY."'.\n");
$ROOT = substr(__DIR__, 0, strlen(__DIR__) - strlen(TESTS_DIRECTORY));
fprintf(STDOUT,"Project Directory: '".$ROOT."'.\n");
fprintf(STDOUT,'Include Path: \''.get_include_path().'\''."\n");
fprintf(STDOUT,"Tryning to load the class 'ClassLoader'...\n");
$isAutoloaderLoaded = false;

if (explode($DS, __DIR__)[0] == 'home') {
    fprintf(STDOUT,"Run Environment: Linux.\n");

    foreach ($WebFioriFrameworkDirs as $dir) {
        //linux
        $file = $DS.$dir.$DS.'Framework'.$DS.'Autoload'.$DS.'ClassLoader.php';
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
        $file = $dir.$DS.'Framework'.$DS.'Autoload'.$DS.'ClassLoader.php';
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
        'WebFiori',
        'vendor',
        'app',
        'Themes'
    ],
    'define-root' => true,
    'root' => $ROOT,
    'on-load-failure' => 'do-nothing'
]);
fprintf(STDOUT,'Autoloader Initialized.'."\n");
fprintf(STDOUT,"---------------------------------\n");
fprintf(STDOUT,"Initializing application...\n");
App::initiate('App', 'public', $ROOT);
App::start();
fprintf(STDOUT,'Done.'."\n");
fprintf(STDOUT,'Autoload Root Directory: \''.ClassLoader::get()->root().'\'.'."\n");
define('TESTS_PATH', ClassLoader::get()->root().$DS.TESTS_DIRECTORY);
fprintf(STDOUT,'Tests Path: '.TESTS_PATH."\n");
fprintf(STDOUT,'App Path: '.APP_PATH."\n");
fprintf(STDOUT,"---------------------------------\n");

fprintf(STDOUT, "Registering shutdown function...\n");
//run code after tests completion.
register_shutdown_function(function()
{
    JsonDriver::setConfigFileName('app-config.json');
    App::getConfig()->remove();
    JsonDriver::setConfigFileName('run-sql-test.json');
    App::getConfig()->remove();
    JsonDriver::setConfigFileName('super-confx.json');
    App::getConfig()->remove();
    $conn = new ConnectionInfo('mssql',SQL_SERVER_USER, SQL_SERVER_PASS, SQL_SERVER_DB, SQL_SERVER_HOST, 1433, [
        'TrustServerCertificate' => 'true'
    ]);
    
    try {
      //  $runner = new SchemaRunner($conn);
      //  $runner->dropChangesTable();
        
    } catch (\Throwable $exc) {
        fprintf(STDOUT,'Error on register_shutdown_function:'."\n\n");
        fprintf(STDOUT, $exc->getMessage()."\n");
    }

});
fprintf(STDOUT, "Registering shutdown function completed.\n");
fprintf(STDOUT,"---------------------------------\n");
fprintf(STDOUT,"Adding themes...\n");
ThemeManager::register(new NewFTestTheme());
ThemeManager::register(new NewTestTheme2());
fprintf(STDOUT,"Done\n");
fprintf(STDOUT,"---------------------------------\n");
fprintf(STDOUT,"Starting to run tests...\n");
fprintf(STDOUT,"---------------------------------\n");