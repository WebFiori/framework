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
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 403 Forbidden");
    die('<!DOCTYPE html><html><head><title>Forbidden</title></head><body>'
    . '<h1>403 - Forbidden</h1><hr><p>Direct access not allowed.</p></body></html>');
}
/**
 * An entity that can be used to store database connection information. 
 * The information that can be stored includes:
 * <ul>
 * <li>Database host address.</li>
 * <li>Port number.</li>
 * <li>The username of the user that will be used to access the database.</li>
 * <li>The password of the user.</li>
 * <li>The name of the database.</li>
 * </ul>
 *
 * @author Ibrahim
 * @version 1.0
 */
class DBConnectionInfo {
    private $port;
    private $host;
    private $uName;
    private $pass;
    private $dbName;
    /**
     * Creates new instance of the class.
     * @param string $user The username of the user that will be used to access 
     * the database.
     * @param string $pass The password of the user.
     * @param string $dbname The name of the database.
     * @param string $host The address of database host. Default value is 
     * 'localhost'.
     * @param int $port Port number that will be used to access database server. 
     * Default is 3306.
     * @since 1.0
     */
    public function __construct($user,$pass,$dbname,$host='localhost',$port=3306) {
        $this->setUsername($user);
        $this->setPassword($pass);
        $this->setDBName($dbname);
        $this->setHost($host);
        $this->setPort($port);
    }
    /**
     * Returns the address of database host.
     * The host address can be a URL, an IP address or 'localhost' if 
     * the database is hosted in the same server that the framework is 
     * installed in.
     * @return string A string that represents the address of the host. If 
     * it is not set, the method will return 'localhost' by default.
     * @since 1.0
     */
    public function getHost() {
        return $this->host;
    }
    /**
     * Sets the address of database host.
     * The host address can be a URL, an IP address or 'localhost' if 
     * the database is hosted in the same server that the framework is 
     * installed in.
     * @param string $hostAddr The address of database host.
     * @since 1.0
     */
    public function setHost($hostAddr) {
        $this->host = $hostAddr;
    }
    /**
     * Returns the password of the user that will be used to access the database.
     * @return string A string that represents the password of the user.
     * @since 1.0
     */
    public function getPassword() {
        return $this->pass;
    }
    /**
     * Sets the password of the user that will be used to access the database.
     * @param string $password A string that represents the password of the user.
     * @since 1.0
     */
    public function setPassword($password) {
        $this->pass = $password;
    }
    /**
     * Returns username of the user that will be used to access the database.
     * @return string A string that represents the username.
     * @since 1.0
     */
    public function getUsername() {
        return $this->uName;
    }
    /**
     * Sets the username of the user that will be used to access the database.
     * @param string $user A string that represents the username.
     * @since 1.0
     */
    public function setUsername($user) {
        $this->uName = $user;
    }
    /**
     * Returns database server port number.
     * @return int Server port number. If it is not set, the method will 
     * return 3306 by default.
     * @since 1.0
     */
    public function getPort() {
        return $this->port;
    }
    /**
     * Sets database server port number.
     * @param int $portNum Server port number. It will be set only if the 
     * given value is greater than 0.
     * @since 1.0
     */
    public function setPort($portNum) {
        if($portNum > 0){
            $this->port = $portNum;
        }
    }
    /**
     * Returns the name of the database.
     * @return string A string that represents the name of the database.
     * @since 1.0
     */
    public function getDBName() {
        return $this->dbName;
    }
    /**
     * Sets the name of the database.
     * @param string $name The name of the database.
     * @since 1.0
     */
    public function setDBName($name) {
        $this->dbName = $name;
    }
}
