<?php

use webfiori\entity\cron\AbstractJob;
use webfiori\entity\cron\CronEmail;
use webfiori\entity\File;

class SampleJob extends AbstractJob {
    
    public function __construct() {
        parent::__construct('Sample Job');
        $this->dailyAt(4, 30);
    }
    
    public function afterExec() {
        $email = new CronEmail('no-reply', [
            'webfiori@example.com' => 'Ibrahim Ali'
        ]);
        $email->send();
    }

    public function execute() {
        $file = new File($this->getJobName(), ROOT_DIR);
        $file->setRawData('This is a test job under execution. Time: '.date(DATE_ISO8601));
        $file->write(false, true);
    }

    public function onFail() {
        $file = new File($this->getJobName(), ROOT_DIR);
        $file->setRawData('The job has faild. Time: '.date(DATE_ISO8601));
        $file->write(true, true);
    }

    public function onSuccess() {
        $file = new File($this->getJobName(), ROOT_DIR);
        $file->setRawData('The job has finished without errors. Time: '.date(DATE_ISO8601));
        $file->write(true, true);
    }

}
return __NAMESPACE__;