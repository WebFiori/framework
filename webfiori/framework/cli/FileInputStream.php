<?php
namespace webfiori\framework\cli;

use webfiori\framework\cli\InputStream;
use webfiori\framework\File;
use webfiori\framework\cli\StdIn;
/**
 * A class that implements input stream which can be based on files.
 *
 * @author Ibrahim
 */
class FileInputStream implements InputStream {
    private $file;
    /**
     * Creates new instance of the class.
     * 
     * @param string $path The absolute path to the file that CLI engine
     * will read inputs from.
     */
    public function __construct($path) {
        $this->file = new File($path);
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
        $this->file->read(0, $bytes);
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
        $input = '';
        $char = '';

        while ($char != 'LF') {
            $char = $this->readAndTranslate();

            if ($char == 'BACKSPACE' && strlen($input) > 0) {
                $input = substr($input, 0, strlen($input) - 1);
            } else if ($char == 'ESC') {
                return '';
            } else if ($char == 'CR') {
                // Do nothing?
            } else if ($char == 'DOWN') {
                // read history;
            } else if ($char == 'UP') {
                // read history;
            } else if ($char != 'CR' && $char != 'LF') {
                if ($char == 'SPACE') {
                    $input .= ' ';
                } else {
                    $input .= $char;
                }
            }
        }

        return $input;
    }
    /**
     * 
     * @return string
     * 
     * @since 1.0
     */
    private function readAndTranslate() {
        $keypress = $this->read();
        $keyMap = StdIn::KEY_MAP;

        if (isset($keyMap[$keypress])) {
            return $keyMap[$keypress];
        }

        return $keypress;
    }
}
