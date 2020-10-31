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
namespace webfiori\entity\cron;

use webfiori\ui\HTMLNode;
use webfiori\ui\TableRow;
use webfiori\framework\File;
use webfiori\entity\mail\EmailMessage;
use webfiori\WebFiori;
/**
 * A class which can be used to send an email regarding the status of 
 * background job execution.
 * This class must be only used in one of the abstract methods of a 
 * background job since using it while no job is active will have no 
 * effect.
 * The email that will be sent will contain technical information about 
 * the job in addition to a basic log file that shows execution steps. Also, 
 * it will contain any log messages which was added by using the method 
 * 'Cron::log()'.
 * @author Ibrahim
 * @version 1.0.1
 */
class CronEmail {
    /**
     * An array that contains the addresses of the people who will get the 
     * email.
     * @var array
     * @since 1.0.1 
     */
    private $reciversArr;
    /**
     * The name of the account that will be used to send the message.
     * @var string 
     * @since 1.0.1 
     */
    private $sendAccName;
    /**
     * Creates new instance of the class.
     * @param string $sendAccName The name of SMTP account that will be 
     * used to send the message. Note that it must be exist in the class 
     * 'MailConfig'.
     * @param array $receivers An associative array of receivers. The 
     * indices are the addresses of the receivers and the values are the 
     * names of the receivers (e.g. 'xy@example.com' => 'Super User');
     * @since 1.0
     */
    public function __construct($sendAccName, $receivers = []) {
        $activeJob = Cron::activeJob();

        if ($activeJob !== null) {
            EmailMessage::createInstance($sendAccName);

            if (gettype($receivers) == 'array' && count($receivers) != 0) {
                foreach ($receivers as $addr => $name) {
                    EmailMessage::addReceiver($name, $addr);
                }
            }

            EmailMessage::subject('Background Task Status: Task \''.$activeJob->getJobName().'\'');
            EmailMessage::importance(1);
            EmailMessage::document()->getBody()->setStyle([
                'font-family' => 'monospace'
            ]);
            $dearNode = new HTMLNode('p');
            $dearNode->addTextNode('Dear,');
            EmailMessage::insertNode($dearNode);
            $paragraph = new HTMLNode('p');
            $paragraph->setStyle([
                'text-align' => 'justify'
            ]);

            if ($activeJob->isSuccess()) {
                $text = 'This automatic system email is sent to notify you that the background job '
                        .'\''.$activeJob->getJobName().'\' was <b style="color:green">successfully completed '
                        .'without any issues</b>. For more details about execution process, '
                        .'please check the attached execution log file.</p>';
            } else {
                $text = 'This automatic email is sent to notify you that the background job '
                        .'\''.$activeJob->getJobName().'\' <b style="color:red">did not successfully complet due some error(s)'
                        .'</b>. To investigate the cause of failure, '
                        .'please check the attached execution log file. It may lead you to '
                        .'the cause of the issue.';
            }
            $paragraph->addTextNode($text, false);
            EmailMessage::insertNode($paragraph);
            EmailMessage::write('<p>Technical Info:</p>');
            EmailMessage::insertNode($this->_createJobInfoTable($activeJob));
            $logTxt = '';

            foreach (Cron::getLogArray() as $logEntry) {
                $logTxt .= $logEntry."\r\n";
            }
            $file = new File($activeJob->getJobName().'-ExecLog-'.date('Y-m-d H-i-s').'.log');
            $file->setRawData($logTxt);
            EmailMessage::attach($file);
        }
    }
    /**
     * Send the message.
     * @since 1.0
     */
    public function send() {
        EmailMessage::send();
    }
    /**
     * Returns an associative array that contains the addresses of the people 
     * who will get the notice.
     * @return array The indices of the array will be the addresses and the 
     * values will be receivers names.
     * @since 1.0.1
     */
    public function getReceivers() {
        return $this->reciversArr;
    }
    /**
     * Returns the name of the account that will be used to send the message.
     * @return string The name of the account that will be used to send the message.
     * @since 1.0.1
     */
    public function getSendAccName() {
        return $this->sendAccName;
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
        $jobTable->addChild($this->_createTableRow('Framework Version:', WebFiori::getConfig()->getVersion()));
        $jobTable->addChild($this->_createTableRow('Root Directory:', ROOT_DIR));

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
