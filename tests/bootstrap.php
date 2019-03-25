<?php
require_once '../entity/AutoLoader.php';
use webfiori\entity\AutoLoader;
AutoLoader::get(array(
    'search-folders'=>array(
        'tests\\entity\\router'
    ),
    'root'=> trim(__DIR__,'\\tests')
));
echo 'Autoloader Initialized.'."\n";
echo 'Root Directory: \''.AutoLoader::get()->getRoot().'\'.'."\n";
echo 'Class Search Paths:'."\n";
$dirs = AutoLoader::getFolders();
foreach ($dirs as $dir){
    echo $dir."\n";
}