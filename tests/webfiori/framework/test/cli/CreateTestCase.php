<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\file\File;

class CreateTestCase extends TestCase {
    public function removeClass($classPath) {
        $file = new File(ROOT_PATH.DS.trim($classPath,'\\').'.php');
        $file->remove();
    }
}
