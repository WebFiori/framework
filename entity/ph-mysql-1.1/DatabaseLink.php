<?php
/**
 * A class that is used to connect to MySQL database. It works as an interface 
 * for <b>mysqli</b> 
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.1
 */
class DatabaseLink implements JsonI{
    /**
     * The name of database host. It can be an IP address (such as '134.123.111.3:3306') or 
     * a URL.
     * @var string 
     * @since 1.0
     */
    private $host;
    /**
     * The name of database user (such as 'Admin').
     * @var string 
     * @since 1.0
     */
    private $user;
    /**
     * The password of the database user.
     * @var string 
     * @since 1.0
     */
    private $pass;
    /**
     * The database instance that will be selected once the connection is 
     * established.
     * @var string 
     * @since 1.0
     */
    private $db;
    /**
     * The result of executing last query, <b>mysqli_result</b> object
     * @var mysqli_result|null 
     * @since 1.0
     */
    private $result;
    /**
     * The last generated error number.
     * @var int 
     * @since 1.0
     */
    private $lastErrorNo;
    /**
     * The last generated error message.
     * @var string 
     * @since 1.0
     */
    private $lastErrorMessage = 'NO ERRORS';
    /**
     * If connected, this will be true.
     * @var boolean 
     * @since 1.0
     */
    private $connected = false;
    /**
     * Database connection. It is simply the handler for executing queries.
     * @var type 
     * @since 1.0
     */
    private $link;
    /**
     * Returns the last generated error message.
     * @return string The last generated error message.
     * @since 1.0
     */
    private $lastQuery;
    public function getErrorMessage(){
        return $this->lastErrorMessage;
    }
    /*
     * 
     * @param type $str
     * @return type
     */
//    public function escapeString($str){
//        return mysqli_real_escape_string ( $this->link , $str );
//    }
    
    public function __construct($host, $user, $password) {
        //set_error_handler(errorHandeler('Connection to database was refused!'));
        $this->link = @mysqli_connect($host, $user, $password);
        $this->user = $user;
        $this->pass = $password;
        $this->host = $host;
        if($this->link){
            $this->connected = true;
        }
        else{
            $this->lastErrorNo = mysqli_connect_errno();
            $this->lastErrorMessage = mysqli_connect_error();
        }
    }
    /**
     * Returns a JSON string that represents the connection.
     * @return string
     * @since 1.1
     */
    public function toJSON(){
        $json = new JsonX();
        $json->add('error-code', $this->getErrorCode());
        $json->add('error-message', $this->getErrorMessage());
        $json->add('query', $this->getLastQuery());
        return $json;
    }
    /**
     * Returns the last executed query.
     * @return MySQLQuery An object of type <b>MySQLQuery</b>
     * @since 1.1
     */
    public function getLastQuery(){
        return $this->lastQuery;
    }
    /**
     * Returns the last generated error number.
     * @return int The last generated error number.
     * @since 1.0
     */
    public function getErrorCode(){
        return $this->lastErrorNo;
    }
    /**
     * Checks if the connection is still active or its dead.
     * @return boolean true if still active, false if dead.
     * @since 1.0
     */
    public function isConnected(){
        return $this->connected;
    }

    /**
     * Return the number of rows returned by last query.
     * If no result returned, the method will return -1.
     * @return int
     * @since 1.0
     */
    public function rows(){
        if($this->result){
            return $this->result->num_rows;
        }
        return -1;
    }
    /**
     * Select a database instance.
     * @param string $dbName The name of the database instance.
     * @return boolean <b>TRUE</b> if the instance is selected. <b>FALSE</b>
     * otherwise.
     * @since 1.0
     */
    public function setDB($dbName){
        if(!mysqli_select_db($this->link, $dbName)){
            $this->db = $dbName;
            $this->lastErrorMessage = $this->link->error;
            $this->lastErrorNo = $this->link->errno;
            return false;
        }
        return true;
    }
    /**
     * Returns the result set in case of executing select query.
     * The method will return <b>NULL</b> in case of none-select queries.
     * @return mysqli_result | NULL
     * @since 1.0
     */
    public function getResult(){
        return $this->result;
    }
    /**
     * Returns one row from the result.
     * @return array an associative array.
     * @since 1.0
     */
    public function getRow(){
        if($this->result){
            return mysqli_fetch_assoc($this->result);
        }
    }
    /**
     * Execute a MySQL query.
     * @param MySQLQuery $query an object of type <b>MySQLQuery</b>.
     * @return boolean true if the query was executed successfully, Other than that, 
     * the method will return false in case of error.
     * @since 1.0
     */
    public function executeQuery($query){
        $this->lastQuery = $query;
        if($this->isConnected()){
            if($query->getType() == 'select' || $query->getType() == 'show'
               || $query->getType() == 'describe' ){
                $r = mysqli_query($this->link, $query->getQuery());
                if($r){
                    $this->result = $r;
                    $this->lastErrorNo = 0;
                    return true;
                }
                else{
                    $this->lastErrorMessage = $this->link->error;
                    $this->lastErrorNo = $this->link->errno;
                    $this->result = NULL;
                    return false;
                }
            }
            else{
                $this->result = NULL;
                $r = mysqli_query($this->link, $query->getQuery());
                if($r == FALSE){
                    $this->lastErrorMessage = $this->link->error;
                    $this->lastErrorNo = $this->link->errno;
                    $this->result = NULL;
                    return false;
                }
                else{
                    $this->lastErrorMessage = 'NO ERRORS';
                    $this->lastErrorNo = 0;
                    $this->result = NULL;
                    return true;
                }
            }
        }
        return false;
    }
    
}

