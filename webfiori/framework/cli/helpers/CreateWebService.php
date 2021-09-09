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
namespace webfiori\framework\cli\helpers;

use webfiori\http\AbstractWebService;
use webfiori\http\ParamTypes;
use webfiori\http\RequestParameter;
use webfiori\framework\cli\commands\CreateCommand;

/**
 * A helper class for creating web services classes.
 *
 * @author Ibrahim
 */
class CreateWebService {
    /**
     *
     * @var CLICommand 
     */
    private $command;
    /**
     * Creates new instance of the class.
     * 
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        $this->command = $command;

        $classInfo = $this->_getCommand()->getClassInfo(APP_DIR_NAME.'\\apis');

        $serviceObj = new ServiceHolder();

        $this->_setServiceName($serviceObj);
        $serviceObj->addRequestMethod($this->_getCommand()->select('Request method:', AbstractWebService::METHODS, 0));

        if ($this->_getCommand()->confirm('Would you like to add request parameters to the service?', false)) {
            $this->_addParamsToService($serviceObj);
        }



        $this->_getCommand()->println('Creating the class...');
        $servicesCreator = new WebServiceWriter($serviceObj, $classInfo);
        $servicesCreator->writeClass();
        $this->_getCommand()->success('Class created.');
        $this->_getCommand()->info('Don\'t forget to add the service to a services manager.');
    }
    /**
     * 
     * @param AbstractWebService $serviceObj
     */
    private function _addParamsToService($serviceObj) {
        $addMore = true;

        do {
            $paramObj = new RequestParameter('h');
            $paramObj->setType($this->_getCommand()->select('Choose parameter type:', ParamTypes::getTypes(), 0));
            $this->_setParamName($paramObj);
            $added = $serviceObj->addParameter($paramObj);
            $paramObj->setIsOptional($this->_getCommand()->confirm('Is this parameter optional?', true));

            if ($added) {
                $this->_getCommand()->success('New parameter added to the service \''.$serviceObj->getName().'\'.');
            } else {
                $this->_getCommand()->warning('The parameter was not added.');
            }
            $addMore = $this->_getCommand()->confirm('Would you like to add another parameter?', false);
        } while ($addMore);
    }
    /**
     * 
     * @return CreateCommand
     */
    private function _getCommand() {
        return $this->command;
    }
    /**
     * 
     * @param RequestParameter $paramObj
     */
    private function _setParamName($paramObj) {
        $validName = false;

        do {
            $paramName = $this->_getCommand()->getInput('Enter a name for the request parameter:');
            $validName = $paramObj->setName($paramName);

            if (!$validName) {
                $this->_getCommand()->error('Given name is invalid.');
            }
        } while (!$validName);
    }
    /**
     * 
     * @param WebService $serviceObj
     */
    private function _setServiceName($serviceObj) {
        $validName = false;

        do {
            $serviceName = $this->_getCommand()->getInput('Enter a name for the new web service:');
            $validName = $serviceObj->setName($serviceName);

            if (!$validName) {
                $this->_getCommand()->error('Given name is invalid.');
            }
        } while (!$validName);
    }
}
