<?php
namespace webfiori\framework\cli\writers;

use webfiori\framework\cli\writers\ClassWriter;
/**
 * A class which is used to write cron jobs classes.
 *
 * @author Ibrahim
 */
class CronJobClassWriter extends ClassWriter {
    /**
     * Creates new instance of the class.
     * 
     * @param array $classInfoArr An associative array that contains the information 
     * of the class that will be created. The array must have the following indices: 
     * <ul>
     * <li><b>name</b>: The name of the class that will be created. If not provided, the 
     * string 'NewClass' is used.</li>
     * <li><b>namespace</b>: The namespace that the class will belong to. If not provided, 
     * the namespace 'webfiori' is used.</li>
     * <li><b>path</b>: The location at which the class will be created on. If not 
     * provided, the constant ROOT_DIR is used. </li>
     * 
     * </ul>
     * @param string $jobName The name of the job.
     * 
     * @param string $jobDesc A short description that description what does the
     * job do.
     * 
     * @param array $argsArr An associative array that holds any arguments that
     * the job needs.
     */
    public function __construct($classInfoArr, $jobName, $jobDesc, $argsArr) {
        parent::__construct($classInfoArr);
        
        $this->appendTop();
        
        $this->append([
            "use webfiori\\framework\\cron\\AbstractJob;",
            "use webfiori\\framework\\cron\\CronEmail;",
            "use webfiori\\framework\\cron\\Cron;\n",
            '/**',
            ' * A background process which was created using the command "create".',
            ' *',
            " * The process will have the name '$jobName'."
        ]);
        
        $argsPartArr = [];
        
        if (count($argsArr) != 0) {
            $argsPartArr = [
                ' * In addition, the proces have the following args:',
                ' * <ul>'
            ];
            

            foreach ($argsArr as $argName => $options) {
                $argsPartArr[] = " * <li>$argName: ".$options['description']."</li>";
            }
            $argsPartArr[] = ' * </ul>';
        }
        $argsPartArr[] = ' */';
        $argsPartArr[] = 'class '.$this->getWriter()->getName().' extends AbstractJob {';
        $this->append($argsPartArr);

        $this->_writeConstructor($jobName, $argsArr, $jobDesc);
        
        $this->append([
            '/**',
            ' * Execute the process.',
            ' */',
            'public function execute() {'
        ], 1);
        
        $this->append('//TODO: Write the code that represents the process.', 2);
        $this->append([
            '}',
            '/**',
            ' * Execute a set of instructions when the job failed to complete without errors.',
            ' */',
            'public function onFail() {'
        ], 1);
        $this->append('//TODO: Implement the action to perform when the job fails to complete without errors.', 2);
        $this->append([
            '}',
            '/**',
            ' * Execute a set of instructions when the job completed without errors.',
            ' */',
            'public function onSuccess() {',
        ], 1);

        $this->append('//TODO: Implement the action to perform when the job executes without errors.', 2);
        $this->append([
            '}',
            '/**',
            ' * Execute a set of instructions after the job has finished to execute.',
            ' */',
            'public function afterExec() {',
        ], 1);

        $this->append('//TODO: Implement the action to perform when the job finishes to execute.', 2);
        $this->append("//\$email = new CronEmail('no-reply', [", 2);
        $this->append("//    'webfiori@example.com' => 'Ibrahim Ali'", 2);
        $this->append('//]);', 2);
        $this->append('}', 1);

        $this->append("}");
    }
    private function _writeConstructor($name, array $args, $jobDesc) {
        $this->append([
            '/**',
            ' * Creates new instance of the class.',
            ' */',
            'public function __construct(){'
        ], 1);
        $this->append([
            "parent::__construct('$name');",
            "\$this->setDescription('". str_replace('\'', '\\\'', $jobDesc)."');"
        ], 2);
        
        
        if (count($args) > 0) {
            $this->append('$this->addExecutionArgs([', 2);

            foreach ($args as $argName => $argOptions) {
                $this->append("'$argName' => [", 3);
                $this->append("'description' => '".str_replace('\'', '\\\'', $argOptions['description'])."'", 4);
                $this->append("],", 3);
            }
            $this->append(']);', 2);
        }
        $this->append([
            '// TODO: Specify the time at which the process will run at.',
            '// You can use one of the following methods to specifiy the time:',
            '//$this->dailyAt(4, 30)',
            '//$this->everyHour();',
            '//$this->everyMonthOn(1, \'00:00\');',
            "//\$this->onMonth('jan', 15, '13:00');",
            "//\$this->weeklyOn('sun', '23:00');",
            "//\$this->cron('* * * * *');"
        ], 2);
        $this->append('}', 1);
    }
}
