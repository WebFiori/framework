<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
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
namespace webfiori\entity;

use jsonx\JsonI;
use jsonx\JsonX;
/**
 * A class that represents a privilege.
 *
 * @author Ibrahim
 * @version 1.0.1
 */
class Privilege implements JsonI {
    /**
     * The ID of the privilege.
     * @var string
     * @since 1.0 
     */
    private $code;
    /**
     * The name of the privilege.
     * @var string
     * @since 1.0 
     */
    private $name;
    /**
     * Creates new instance of the class
     * @param string $id The unique identifier of the privilege. Default is 
     * 'PR'.
     * @param string $name The name of the privilege. It is provided only 
     * in case of displaying privilege in some UI view. Default is empty string.
     * @since 1.0
     */
    public function __construct($id = 'PR',$name = '') {
        if (!$this->setID($id)) {
            $this->setID('PR');
        }

        if (!$this->setName($name)) {
            $this->setName('PR_NAME');
        }
    }
    /**
     * Returns the ID of the privilege.
     * @return string The ID of the privilege. If the ID was not set, 
     * the method will return 'PR'.
     * @since 1.0
     */
    public function getID() {
        return $this->code;
    }
    /**
     * Returns the name of the privilege.
     * @return string The name of the privilege. If the name was not updated, 
     * the method will return 'PR_NAME'.
     * @since 1.0
     */
    public function getName() {
        return $this->name;
    }
    /**
     * Sets the ID of the privilege
     * @param string $code The ID of the privilege. Only set if the given string 
     * is not empty. In addition, The ID of the privilege can only consist 
     * of the following characters: [A-Z], [a-z], [0-9] and underscore.
     * @return boolean If the ID of the privilege is updated, the method will return 
     * true. If not updated, it will return false.
     * @since 1.0
     */
    public function setID($code) {
        $xid = trim($code);
        $len = strlen($xid);

        for ($x = 0 ; $x < $len ; $x++) {
            $ch = $xid[$x];

            if (!($ch == '_' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || ($ch >= '0' && $ch <= '9'))) {
            
                return false;   
            }
        }
        $this->code = $xid;

        return true;
    }
    /**
     * Sets the name of the privilege.
     * @param string $name The name of the privilege. It is only set when 
     * the given string is not empty.
     * @return boolean If the privilege name was set, the method will return 
     * true. If not set, the method will return false.
     * @since 1.0
     */
    public function setName($name) {
        $trimmed = trim($name);

        if (strlen($trimmed) > 0) {
            $this->name = $trimmed;

            return true;
        }

        return false;
    }
    /**
     * Returns an object of type JsonX that contains group info as JSON string.
     * The generated JSON string will have the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"privilegeId":"",<br/>
     * &nbsp;&nbsp;"name":"",<br/>
     * }
     * </p> 
     */
    public function toJSON() {
        $j = new JsonX();
        $j->add('privilegeId', $this->getID());
        $j->add('name', $this->getName());

        return $j;
    }
}
