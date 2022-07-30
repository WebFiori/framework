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
namespace webfiori\framework\cli\writers;

use webfiori\framework\EAbstractWebService;
use webfiori\framework\writers\ClassWriter;
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
    public function __construct($webServicesObj = null) {
        parent::__construct('NewWebService', ROOT_DIR.DS.APP_DIR_NAME.DS.'apis', APP_DIR_NAME.'\\apis');
        
        $this->setSuffix('Service');
        $this->addUseStatement(EAbstractWebService::class);
        $this->servicesObj = new ServiceHolder();
        
        if (($webServicesObj instanceof AbstractWebService)) {
            $this->servicesObj = $webServicesObj;
            
        }
    }
    /**
     * Adds new request parameter.
     * 
     * The parameter will only be added if no parameter which has the same 
     * name as the given one is added before.
     * 
     * @param RequestParameter|array $param The parameter that will be added. It 
     * can be an object of type 'RequestParameter' or an associative array of 
     * options. The array can have the following indices:
     * <ul>
     * <li><b>name</b>: The name of the parameter. It must be provided.</li>
     * <li><b>type</b>: The datatype of the parameter. If not provided, 'string' is used.</li>
     * <li><b>optional</b>: A boolean. If set to true, it means the parameter is 
     * optional. If not provided, 'false' is used.</li>
     * <li><b>min</b>: Minimum value of the parameter. Applicable only for 
     * numeric types.</li>
     * <li><b>max</b>: Maximum value of the parameter. Applicable only for 
     * numeric types.</li>
     * <li><b>allow-empty</b>: A boolean. If the type of the parameter is string or string-like 
     * type and this is set to true, then empty strings will be allowed. If 
     * not provided, 'false' is used.</li>
     * <li><b>custom-filter</b>: A PHP function that can be used to filter the 
     * parameter even further</li>
     * <li><b>default</b>: An optional default value to use if the parameter is 
     * not provided and is optional.</li>
     * <li><b>description</b>: The description of the attribute.</li>
     * </ul>
     * 
     * @return boolean If the given request parameter is added, the method will 
     * return true. If it was not added for any reason, the method will return 
     * false.
     * 
     * @since 1.0
     */
    public function addRequestParam($options) : bool {
        return $this->servicesObj->addParameter($options);
    }
    /**
     * Adds new request method.
     * 
     * The value that will be passed to this method can be any string 
     * that represents HTTP request method (e.g. 'get', 'post', 'options' ...). It 
     * can be in upper case or lower case.
     * 
     * @param string $meth The request method.
     * 
     */
    public function addRequestMethod($meth) {
        $this->servicesObj->addRequestMethod($meth);
    }
    /**
     * 
     * @param RequestParameter $param
     */
    private function _appendParam($param) {
        $this->append("'".$param->getName()."' => [", 3);
        $this->append("'type' => '".$param->getType()."',", 4);

        if ($param->isOptional()) {
            $this->append("'optional' => true,", 4);
        }

        if ($param->getDefault() !== null) {

            if (($param->getType() == 'string' || $param->getType() == 'url' || $param->getType() == 'email') && strlen($param->getDefault()) > 0) {
                $this->append("'default' => '".$param->getDefault()."',", 4);
            } else if ($param->getType() == 'boolean') {
                if ($param->getDefault() === true) {
                    $this->append("'default' => true,", 4);
                } else {
                    $this->append("'default' => false,", 4);
                }
            } else {
                $this->append("'default' => ".$param->getDefault().",", 4);
            }
        }

        if (($param->getType() == 'string' || $param->getType() == 'url' || $param->getType() == 'email') && $param->isEmptyStringAllowed()) {
            $this->append("'allow-empty' => true,", 4);
        }

        if ($param->getDescription() !== null) {
            $this->append("'description' => '".str_replace('\'', '\\\'', $param->getDescription())."',", 4);
        }
        $this->append('],', 3);
    }
    private function _implementMethods() {
        $name = $this->servicesObj->getName();
        $this->append([
            "/**",
            " * Checks if the client is authorized to call a service or not.",
            " *",
            " * @return boolean If the client is authorized, the method will return true.",
            " */",
            "public function isAuthorized() {",
        ], 1);
        $this->append([
            '// TODO: Check if the client is authorized to call the service \''.$name.'\'.',
            '// You can ignore this method or remove it.',
            '//$authHeader = $this->getAuthHeader();',
            '//$authType = $authHeader[\'type\'];',
            '//$token = $authHeader[\'credentials\'];'
        ], 2);
        $this->append('}', 1);

        $this->append([
            "/**",
            " * Process the request.",
            " */",
            "public function processRequest() {",
        ], 1);
        $this->append('// TODO: process the request for the service \''.$name.'\'.', 2);
        $this->append('$this->getManager()->serviceNotImplemented();', 2);
        $this->append('}', 1);
    }

    private function _writeConstructor() {
        $this->append([
            "/**",
            " * Creates new instance of the class.",
            " */",
            'public function __construct(){',
        ], 1);
        $this->append('parent::__construct(\''.$this->servicesObj->getName().'\');', 2);
        $this->append('$this->addRequestMethod(\''.$this->servicesObj->getRequestMethods()[0].'\');', 2);
        $this->_appendParams($this->servicesObj->getParameters());
        $this->append('}', 1);
    }
    private function _appendParams($paramsArray) {
        if (count($paramsArray) !== 0) {
            $this->append('$this->addParameters([', 2);
            
            foreach ($paramsArray as $paramObj) {
                $this->_appendParam($paramObj);
            }
            $this->append(']);', 2);
        }
    }
    private function _writeServiceDoc($service) {
        $docArr = [];
        if (count($service->getParameters()) != 0) {
            $docArr[] = " * This service has the following parameters:";
            $docArr[] = ' * <ul>';

            foreach ($service->getParameters() as $param) {
                $docArr[] = ' * <li><b>'.$param->getName().'</b>: Data type: '.$param->getType().'.</li>';
            }
            $docArr[] = ' * </ul>';
            $this->append($docArr);
        }
    }

    public function writeClassBody() {
        $this->_writeConstructor();
        $this->_implementMethods();
        $this->append('}');
    }

    public function writeClassComment() {
        $this->append([
            "",
            '',
            "/**",
            " * A class that contains the implementation of the web service '".$this->servicesObj->getName()."'."
        ]);
        $this->_writeServiceDoc($this->servicesObj);
        $this->append(" */");
    }

    public function writeClassDeclaration() {
        $this->append('class '.$this->getName().' extends EAbstractWebService {');
    }

}
