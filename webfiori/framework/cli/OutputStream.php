<?php
namespace webfiori\framework\cli;

/**
 * An interface that can be used to implement output streams at which CLI
 * can send output to.
 *
 * @author Ibrahim
 */
interface OutputStream {
    /**
     * Print out a string and terminates the current line by writing the 
     * line separator string (or PHP_EOL).
     * 
     * The implementation of this method should work like the function fprintf(). 
     * 
     * @param string $str The string that will be printed to the stream.
     * 
     * @param mixed $_ One or more extra arguments that can be supplied to the 
     * method.
     * 
     * @since 1.0
     */
    public function println($str, ...$_);
    /**
     * Print out a string.
     * 
     * The implementation of this method should work like the function fprintf(). 
     * 
     * @param string $str The string that will be printed to the stream.
     * 
     * @param mixed $_ One or more extra arguments that can be supplied to the 
     * method. 
     * 
     * @since 1.0
     */
    public function prints($str, ...$_);
}
