<?php
/*
 * The MIT License
 *
 * Copyright 2020 Ibrahim, WebFiori Framework.
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
namespace webfiori\entity\cli;

use webfiori\entity\cron\Cron;
/**
 * A CLI command which is used to list all scheduled cron jobs.
 *
 * @author Ibrahim
 */
class ListCronCommand extends CLICommand {
    /**
     * Creates new instance of the class.
     * The command will have name '--list-jobs'. The command is used to list 
     * all registered background jobs.
     */
    public function __construct() {
        parent::__construct('--list-jobs', [], 'List all scheduled CRON jobs.');
    }
    /**
     * Execute the command.
     * @return int If the command executed without any errors, the 
     * method will return 0. Other than that, it will return false.
     * @since 1.0
     */
    public function exec() {
        $jobs = Cron::jobsQueue();
        $i = 1;
        fprintf(STDOUT, "Number Of Jobs: ".$jobs->size()."\n");

        while ($job = $jobs->dequeue()) {
            if ($i < 10) {
                fprintf(STDOUT, $this->formatOutput("--------- Job #0$i ---------\n", [
                    'color' => 'light-blue',
                    'bold' => true
                ]));
            } else {
                fprintf(STDOUT, $this->formatOutput("--------- Job #$i ---------\n", [
                    'color' => 'light-blue',
                    'bold' => true
                ]));
            }
            fprintf(STDOUT, "Job Name %".(18 - strlen('Job Name'))."s %s\n",":",$job->getJobName());
            fprintf(STDOUT, "Cron Expression %".(18 - strlen('Cron Expression'))."s %s\n",":",$job->getExpression());
            $i++;
        }
        return 0;
    }
    
}
