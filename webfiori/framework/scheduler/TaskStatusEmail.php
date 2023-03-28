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
 * background task execution.
 * 
 * This class must be only used in one of the abstract methods of a 
 * background task since using it while no task is active will have no 
 * effect.
 * 
 * The email that will be sent will contain technical information about 
 * the task in addition to a basic log file that shows execution steps. Also, 
 * it will contain any log messages which was added by using the method 
 * 'Cron::log()'.
 * 
 * @author Ibrahim
 * 
 * @version 1.0.2
 */
class TaskStatusEmail extends EmailMessage {
    /**
     * Creates new instance of the class.
     * 
     * @param string $sendAccName The name of SMTP account that will be 
     * used to send the message. Note that it must be existed in the class
     * 'MailConfig'.
     * 
     * @param array $receivers An associative array of receivers. The 
     * indices are the addresses of the receivers and the values are the 
     * names of the receivers (e.g. 'xy@example.com' => 'Super User');
     * 
     * @since 1.0
     */
    public function __construct($sendAccName, array $receivers = []) {
        parent::__construct($sendAccName);
        $activeTask = TasksManager::activeTask();

        if ($activeTask !== null) {
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

            if ($activeTask->isSuccess()) {
                $this->setSubject('Background Task Status: Task \''.$activeTask->getTaskName().'\' 😃');
                $text = 'This automatic system email is sent to notify you that the background task '
                        .'\''.$activeTask->getTaskName().'\' was <b style="color:green">successfully completed '
                        .'without any issues</b>. For more details about execution process, '
                        .'please check the attached execution log file.</p>';
            } else {
                $this->setSubject('Background Task Status: Task \''.$activeTask->getTaskName().'\' 😲');
                $text = 'This automatic email is sent to notify you that the background task '
                        .'\''.$activeTask->getTaskName().'\' <b style="color:red">did not successfully complet due some error(s)'
                        .'</b>. To investigate the cause of failure, '
                        .'please check the attached execution log file. It may lead you to '
                        .'the cause of the issue.';
            }
            $paragraph->addTextNode($text, false);
            $this->insert($paragraph);
            $this->insert('p')->text('Technical Info:');
            $this->insert($this->createTaskInfoTable($activeTask));
            $logTxt = '';

            foreach (TasksManager::getLogArray() as $logEntry) {
                $logTxt .= $logEntry."\r\n";
            }
            $file = new File(ROOT_PATH.DS.APP_DIR.DS.'sto'.DS.'logs'.DS.'cron'.DS.$activeTask->getTaskName().'-ExecLog-'.date('Y-m-d H-i-s').'.log');
            $file->setRawData($logTxt);
            $file->create(true);
            $file->write();
            $this->addAttachment($file);
        }
    }
    /**
     * 
     * @param AbstractTask $task
     * @return HTMLNode
     */
    private function createTaskInfoTable(AbstractTask $task): HTMLNode {
        $taskTable = new HTMLNode('table');
        $taskTable->setStyle([
            'border-collapse' => 'collapse'
        ]);
        $taskTable->setAttribute('border', 1);
        $taskTable->addChild($this->createTableRow('Task Name:', $task->getTaskName()));
        $taskTable->addChild($this->createTableRow('Expression:', $task->getExpression()));
        $taskTable->addChild($this->createTableRow('Check Started:', TasksManager::timestamp()));
        $taskTable->addChild($this->createTableRow('Run Time:', date('Y-m-d H:i:s')));
        $taskTable->addChild($this->createTableRow('PHP Version:', PHP_VERSION));
        $taskTable->addChild($this->createTableRow('Framework Version:', WF_VERSION));
        $taskTable->addChild($this->createTableRow('Framework Release Date:', WF_RELEASE_DATE));
        $taskTable->addChild($this->createTableRow('Root Directory:', ROOT_PATH));
        $taskTable->addChild($this->createTableRow('Application Directory:', ROOT_PATH.DS.APP_DIR));
        $taskTable->addChild($this->createTableRow('Application Version:', WebFioriApp::getAppConfig()->getVersion()));
        $taskTable->addChild($this->createTableRow('Version Type:', WebFioriApp::getAppConfig()->getVersionType()));
        $taskTable->addChild($this->createTableRow('Application Release Date:', WebFioriApp::getAppConfig()->getReleaseDate()));

        if ($task->isSuccess()) {
            $taskTable->addChild($this->createTableRow('Exit Status:', '<b style="color:green">Success</b>'));
        } else {
            $taskTable->addChild($this->createTableRow('Exit Status:', '<b style="color:red">Failed</b>'));
        }

        return $taskTable;
    }
    private function createTableRow($label, $info) {
        $row = new TableRow();
        $row->addCell('<b>'.$label.'</b>');
        $row->addCell($info);

        return $row;
    }
}
