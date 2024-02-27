<?php

namespace app\apis;

use webfiori\http\Response;

class RoutingTestClass {
    public function __construct(string $param = null, string $second = null) {
        Response::write("I'm inside the class.");
    }
    public function doSomething() {
        Response::clear();
        Response::write("I'm doing something.");
    }
    public function doSomethingExtra(string $p1 = null, string $p2 = null) {
        Response::clear();
        Response::write("I'm doing something.");
    }
}
