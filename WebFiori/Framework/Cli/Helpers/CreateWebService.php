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
namespace WebFiori\Framework\Cli\Helpers;

use WebFiori\Framework\Cli\Commands\CreateCommand;
use WebFiori\Framework\Writers\ServiceHolder;
use WebFiori\Framework\Writers\WebServiceWriter;
use WebFiori\Http\ParamType;
use WebFiori\Http\RequestMethod;
use WebFiori\Http\RequestParameter;

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
    public function addRequestMethods() {
        $toSelect = RequestMethod::getAll();
        $addOne = true;

        while ($addOne) {
            array_multisort($toSelect);
            $this->serviceObj->addRequestMethod($this->select('Request method:', $toSelect, 2));

            if (count($toSelect) > 1) {
                $addOne = $this->confirm('Would you like to add another request method?', false);
            } else {
                $addOne = false;
            }
        }
    }
    public function readClassInfo() {
        $this->setClassInfo(APP_DIR.'\\Apis', 'Service');

        $this->setServiceName();
        $this->serviceObj->setDescription($this->getInput('Description:'));
        $this->addRequestMethods();

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
            $this->setParamName($paramObj);
            $paramObj->setType($this->select('Choose parameter type:', ParamType::getTypes(), 0));
            $paramObj->setDescription($this->getInput('Description:'));
            $added = $this->serviceObj->addParameter($paramObj);
            $paramObj->setIsOptional($this->confirm('Is this parameter optional?', true));

            if ($paramObj->getType() == ParamType::STRING || $paramObj->getType() == ParamType::URL || $paramObj->getType() == ParamType::EMAIL) {
                $paramObj->setIsEmptyStringAllowed($this->confirm('Are empty values allowed?', false));
                $this->setMinAndMaxLength($paramObj);
            }

            if ($paramObj->getType() == ParamType::INT || $paramObj->getType() == ParamType::DOUBLE) {
                $this->setMinAndMax($paramObj);
            }

            if ($added) {
                $this->success('New parameter added.');
            } else {
                $this->warning('The parameter was not added.');
            }
            $addMore = $this->confirm('Would you like to add another parameter?', false);
        } while ($addMore);
    }
    private function setMinAndMax(RequestParameter $param) {
        $setMinMax = $this->confirm('Would you like to set minimum and maximum limites?', false);

        if (!$setMinMax) {
            return;
        }
        $isValid = false;
        $method = $param->getType() == ParamType::INT ? 'readInteger' : 'readFloat';

        while (!$isValid) {
            $min = $this->getCommand()->$method('Minimum value:');
            $max = $this->getCommand()->$method('Maximum value:');

            if ($min < $max) {
                $param->setMinValue($min);
                $param->setMaxValue($max);
                $isValid = true;
            } else {
                $this->error('Minimum and maximum should not overlap.');
            }
        }
    }
    private function setMinAndMaxLength(RequestParameter $param) {
        $setMinMax = $this->confirm('Would you like to set minimum and maximum length?', false);

        if (!$setMinMax) {
            return;
        }
        $isValid = false;

        while (!$isValid) {
            $min = $this->getCommand()->readInteger('Minimum length:');
            $max = $this->getCommand()->readInteger('Maximum length:');

            if ($min < $max) {
                $param->setMinLength($min);
                $param->setMaxLength($max);
                $isValid = true;
            } else {
                $this->error('Minimum and maximum should not overlap.');
            }
        }
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
