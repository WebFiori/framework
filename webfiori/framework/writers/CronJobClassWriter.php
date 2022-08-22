<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2020 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework\writers;

use webfiori\framework\cron\AbstractJob;
use webfiori\framework\cron\Cron;
use webfiori\framework\cron\CronEmail;
use webfiori\framework\cron\CronJob;
use webfiori\framework\cron\JobArgument;
use webfiori\framework\writers\ClassWriter;
/**
 * A class which is used to write cron jobs classes.
 *
 * @author Ibrahim
 */
class CronJobClassWriter extends ClassWriter {
    private $job;
    /**
     * Creates new instance of the class.
     * 
     * @param string $jobName The name of the job.
     * 
     * @param string $jobDesc A short description that description what does the
     * job do.
     * 
     * @param array $argsArr An associative array that holds any arguments that
     * the job needs.
     */
    public function __construct($jobName = '', $jobDesc = '', array $argsArr = []) {
        parent::__construct('NewJob', ROOT_DIR.DS.APP_DIR_NAME.DS.'jobs', APP_DIR_NAME.'\\jobs');
        $this->job = new CronJob();
        if (!$this->setJobName($jobName)) {
            $this->setJobName('New Job');
        }
        if (!$this->setJobDescription($jobDesc)) {
            $this->setJobDescription('No Description');
        }
        foreach ($argsArr as $jobArg) {
            $this->addArgument($jobArg);
        }
        $this->setSuffix('Job');
        $this->addUseStatement([
            AbstractJob::class,
            CronEmail::class,
            Cron::class,
        ]);
    }
    /**
     * Returns the object which holds the basic information of the job that will
     * be created.
     * 
     * @return CronJob
     */
    public function getJob() : CronJob {
        return $this->job;
    }
    /**
     * Adds new execution argument to the job.
     * 
     * @param JobArgument $arg An object which holds argument information.
     */
    public function addArgument(JobArgument $arg) {
        $this->getJob()->addExecutionArg($arg);
    }
    /**
     * Sets the name of the job.
     * 
     * The name is a unique string which is used by each created job. It acts as
     * an identifier for the job.
     * 
     * @param string $jobName The name of the job. Must be non-empty string.
     * 
     * @return bool If the name is set, the method will return true. Other then
     * that, the method will return false.
     */
    public function setJobName(string $jobName) : bool {
        return $this->getJob()->setJobName($jobName);
    }
    /**
     * Returns the name of the job.
     * 
     * @return string The name of the job. Default return value is 'New Job'.
     */
    public function getJobName() : string {
        return $this->getJob()->getJobName();
    }
    /**
     * Returns the description of the job.
     * 
     * @return string The description of the job. Default return value is 'No Description'.
     */
    public function getJobDescription() : string {
        return $this->getJob()->getDescription(); 
    }
    /**
     * Sets the description of the job.
     * 
     * The description is usually used to describe what does the job will do
     * when it gets executed.
     * 
     * @param string $jobDesc The description of the job. Must be non-empty string.
     * 
     * @return bool If the description is set, the method will return true. Other then
     * that, the method will return false.
     */
    public function setJobDescription(string $jobDesc) : bool {
        return $this->getJob()->setDescription($jobDesc);
    }
    private function _writeConstructor() {
        $this->append([
            '/**',
            ' * Creates new instance of the class.',
            ' */',
            $this->f('__construct')
        ], 1);
        $this->append([
            "parent::__construct('".$this->getJobName()."');",
            "\$this->setDescription('". str_replace('\'', '\\\'', $this->getJobDescription())."');"
        ], 2);
        
        $jobArgs = $this->getJob()->getArguments();
        if (count($jobArgs) > 0) {
            $this->append('$this->addExecutionArgs([', 2);

            foreach ($jobArgs as $argObj) {
                $this->append("'".$argObj->getName()."' => [", 3);
                $this->append("'description' => '".str_replace('\'', '\\\'', $argObj->getDescription())."',", 4);
                if ($argObj->getDefault() !== null) {
                    $this->append("'default' => '".str_replace('\'', '\\\'', $argObj->getDefault())."',", 4);
                }
                $this->append("],", 3);
            }
            $this->append(']);', 2);
        }
        $this->append([
            '// TODO: Specify the time at which the process will run at.',
            '// You can use one of the following methods to specifiy the time:',
            '//$this->dailyAt(4, 30);',
            '//$this->everyHour();',
            '//$this->everyMonthOn(1, \'00:00\');',
            "//\$this->onMonth('jan', 15, '13:00');",
            "//\$this->weeklyOn('sun', '23:00');",
            "//\$this->cron('* * * * *');"
        ], 2);
        $this->append('}', 1);
    }

    public function writeClassBody() {
        $this->_writeConstructor();
        $this->append([
            '/**',
            ' * Execute the process.',
            ' */',
            $this->f('execute')
        ], 1);
        
        $this->append('//TODO: Write the code that represents the process.', 2);
        $this->append([
            '}',
            '/**',
            ' * Execute a set of instructions when the job failed to complete without errors.',
            ' */',
            $this->f('onFail')
        ], 1);
        $this->append('//TODO: Implement the action to perform when the job fails to complete without errors.', 2);
        $this->append([
            '}',
            '/**',
            ' * Execute a set of instructions when the job completed without errors.',
            ' */',
            $this->f('onSuccess'),
        ], 1);

        $this->append('//TODO: Implement the action to perform when the job executes without errors.', 2);
        $this->append([
            '}',
            '/**',
            ' * Execute a set of instructions after the job has finished to execute.',
            ' */',
            $this->f('afterExec'),
        ], 1);

        $this->append('//TODO: Implement the action to perform when the job finishes to execute.', 2);
        $this->append("//\$email = new CronEmail('no-reply', [", 2);
        $this->append("//    'webfiori@example.com' => 'Ibrahim Ali'", 2);
        $this->append('//]);', 2);
        $this->append('}', 1);

        $this->append("}");
    }

    public function writeClassComment() {
        $this->append([
            '/**',
            ' * A background process which was created using the command "create".',
            ' *',
            " * The process will have the name '".$this->getJobName()."'."
        ]);
        
        $argsPartArr = [];
        $args = $this->getJob()->getArguments();
        if (count($args) != 0) {
            $argsPartArr = [
                ' * In addition, the proces have the following args:',
                ' * <ul>'
            ];
            

            foreach ($args as $argObj) {
                $argsPartArr[] = " * <li>".$argObj->getName().": ".$argObj->getDescription()."</li>";
            }
            $argsPartArr[] = ' * </ul>';
        }
        $argsPartArr[] = ' */';
        $this->append($argsPartArr);
    }

    public function writeClassDeclaration() {
        $this->append('class '.$this->getName().' extends AbstractJob {');
    }

}
