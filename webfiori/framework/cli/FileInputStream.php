<?php
namespace webfiori\framework\cli;

use webfiori\framework\cli\InputStream;
use webfiori\framework\File;
use webfiori\framework\cli\KeysMap;
/**
 * A class that implements input stream which can be based on files.
 *
 * @author Ibrahim
 */
class FileInputStream implements InputStream {
    private $file;
    private $seek;
    /**
     * Creates new instance of the class.
     * 
     * @param string $path The absolute path to the file that CLI engine
     * will read inputs from.
     */
    public function __construct($path) {
        $this->file = new File($path);
        $this->seek = 0;
    }
    /**
     * Reads a string of bytes from the file.
     * 
     * This method is used to read specific number of characters from the
     * file which is given as input stream.
     * 
     * @return string The method will return a string from the file.
     * 
     * @since 1.0
     */
    public function read($bytes = 1) {
        $this->file->read($this->seek, $this->seek + $bytes);
        $this->seek += $bytes;
        return $this->file->getRawData();
    }
    /**
     * Reads one line from the file.
     * 
     * The method will continue to read from the file till it finds end of 
     * line character "\n".
     * 
     * @return string The method will return the string which was taken from 
     * the file without the end of line character.
     * 
     * @since 1.0
     */
    public function readLine() {
        return KeysMap::readLine($this);
    }
}
