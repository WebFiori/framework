<?php
require_once 'root.php';
$uri = filter_var($_SERVER['REQUEST_URI']);
$split = Router::splitURI($uri);
$route = '';
for($x = 1 ; $x < count($split['uri-broken']) ; $x++){
    $route .= '/'.$split['uri-broken'][$x];
}
Router::get()->route($route);