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
namespace webfiori\framework\cli;

use InvalidArgumentException;
use restEasy\WebService;
use restEasy\WebServicesSet;
use webfiori\http\AbstractWebService;
use webfiori\http\RequestParameter;

/**
 * A writer class which is used to create new web service class.
 *
 * @author Ibrahim
 * @version 1.0
 */
class WebServiceWriter extends ClassWriter {
    /**
     *
     * @var AbstractWebService
     */
    private $servicesObj;
    /**
     * Creates new instance of the class.
     * 
     * @param AbstractWebService $webServicesObj The object that will be written to the 
     * class.
     * 
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
        if (!$webServicesObj instanceof AbstractWebService) {
            throw new InvalidArgumentException('Given parameter is not an instance of \'webfiori\restEasy\AbstractWebService\'');
        }
        $this->servicesObj = $webServicesObj;
        $this->_writeHeaderSec();
        $this->_writeConstructor();
        $this->_implementMethods();
        $this->append('}');
    }
    /**
     * 
     * @param RequestParameter $param
     */
    private function _appendParam($param) {
        $this->append('$this->addParameter([', 2);
        $this->append("'name' => '".$param->getName()."',", 3);
        $this->append("'type' => '".$param->getType()."',", 3);

        if ($param->isOptional()) {
            $this->append("'optional' => true,", 3);
        }

        if ($param->getDefault() !== null) {
            $param->setDefault($param);

            if (($param->getType() == 'string' || $param->getType() == 'url' || $param->getType() == 'email') && strlen($param->getDefault()) > 0) {
                $this->append("'default' => '".$param->getDefault()."',", 3);
            } else if ($param->getType() == 'boolean') {
                if ($param->getDefault() === true) {
                    $this->append("'default' => true,", 3);
                } else {
                    $this->append("'default' => false,", 3);
                }
            } else {
                $this->append("'default' => ".$param->getDefault().",", 3);
            }
        }

        if (($param->getType() == 'string' || $param->getType() == 'url' || $param->getType() == 'email') && $param->isEmptyStringAllowed()) {
            $this->append("'allow-empty' => '".$param->getDefault()."',", 3);
        }

        if ($param->getDescription() !== null) {
            $this->append("'description' => '".str_replace('\'', '\\\'', $param->getDefault())."',", 3);
        }
        $this->append(']);', 2);
    }
    private function _implementMethods() {
        $name = $this->servicesObj->getName();
        $this->append("/**", 1);
        $this->append(" * Checks if the client is authorized to call a service or not.", 1);
        $this->append(" *", 1);
        $this->append(" * @return boolean If the client is authorized, the method will return true.", 1);
        $this->append(" */", 1);
        $this->append("public function isAuthorized() {", 1);
        $this->append('// TODO: Check if the client is authorized to call the service \''.$name.'\'.', 2);
         $this->append('// You can ignore this method or remove it.', 2);
        $this->append('}', 1);

        $this->append("/**", 1);
        $this->append(" * Process the request.", 1);
        $this->append(" */", 1);
        $this->append("public function processRequest() {", 1);
        $this->append('// TODO: process the request for the service \''.$name.'\'.', 2);
        $this->append('$this->getManager()->serviceNotImplemented();', 2);
        $this->append('}', 1);
    }

    private function _writeConstructor() {
        $this->append("/**", 1);
        $this->append(" * Creates new instance of the class.", 1);
        $this->append(" */", 1);
        $this->append('public function __construct(){', 1);
        $this->append('parent::__construct(\''.$this->servicesObj->getName().'\');', 2);
        $this->append('$this->addRequestMethod(\''.$this->servicesObj->getRequestMethods()[0].'\');', 2);
        foreach ($this->servicesObj->getParameters() as $paramObj) {
            $this->_appendParam($paramObj);
        }
        $this->append('}', 1);
    }
    private function _writeHeaderSec() {
        $this->append("<?php\n");
        $this->append('namespace '.$this->getNamespace().";\n");
        $this->append("use webfiori\\http\\AbstractWebService;");
        $this->append("");
        $this->append("/**");
        $this->append(" * A class that contains the implementation of the web service '".$this->servicesObj->getName()."'.");
        $this->_writeServiceDoc($this->servicesObj);
        $this->append(" */");
        $this->append('class '.$this->getName().' extends AbstractWebService {');
    }
    private function _writeServiceDoc($service) {
        if (count($service->getParameters()) != 0) {
            $this->append(" * This service has the following parameters:");
            $this->append(' * <ul>');

            foreach ($service->getParameters() as $param) {
                $this->append(' * <li><b>'.$param->getName().'</b>: Data type: '.$param->getType().'.</li>');
            }
            $this->append(' * </ul>');
        }
    }
}
