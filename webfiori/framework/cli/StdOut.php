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
class StdOut implements OutputStream {
    public function println(string $str, ...$_) {
        $toPass = [
            $this->asString($str)."\e[0m\e[k\n"
        ];

        foreach ($_ as $val) {
            $toPass[] = $val;
        }
        call_user_func_array([$this, 'prints'], $toPass);
    }

    public function prints(string $str, ...$_) {
        $arrayToPass = [
            STDOUT,
            $str
        ];

        foreach ($_ as $val) {
            $type = gettype($val);

            if ($type != 'array') {
                $arrayToPass[] = $val;
            }
        }
        call_user_func_array('fprintf', $arrayToPass);
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
