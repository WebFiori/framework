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

/**
 * Description of Router
 *
 * @author Ibrahim
 */
class Router {
    public static function splitURI($uri) {
        $retVal = array(
            'uri'=>$uri,
            'uri-without-query-string'=>'',
            'query-string'=>'',
            'uri-breaked'=>array(),
            'query-string-breaked'=>array()
        );
        $split = explode('?', $uri);
        $retVal['query-string'] = isset($split[1]) ? $split[1] : '';
        $retVal['uri-without-query-string'] = $split[0];
        $uriSplit = explode('/', $retVal['uri-without-query-string']);
        foreach ($uriSplit as $val){
            if($val != ''){
                array_push($retVal['uri-breaked'], $val);
            }
        }
        if($retVal['query-string'] != ''){
            $queryStringSplit = explode('&', $retVal['query-string']);
            foreach ($queryStringSplit as $val){
                $qSplit = explode('=', $val);
                $arr = array('key'=>$qSplit[0],'value'=>$qSplit[0]);
                array_push($retVal['query-string-breaked'], $arr);
            }
        }
        return $retVal;
    }
}
