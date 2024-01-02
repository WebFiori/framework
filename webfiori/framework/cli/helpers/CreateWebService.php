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
namespace webfiori\framework\cli\helpers;

use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\writers\ServiceHolder;
use webfiori\framework\writers\WebServiceWriter;
use webfiori\http\ParamTypes;
use webfiori\http\RequestMethod;
use webfiori\http\RequestParameter;

/**
 * A helper class for creating web services classes.
 *
 * @author Ibrahim
 */
class CreateWebService extends CreateClassHelper {
    private $serviceObj;
    /**
     * Creates new instance of the class.
     *
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        $this->serviceObj = new ServiceHolder();
        parent::__construct($command, new WebServiceWriter($this->serviceObj));
    }
    public function readClassInfo() {
        $this->setClassInfo(APP_DIR.'\\apis', 'Service');

        $this->setServiceName();
        $this->serviceObj->addRequestMethod($this->select('Request method:', RequestMethod::getAll(), 0));

        if ($this->confirm('Would you like to add request parameters to the service?', false)) {
            $this->addParamsToService();
        }

        $this->println('Creating the class...');
        $this->writeClass();
        $this->info('Don\'t forget to add the service to a services manager.');
    }
    private function addParamsToService() {
        do {
            $paramObj = new RequestParameter('h');
            $paramObj->setType($this->select('Choose parameter type:', ParamTypes::getTypes(), 0));
            $this->setParamName($paramObj);
            $added = $this->serviceObj->addParameter($paramObj);
            $paramObj->setIsOptional($this->confirm('Is this parameter optional?', true));

            if ($added) {
                $this->success('New parameter added to the service \''.$this->serviceObj->getName().'\'.');
            } else {
                $this->warning('The parameter was not added.');
            }
            $addMore = $this->confirm('Would you like to add another parameter?', false);
        } while ($addMore);
    }
    /**
     *
     * @param RequestParameter $paramObj
     */
    private function setParamName(RequestParameter $paramObj) {
        do {
            $paramName = $this->getInput('Enter a name for the request parameter:');
            $validName = $paramObj->setName($paramName);

            if (!$validName) {
                $this->error('Given name is invalid.');
            }
        } while (!$validName);
    }
    private function setServiceName() {
        do {
            $serviceName = $this->getInput('Enter a name for the new web service:');
            $validName = $this->serviceObj->setName($serviceName);

            if (!$validName) {
                $this->error('Given name is invalid.');
            }
        } while (!$validName);
    }
}
