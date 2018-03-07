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
 * @version 1.0
 */
class APIAction implements JsonI{
    /**
     * The name of the action.
     * @var string
     * @since 1.0 
     */
    private $name;
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
     * Sets the request method that is used to fire the action.
     * @param string $method The request method (Get, Post...).
     * @since 1.0
     */
    public function setActionMethod($method){
        $this->actionMethod = $method;
    }
    /**
     * Reqtrns the request method that is used to fire the action.
     * @return string The request method (Get, Post...).
     * @since 1.0
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
        $json->add('parameters', $this->parameters);
        return $json;
    }

}

