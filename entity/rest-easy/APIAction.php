<?php

/* 
 * The MIT License
 *
 * Copyright 2018 Ibrahim BinAlshikh, rest-easy (v1.4.2).
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
/**
 * A class that represents API action.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.3
 */
class APIAction implements JsonI{
    /**
     * An array that contains the names of request methods.
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
     * An array that holds an objects of type <b>RequestParameter</b>.
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
     * Creates new instance of <b>APIAction</b>.
     * @param string $name [Optional] The name of the action. A valid action name must 
     * follow the following rules:
     * <ul>
     * <li>It can contain the letters [A-Z] and [a-z].</li>
     * <li>It can contain the numbers [0-9].</li>
     * <li>It can have the character '-' and the character '_'.</li>
     * </ul>
     */
    public function __construct($name='') {
        if(!$this->setName($name)){
            $this->setName('an-action');
        }
    }
    /**
     * Returns an array that contains information about possible responses.
     * @return array An array that contains information about possible responses.
     * @since 1.3
     */
    public final function getResponsesDescriptions() {
        return $this->responses;
    }
    /**
     * Adds response description.
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
     * @param sting $desc Action description. Used to help front-end to identify 
     * the use of the action.
     * @since 1.2
     */
    public final function setDescription($desc) {
        $this->actionDesc = $desc;
    }
    /**
     * Returns the description of the action.
     * @return string|NULL The description of the action. If the description is 
     * not set, the function will return <b>NULL</b>.
     * @since 1.2
     */
    public final function getDescription() {
        return $this->actionDesc;
    }
    /**
     * Sets the version number at which the action was added to the API.
     * @param string The version number at which the action was added to the API. This 
     * function is called automatically when an action is added to any object of 
     * type <b>API</b>.
     * @since 1.2
     */
    public final function setSince($sinceAPIv) {
        $this->sinceVersion = $sinceAPIv;
    }
    /**
     * Returns the version number at which the action was added to the API.
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
     * @param string $method The request method (e.g. 'get', 'post', 'options' ...). It 
     * can be in upper case or lower case.
     * @return boolean <b>TRUE</b> in case the request method is added. If the given 
     * request method is already added or the method is already added, the function 
     * will return <b>FALSE</b>.
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
     * @return array An array that contains all action request methods. Request 
     * methods can be added using the function <b>APIAction::addRequestMethod($method)</b>
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
     * @return string|NULL The function will return the removed request method. 
     * In case nothing has changed, the function will return <b>NULL</b>.
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
     * @param string $name The name of the action. A valid action name must 
     * follow the following rules:
     * <ul>
     * <li>It can contain the letters [A-Z] and [a-z].</li>
     * <li>It can contain the numbers [0-9].</li>
     * <li>It can have the character '-' and the character '_'.</li>
     * </ul>
     * @return boolean If the given name is valid, the function will return 
     * <b>TRUE</b> once the name is set. <b>FALSE</b> is returned if the given 
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
     * Returns an array that contains an objects of type <b>RequestParameter</b>.
     * @return array an array that contains an objects of type <b>RequestParameter</b>.
     * @since 1.0
     */
    public final function getParameters(){
        return $this->parameters;
    }
    /**
     * Returns action parameter given its name.
     * @param string $paramName The name of the parameter.
     * @return RequestParameter | NULL Returns an objects of type <b>RequestParameter</b> if 
     * a parameter with the given name was found. <b>NULL</b> if nothing is found.
     * @since 1.2
     */
    public final function getParameterByName($paramName) {
        $paramName .= '';
        if(strlen($paramName) != 0){
            foreach ($this->parameters as $param){
                if($param->getName() == $paramName){
                    return $param;
                }
            }
        }
        return NULL;
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
     * Returns a <b>JsonX</b> object that represents the action.
     * @return JsonX an object of type <b>JsonX</b>.
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

