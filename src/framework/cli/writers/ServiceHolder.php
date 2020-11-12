<?php
/* 
 * The MIT License
 *
 * Copyright 2020 Ibrahim BinAlshikh, restEasy library.
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
namespace webfiori\framework\cli;

use webfiori\restEasy\AbstractWebService;
/**
 * A class which is used to hold CLI created services temporary.
 * 
 * This class does not hold any web service. The main aim of this class is 
 * to hold the service which is created using CLI and later on, create the 
 * actual class that contains the web service.
 * 
 * @author Ibrahim
 * 
 * @version 1.0
 */
class ServiceHolder extends AbstractWebService {
    public function __construct() {
        parent::__construct('');
    }
    /**
     * 
     * @return boolean Always return false.
     */
    public function isAuthorized() {
        return false;
    }
    /**
     * Process the request.
     * 
     * Actually, this method does nothing.
     */
    public function processRequest() {
    }
}
