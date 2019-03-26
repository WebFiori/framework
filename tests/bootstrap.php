<?php
$root = trim(__DIR__,DIRECTORY_SEPARATOR.'tests');
echo 'Root = \''.__DIR__.'\'.'."\n";
echo 'Include Path: \''. get_include_path().'\''."\n";
print_r(explode(DIRECTORY_SEPARATOR, $root));
if(explode(DIRECTORY_SEPARATOR, $root)[0] == 'home'){
    //linux 
    require_once '/'.trim($root,'/\\').DIRECTORY_SEPARATOR.'entity'.DIRECTORY_SEPARATOR.'AutoLoader.php';
}
else{
    require_once trim($root,'/\\').DIRECTORY_SEPARATOR.'entity'.DIRECTORY_SEPARATOR.'AutoLoader.php';
}
use webfiori\entity\AutoLoader;
AutoLoader::get(array(
    'search-folders'=>array(
        'tests\\entity\\router'
    ),
    'root'=> $root
));
echo 'Autoloader Initialized.'."\n";
echo 'Root Directory: \''.AutoLoader::get()->getRoot().'\'.'."\n";
echo 'Class Search Paths:'."\n";
$dirs = AutoLoader::getFolders();
foreach ($dirs as $dir){
    echo $dir."\n";
}