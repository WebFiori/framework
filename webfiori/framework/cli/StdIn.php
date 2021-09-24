<?php
namespace webfiori\framework\cli;

/**
 * A class that implements default standard output for command line interface.
 *
 * @author Ibrahim
 * 
 * @since 2.3.1
 * 
 * @version 1.0
 */
class StdIn implements InputStream {
    /**
     * An array that holds a map of special keyboard keys codes and the 
     * meaning of each code.
     * 
     * @var array
     * 
     * @since 1.0
     */
    const KEY_MAP = [
        "\033[A" => 'UP',
        "\033[B" => 'DOWN',
        "\033[C" => 'RIGHT',
        "\033[D" => 'LEFT',
        "\n" => 'LF',
        "\r" => 'CR',
        " " => 'SPACE',
        "\010" => 'BACKSPACE',
        "\177" => 'BACKSPACE',
        "\t" => 'TAP',
        "\e" => 'ESC'
    ];
    /**
     * Reads a string of bytes from STDIN.
     * 
     * This method is used to read specific number of characters from STDIN.
     * 
     * @return string The method will return the string which was given as input 
     * in STDIN.
     * 
     * @since 1.0
     */
    public function read($bytes = 1) {
        $input = '';

        while (strlen($input) < $bytes) {
            $char = $this->readAndTranslate();

            if ($char == 'BACKSPACE' && strlen($input) > 0) {
                $input = substr($input, 0, strlen($input) - 1);
            } else if ($char == 'ESC') {
                return '';
            } else if ($char == 'DOWN') {
                // read history?
            } else if ($char == 'UP') {
                // read history?
            } else {
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
     * Reads one line from STDIN.
     * 
     * The method will continue to read from STDIN till it finds end of 
     * line character "\n".
     * 
     * @return string The method will return the string which was taken from 
     * STDIN without the end of line character.
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
        $keypress = fgetc(STDIN);
        $keyMap = self::KEY_MAP;

        if (isset($keyMap[$keypress])) {
            return $keyMap[$keypress];
        }

        return $keypress;
    }
}
