<?php
/**
 * A base class that is used to construct MySQL queries. It can be used as a base 
 * class for constructing other MySQL queries.<br/>
 * @uses Table Used by the 'create table' Query.
 * @uses ForeignKey Used to alter a table and insert a foreign key in it.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.4
 */
class MySQLQuery implements JsonI{
    /**
     * A constant that indicates an error has occurred while executing the query.
     * @var string 
     * @since 1.4 
     */
    const QUERY_ERR = 'query_error';
    /**
     * An array that contains the supported MySQL query types.
     * @since 1.1
     */
    const Q_TYPES = array(
        'select','update','delete','insert','show','create','alter'
    );
    /**
     * A constant for the query 'select * from'.
     * @since 1.0
     */
    const SELECT = 'select * from ';
    /**
     * A constant that represents the ID column of a table.
     * @since 1.0
     */
    const ID_COL = 'id';
    /**
     * A constant for the query 'insert into'.
     * @since 1.0
     */
    const INSERT = 'insert into ';
    /**
     * A constant for the query 'delete from'.
     * @since 1.0
     */
    const DELETE = 'delete from ';
    /**
     * The query that will be constructed using class methods.
     * @var string 
     * @since 1.0
     */
    private $query;
    /**
     * A string that represents the type of the query such as 'select' or 'update'.
     * @var string 
     * @since 1.0
     */
    private $queryType;
    
    public function __construct() {
        $this->query = self::SELECT.' a_table';
        $this->queryType = 'select';
    }
    /**
     * Constructs a query that can be used to alter the properties of a table
     * given its name.
     * @param string $tblName The name of the table.
     * @param array $alterOps An array that contains the alter operations.
     * @since 1.4
     */
    public function alter($tblName, $alterOps){
        $q = 'alter table '.$tblName.' ';
        $count = count($alterOps);
        for($x = 0 ; $x < $count ; $x++){
            if($x + 1 == $count){
                $q .= $alterOps[$x].';';
            }
            else{
                $q .= $alterOps[$x].', ';
            }
        }
        $this->setQuery($q, 'alter');
    }
    /**
     * Constructs a query that can be used to alter a table and add a 
     * foreign key to it.
     * @param ForeignKey $key An object of type <b>ForeignKey</b>.
     * @since 1.4
     */
    public function foreignKey($key){
        $this->setQuery($key->getAlterStatement(), 'alter');
    }
    /**
     * Constructs a query that can be used to create a new table.
     * @param Table $table an instance of <b>Table</b>.
     * @since 1.4
     */
    public function createTable($table){
        if($table != NULL){
            $query = 'create table if not exists '.$table->getName().'(';
            $keys = $table->keys();
            $count = count($keys);
            for($x = 0 ; $x < $count ; $x++){
                if($x + 1 == $count){
                    $query .= $table->columns()[$keys[$x]];
                }
                else{
                    $query .= $table->columns()[$keys[$x]].', ';
                }
            }
            $query .= ')';
            $query .= 'ENGINE = '.$table->getEngine().' ';
            $query .= 'DEFAULT CHARSET = '.$table->getCharSet().'; ';
            
            //add forign keys
            $count2 = count($table->forignKeys());
            for($x = 0 ; $x < $count2 ; $x++){
                $query .= $table->forignKeys()[$x]->getAlterStatement().'; ';
            }
            $this->setQuery($query, 'create');
        }
    }
    /**
     * Escape any MySQL special characters from a string.
     * @param string $query The string that the characters will be escaped from.
     * @return string A string with escaped MySQL characters.
     * @since 1.4
     */
    public static function escapeMySQLSpeciarChars($query){
        $escapedQuery = '';
        $query = ''.$query;
        if($query){
            $mysqlSpecial = array(
                "\\","'"
            );
            $mysqlSpecialEsc = array(
                "\\\\","\'"
            );
            $count = count($mysqlSpecial);
            for($i = 0 ; $i < $count ; $i++){
                if($i == 0){
                    $escapedQuery = str_replace($mysqlSpecial[$i], $mysqlSpecialEsc[$i], $query);
                }
                else{
                    $escapedQuery = str_replace($mysqlSpecial[$i], $mysqlSpecialEsc[$i], $escapedQuery);
                }
            }
        }
        return $escapedQuery;
    }
    /**
     * Constructs a query that can be used to show some information about something.
     * @param string $toShow The thing that will be shown.
     * @since 1.4
     */
    public function show($toShow){
        $this->setQuery('show '.$toShow, 'show');
    }
    /**
     * Returns the value of the property <b>$query</b>.
     * @return string a MySql query.
     * @since 1.0
     */
    public function getQuery(){
        return $this->query;
    }
    /**
     * Returns the type of the query.
     * @return string The type of the query (such as 'select', 'update').
     * @since 1.0
     */
    public function getType(){
        return $this->queryType;
    }
    /**
     * Sets the value of the property <b>$query</b>.
     * @param string $query a MySQL query.
     * @param string $type The type of the query (such as 'select', 'update').
     * @since 1.0
     * @throws Exception If the given query type is not supported.
     * @see MySQLQuery::Q_TYPES For supported query types.
     */
    public function setQuery($query,$type){
        $ltype = strtolower($type.'');
        if(in_array($ltype, self::Q_TYPES)){
            $this->query = $query;
            $this->queryType = $ltype;
        }
        else{
            throw new Exception('Unsupported query type: '+$type);
        }
    }
    /**
     * Constructs a query that can be used to select all columns from a table.
     * @param string $tName The name of the table.
     * @since 1.0
     */
    public function selectAll($tName){
        $this->setQuery(self::SELECT.$tName, 'select');
    }
    /**
     * Constructs a query that can be used to get table data based on a specific 
     * column value.
     * @param string $table The name of the table.
     * @param string $col The name of the column in the table.
     * @param string $val The value that is used to filter data.
     * @since 1.0
     */
    public function selectByColVal($table,$col,$val){
        $this->setQuery(self::SELECT.$table.' where '.$col.' = '.$val, 'select');
    }
    /**
     * Constructs a query that can be used to get table data by using ID column.
     * @param string $table The name of the table.
     * @param string $id The value of the ID column.
     * @since 1.0
     */
    public function selectByID($table,$id){
        $this->setQuery(self::SELECT.$table.' where '.self::ID_COL.' = '.$id, 'select');
    }
    /**
     * Constructs a query that can be used to insert data into a table.
     * @param string $table The name of the table.
     * @param array $Arr An associative array of keys and values. The keys will 
     * be acting as the columns names and the values will be acting as the values 
     * that will be inserted.
     * @since 1.0
     */
    public function insert($table,$Arr){
        $cols = '';
        $vals = '';
        $count = count($Arr);
        $index = 0;
        foreach($Arr as $col => $val){
            if($index + 1 == $count){
                $cols .= $col;
                $vals .= $val;
            }
            else{
                $cols .= $col.', ';
                $vals .= $val.', ';
            }
            $index++;
        }
        $cols = ' ('.$cols.')';
        $vals = ' ('.$vals.')';
        $this->setQuery(self::INSERT.$table.$cols.' values '.$vals, 'insert');
    }
    /**
     * Constructs a query that can be used to delete a row from a table using 
     * the ID column.
     * @param string $table The name of the table.
     * @param string $id The value of the ID on the row.
     * @since 1.0
     */
    public function delete($table,$id,$idColName=self::ID_COL){
        $this->setQuery(self::DELETE.$table.' where '.$idColName.' = '.$id, 'delete');
    }
    /**
     * Constructs a query that can be used to update the values of a table row.
     * @param string $table The name of the table.
     * @param array $arr An associative array of keys and values. The keys will 
     * be acting as the columns names and the values will be acting as the new 
     * values for each field.
     * @param string $id The value of the ID column.
     * @since 1.0
     */
    public function update($table,$arr,$id,$idColName=self::ID_COL){
        $cols = '';
        $count = count($arr);
        $index = 0;
        foreach($arr as $col => $val){
            if($index + 1 == $count){
                $cols .= $col.' = '.$val;
            }
            else{
                $cols .= $col.' = '.$val.', ';
            }
            $index++;
        }
        $this->setQuery('update '.$table.' set '.$cols.' where '.$idColName.' = '.$id, 'update');
    }
    /**
     * Updates a table columns that has a datatype of blob from source files.
     * @param string $table The name of the table.
     * @param array $arr An associative array of keys and values. The keys will 
     * be acting as the columns names and the values should be a path to a file 
     * on the host machine.
     * @param string $id  the ID of the record that will be updated.
     * @since 1.2
     */
    public function updateBlobFromFile($table,$arr,$id){
        $cols = '';
        $count = count($arr);
        $index = 0;
        foreach($arr as $col => $val){
            $fixedPath = str_replace('\\', '/', $val);
            $file = fopen($fixedPath, 'r');
            $data = '\'\'';
            if($file !== FALSE){
                $fileContent = fread($file, filesize($fixedPath));
                if($fileContent !== FALSE){
                    $data = '\''. addslashes($fileContent).'\'';
                }
            }
            if($index + 1 == $count){
                $cols .= $col.' = '.$data;
            }
            else{
                $cols .= $col.' = '.$data.', ';
            }
            $index++;
        }
        $this->setQuery('update '.$table.' set '.$cols.' where '.self::ID_COL.' = '.$id, 'update');
    }
    /**
     * Returns a JSON string that represents the query.
     * @return string A JSON object on the following formate: 
     * <b><br/>{<br/>&nbsp;&nbsp;"query":"",<br/>&nbsp;&nbsp;"query-type":""<br/>}</b>
     * @since 1.1
     */
    public function toJSON(){
        $json = new JsonX();
        $json->add('query', $this->getQuery());
        $json->add('type', $this->getType());
        return $json;
    }
    /**
     * Constructs a query that can be used to select maximum value of a table column.
     * @param string $table The name of the table.
     * @param string $col The name of the column.
     * @param string $rename The new name of the column that contains max value (optional). 
     * the default is 'max({col_name}' where 'col_name' is the name of the column.
     * @since 1.3
     */
    public function selectMax($table,$col,$rename='max'){
        if($rename !== 'max'){
            $this->setQuery('select max('.$col.') as '.$rename.' from '.$table, 'select');
        }
    }
    /**
     * Constructs a query that can be used to select minimum value of a table column.
     * @param string $table The name of the table.
     * @param string $col The name of the column.
     * @param string $rename The new name of the column that contains min value (optional). 
     * the default is 'min({col_name}' where 'col_name' is the name of the column.
     * @since 1.3
     */
    public function selectMin($table,$col,$rename='max'){
        if($rename !== 'max'){
            $this->setQuery('select min('.$col.') as '.$rename.' from '.$table, 'select');
        }
    }
    /**
     * Constructs a query that can be used to get the maximum value of the ID column 
     * in a table. The value will be contained in a column with the name 'id'.
     * @param string $table The name of the table.
     * @since 1.3
     */
    public function selectMaxID($table){
        $this->selectMax($table, self::ID_COL, self::ID_COL); 
    }
    /**
     * Constructs a query that can be used to get the minimum value of the ID column 
     * in a table. The value will be contained in a column with the name 'id'.
     * @param string $table The name of the table.
     * @since 1.3
     */
    public function selectMinID($table){
        $this->selectMin($table, self::ID_COL, self::ID_COL); 
    }
    public function __toString() {
        return 'Query: '.$this->getQuery().'<br/>'.'Query Type: '.$this->getType().'<br/>';
    }
}
