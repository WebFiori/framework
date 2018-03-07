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
 * A class that represents request parameter.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class RequestParameter implements JsonI{
    /**
     * The type of the data the parameter will represents.
     * @var string
     * @since 1.0 
     */
    private $type;
    /**
     * The name of the parameter.
     * @var string
     * @since 1.0 
     */
    private $name;
    /**
     * Indicates wither the attribute is optional or not.
     * @var boolean <b>TRUE</b> if the parameter is optional.
     * @since 1.0
     */
    private $isOptional;
    /**
     * Creates new instance of <b>RequestParameter</b>
     * @param string $name The name of the parameter as it appears in the request body.
     * @param string $type The type of the data that will be in the parameter (integer, 
     * string, email etc...)
     * @param boolean $isOptional Set to <b>TRUE</b> if the parameter is optional.
     */
    public function __construct($name,$type='string',$isOptional=false) {
        $this->name = $name;
        $this->isOptional = $isOptional;
        if(in_array($type, APIFilter::TYPES)){
            $this->type = $type;
        }
    }
    /**
     * Returns the name of the parameter.
     * @return string The name of the parameter.
     * @since 1.0
     */
    public function getName(){
        return $this->name;
    }
    /**
     * Returns a boolean value that indicates if the parameter is optional or not.
     * @return boolean <b>TRUE</b> if the parameter is optional and <b>FALSE</b> 
     * if not.
     * @since 1.0
     */
    public function isOptional(){
        return $this->isOptional;
    }
    /**
     * Returns the type of the parameter.
     * @return string The type of the parameter (Such as 'string', 'email', 'integer').
     * @since 1.0
     */
    public function getType(){
        return $this->type;
    }
    /**
     * Returns a JsonX object that represents the request parameter.
     * @return JsonX An object of type <b>JsonX</b>.
     * @since 1.0
     */
    public function toJSON() {
        $json = new JsonX();
        $json->add('name', $this->name);
        $json->add('type', $this->getType());
        $json->add('is-optional', $this->isOptional());
        return $json;
    }

}
