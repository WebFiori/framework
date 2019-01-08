<?php

/* 
 * The MIT License
 *
 * Copyright 2018 Ibrahim BinAlshikh.
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
 * A class that represents API action.
 * @author Ibrahim
 * @version 1.3
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
    private $reqMethods = array();
    /**
     * An array that holds an objects of type RequestParameter.
     * @var array
     * @since 1.0 
     */
    private $parameters = array();
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
     * @param string $name The name of the action. 
     */
    public function __construct($name='') {
        if(!$this->setName($name)){
            $this->setName('an-action');
        }
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
        if(strlen($description) != 0){
            $this->responses[] = $description;
        }
    }
    /**
     * Sets the description of the action.
     * Used to help front-end to identify the use of the action.
     * @param sting $desc Action description.
     * @since 1.2
     */
    public final function setDescription($desc) {
        $this->actionDesc = $desc;
    }
    /**
     * Returns the description of the action.
     * @return string|NULL The description of the action. If the description is 
     * not set, the method will return NULL.
     * @since 1.2
     */
    public final function getDescription() {
        return $this->actionDesc;
    }
    /**
     * Sets the version number at which the action was added to the API.
     * This method is called automatically when an action is added to any object of 
     * type WebAPI. The developer does not have to use this method.
     * @param string The version number at which the action was added to the API.
     * @since 1.2
     */
    public final function setSince($sinceAPIv) {
        $this->sinceVersion = $sinceAPIv;
    }
    /**
     * Returns the version number at which the action was added to the API.
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
     * @param RequestParameter $param The action that will be added.
     * @since 1.0
     */
    public function addParameter($param){
        if($param instanceof RequestParameter){
            array_push($this->parameters, $param);
        }
    }
    /**
     * Adds new action request method.
     * The value that will be passed to this method can be any string 
     * that represents HTTP request method (e.g. 'get', 'post', 'options' ...). It 
     * can be in upper case or lower case.
     * @param string $method The request method.
     * @return boolean TRUE in case the request method is added. If the given 
     * request method is already added or the method is unknown, the method 
     * will return FALSE.
     * @since 1.1
     */
    public final function addRequestMethod($method){
        $uMethod = strtoupper($method);
        if(in_array($uMethod, self::METHODS)){
            if(!in_array($uMethod, $this->reqMethods)){
                array_push($this->reqMethods, $uMethod);
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * Returns an array that contains all action request methods.
     * Request methods can be added using the method APIAction::addRequestMethod().
     * @return array An array that contains all action request methods.
     * @see APIAction::addRequestMethod($method)
     * @since 1.1
     * 
     */
    public final function getActionMethods(){
        return $this->reqMethods;
    }
    /**
     * Removes a request method from the previously added ones. 
     * @param string $method The request method (e.g. 'get', 'post', 'options' ...). It 
     * can be in upper case or lower case.
     * @return string|NULL The method will return the removed request method. 
     * In case nothing has changed, the method will return NULL.
     * @since 1.1
     */
    public function removeRequestMethod($method){
        $uMethod = strtoupper($method);
        if(in_array($uMethod, $this->getActionMethods())){
            $count = count($this->getActionMethods());
            for($x = 0 ; $x < $count ; $x++){
                if($this->getActionMethods()[$x] == $uMethod){
                    unset($this->getActionMethods()[$x]);
                    return $uMethod;
                }
            }
        }
        return NULL;
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
     * TRUE once the name is set. FALSE is returned if the given 
     * name is invalid.
     * @since 1.0
     */
    public final function setName($name){
        $name .= '';
        $len = strlen($name);
        if($len != 0){
            if(strpos($name, ' ') === FALSE){
                for ($x = 0 ; $x < $len ; $x++){
                    $ch = $name[$x];
                    if($ch == '_' || $ch == '-' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || ($ch >= '0' && $ch <= '9')){

                    }
                    else{
                        return FALSE;
                    }
                }
                $this->name = $name;
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * Returns an array that contains an objects of type RequestParameter.
     * @return array an array that contains an objects of type RequestParameter.
     * @since 1.0
     */
    public final function getParameters(){
        return $this->parameters;
    }
    /**
     * Returns action parameter given its name.
     * @param string $paramName The name of the parameter.
     * @return RequestParameter|NULL Returns an objects of type RequestParameter if 
     * a parameter with the given name was found. NULL if nothing is found.
     * @since 1.2
     */
    public final function &getParameterByName($paramName) {
        $paramName .= '';
        if(strlen($paramName) != 0){
            foreach ($this->parameters as $param){
                if($param->getName() == $paramName){
                    return $param;
                }
            }
        }
        $null = NULL;
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
     * &nbsp;&nbsp;"responses":"[]"<br/>
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

}

