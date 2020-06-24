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
use restEasy\APIAction;
use restEasy\RequestParameter;
use restEasy\WebServices;

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
     * Creates new instance of the class.
     * @param WebServices $webServicesObj The object that will be written to the 
     * class.
     * @param array $classInfoArr An associative array that contains the information 
     * of the class that will be created. The array must have the following indices: 
     * <ul>
     * <li><b>name</b>: The name of the class that will be created. If not provided, the 
     * string 'NewClass' is used.</li>
     * <li><b>namespace</b>: The namespace that the class will belong to. If not provided, 
     * the namespace 'webfiori' is used.</li>
     * <li><b>path</b>: The location at which the query will be created on. If not 
     * provided, the constant ROOT_DIR is used. </li>
     * </ul>
     */
    public function __construct($webServicesObj, $classInfoArr) {
        parent::__construct($classInfoArr);

        if (!$webServicesObj instanceof WebServices) {
            throw new InvalidArgumentException('Given parameter is not an instance of \'WebServices\'');
        }
        $this->servicesObj = $webServicesObj;
        $this->_writeHeaderSec();
        $this->_writeConstructor();
        $this->_implementMethods();
        $this->append('}');
        $this->append('return __NAMESPACE__;');
    }
    private function _addServices() {
        foreach ($this->servicesObj->getActions() as $action) {
            $this->_appendService($action);
        }

        foreach ($this->servicesObj->getAuthActions() as $action) {
            $this->_appendService($action, true);
        }
    }
    /**
     * 
     * @param RequestParameter $param
     */
    private function _appendParam($param) {
        $this->append('[', 4);
        $this->append("'name' => '".$param->getName()."',", 5);
        $this->append("'type' => '".$param->getType()."',", 5);

        if ($param->isOptional()) {
            $this->append("'optional' => true,", 5);
        }

        if ($param->getDefault() !== null) {
            $param->setDefault($param);

            if (($param->getType() == 'string' || $param->getType() == 'url' || $param->getType() == 'email') && strlen($param->getDefault()) > 0) {
                $this->append("'default' => '".$param->getDefault()."',", 5);
            } else {
                if ($param->getType() == 'boolean') {
                    if ($param->getDefault() === true) {
                        $this->append("'default' => true,", 5);
                    } else {
                        $this->append("'default' => false,", 5);
                    }
                } else {
                    $this->append("'default' => ".$param->getDefault().",", 5);
                }
            }
        }
 
        if (($param->getType() == 'string' || $param->getType() == 'url' || $param->getType() == 'email') && $param->isEmptyStringAllowed()) {
            $this->append("'allow-empty' => '".$param->getDefault()."',", 5);
        }

        if ($param->getDescription() !== null) {
            $this->append("'description' => '".str_replace('\'', '\\\'', $param->getDefault())."',", 5);
        }
        $this->append('],', 4);
    }
    /**
     * 
     * @param APIAction $service
     */
    private function _appendService($service, $requireAuth = false) {
        $this->append('$this->addAction(APIAction::createService([', 2);
        $this->append("'name' => '".$service->getName()."',", 3);
        $this->append("'request-methods' => [", 3);

        foreach ($service->getRequestMethods() as $method) {
            $this->append("'$method',", 4);
        }
        $this->append("],", 3);

        if (count($service->getParameters()) != 0) {
            $this->append("'parameters' => [", 3);

            foreach ($service->getParameters() as $param) {
                $this->_appendParam($param);
            }
            $this->append("],", 3);
        }

        if (count($service->getResponsesDescriptions()) != 0) {
            $this->append("'responses' => [", 3);

            foreach ($service->getResponsesDescriptions() as $desc) {
                $this->append("'".str_replace('\'', '\\\'', $desc)."',");
            }
            $this->append("],", 3);
        }

        if ($requireAuth) {
            $this->append('], true));', 2);
        } else {
            $this->append('], false));', 2);
        }
    }
    private function _implementMethods() {
        $this->append("/**", 1);
        $this->append(" * Checks if the client is authorized to call a service or not.", 1);
        $this->append(" * @return boolean If the client is authorized, the method will return true.", 1);
        $this->append(" */", 1);
        $this->append("public function isAuthorized() {", 1);
        $this->append('$calledServiceName = $this->getAction();', 2);

        $authActionIndex = 0;
        $authCount = count($this->servicesObj->getAuthActions());

        foreach ($this->servicesObj->getAuthActions() as $service) {
            if ($authActionIndex == 0) {
                $this->append('if ($calledServiceName == \''.$service->getName().'\') {', 2);
                $this->append('// TODO: Check if the client is authorized to call the service \''.$service->getName().'\'.', 3);
            } else {
                $this->append('} else if ($calledServiceName == \''.$service->getName().'\') {', 2);
                $this->append('// TODO: Check if the client is authorized to call the service \''.$service->getName().'\'.', 3);
            }

            if ($authActionIndex + 1 == $authCount) {
                $this->append('}', 2);
            }
            $authActionIndex++;
        }

        $this->append('return false;', 2);
        $this->append('}', 1);

        $this->append("/**", 1);
        $this->append(" * Process the request.", 1);
        $this->append(" */", 1);
        $this->append("public function processRequest() {", 1);
        $this->append('$calledServiceName = $this->getAction();', 2);


        $totalActions = count($this->servicesObj->getActions()) + $authCount;

        $actionIndex = 0;

        foreach ($this->servicesObj->getActions() as $service) {
            $this->_serviceWrite($service, $actionIndex, $totalActions);
        }

        foreach ($this->servicesObj->getAuthActions() as $service) {
            $this->_serviceWrite($service, $actionIndex, $totalActions);
        }
        $this->append('}', 1);
    }
    private function _serviceWrite($service, &$actionIndex, $totalActions) {
        if ($actionIndex == 0) {
            $this->append('if ($calledServiceName == \''.$service->getName().'\') {', 2);
            $this->append('// TODO: process the request for the service \''.$service->getName().'\'.', 3);
        } else {
            $this->append('} else if ($calledServiceName == \''.$service->getName().'\') {', 2);
            $this->append('// TODO: process the request for the service \''.$service->getName().'\'.', 3);
        }

        if ($actionIndex + 1 == $totalActions) {
            $this->append('}', 2);
        }
        $actionIndex++;
    }
    private function _writeConstructor() {
        $this->append("/**", 1);
        $this->append(" * Creates new instance of the class.", 1);
        $this->append(" */", 1);
        $this->append('public function __construct(){', 1);
        $this->append('parent::__construct(\'1.0.0\');', 2);
        $this->_addServices();
        $this->append('}', 1);
    }
    private function _writeHeaderSec() {
        $this->append("<?php\n");
        $this->append('namespace '.$this->getNamespace().";\n");
        $this->append("use webfiori\\entity\\ExtendedWebServices;");
        $this->append("use restEasy\\APIAction;");
        $this->append('');
        $this->append("/**\n"
                ." * A class that contains a set of web services.\n"
                ." * This class contains the following web APIs:\n"
                ." * <ul>"
                );

        foreach ($this->servicesObj->getActions() as $service) {
            if (count($service->getParameters()) != 0) {
                $this->append(" * <li><b>".$service->getName()."</b>: This service has the following parameters:");
                $this->append(' * <ul>');

                foreach ($service->getParameters() as $param) {
                    $this->append(' * <li><b>'.$param->getName().'</b>: Data type: '.$param->getType().'.');
                }
                $this->append(' * </ul>');
                $this->append(' * </li>');
            } else {
                $this->append(" * <li><b>".$service->getName()."</b></li>");
            }
        }
        $this->append(" * </ul>\n */");
        $this->append('class '.$this->getName().' extends ExtendedWebServices {');
    }
}
