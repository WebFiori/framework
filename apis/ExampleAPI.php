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

if(!defined('ROOT_DIR')){
    http_response_code(403);
    die('{"message":"Forbidden"}');
}
//create class and extend the base class API
class ExampleAPI extends API{
    
    public function __construct() {
        parent::__construct();
        
        //create API action
        $a1 = new APIAction('say-hello');
        
        //set action request method
        $a1->addRequestMethod('get');
        
        //add the action to the API
        $this->addAction($a1);
    }
    
    public function isAuthorized() {
        //check if the user 
        //is authorized to perform specific action.
        return TRUE;
    }

    public function processRequest() {
        //get the action,
        //and perform it
        $a = $this->getAction();
        if($a == 'say-hello'){
            //say hello by sending html document
            $this->send('text/html', '<html><head><title>Say Hello</title></head><body><p>hello</p></body></html>');
        }
        else{
            $this->send('application/json', '{"description":"This is a test api"}');
        }
    }

}
//create an instance of the API
$api = new ExampleAPI();
//call the function process() 
// to process user request.
$api->process();
