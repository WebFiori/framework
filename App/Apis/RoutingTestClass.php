<?php
namespace App\Apis;

use WebFiori\Framework\App;

class RoutingTestClass {
    public function __construct(?string $param = null, ?string $second = null) {
        App::getResponse()->write("I'm inside the class.");
    }
    public function doSomething() {
        App::getResponse()->clear();
        App::getResponse()->write("I'm doing something.");
    }
    public function doSomethingExtra(?string $p1 = null, ?string $p2 = null) {
        App::getResponse()->clear();
        App::getResponse()->write("I'm doing something.");
    }
}
