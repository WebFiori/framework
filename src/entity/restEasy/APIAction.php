<?php

/* 
 * The MIT License
 *
 * Copyright 2019 Ibrahim BinAlshikh, restEasy library.
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
namespace restEasy;
use jsonx\JsonI;
use jsonx\JsonX;
/**
 * A class that represents one web service.
 * A web service is simply an action that is performed by a web 
 * server to do something. For example, It is possible to have a web service 
 * which is responsible for creating new user profile. Think of it as an 
 * action taken to perform specific task.
 * @author Ibrahim
 * @version 1.3.1
 */
class APIAction implements JsonI{
    /**
     * An array that contains the names of request methods.
     * This array contains the following strings:
     * <ul>
     * <li>GET</li>
     * <li>HEAD</li>
     * <li>POST</li>
     * <li>PUT</li>
     * <li>DELETE</li>
     * <li>TRACE</li>
     * <li>OPTIONS</li>
     * <li>PATCH</li>
     * <li>CONNECT</li>
     * </ul>
     * @var array An array that contains the names of request methods.
     * @since 1.1
     */
    const METHODS = array(
        'GET','HEAD','POST','PUT','DELETE','TRACE',
        'OPTIONS','PATCH','CONNECT'
    );
    /**
     * The name of the action.
     * @var string
     * @since 1.0 
     */
    private $name;
    /**
     * An array that contains action request methods.
     * @var array
     * @since 1.1 
     */
    private $reqMethods = [];
    /**
     * An array that holds an objects of type RequestParameter.
     * @var array
     * @since 1.0 
     */
    private $parameters = [];
    /**
     * An optional description for the action.
     * @var sting
     * @since 1.2 
     */
    private $actionDesc;
    /**
     * An attribute that is used to tell since which API version the 
     * action was added.
     * @var string
     * @since 1.2 
     */
    private $sinceVersion;
    /**
     * An array that contains descriptions of 
     * possible responses.
     * @var array
     * @since 1.3 
     */
    private $responses;
    /**
     * Creates new instance of the class.
     * The developer can supply an optional action name. 
     * A valid action name must follow the following rules:
     * <ul>
     * <li>It can contain the letters [A-Z] and [a-z].</li>
     * <li>It can contain the numbers [0-9].</li>
     * <li>It can have the character '-' and the character '_'.</li>
     * </ul>
     * If The given name is invalid, the name of the action will be set to 'an-action'.
     * @param string $name The name of the action. 
     */
    public function __construct($name) {
        if(!$this->setName($name)){
            $this->setName('an-action');
        }
        $this->reqMethods = [];
        $this->parameters = [];
        $this->responses = [];
    }
    /**
     * Returns an indexed array that contains information about possible responses.
     * It is used to describe the API for front-end developers and help them 
     * identify possible responses if they call the API using the specified action.
     * @return array An array that contains information about possible responses.
     * @since 1.3
     */
    public final function getResponsesDescriptions() {
        return $this->responses;
    }
    /**
     * Adds response description.
     * It is used to describe the API for front-end developers and help them 
     * identify possible responses if they call the API using the specified action.
     * @param string $description A paragraph that describes one of 
     * the possible responses due to performing the action.
     * @since 1.3
     */
    public final function addResponseDescription($description) {
        $trimmed = trim($description);
        if(strlen($trimmed) != 0){
            $this->responses[] = $trimmed;
        }
    }
    /**
     * Sets the description of the action.
     * Used to help front-end to identify the use of the action.
     * @param sting $desc Action description.
     * @since 1.2
     */
    public final function setDescription($desc) {
        $this->actionDesc = trim($desc);
    }
    /**
     * Returns the description of the action.
     * @return string|null The description of the action. If the description is 
     * not set, the method will return null.
     * @since 1.2
     */
    public final function getDescription() {
        return $this->actionDesc;
    }
    /**
     * Sets version number or name at which the action was added to the API.
     * This method is called automatically when an action is added to any object of 
     * type WebAPI. The developer does not have to use this method.
     * @param string The version number at which the action was added to the API.
     * @since 1.2
     */
    public final function setSince($sinceAPIv) {
        $this->sinceVersion = $sinceAPIv;
    }
    /**
     * Returns version number or name at which the action was added to the API.
     * Version number is set based on the version number which was set in the 
     * class WebAPI.
     * @return string The version number at which the action was added to the API.
     * @since 1.2
     */
    public final function getSince() {
        return $this->sinceVersion;
    }
    /**
     * Adds new request parameter for the action.
     * The parameter will only be added if no parameter which has the same 
     * name as the given one is added before.
     * @param RequestParameter $param The action that will be added.
     * @return boolean If the given request parameter is added, the method will 
     * return true. If it was not added for any reason, the method will return 
     * false.
     * @since 1.0
     */
    public function addParameter($param){
        if($param instanceof RequestParameter){
            if(!$this->hasParameter($param->getName())){
                $this->parameters[] = $param;
                return true;
            }
        }
        return false;
    }
    /**
     * Checks if the action has a specific request parameter given its name.
     * Note that the name of the parameter is case sensitive. This means that 
     * 'get-profile' is not the same as 'Get-Profile'.
     * @param string $name The name of the parameter.
     * @return boolean If a request parameter which has the given name is added 
     * to the action, the method will return true. Otherwise, the method will return 
     * false.
     * @since 1.3.1
     */
    public function hasParameter($name) {
        $trimmed = trim($name);
        if(strlen($name) != 0){
            foreach ($this->getParameters() as $param){
                if($param->getName() == $trimmed){
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Removes a request parameter from the action given its name.
     * @param string $paramName The name of the parameter (case sensitive).
     * @return null|RequestParameter If a parameter which has the given name 
     * was removed, the method will return an object of type 'RequestParameter' 
     * that represents the removed parameter. If nothing is removed, the 
     * method will return null.
     * @since 1.3.1
     */
    public function removeParameter($paramName) {
        $trimmed = trim($paramName);
        $params = &$this->getParameters();
        $index = -1;
        $count = count($params);
        for($x = 0 ; $x < $count ; $x++){
            if($params[$x]->getName() == $trimmed){
                $index = $x;
                break;
            }
        }
        $retVal = null;
        if($index != -1){
            if($count == 1){
                $retVal = $params[0];
                unset($params[0]);
            }
            else{
                $retVal = $params[$index];
                $params[$index] = $params[$count - 1];
                unset($params[$count - 1]);
            }
        }
        return $retVal;
    }
    /**
     * Adds new action request method.
     * The value that will be passed to this method can be any string 
     * that represents HTTP request method (e.g. 'get', 'post', 'options' ...). It 
     * can be in upper case or lower case.
     * @param string $method The request method.
     * @return boolean true in case the request method is added. If the given 
     * request method is already added or the method is unknown, the method 
     * will return false.
     * @since 1.1
     */
    public final function addRequestMethod($method){
        $uMethod = strtoupper(trim($method));
        if(in_array($uMethod, self::METHODS)){
            if(!in_array($uMethod, $this->reqMethods)){
                $this->reqMethods[] = $uMethod;
                return true;
            }
        }
        return false;
    }
    /**
     * Returns an array that contains all action request methods.
     * Request methods can be added using the method APIAction::addRequestMethod().
     * @return array An array that contains all action request methods.
     * @see APIAction::addRequestMethod($method)
     * @since 1.1
     * 
     */
    public final function &getActionMethods(){
        return $this->reqMethods;
    }
    /**
     * Removes a request method from the previously added ones. 
     * @param string $method The request method (e.g. 'get', 'post', 'options' ...). It 
     * can be in upper case or lower case.
     * @return boolean If the given request method is remove, the method will 
     * return true. Other than that, the method will return true.
     * @since 1.1
     */
    public function removeRequestMethod($method){
        $uMethod = strtoupper(trim($method));
        $actionMethods = &$this->getActionMethods();
        if(in_array($uMethod, $actionMethods)){
            $count = count($actionMethods);
            $methodIndex = -1;
            for($x = 0 ; $x < $count ; $x++){
                if($this->getActionMethods()[$x] == $uMethod){
                    $methodIndex = $x;
                    break;
                }
            }
            if($count == 1){
                unset($actionMethods[0]);
            }
            else{
                $actionMethods[$methodIndex] = $actionMethods[$count - 1];
                unset($actionMethods[$count - 1]);
            }
            return true;
        }
        return false;
    }
    /**
     * Sets the name of the action.
     * A valid action name must follow the following rules:
     * <ul>
     * <li>It can contain the letters [A-Z] and [a-z].</li>
     * <li>It can contain the numbers [0-9].</li>
     * <li>It can have the character '-' and the character '_'.</li>
     * </ul>
     * @param string $name The name of the action.
     * @return boolean If the given name is valid, the method will return 
     * true once the name is set. false is returned if the given 
     * name is invalid.
     * @since 1.0
     */
    public final function setName($name){
        $trimmedName = trim($name);
        $len = strlen($trimmedName);
        if($len != 0){
            for ($x = 0 ; $x < $len ; $x++){
                $ch = $trimmedName[$x];
                if($ch == '_' || $ch == '-' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || ($ch >= '0' && $ch <= '9')){

                }
                else{
                    return false;
                }
            }
            $this->name = $name;
            return true;
        }
        return false;
    }
    /**
     * Returns an array that contains an objects of type RequestParameter.
     * @return array an array that contains an objects of type RequestParameter.
     * @since 1.0
     */
    public final function &getParameters(){
        return $this->parameters;
    }
    /**
     * Returns action parameter given its name.
     * @param string $paramName The name of the parameter.
     * @return RequestParameter|null Returns an objects of type RequestParameter if 
     * a parameter with the given name was found. null if nothing is found.
     * @since 1.2
     */
    public final function getParameterByName($paramName) {
        $trimmed = trim($paramName);
        if(strlen($trimmed) != 0){
            foreach ($this->parameters as $param){
                if($param->getName() == $trimmed){
                    return $param;
                }
            }
        }
        $null = null;
        return $null;
    }
    /**
     * Returns the name of the action.
     * @return string The name of the action.
     * @since 1.0
     */
    public final function getName(){
        return $this->name;
    }
    /**
     * Returns a JsonX object that represents the action.
     * The generated JSON string from the returned JsonX object will have 
     * the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"name":"",<br/>
     * &nbsp;&nbsp;"since":"",<br/>
     * &nbsp;&nbsp;"description":"",<br/>
     * &nbsp;&nbsp;"request-methods":[],<br/>
     * &nbsp;&nbsp;"parameters":[],<br/>
     * &nbsp;&nbsp;"responses":[]<br/>
     * }
     * </p>
     * @return JsonX an object of type JsonX.
     * @since 1.0
     */
    public function toJSON() {
        $json = new JsonX();
        $json->add('name', $this->getName());
        $json->add('since', $this->getSince());
        $json->add('description', $this->getDescription());
        $json->add('request-methods', $this->reqMethods);
        $json->add('parameters', $this->parameters);
        $json->add('responses', $this->getResponsesDescriptions());
        return $json;
    }
    /**
     * 
     * @return string
     * @since 1.3.1
     */
    public function __toString() {
        $retVal = "APIAction[\n";
        $retVal .= "    Name => '".$this->getName()."',\n";
        $retVal .= "    Description => '".$this->getDescription()."',\n";
        $since = $this->getSince() === null ? 'null' : $this->getSince();
        $retVal .= "    Since => '$since',\n";
        $reqMethodsStr = "[\n";
        for($x = 0,  $count = count($this->getActionMethods()) ; $x < $count ; $x++) {
            $meth = $this->getActionMethods()[$x];
            if($x + 1 == $count){
                $reqMethodsStr .= "        $meth\n";
            }
            else{
                $reqMethodsStr .= "        $meth,\n";
            }
        }
        $reqMethodsStr .= "    ],\n";
        $retVal .= "    Request Methods => $reqMethodsStr";
        $paramsStr = "[\n";
        for($x = 0 , $count = count($this->getParameters()); $x < $count ; $x++){
            $param = $this->getParameters()[$x];
            if($x + 1 == $count){
                $paramsStr .= "        ".$param->getName()." => [\n";
                $paramsStr .= "            Type => '". $param->getType()."',\n";
                $descStr = $param->getDescription() === null ? 'null' : $param->getDescription();
                $paramsStr .= "            Description => '$descStr',\n";
                $isOptional = $param->isOptional() ? 'true' : 'false';
                $paramsStr .= "            Is Optional => '$isOptional',\n";
                $defaultStr = $param->getDefault() === null ? 'null' : $param->getDefault();
                $paramsStr .= "            Default => '$defaultStr',\n";
                $min = $param->getMinVal() === null ? 'null' : $param->getMinVal();
                $paramsStr .= "            Minimum Value => '$min',\n";
                $max = $param->getMaxVal() === null ? 'null' : $param->getMaxVal();
                $paramsStr .= "            Maximum Value => '$max'\n        ]\n";
            }
            else{
                $paramsStr .= "        ".$param->getName()." => [\n";
                $paramsStr .= "            Type => '". $param->getType()."',\n";
                $descStr = $param->getDescription() === null ? 'null' : $param->getDescription();
                $paramsStr .= "            Description => '$descStr',\n";
                $isOptional = $param->isOptional() ? 'true' : 'false';
                $paramsStr .= "            Is Optional => '$isOptional',\n";
                $defaultStr = $param->getDefault() === null ? 'null' : $param->getDefault();
                $paramsStr .= "            Default => '$defaultStr',\n";
                $min = $param->getMinVal() === null ? 'null' : $param->getMinVal();
                $paramsStr .= "            Minimum Value => '$min',\n";
                $max = $param->getMaxVal() === null ? 'null' : $param->getMaxVal();
                $paramsStr .= "            Maximum Value => '$max'\n        ],\n";
            }
        }
        $paramsStr .= "    ],\n";
        $retVal .= "    Parameters => $paramsStr";
        $responses = "[\n";
        $count = count($this->getResponsesDescriptions());
        for($x = 0 ; $x < $count ; $x++){
            if($x + 1 == $count){
                $responses .= "        Response #$x => '".$this->getResponsesDescriptions()[$x]."'\n";
            }
            else{
                $responses .= "        Response #$x => '".$this->getResponsesDescriptions()[$x]."',\n";
            }
        }
        $responses .= "    ]\n";
        $retVal .= "    Responses Descriptions => $responses]\n";
        return $retVal;
    }
}

