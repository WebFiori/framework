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

use webfiori\framework\cron\Cron;

/**
 * A class that has one method to initialize cron jobs.
 *
 * @author Ibrahim
 * @version 1.0
 */
class InitCron {
    /**
     * A method that can be used to initialize cron jobs.
     * 
     * The main aim of this method is to give the developer a way to register 
     * the jobs which are created outside the folder 'app/jobs'. To register 
     * any job, use the method Cron::scheduleJob().
     * 
     * @since 1.0
     */
    public static function init() {
        //set an optional password to protect jobs from 
        //unauthorized execution access
        //default password: 123456
        Cron::password('8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92');
        //enable job execution log
        Cron::execLog(true);

        //add jobs
        Cron::dailyJob("13:00", "Test Job", function ()
        {
            echo "I'm Running in Background.\n";
        });
    }
}
