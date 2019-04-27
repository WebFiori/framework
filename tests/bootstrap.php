<?php
use webfiori\entity\AutoLoader;
$DS = DIRECTORY_SEPARATOR;
$root = trim(__DIR__,$DS.'tests');
echo php_sapi_name()."\n";
echo 'Include Path: \''. get_include_path().'\''."\n";
if(explode($DS, $root)[0] == 'home'){
    //linux 
    require_once $DS.trim($root,'/\\').$DS.'src'.$DS.'entity'.$DS.'AutoLoader.php';
}
else{
    require_once trim($root,'/\\').$DS.'src'.$DS.'entity'.$DS.'AutoLoader.php';
}
AutoLoader::get(array(
    'search-folders'=>array(
        'tests',
        'src'
    ),
    'root'=> $root,
    'define-root'=>true,
    'on-load-failure'=>'do-nothing'
));
echo 'Autoloader Initialized.'."\n";
echo 'Root Directory: \''.AutoLoader::get()->getRoot().'\'.'."\n";
echo 'Class Search Paths:'."\n";
$dirs = AutoLoader::getFolders();
foreach ($dirs as $dir){
    echo $dir."\n";
}
echo "Starting to run tests...\n";