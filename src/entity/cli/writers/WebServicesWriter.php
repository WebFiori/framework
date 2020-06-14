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

namespace webfiori\entity\cli;

use InvalidArgumentException;
use restEasy\WebServices;
use restEasy\APIAction;
use restEasy\RequestParameter;

/**
 * A writer class which is used to create new web services class.
 *
 * @author Ibrahim
 * @version 1.0
 */
class WebServicesWriter extends ClassWriter {
    /**
     *
     * @var WebServices 
     */
    private $servicesObj;
    /**
     * 
     * @param WebServices $webServicesObj
     * @param array $classInfoArr
     */
    public function __construct($webServicesObj, $classInfoArr) {
        parent::__construct($classInfoArr);
        if (!$webServicesObj instanceof WebServices) {
            throw new InvalidArgumentException('Given parameter is not an instance of \'WebServices\'');
        }
        $this->servicesObj = $webServicesObj;
        $this->_writeHeaderSec();
    }
    private function _writeHeaderSec() {
        $this->append("<?php\n");
        $this->append('namespace '.$this->getNamespace().";\n");
        $this->append("use webfiori\entity\ExtendedWebServices;");
        $this->append('');
        $this->append("/**\n"
                . " * A class that contains a set of web services.\n"
                . " * This class contains the following web APIs:\n"
                . " * <ul>"
                );
        foreach ($this->servicesObj->getActions() as $service){
            if (count($service->getParameters()) != 0) {
                $this->append(" * <li><b>".$service->getName()."</b>: This service has the following parameters:", 1);
                $this->append(' * <ul>', 1);
                foreach ($service->getParameters() as $param) {
                    $this->append(' * <li><b>'.$param->getName().'</b>: Data type: '.$param->getType().'.', 1);
                }
                $this->append(' * </ul>', 1);
                $this->append(' * </li>', 1);
            } else {
                $this->append(" * <li><b>".$service->getName()."</b>", 1);
            }
        }
        $this->append(" * </ul>\n */");
        $this->append('class '.$this->getName().' extends ExtendedWebServices {');
    }
    private function _writeConstructor() {
        $this->append("/**", 1);
        $this->append(" * Creates new instance of the class.", 1);
        $this->append(" */", 1);
        $this->append('public function __construct(){', 1);
        $this->append('parent::__construct(\'1.0.0\')', 2);
        
        $this->append('}', 1);
    }
    private function _addServices() {
        
    }
    /**
     * 
     * @param APIAction $service
     */
    private function _appendService($service) {
        $this->append('$this->addAction(APIAction::createService([', 2);
        $this->append("'name' => '".$service->getName()."',", 3);
        $this->append("'request-methods' => [", 3);
        foreach ($service->getRequestMethods() as $method) {
            $this->append("'$method',", 4);
        }
        if (count($service->getParameters()) != 0) {
            $this->append("'parameters' => [", 3);
            foreach ($service->getParameters() as $param) {
                $this->_appendParam($param);
            }
            $this->append("],", 3);
        }
        $this->append("],", 3);
        if (count($service->getResponsesDescriptions()) != 0) {
            $this->append("'responses' => [", 3);
            foreach ($service->getResponsesDescriptions() as $desc) {
                $this->append("'". str_replace('\'', '\\\'', $desc)."',");
            }
            $this->append("],", 3);
        }
        $this->append(']);', 2);
    }
    /**
     * 
     * @param RequestParameter $param
     */
    private function _appendParam($param) {
        
    }
    private function _implementMethods() {
        $this->append("public function isAuthorized() {", 1);
        $this->append('//TODO: Check if client is allowed to use the service or not.', 3);
        $this->append('}', 1);
        
        $this->append("public function processRequest() {", 1);
        $this->append('$calledServiceName = $this->getAction();', 3);
        $totalActions = count($this->servicesObj->getActions()) + count($this->servicesObj->getAuthActions());
        $actionIndex = 0;
        foreach ($this->servicesObj->getActions() as $service) {
            if ($service instanceof APIAction);
            if ($actionIndex == 0) {
                $this->append('if ($calledServiceName == \''.$service->getName().'\') {', 2);
                $this->append('// TODO: process the request for the service \''.$service->getName().'\'.', 3);
                $actionIndex++;
                continue;
            } else {
                $this->append('} else if ($calledServiceName == \''.$service->getName().'\') {', 2);
                $this->append('// TODO: process the request for the service \''.$service->getName().'\'.', 3);
            }
            if ($actionIndex + 1 == $totalActions) {
                $this->append('}', 2);
            }
            $actionIndex++;
        }
        foreach ($this->servicesObj->getAuthActions() as $service) {
            if ($actionIndex == 0) {
                $this->append('if ($calledServiceName == \''.$service->getName().'\') {', 2);
                $this->append('// TODO: process the request for the service \''.$service->getName().'\'.', 3);
                $actionIndex++;
                continue;
            } else {
                $this->append('} else if ($calledServiceName == \''.$service->getName().'\') {', 2);
                $this->append('// TODO: process the request for the service \''.$service->getName().'\'.', 3);
            }
            if ($actionIndex + 1 == $totalActions) {
                $this->append('}', 2);
            }
            $actionIndex++;
        }
        $this->append('//TODO: Check if client is allowed to use the service or not.', 3);
        $this->append('}', 1);
    }
}
