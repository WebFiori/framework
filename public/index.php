<?php

namespace WebFiori;

$DS = DIRECTORY_SEPARATOR;

require __DIR__.$DS.'..'.$DS.'vendor'.$DS.'autoload.php';

use WebFiori\Framework\App;

App::initiate('App', 'public', __DIR__);
App::start();
App::handle();
