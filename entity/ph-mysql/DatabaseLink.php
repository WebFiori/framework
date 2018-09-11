<?php
/**
 * A class that is used to connect to MySQL database. It works as an interface 
 * for <b>mysqli</b> 
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.2
 */
class DatabaseLink{
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
     * Database connection. It is simply the handler for executing queries.
     * @var type 
     * @since 1.0
     */
    private $link;
    /**
     * The last executed query.
     * @var MySQLQuer An object of type 'MySQLQuery.
     * @since 1.0
     */
    private $lastQuery;
    /**
     * An array which contains rows from executing MySQL query.
     * @var array|NULL
     * @since 1.2 
     */
    private $resultRows;
    public function getErrorMessage(){
        return $this->lastErrorMessage;
    }

    public function __construct($host, $user, $password) {
        //set_error_handler(errorHandeler('Connection to database was refused!'));
        $this->link = @mysqli_connect($host, $user, $password);
        $this->user = $user;
        $this->pass = $password;
        $this->host = $host;
        if($this->link){
            
        }
        else{
            $this->lastErrorNo = mysqli_connect_errno();
            $this->lastErrorMessage = mysqli_connect_error();
        }
    }
    /**
     * Returns the last executed query.
     * @return MySQLQuery An object of type 'MySQLQuery'
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
     * @return boolean true if still active, false if dead. If the connection is 
     * dead, more details can be found by getting the error message and error 
     * number.
     * @since 1.0
     */
    public function isConnected(){
        $test = FALSE;
        if($this->link instanceof mysqli){
            $test = mysqli_ping($this->link);
            if($test === FALSE){
                $this->lastErrorMessage = mysqli_error($this->link);
                $this->lastErrorNo = mysqli_errno($this->link);
            }
        }
        return $test;
    }

    /**
     * Return the number of rows returned by last query.
     * If no result returned, the method will return -1.
     * @return int
     * @since 1.0
     */
    public function rows(){
        if($this->result){
            return count($this->getRows());
        }
        return -1;
    }
    /**
     * Select a database instance.
     * @param string $dbName The name of the database instance.
     * @return boolean TRUE if the instance is selected. FALSE
     * otherwise.
     * @since 1.0
     */
    public function setDB($dbName){
        if(!mysqli_select_db($this->link, $dbName)){
            $this->lastErrorMessage = $this->link->error;
            $this->lastErrorNo = $this->link->errno;
            return false;
        }
        else{
            $this->db = $dbName;
        }
        return true;
    }
    /**
     * Returns the result set in case of executing select query.
     * The method will return NULL in case of none-select queries.
     * @return mysqli_result|NULL
     * @since 1.0
     */
    public function getResult(){
        return $this->result;
    }
    /**
     * Returns one row from the result.
     * @return array|NULL an associative array that represents a table row. 
     * The value is taken from the function 'mysqli_fetch_assoc()'.
     * If no results are fetched, the function will return NULL. 
     * @since 1.0
     */
    public function getRow(){
        if($this->result){
            return mysqli_fetch_assoc($this->result);
        }
        return NULL;
    }
    /**
     * Returns an array which contains all fetched results from the database.
     * @return array An array which contains all fetched results from the database. 
     * Each row will be an associative array. The index will represents the 
     * column of the table.
     * @since 1.2
     */
    public function getRows(){
        if($this->resultRows != NULL){
            return $this->resultRows;
        }
        $result = $this->getResult();
        $rows = $result !== NULL ? mysqli_fetch_all($result, MYSQLI_ASSOC) : array();
        if(count($rows) != 0){
            $this->resultRows = $rows;
        }
        return $rows;
    }
    /**
     * Returns an array which contains all data from a specific column given its 
     * name.
     * @param string $colKey The name of the column as specified in the last 
     * executed query.
     * @return array An array which contains all data from the given column. 
     * if the column does not exist, the function will return empty array.
     * @since 1.2
     */
    public function getColumn($colKey) {
        $retVal = array();
        $rows = $this->getRows();
        $colNameInDb = $this->getLastQuery()->getColName($colKey);
        if($colKey != Table::NO_SUCH_COL){
            foreach ($rows as $row){
                if(isset($row[$colNameInDb])){
                    $retVal[] = $row[$colNameInDb];
                }
                else{
                    break;
                }
            }
        }
        return $retVal;
    }
    /**
     * Execute a MySQL query.
     * @param MySQLQuery $query an object of type 'MySQLQuery'.
     * @return boolean true if the query was executed successfully, Other than that, 
     * the method will return false in case of error.
     * @since 1.0
     */
    public function executeQuery($query){
        if($query instanceof MySQLQuery){
            $this->resultRows = NULL;
            $this->lastQuery = $query;
            if($this->isConnected()){
                $eploded = explode(';', trim($query->getQuery(), ';'));
                if(count($eploded) != 1){
                    $r = mysqli_multi_query($this->link, $query->getQuery());
                    while(mysqli_more_results($this->link)){
                        $x = mysqli_store_result($this->link);
                        mysqli_next_result($this->link);
                    }
                    if($r !== TRUE){
                        $this->lastErrorMessage = $this->link->error;
                        $this->lastErrorNo = $this->link->errno;
                    }
                    return $r;
                }
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
        }
        return false;
    }
    
}

