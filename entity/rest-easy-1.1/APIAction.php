<?php

/* 
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
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
 * @version 1.1
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
     * The request method that is used to fire the action.
     * @var string 'Get' or 'Post' or other... 
     * @since 1.0
     * @deprecated since version 1.1
     */
    private $actionMethod;
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
    public function addRequestMethod($method){
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
    public function getActionMethods(){
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
     * Sets the request method that is used to fire the action.
     * @param string $method The request method (Get, Post...).
     * @since 1.0
     * @deprecated since version 1.1 Use <b>APIAction::addRequestMethod($method)</b> instead.
     */
    public function setActionMethod($method){
        $this->actionMethod = strtoupper($method);
    }
    /**
     * Returns the request method that is used to fire the action.
     * @return string The request method (Get, Post...).
     * @since 1.0
     * @deprecated since version 1.1
     */
    public function getActionMethod(){
        return $this->actionMethod;
    }
    /**
     * Sets the name of the action.
     * @param string $name The name of the action.
     * @since 1.0
     */
    public function setName($name){
        $this->name = $name;
    }
    /**
     * Returns an array that contains an objects of type <b>RequestParameter</b>.
     * @return array an array that contains an objects of type <b>RequestParameter</b>.
     * @since 1.0
     */
    public function getParameters(){
        return $this->parameters;
    }
    /**
     * Returns the name of the action.
     * @return string The name of the action.
     * @since 1.0
     */
    public function getName(){
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
        $json->add('request-method', $this->getActionMethod());
        $json->add('request-methods', $this->reqMethods);
        $json->add('parameters', $this->parameters);
        return $json;
    }

}

