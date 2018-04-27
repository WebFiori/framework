<?php
//show any errors
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


//load JsonX
require_once '../JsonX.php';

//initialize an object of the class JsonX
$j = new JsonX();

//add a number attribute
$j->addNumber('my-number', 34);

//add a boolean with 'false' as its value. 
$j->addBoolean('my-boolean', FALSE);

//add a string
$j->addString('a-string', 'Hello, I\'m JsonX! I like "json". ');

header('content-type:application/json');
//display json object in the browser.
echo $j;

