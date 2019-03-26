<?php
use webfiori\entity\AutoLoader;
$root = trim(__DIR__,DIRECTORY_SEPARATOR.'tests');
//echo 'Include Path: \''. get_include_path().'\''."\n";
if(explode(DIRECTORY_SEPARATOR, $root)[0] == 'home'){
    //linux 
    require_once '/'.trim($root,'/\\').DIRECTORY_SEPARATOR.'entity'.DIRECTORY_SEPARATOR.'AutoLoader.php';
}
else{
    require_once trim($root,'/\\').DIRECTORY_SEPARATOR.'entity'.DIRECTORY_SEPARATOR.'AutoLoader.php';
}
AutoLoader::get(array(
    'search-folders'=>array(
        'tests\\entity\\router'
    ),
    'root'=> $root,
    'on-load-failure'=>'do-nothing'
));
//echo 'Autoloader Initialized.'."\n";
//echo 'Root Directory: \''.AutoLoader::get()->getRoot().'\'.'."\n";
//echo 'Class Search Paths:'."\n";
//$dirs = AutoLoader::getFolders();
//foreach ($dirs as $dir){
//    echo $dir."\n";
//}