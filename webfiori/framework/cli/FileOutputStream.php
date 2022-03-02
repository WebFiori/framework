<?php
namespace webfiori\framework\cli;

use webfiori\framework\cli\OutputStream;
use webfiori\framework\File;
/**
 * A class that implements output stream which can be based on files.
 *
 * @author Ibrahim
 */
class FileOutputStream implements OutputStream {
    private $file;
    /**
     * Creates new instance of the class.
     * 
     * Note that the method will attempt to remove the file and re-create it.
     * 
     * @param string $path The absolute path to the file that CLI engine
     * will send outputs to.
     */
    public function __construct($path) {
        $this->file = new File($path);
        $this->reset();
    }
    /**
     * Removes the file that represents output stream and re-create it.
     */
    public function reset() {
        $this->file->remove();
        $this->file->write(false, true);
    }
    /**
     * 
     * @return string
     */
    public function readOutput() {
        $f = new File($this->file->getAbsolutePath());
        $f->read();
        $raw = $f->getRawData();
        return explode("\n", $raw);
    }
    public function println($str, ...$_) {
        $toPass = [
            $this->asString($str)."\n"
        ];

        foreach ($_ as $val) {
            $toPass[] = $val;
        }
        call_user_func_array([$this, 'prints'], $toPass);
    }

    public function prints($str, ...$_) {
        $arrayToPass = [
            $str
        ];

        foreach ($_ as $val) {
            $type = gettype($val);

            if ($type != 'array') {
                $arrayToPass[] = $val;
            }
        }
        
        $toWrite = call_user_func_array('sprintf', $arrayToPass);
        $this->file->setRawData($toWrite);
        $this->file->write();
    }
    private function asString($var) {
        $type = gettype($var);

        if ($type == 'boolean') {
            return $var === true ? 'true' : 'false';
        } else if ($type == 'null') {
            return 'null';
        }

        return $var;
    }
}
