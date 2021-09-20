<?php
namespace webfiori\tests\entity\cli;

use webfiori\framework\File;
use webfiori\framework\cli\OutputStream;
/**
 * Description of FileOutputStream
 *
 * @author Ibrahim
 */
class FileOutputStream implements OutputStream {
    private $file;
    public function __construct() {
        $this->file = new File(__DIR__.DS.'cli-output.txt');
    }
    public function println($str, ...$_) {
        $args = [$str];
        foreach ($_ as $arg) {
            $args[] = $arg;
        }
        var_dump($str);
        $this->file->setRawData(call_user_func_array('sprintf', $args));
        $this->file->write(true, true);
    }

    public function prints($str, ...$_) {
        
        $args = [$str];
        foreach ($_ as $arg) {
            $args[] = $arg;
        }
        
        $this->file->setRawData(call_user_func_array('sprintf', $args));
        $this->file->write(true, true);
    }

}
