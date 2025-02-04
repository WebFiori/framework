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
namespace webfiori\framework\writers;

use webfiori\http\AbstractWebService;
use webfiori\http\ParamOption;
use webfiori\http\ParamType;
use webfiori\http\RequestMethod;
use webfiori\http\RequestParameter;

/**
 * A writer class which is used to create new web service class.
 *
 * @author Ibrahim
 * @version 1.0
 */
class WebServiceWriter extends ClassWriter {
    private $processCode;
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
     * provided, the constant ROOT_PATH is used. </li>
     * </ul>
     */
    public function __construct(?AbstractWebService $webServicesObj = null) {
        parent::__construct('NewWebService', APP_PATH.'apis', APP_DIR.'\\apis');

        $this->setSuffix('Service');
        $this->addUseStatement(AbstractWebService::class);
        $this->addUseStatement(ParamType::class);
        $this->addUseStatement(ParamOption::class);
        $this->addUseStatement(RequestMethod::class);
        $this->servicesObj = new ServiceHolder();

        if ($webServicesObj instanceof AbstractWebService) {
            $this->servicesObj = $webServicesObj;
        }
        $this->processCode = [];
    }
    public function addProcessCode($lineOrLines, $tab = 2) {
        $arrToAdd = [
            'tab-size' => $tab,
            'lines' => []
        ];

        if (gettype($lineOrLines) == 'array') {
            foreach ($lineOrLines as $l) {
                $arrToAdd['lines'][] = $l;
            }
        } else {
            $arrToAdd['lines'][] = $lineOrLines;
        }
        $this->processCode[] = $arrToAdd;
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

    public function writeClassBody() {
        $this->writeConstructor();
        $this->implementMethods();
        $this->append('}');
    }

    public function writeClassComment() {
        $this->append([
            "",
            '',
            "/**",
            " * A class that contains the implementation of the web service '".$this->servicesObj->getName()."'."
        ]);
        $this->writeServiceDoc($this->servicesObj);
        $this->append(" */");
    }

    public function writeClassDeclaration() {
        $this->append('class '.$this->getName().' extends AbstractWebService {');
    }
    /**
     *
     * @param RequestParameter $param
     */
    private function appendParam($param) {
        $this->append("'".$param->getName()."' => [", 3);

        $this->append("ParamOption::TYPE => ".$this->getType($param->getType()).",", 4);

        if ($param->isOptional()) {
            $this->append("ParamOption::OPTIONAL => true,", 4);
        }

        if ($param->getDefault() !== null) {
            $toAppend = "ParamOption::DEFAULT => ".$param->getDefault().",";

            if (($param->getType() == ParamType::STRING || $param->getType() == ParamType::URL || $param->getType() == ParamType::EMAIL) && strlen($param->getDefault()) > 0) {
                $toAppend = "ParamOption::DEFAULT => '".$param->getDefault()."',";
            } else if ($param->getType() == ParamType::BOOL) {
                $toAppend = $param->getDefault() === true ? "ParamOption::DEFAULT => true," : "ParamOption::DEFAULT => false,";
            }
            $this->append($toAppend, 4);
        }

        if (($param->getType() == ParamType::STRING || $param->getType() == ParamType::URL || $param->getType() == ParamType::EMAIL)) {
            if ($param->isEmptyStringAllowed()) {
                $this->append("ParamOption::EMPTY => true,", 4);
            }

            if ($param->getMinLength() !== null) {
                $this->append("ParamOption::MIN_LENGTH => ".$param->getMinLength().",", 4);
            }

            if ($param->getMaxLength() !== null) {
                $this->append("ParamOption::MAX_LENGTH => ".$param->getMaxLength().",", 4);
            }
        }

        if ($param->getType() == ParamType::INT || $param->getType() == ParamType::DOUBLE) {
            $minFloat = defined('PHP_FLOAT_MIN') ? PHP_FLOAT_MIN : 2.2250738585072E-308;
            $maxFloat = defined('PHP_FLOAT_MAX') ? PHP_FLOAT_MAX : 1.7976931348623E+308;

            if ($param->getMinValue() !== null && ($param->getMinValue() != $minFloat && $param->getMinValue() != $maxFloat)) {
                $this->append("ParamOption::MIN => ".$param->getMinValue().",", 4);
            }
            $maxInt = PHP_INT_MAX;
            $minInt = defined('PHP_INT_MIN') ? PHP_INT_MIN : ~PHP_INT_MAX;

            if ($param->getMaxValue() !== null && ($param->getMaxValue() != $maxInt && $param->getMinValue() != $minInt)) {
                $this->append("ParamOption::MAX => ".$param->getMinValue().",", 4);
            }
        }

        if ($param->getDescription() !== null) {
            $this->append("ParamOption::DESCRIPTION => '".str_replace('\'', '\\\'', $param->getDescription())."',", 4);
        }
        $this->append('],', 3);
    }
    private function appendParams($paramsArray) {
        if (count($paramsArray) !== 0) {
            $this->append('$this->addParameters([', 2);

            foreach ($paramsArray as $paramObj) {
                $this->appendParam($paramObj);
            }
            $this->append(']);', 2);
        }
    }
    private function getMethod($method) {
        switch ($method) {
            case RequestMethod::CONNECT:{
                return "RequestMethod::CONNECT";
            }
            case RequestMethod::DELETE:{
                return "RequestMethod::DELETE";
            }
            case RequestMethod::GET:{
                return "RequestMethod::GET";
            }
            case RequestMethod::HEAD:{
                return "RequestMethod::HEAD";
            }
            case RequestMethod::OPTIONS:{
                return "RequestMethod::OPTIONS";
            }
            case RequestMethod::PATCH:{
                return "RequestMethod::PATCH";
            }
            case RequestMethod::POST:{
                return "RequestMethod::POST";
            }
            case RequestMethod::PUT:{
                return "RequestMethod::PUT";
            }
            case RequestMethod::TRACE:{
                return "RequestMethod::TRACE";
            }
        }
    }
    private function getType(string $type) {
        switch ($type) {
            case 'int': {
                return 'ParamType::INT';
            }
            case 'integer': {
                return 'ParamType::INT';
            }
            case 'string': {
                return 'ParamType::STRING';
            }
            case 'array': {
                return 'ParamType::ARR';
            }
            case 'bool': {
                return 'ParamType::BOOL';
            }
            case 'boolean': {
                return 'ParamType::BOOL';
            }
            case 'double': {
                return 'ParamType::DOUBLE';
            }
            case 'email': {
                return 'ParamType::EMAIL';
            }
            case 'json-obj': {
                return 'ParamType::JSON_OBJ';
            }
            case 'url': {
                return 'ParamType::URL';
            }
        }
    }
    private function implementMethods() {
        $name = $this->servicesObj->getName();
        $this->append([
            "/**",
            " * Checks if the client is authorized to call a service or not.",
            " *",
            " * @return boolean If the client is authorized, the method will return true.",
            " */",
            $this->f('isAuthorized'),
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
            $this->f('processRequest'),
        ], 1);

        if (count($this->processCode) == 0) {
            $this->append('// TODO: process the request for the service \''.$name.'\'.', 2);
            $this->append('$this->getManager()->serviceNotImplemented();', 2);
        } else {
            foreach ($this->processCode as $arr) {
                $this->append($arr['lines'], $arr['tab-size']);
            }
        }
        $this->append('}', 1);
    }
    private function writeConstructor() {
        $this->append([
            "/**",
            " * Creates new instance of the class.",
            " */",
            $this->f('__construct'),
        ], 1);
        $this->append('parent::__construct(\''.$this->servicesObj->getName().'\');', 2);
        $this->append('$this->setDescription(\''.str_replace("'", "\\'", $this->servicesObj->getDescription()).'\');', 2);
        $this->append('$this->setRequestMethods([', 2);

        foreach ($this->servicesObj->getRequestMethods() as $method) {
            $this->append($this->getMethod($method).',', 3);
        }
        $this->append(']);', 2);
        $this->appendParams($this->servicesObj->getParameters());
        $this->append('}', 1);
    }
    private function writeServiceDoc($service) {
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
}
