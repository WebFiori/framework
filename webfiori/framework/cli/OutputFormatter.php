<?php
namespace webfiori\framework\cli;

/**
 * The responsibility of this class is to format the output of running
 * a command using ANSI.
 *
 * @author Ibrahim
 */
class OutputFormatter {
    /**
     * An associative array that contains color codes and names.
     * @since 1.0
     */
    const COLORS = [
        'black' => 30,
        'red' => 31,
        'light-red' => 91,
        'green' => 32,
        'light-green' => 92,
        'yellow' => 33,
        'light-yellow' => 93,
        'white' => 97,
        'gray' => 37,
        'blue' => 34,
        'light-blue' => 94
    ];
    /**
     * Formats an output string.
     * 
     * This method is used to add colors to the output string or 
     * make it bold or underlined. The returned value of this 
     * method can be sent to any output stream using the method 'fprintf()'. 
     * Note that the support for colors
     * and formatting will depend on the terminal configuration. In addition, 
     * if the constant NO_COLOR is defined or is set in the environment, the 
     * returned string will be returned without coloring options.
     * 
     * @param string $string The string that will be formatted.
     * 
     * @param array $formatOptions An associative array of formatting 
     * options. Supported options are:
     * <ul>
     * <li><b>color</b>: The foreground color of the output text. Supported colors 
     * are: 
     * <ul>
     * <li>white</li>
     * <li>black</li>
     * <li>red</li>
     * <li>light-red</li>
     * <li>green</li>
     * <li>light-green</li>
     * <li>yellow</li>
     * <li>light-yellow</li>
     * <li>gray</li>
     * <li>blue</li>
     * <li>light-blue</li>
     * </ul>
     * </li>
     * <li><b>ansi</b>: A boolean. If set to true, the text will 
     * be formatted using ANSI escape sequences. If set to false, the input
     * string is returned without change.</li>
     * <li><b>bg-color</b>: The background color of the output text. Supported colors 
     * are the same as the supported colors by the 'color' option.</li>
     * <li><b>bold</b>: A boolean. If set to true, the text will 
     * be bold.</li>
     * <li><b>underline</b>: A boolean. If set to true, the text will 
     * be underlined.</li>
     * <li><b>reverse</b>: A boolean. If set to true, the foreground 
     * color and background color will be reversed (invert the foreground and background colors).</li>
     * <li><b>blink</b>: A boolean. If set to true, the text will 
     * blink.</li>
     * </ul>
     * @return string The string after applying the formatting to it.
     * 
     * @since 1.0
     */
    public static function formatOutput(string $string, array $formatOptions = []) {
        $validatedOptions = self::_validateOutputOptions($formatOptions);

        return self::_getFormattedOutput($string, $validatedOptions);
    }
    private static function _getFormattedOutput($outputString, $formatOptions) {
        $outputManner = self::getCharsManner($formatOptions);

        if (strlen($outputManner) != 0) {
            return "\e[".$outputManner."m$outputString\e[0m";
        }

        return $outputString;
    }
    private static function _validateOutputOptions($formatArr) {
        $noColor = 'NO_COLOR';

        if (gettype($formatArr) == 'array' && count($formatArr) !== 0) {
            if (!isset($formatArr['bold'])) {
                $formatArr['bold'] = false;
            }

            if (!isset($formatArr['underline'])) {
                $formatArr['underline'] = false;
            }

            if (!isset($formatArr['blink'])) {
                $formatArr['blink'] = false;
            }

            if (!isset($formatArr['reverse'])) {
                $formatArr['reverse'] = false;
            }

            if (!isset($formatArr['color'])) {
                $formatArr['color'] = $noColor;
            }

            if (!isset($formatArr['bg-color'])) {
                $formatArr['bg-color'] = $noColor;
            }

            return $formatArr;
        }

        return [
            'bold' => false,
            'underline' => false,
            'reverse' => false,
            'blink' => false,
            'color' => $noColor, 
            'bg-color' => $noColor
        ];
    }
    private static function addManner($str, $code) {
        if (strlen($str) > 0) {
            return $str.';'.$code;
        }

        return $str.$code;
    }
    private static function getCharsManner($options) {
        $mannerStr = '';

        if (isset($options['ansi']) && $options['ansi'] === false) {
            return $mannerStr;
        }

        if ($options['bold']) {
            $mannerStr = self::addManner($mannerStr, 1);
        }

        if ($options['underline']) {
            $mannerStr = self::addManner($mannerStr, 4);
        }

        if ($options['blink']) {
            $mannerStr = self::addManner($mannerStr, 5);
        }

        if ($options['reverse']) {
            $mannerStr = self::addManner($mannerStr, 7);
        }

        if (defined('NO_COLOR') || isset($_SERVER['NO_COLOR']) || getenv('NO_COLOR') !== false) {
            //See https://no-color.org/ for more info.
            return $mannerStr;
        }
        $colorsArr = self::COLORS;
        if ($options['color'] != 'NO_COLOR') {
            
            if (isset($colorsArr[$options['color']])) {
                $mannerStr = self::addManner($mannerStr, $colorsArr[$options['color']]);
            }
        }

        if ($options['bg-color'] != 'NO_COLOR') {
            if (isset($colorsArr[$options['bg-color']])) {
                $mannerStr = self::addManner($mannerStr, $colorsArr[$options['bg-color']] + 10);
            }
        }

        return $mannerStr;
    }
}
