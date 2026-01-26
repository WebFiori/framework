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
namespace WebFiori\Framework\Writers;

use WebFiori\Framework\Scheduler\AbstractTask;
use WebFiori\Framework\Scheduler\BaseTask;
use WebFiori\Framework\Scheduler\TaskArgument;
use WebFiori\Framework\Scheduler\TasksManager;
use WebFiori\Framework\Scheduler\TaskStatusEmail;
/**
 * A class which is used to write scheduler tasks classes.
 *
 * @author Ibrahim
 */
class SchedulerTaskClassWriter extends ClassWriter {
    private $task;
    /**
     * Creates new instance of the class.
     *
     * @param string $className The name of the class that will represent the
     * task.
     *
     * @param string $taskName The name of the task.
     *
     * @param string $taskDesc A short description that description what does the
     * task do.
     *
     * @param array $argsArr An associative array that holds any arguments that
     * the task needs.
     */
    public function __construct(string $className = 'NewTask', $taskName = '', $taskDesc = '', array $argsArr = []) {
        parent::__construct($className, APP_PATH.'Tasks', APP_DIR.'\\Tasks');
        $this->task = new BaseTask();

        if (!$this->setTaskName($taskName)) {
            $this->setTaskName('New Task');
        }

        if (!$this->setTaskDescription($taskDesc)) {
            $this->setTaskDescription('No Description');
        }
        $this->getTask()->setDescription($this->getTaskDescription());

        foreach ($argsArr as $taskArg) {
            $this->addArgument($taskArg);
        }
        $this->setSuffix('Task');
        $this->addUseStatement([
            AbstractTask::class,
            TaskStatusEmail::class,
            TasksManager::class,
        ]);
    }
    /**
     * Adds new execution argument to the task.
     *
     * @param TaskArgument $arg An object which holds argument information.
     */
    public function addArgument(TaskArgument $arg) {
        $this->getTask()->addExecutionArg($arg);
    }
    /**
     * Returns the object which holds the basic information of the task that will
     * be created.
     *
     * @return BaseTask
     */
    public function getTask() : BaseTask {
        return $this->task;
    }
    /**
     * Returns the description of the task.
     *
     * @return string The description of the task. Default return value is 'No Description'.
     */
    public function getTaskDescription() : string {
        return $this->getTask()->getDescription();
    }
    /**
     * Returns the name of the task.
     *
     * @return string The name of the task. Default return value is 'New Task'.
     */
    public function getTaskName() : string {
        return $this->getTask()->getTaskName();
    }
    /**
     * Sets the description of the task.
     *
     * The description is usually used to describe what does the task will do
     * when it gets executed.
     *
     * @param string $taskDesc The description of the task. Must be non-empty string.
     *
     * @return bool If the description is set, the method will return true. Other then
     * that, the method will return false.
     */
    public function setTaskDescription(string $taskDesc) : bool {
        return $this->getTask()->setDescription($taskDesc);
    }
    /**
     * Sets the name of the task.
     *
     * The name is a unique string which is used by each created task. It acts as
     * an identifier for the task.
     *
     * @param string $taskName The name of the task. Must be non-empty string.
     *
     * @return bool If the name is set, the method will return true. Other then
     * that, the method will return false.
     */
    public function setTaskName(string $taskName) : bool {
        return $this->getTask()->setTaskName($taskName);
    }

    public function writeClassBody() {
        $this->writeConstructor();
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
            ' * Execute a set of instructions when the task failed to complete without errors.',
            ' */',
            $this->f('onFail')
        ], 1);
        $this->append('//TODO: Implement the action to perform when the task fails to complete without errors.', 2);
        $this->append([
            '}',
            '/**',
            ' * Execute a set of instructions when the task completed without errors.',
            ' */',
            $this->f('onSuccess'),
        ], 1);

        $this->append('//TODO: Implement the action to perform when the task executes without errors.', 2);
        $this->append([
            '}',
            '/**',
            ' * Execute a set of instructions after the task has finished to execute.',
            ' */',
            $this->f('afterExec'),
        ], 1);

        $this->append('//TODO: Implement the action to perform when the task finishes to execute.', 2);
        $this->append("//\$email = new TaskStatusEmail('no-reply', [", 2);
        $this->append("//    'WebFiori@example.com' => 'Ibrahim Ali'", 2);
        $this->append('//]);', 2);
        $this->append('}', 1);

        $this->append("}");
    }

    public function writeClassComment() {
        $this->append([
            '/**',
            ' * A background process which was created using the command "create".',
            ' *',
            " * The process will have the name '".$this->getTaskName()."'."
        ]);

        $argsPartArr = [];
        $args = $this->getTask()->getArguments();

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
        $this->append('class '.$this->getName().' extends AbstractTask {');
    }
    protected function writeConstructor(array $params = [],
        $body = '',
        string $description = 'Creates new instance of the class.',
        int $indent = 1) {
        $this->append([
            '/**',
            ' * Creates new instance of the class.',
            ' */',
            $this->f('__construct')
        ], 1);
        $this->append([
            "parent::__construct('".$this->getTaskName()."');",
            "\$this->setDescription('".str_replace('\'', '\\\'', $this->getTaskDescription())."');"
        ], 2);

        $taskArgs = $this->getTask()->getArguments();

        if (count($taskArgs) > 0) {
            $this->append('$this->addExecutionArgs([', 2);

            foreach ($taskArgs as $argObj) {
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
}
