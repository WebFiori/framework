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

use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\cli\writers\ServiceHolder;
use webfiori\framework\cli\writers\WebServiceWriter;
use webfiori\http\AbstractWebService;
use webfiori\http\ParamTypes;
use webfiori\http\RequestParameter;
use webfiori\framework\cli\helpers\CreateClassHelper;

/**
 * A helper class for creating web services classes.
 *
 * @author Ibrahim
 */
class CreateWebService extends CreateClassHelper {
    /**
     * Creates new instance of the class.
     * 
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        $serviceObj = new ServiceHolder();
        parent::__construct($command, new WebServiceWriter($serviceObj));

        $this->setClassInfo(APP_DIR_NAME.'\\apis', 'Service');

        

        $this->_setServiceName($serviceObj);
        $serviceObj->addRequestMethod($this->select('Request method:', AbstractWebService::METHODS, 0));

        if ($this->confirm('Would you like to add request parameters to the service?', false)) {
            $this->_addParamsToService($serviceObj);
        }

        $this->println('Creating the class...');
        $this->writeClass();
        $this->info('Don\'t forget to add the service to a services manager.');
    }
    /**
     * 
     * @param AbstractWebService $serviceObj
     */
    private function _addParamsToService($serviceObj) {
        $addMore = true;

        do {
            $paramObj = new RequestParameter('h');
            $paramObj->setType($this->select('Choose parameter type:', ParamTypes::getTypes(), 0));
            $this->_setParamName($paramObj);
            $added = $serviceObj->addParameter($paramObj);
            $paramObj->setIsOptional($this->confirm('Is this parameter optional?', true));

            if ($added) {
                $this->success('New parameter added to the service \''.$serviceObj->getName().'\'.');
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
    private function _setParamName($paramObj) {
        $validName = false;

        do {
            $paramName = $this->getInput('Enter a name for the request parameter:');
            $validName = $paramObj->setName($paramName);

            if (!$validName) {
                $this->error('Given name is invalid.');
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
            $serviceName = $this->getInput('Enter a name for the new web service:');
            $validName = $serviceObj->setName($serviceName);

            if (!$validName) {
                $this->error('Given name is invalid.');
            }
        } while (!$validName);
    }
}
