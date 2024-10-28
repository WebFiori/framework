<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2024 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace webfiori\framework\cli\helpers;

use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\writers\APITestCaseWriter;
use webfiori\http\WebServicesManager;
/**
 * A helper class which is used to help in creating test cases for web APIs classes using CLI.
 *
 * @author Ibrahim
 *
 */
class CreateAPITestCase extends CreateClassHelper {
    /**
     * @var APITestCaseWriter
     */
    private $writer;
    /**
     * Creates new instance of the class.
     *
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        parent::__construct($command, new APITestCaseWriter());
        $this->writer = $this->getWriter();
    }
    public function readClassInfo() {
        if (!$this->readManagerInfo()) {
            return false;
        }
        if (!$this->readServiceInfo()) {
            return false;
        }
        $nsArr = explode('\\', get_class($this->writer->getService()));
        array_pop($nsArr);
        $ns = implode('\\', $nsArr);
        $this->setClassName($this->writer->getServiceName().'Test');
        $this->setNamespace('tests\\'.$ns);
        $this->setPath(ROOT_PATH.DS.'tests'.DS.implode(DS, $nsArr));


        if ($this->getCommand()->isArgProvided('--defaults')) {
            $this->writeClass();
        } else {
            $this->checkPlace($ns);
        }

        return true;
    }
    private function checkPlace($ns) {
        $this->println("Test case will be created with following parameters:");
        $this->println("PHPUnit Version: ".$this->writer->getPhpUnitVersion());
        $this->println("Name: ".$this->getWriter()->getName(true));
        $this->println("Path: ".$this->getWriter()->getPath());
        $confrm = $this->confirm('Would you like to use default parameters?', true);

        if ($confrm) {
            $this->writeClass();
        } else {
            $this->writer->setPhpUnitVersion($this->getCommand()->readInteger('PHPUnit Version:', 11));
            $this->setClassInfo('tests\\'.$ns, 'Test');
            $this->writeClass();
        }
    }
    private function readManagerInfo() : bool {
        $m = $this->getCommand()->getArgValue('--manager');
        $instance = null;

        if ($m !== null) {
            if (class_exists($m)) {
                $instance = new $m();

                if ($instance instanceof WebServicesManager) {
                    $this->writer->setServicesManager($instance);

                    return true;
                } else {
                    $this->error("The argument --manager has invalid value.");

                    return false;
                }
            } else {
                $this->error("The argument --manager has invalid value.");

                return false;
            }
        }

        if ($instance === null) {
            while (!($instance instanceof WebServicesManager)) {
                $instance = $this->getCommand()->readInstance('Please enter services manager information:');

                if (!($instance instanceof WebServicesManager)) {
                    $this->error('Provided class is not an instance of '.WebServicesManager::class);
                } else {
                    $this->writer->setServicesManager($instance);

                    return true;
                }
            }
        }
    }
    private function readServiceInfo() : bool {
        $selected = $this->getCommand()->getArgValue('--service');
        $services = $this->writer->getServicesManager()->getServices();

        if ($selected !== null) {
            if (!isset($services[$selected])) {
                $this->info('Selected services manager has no service with name \''.$selected.'\'.');
            } else {
                $this->writer->setService($services[$selected]);

                return true;
            }
        }

        if (count($services) == 0) {
            $this->info('Provided services manager has 0 registered services.');

            return false;
        }
        $selected = $this->select('Which service you would like to have a test case for?', array_keys($services));
        $this->writer->setService($services[$selected]);
        return true;
    }
}
