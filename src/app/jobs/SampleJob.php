<?php
namespace webfiori\jobs;

use webfiori\entity\cron\AbstractJob;
use webfiori\entity\cron\CronEmail;
use webfiori\entity\File;
/**
 * A sample job that shows how to create jobs and make them schedule automatically.
 */
class SampleJob extends AbstractJob {
    public function __construct() {
        parent::__construct('Sample Job');
        $this->dailyAt(4, 30);
    }
    /**
     * A code that will get executed after the job finished successfully or not.
     */
    public function afterExec() {
        $email = new CronEmail('no-reply', [
            'webfiori@example.com' => 'Ibrahim Ali'
        ]);
        $email->send();
    }
    /**
     * The actual code that will get executed when the job is running.
     */
    public function execute() {
        $file = new File($this->getJobName(), ROOT_DIR);
        $file->setRawData('This is a test job under execution. Time: '.date(DATE_ISO8601));
        $file->write(false, true);

        return true;
    }
    /**
     * Executed when the job failed to completed successfully.
     */
    public function onFail() {
        $file = new File($this->getJobName(), ROOT_DIR);
        $file->setRawData('The job has faild. Time: '.date(DATE_ISO8601));
        $file->write(true, true);
    }
    /**
     * Executed after the job has completed without any errors.
     */
    public function onSuccess() {
        $file = new File($this->getJobName(), ROOT_DIR);
        $file->setRawData('The job has finished without errors. Time: '.date(DATE_ISO8601));
        $file->write(true, true);
    }
}
//Must return the namespace of the job if it has a namespace after creating it.
//This makes the job to schedule automatically.
//This step can be ignored if the job has no namespace.
return __NAMESPACE__;
