<?php
namespace webfiori\framework\cli\writers;

use webfiori\framework\cli\writers\ClassWriter;
/**
 * A class which is used to write cron jobs classes.
 *
 * @author Ibrahim
 */
class CronJobClassWriter extends ClassWriter {
    private $name;
    private $args;
    private $desc;
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
    public function __construct($classInfoArr = [], $jobName = '', $jobDesc = '', array $argsArr = []) {
        parent::__construct($classInfoArr);
        $this->name = $jobName;
        $this->args = $argsArr;
        $this->desc = $jobDesc;
        
        $this->addUseStatement([
            "webfiori\\framework\\cron\\AbstractJob",
            "webfiori\\framework\\cron\\CronEmail",
            "webfiori\\framework\\cron\\Cron",
        ]);
    }
    public function setArgs(array $argsArr) {
        $this->args = $argsArr;
    }
    public function setJobName($jobName) {
        $this->name = $jobName;
    }
    public function setJobDescription($jobDesc) {
        $this->desc = $jobDesc;
    }
    private function _writeConstructor() {
        $this->append([
            '/**',
            ' * Creates new instance of the class.',
            ' */',
            'public function __construct(){'
        ], 1);
        $this->append([
            "parent::__construct('$this->name');",
            "\$this->setDescription('". str_replace('\'', '\\\'', $this->desc)."');"
        ], 2);
        
        
        if (count($this->args) > 0) {
            $this->append('$this->addExecutionArgs([', 2);

            foreach ($this->args as $argName => $argOptions) {
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

    public function writeClassBody() {
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

    public function writeClassComment() {
        $this->append([
            '/**',
            ' * A background process which was created using the command "create".',
            ' *',
            " * The process will have the name '$this->name'."
        ]);
        
        $argsPartArr = [];
        
        if (count($this->args) != 0) {
            $argsPartArr = [
                ' * In addition, the proces have the following args:',
                ' * <ul>'
            ];
            

            foreach ($this->args as $argName => $options) {
                $argsPartArr[] = " * <li>$argName: ".$options['description']."</li>";
            }
            $argsPartArr[] = ' * </ul>';
        }
        $argsPartArr[] = ' */';
        $this->append($argsPartArr);
    }

    public function writeClassDeclaration() {
        $this->append('class '.$this->getWriter()->getName().' extends AbstractJob {');
    }

}
