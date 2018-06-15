<?php

/* 
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
require_once '../../root.php';
isDirTest('pages', FALSE, TRUE);
isDirTest('pages', TRUE, TRUE);
isDirTest(ROOT_DIR.'/pages', FALSE, FALSE);
function isDirTest($dir,$createIfNot,$expResult){
    $result = Util::isDirectory($dir);
    if($result == TRUE){
        echo 'The string\''.$dir.'\' is a directory<br/>';
    }
    else{
        if($createIfNot === TRUE){
            echo 'The string\''.$dir.'\' is not a directory<br/>';
            echo 'Trying to create it<br/>';
            $result = Util::isDirectory($dir, TRUE);
            if($result === TRUE){
                echo 'The directory \''.$dir.'\' was created<br/>';
            }
            else{
                echo 'Unable to create directory<br/>';
            }
        }
        else{
            echo 'The string\''.$dir.'\' is not a directory<br/>';
        }
    }
}