<?php
namespace app\jobs;

use webfiori\framework\cron\AbstractJob;
use webfiori\framework\cron\CronEmail;
use webfiori\framework\cron\Cron;
/**
 * A background process which was created using the command "create".
 *
 * The process will have the name 'Send Sales Report'.
 * In addition, the proces have the following args:
 * <ul>
 * <li>start: Start date of the report.</li>
 * <li>end: End date of the report.</li>
 * </ul>
 */
class Fail3TestJob extends AbstractJob {
    /**
     * Creates new instance of the class.
     */
    public function __construct(){
        parent::__construct('Send Sales Report 2');
        // TODO: Specify the time at which the process will run at.
        // You can use one of the following methods to specifiy the time:
        //$this->dailyAt(4, 30)
        //$this->everyHour();
        //$this->everyMonthOn(1, '00:00');
        //$this->onMonth('jan', 15, '13:00');
        //$this->weeklyOn('sun', '23:00');
        //$this->cron('* * * * *');
    }
    /**
     * Execute the process.
     */
    public function execute() {
        $x = $y;
    }
    /**
     * Execute a set of instructions when the job failed to complete without errors.
     */
    public function onFail() {
        
    }
    /**
     * Execute a set of instructions when the job completed without errors.
     */
    public function onSuccess() {
        //TODO: Implement the action to perform when the job executes without errors.
    }
    /**
     * Execute a set of instructions after the job has finished to execute.
     */
    public function afterExec() {
        //TODO: Implement the action to perform when the job finishes to execute.
        //$email = new CronEmail('no-reply', [
        //    'webfiori@example.com' => 'Ibrahim Ali'
        //]);
    }
}
