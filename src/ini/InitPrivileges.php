<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
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
namespace webfiori\ini;
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 404 Not Found");
    die('<!DOCTYPE html><html><head><title>Not Found</title></head><body>'
    . '<h1>404 - Not Found</h1><hr><p>The requested resource was not found on the server.</p></body></html>');
}
use webfiori\entity\Access;
/**
 * A class that has one method which is used to initialize privileges.
 * 
 * @author Ibrahim
 * @version 1.0
 */
class InitPrivileges {
    /**
     * Initialize user groups and privileges.
     * The developer can modify the body of this method to create user groups 
     * and assign privileges to each group. To create new group, use the 
     * method Access::newGroup(). To create a privilege in a group, use the 
     * method Access::newPrivilege().
     * @since 1.0
     */
    public static function init() {
        //Access::newGroup('MY_GROUP');
        //Access::newPrivilege('MY_GROUP', 'MY_PR');
    }
}
