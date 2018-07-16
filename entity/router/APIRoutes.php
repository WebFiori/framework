<?php

/*
 * The MIT License
 *
 * Copyright 2018 ibrah.
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
 * A class that only has a function to create API routes.
 *
 * @author Ibrahim
 * @version 1.0
 */
class APIRoutes {
    /**
     * Create all API routes. Include your own here.
     * @since 1.0
     */
    public static function create() {
        Router::api('/SysAPIs/{action}', '/SysAPIs.php');
        Router::api('/SysAPIs', '/SysAPIs.php');
        Router::api('/SysAPIs/{action}', '/SysAPIs.php');
        Router::api('/AuthAPI/{action}', '/AuthAPI.php');
        Router::api('/ExampleAPI/{action}', '/ExampleAPI.php');
        Router::api('/NumsAPIs/{action}', '/NumsAPIs.php');
        Router::api('/PasswordAPIs/{action}', '/PasswordAPIs.php');
        Router::api('/UserAPIs/{action}', '/UserAPIs.php');
        Router::api('/WebsiteAPIs/{action}', '/WebsiteAPIs.php');
    }
}
