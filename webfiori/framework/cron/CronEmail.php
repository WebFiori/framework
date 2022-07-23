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
namespace webfiori\framework\cron;

use webfiori\file\File;
use webfiori\framework\EmailMessage;
use webfiori\framework\WebFioriApp;
use webfiori\ui\HTMLNode;
use webfiori\ui\TableRow;
/**
 * A class which can be used to send an email regarding the status of 
 * background job execution.
 * 
 * This class must be only used in one of the abstract methods of a 
 * background job since using it while no job is active will have no 
 * effect.
 * 
 * The email that will be sent will contain technical information about 
 * the job in addition to a basic log file that shows execution steps. Also, 
 * it will contain any log messages which was added by using the method 
 * 'Cron::log()'.
 * 
 * @author Ibrahim
 * 
 * @version 1.0.2
 */
class CronEmail extends EmailMessage {
    /**
     * Creates new instance of the class.
     * 
     * @param string $sendAccName The name of SMTP account that will be 
     * used to send the message. Note that it must be exist in the class 
     * 'MailConfig'.
     * 
     * @param array $receivers An associative array of receivers. The 
     * indices are the addresses of the receivers and the values are the 
     * names of the receivers (e.g. 'xy@example.com' => 'Super User');
     * 
     * @since 1.0
     */
    public function __construct($sendAccName, $receivers = []) {
        parent::__construct($sendAccName);
        $activeJob = Cron::activeJob();

        if ($activeJob !== null) {
            if (gettype($receivers) == 'array' && count($receivers) != 0) {
                foreach ($receivers as $addr => $name) {
                    $this->addReceiver($name, $addr);
                }
            }


            $this->setPriority(1);
            $this->getDocument()->getBody()->setStyle([
                'font-family' => 'monospace'
            ]);
            $dearNode = new HTMLNode('p');
            $dearNode->addTextNode('Dear,');
            $this->insert($dearNode);
            $paragraph = new HTMLNode('p');
            $paragraph->setStyle([
                'text-align' => 'justify'
            ]);

            if ($activeJob->isSuccess()) {
                $this->setSubject('Background Task Status: Task \''.$activeJob->getJobName().'\' 😃');
                $text = 'This automatic system email is sent to notify you that the background job '
                        .'\''.$activeJob->getJobName().'\' was <b style="color:green">successfully completed '
                        .'without any issues</b>. For more details about execution process, '
                        .'please check the attached execution log file.</p>';
            } else {
                $this->setSubject('Background Task Status: Task \''.$activeJob->getJobName().'\' 😲');
                $text = 'This automatic email is sent to notify you that the background job '
                        .'\''.$activeJob->getJobName().'\' <b style="color:red">did not successfully complet due some error(s)'
                        .'</b>. To investigate the cause of failure, '
                        .'please check the attached execution log file. It may lead you to '
                        .'the cause of the issue.';
            }
            $paragraph->addTextNode($text, false);
            $this->insert($paragraph);
            $this->insert('p')->text('Technical Info:');
            $this->insert($this->_createJobInfoTable($activeJob));
            $logTxt = '';

            foreach (Cron::getLogArray() as $logEntry) {
                $logTxt .= $logEntry."\r\n";
            }
            $file = new File($activeJob->getJobName().'-ExecLog-'.date('Y-m-d H-i-s').'.log');
            $file->setRawData($logTxt);
            $this->addAttachment($file);
        }
    }
    /**
     * 
     * @param AbstractJob $job
     * @return HTMLNode
     */
    private function _createJobInfoTable($job) {
        $jobTable = new HTMLNode('table');
        $jobTable->setStyle([
            'border-collapse' => 'collapse'
        ]);
        $jobTable->setAttribute('border', 1);
        $jobTable->addChild($this->_createTableRow('Job Name:', $job->getJobName()));
        $jobTable->addChild($this->_createTableRow('Exprssion:', $job->getExpression()));
        $jobTable->addChild($this->_createTableRow('Check Started:', Cron::timestamp()));
        $jobTable->addChild($this->_createTableRow('Run Time:', date('Y-m-d H:i:s')));
        $jobTable->addChild($this->_createTableRow('PHP Version:', PHP_VERSION));
        $jobTable->addChild($this->_createTableRow('Framework Version:', WF_VERSION));
        $jobTable->addChild($this->_createTableRow('Framework Release Date:', WF_RELEASE_DATE));
        $jobTable->addChild($this->_createTableRow('Root Directory:', ROOT_DIR));
        $jobTable->addChild($this->_createTableRow('Application Directory:', ROOT_DIR.DS.APP_DIR_NAME));
        $jobTable->addChild($this->_createTableRow('Application Version:', WebFioriApp::getAppConfig()->getVersion()));
        $jobTable->addChild($this->_createTableRow('Version Type:', WebFioriApp::getAppConfig()->getVersionType()));
        $jobTable->addChild($this->_createTableRow('Application Release Date:', WebFioriApp::getAppConfig()->getReleaseDate()));

        if ($job->isSuccess()) {
            $jobTable->addChild($this->_createTableRow('Exit Status:', '<b style="color:green">Success</b>'));
        } else {
            $jobTable->addChild($this->_createTableRow('Exit Status:', '<b style="color:red">Failed</b>'));
        }

        return $jobTable;
    }
    private function _createTableRow($label, $info) {
        $row = new TableRow();
        $row->addCell('<b>'.$label.'</b>');
        $row->addCell($info);

        return $row;
    }
}
