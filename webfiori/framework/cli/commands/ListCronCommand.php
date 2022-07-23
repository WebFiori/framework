<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2019 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework\cli\commands;

use webfiori\cli\CLICommand;
use webfiori\framework\cron\Cron;
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
        parent::__construct('list-jobs', [], 'List all scheduled CRON jobs.');
    }
    /**
     * Execute the command.
     * @return int If the command executed without any errors, the 
     * method will return 0. Other than that, it will return false.
     * @since 1.0
     */
    public function exec() : int {
        $jobs = Cron::jobsQueue();
        $i = 1;
        $this->println("Number Of Jobs: ".$jobs->size());

        while ($job = $jobs->dequeue()) {
            if ($i < 10) {
                $this->println("--------- Job #0$i ---------", [
                    'color' => 'light-blue',
                    'bold' => true
                ]);
            } else {
                $this->println("--------- Job #$i ---------", [
                    'color' => 'light-blue',
                    'bold' => true
                ]);
            }
            $this->println("Job Name %".(18 - strlen('Job Name'))."s %s",[], ":",$job->getJobName());
            $this->println("Cron Expression %".(18 - strlen('Cron Expression'))."s %s",[],":",$job->getExpression());
            $i++;
        }

        return 0;
    }
}
