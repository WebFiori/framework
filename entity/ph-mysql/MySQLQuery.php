<?php
/**
 * A base class that is used to construct MySQL queries. It can be used as a base 
 * class for constructing other MySQL queries.<br/>
 * @uses Table Used by the 'create table' Query.
 * @uses ForeignKey Used to alter a table and insert a foreign key in it.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.8.1
 */
abstract class MySQLQuery implements JsonI{
    /**
     * Line feed character.
     * @since 1.8.1
     */
    const NL = "\n";
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
    /**
     * Constructs a query that can be used to get the number of tables in a 
     * schema given its name.
     * @param string $schemaName The name of the schema. The result of executing 
     * the query is a table with one row and one column. The column name will be 
     * 'tables_count' which will contain an integer value that indicates the 
     * number of tables in the schema. If the schema does not exist or has no tables, 
     * the result in the given column will be 0.
     * @since 1.8
     */
    public function schemaTablesCount($schemaName){
        $this->query = 'select count(*) as tables_count from information_schema.tables where TABLE_TYPE = \'BASE TABLE\' and TABLE_SCHEMA = \''.$schemaName.'\';';
        $this->queryType = 'select';
    }
    /**
     * Constructs a query that can be used to get all tables in a schema given its name.
     * @param string $schemaName The name of the schema. The result of executing the query 
     * is a table with one colum. The name of the column is 'TABLE_NAME'. The column 
     * will simply contain all the names of the tables in the schema. If the given 
     * schema does not exist or has no tables, The result will be an empty table.
     * @since 1.8 
     */
    public function getSchemaTables($schemaName) {
        $this->query = 'select TABLE_NAME from information_schema.tables where TABLE_TYPE = \'BASE TABLE\' and TABLE_SCHEMA = \''.$schemaName.'\'';
        $this->queryType = 'select';
    }
    /**
     * Constructs a query that can be used to get the number of views in a 
     * schema given its name.
     * @param string $schemaName The name of the schema. The result of executing 
     * the query is a table with one row and one column. The column name will be 
     * 'views_count' which will contain an integer value that indicates the 
     * number of views in the schema. If the schema does not exist or has no views, 
     * the result in the given column will be 0.
     * @since 1.8
     */
    public function schemaViewsCount($schemaName){
        $this->query = 'select count(*) as views_count from information_schema.tables where TABLE_TYPE = \'VIEW\' and TABLE_SCHEMA = \''.$schemaName.'\';';
        $this->queryType = 'select';
    }
    /**
     * Constructs a query that can be used to get all views in a schema given its name.
     * @param string $schemaName The name of the schema. The result of executing the query 
     * is a table with one colum. The name of the column is 'TABLE_NAME'. The column 
     * will simply contain all the names of the views in the schema. If the given 
     * schema does not exist or has no views, The result will be an empty table.
     * @since 1.8 
     */
    public function getSchemaViews($schemaName) {
        $this->query = 'select TABLE_NAME from information_schema.tables where TABLE_TYPE = \'VIEW\' and TABLE_SCHEMA = \''.$schemaName.'\'';
        $this->queryType = 'select';
    }
    public function __construct() {
        $this->query = self::SELECT.' a_table';
        $this->queryType = 'select';
    }
    /**
     * Constructs a query that can be used to alter the properties of a table
     * given its name.
     * @param array $alterOps An array that contains the alter operations.
     * @since 1.4
     */
    public function alter($alterOps){
        $q = 'alter table '.$this->getStructureName().self::NL;
        $count = count($alterOps);
        for($x = 0 ; $x < $count ; $x++){
            if($x + 1 == $count){
                $q .= $alterOps[$x].';'.self::NL;
            }
            else{
                $q .= $alterOps[$x].','.self::NL;
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
    private function createTable($table,$inclComments=false){
        if($table instanceof Table){
            $query = '';
            if($inclComments){
                $query .= '-- Structure of the table \''.$this->getStructureName().'\''.self::NL;
                $query .= '-- Number of columns: \''.count($this->getStructure()->columns()).'\''.self::NL;
                $query .= '-- Number of forign keys: \''.count($this->getStructure()->forignKeys()).'\''.self::NL;
            }
            $query = 'create table if not exists '.$table->getName().'('.self::NL;
            $keys = $table->keys();
            $count = count($keys);
            for($x = 0 ; $x < $count ; $x++){
                if($x + 1 == $count){
                    $query .= '    '.$table->columns()[$keys[$x]].self::NL;
                }
                else{
                    $query .= '    '.$table->columns()[$keys[$x]].','.self::NL;
                }
            }
            $query .= ')'.self::NL;
            $query .= 'ENGINE = '.$table->getEngine().self::NL;
            $query .= 'DEFAULT CHARSET = '.$table->getCharSet().self::NL;
            $query .= 'collate = utf8_general_ci;'.self::NL;
            
            //add forign keys
            $count2 = count($table->forignKeys());
            if($inclComments && $count2 != 0){
                $query .= '-- Forign keys of the table '.self::NL;
            }
            for($x = 0 ; $x < $count2 ; $x++){
                $query .= $table->forignKeys()[$x]->getAlterStatement().';'.self::NL;
            }
            if($inclComments){
                $query .= '-- End of the Structure of the table \''.$this->getStructureName().'\''.self::NL;
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
     * @param int $limit [Optional] The value of the attribute 'limit' of the select statement. 
     * If zero or a negative value is given, it will not be included in the generated 
     * MySQL query. Default is -1.
     * @param int $offset [Optional] The value of the attribute 'offset' of the select statement. 
     * If zero or a negative value is given, it will not be included in the generated 
     * MySQL query. Default is -1.
     * @since 1.0
     */
    public function selectAll($limit=-1,$offset=-1){
        if($limit > 0 && $offset > 0){
            $lmit = 'limit '.$limit.' offset '.$offset;
        }
        else if($limit > 0 && $offset <= 0){
            $lmit = 'limit '.$limit;
        }
        else{
            $lmit = '';
        }
        $this->setQuery(self::SELECT.$this->getStructureName().' '.$lmit, 'select');
    }
    /**
     * Constructs a query that can be used to get table data based on a specific 
     * column value.
     * @param string $col The name of the column in the table.
     * @param string $val The value that is used to filter data.
     * @param string $cond [Optional] The condition of select statement. It can be '=' or 
     * '!='. If anything else is given, '=' will be used. Note that if 
     * the parameter <b>$val</b> is equal to 'IS NULL' or 'IS NOT NULL', 
     * This parameter is ignored. Default is '='.
     * @param int $limit [Optional] The value of the attribute 'limit' of the select statement. 
     * If zero or a negative value is given, it will not be included in the generated 
     * MySQL query. Default is -1.
     * @param int $offset [Optional] The value of the attribute 'offset' of the select statement. 
     * If zero or a negative value is given, it will not be included in the generated 
     * MySQL query. Default is -1.
     * @since 1.0
     */
    public function selectByColVal($col,$val,$cond='=',$limit=-1,$offset=-1){
        if($limit > 0 && $offset > 0){
            $lmit = 'limit '.$limit.' offset '.$offset;
        }
        else if($limit > 0 && $offset <= 0){
            $lmit = 'limit '.$limit;
        }
        else{
            $lmit = '';
        }
        $valUpper = strtoupper(trim($val));
        if($valUpper == 'IS NOT NULL' || $valUpper == 'IS NULL'){
            $this->setQuery(self::SELECT.$this->getStructureName().' where '.$col.' '.$val.' '.$lmit, 'select');
        }
        else{
            if(trim($cond) == '!='){
                $this->setQuery(self::SELECT.$this->getStructureName().' where '.$col.' != '.$val.' '.$lmit, 'select');
            }
            else{
                $this->setQuery(self::SELECT.$this->getStructureName().' where '.$col.' = '.$val.' '.$lmit, 'select');
            }
        }
    }
    /**
     * Selects a values from a table given specific columns values.
     * @param array $cols An array that contains an objects of type <b>Column</b>.
     * @param array $vals An array that contains values. 
     * @param array $valsConds An array that can contains two possible values: 
     * '=' or '!='. If anything else is given at specific index, '=' will be used. 
     * Note that if the value at <b>$vals[$index]</b> is equal to 'IS NULL' or 'IS NOT NULL', 
     * The value at <b>$valsConds[$index]</b> is ignored. 
     * @param array $jointOps An array of conditions (Such as 'or', 'and', 'xor').
     * @since 1.6
     */
    public function selectByColsVals($cols,$vals,$valsConds,$jointOps,$limit=-1,$offset=-1){
        $where = '';
        $count = count($cols);
        $index = 0;
        foreach($cols as $col){
            $equalityCond = trim($valsConds[$index]);
            if($equalityCond != '!=' && $equalityCond != '='){
                $equalityCond = '=';
            }
            if($col instanceof Column){
                $valUpper = strtoupper(trim($vals[$index]));
                if($valUpper == 'IS NULL' || $valUpper == 'IS NOT NULL'){
                    if($index + 1 == $count){
                        $where .= $col->getName().' '.$vals[$index].'';
                    }
                    else{
                        $where .= $col->getName().' '.$vals[$index].' '.$jointOps[$index].' ';
                    }
                }
                else{
                    if($index + 1 == $count){
                        $where .= $col->getName().' '.$equalityCond.' ';
                        if($col->getType() == 'varchar' || $col->getType() == 'datetime' || $col->getType() == 'timestamp' || $col->getType() == 'text' || $col->getType() == 'mediumtext'){
                            $where .= '\''.$vals[$index].'\'' ;
                        }
                        else{
                            $where .= $vals[$index];
                        }
                    }
                    else{
                        $where .= $col->getName().' '.$equalityCond.' ';
                        if($col->getType() == 'varchar' || $col->getType() == 'datetime' || $col->getType() == 'timestamp' || $col->getType() == 'text' || $col->getType() == 'mediumtext'){
                            $where .= '\''.$vals[$index].'\' '.$jointOps[$index].' ' ;
                        }
                        else{
                            $where .= $vals[$index].' '.$jointOps[$index].' ';
                        }
                    }
                }
            }
            $index++;
        }
        if($limit > 0 && $offset > 0){
            $lmit = 'limit '.$limit.' offset '.$offset;
        }
        else if($limit > 0 && $offset <= 0){
            $lmit = 'limit '.$limit;
        }
        else{
            $lmit = '';
        }
        $this->setQuery(self::SELECT.$this->getStructureName().' where '.$where.' '.$lmit.';', 'select');
    }
    /**
     * Constructs a query that can be used to get table data by using ID column.
     * @param string $id The value of the ID column.
     * @since 1.0
     */
    public function selectByID($id){
        $this->setQuery(self::SELECT.$this->getStructureName().' where '.self::ID_COL.' = '.$id, 'select');
    }
    /**
     * Constructs a query that can be used to insert data into a table.
     * @param array $Arr An associative array of keys and values. The keys will 
     * be acting as the columns names and the values will be acting as the values 
     * that will be inserted.
     * @since 1.0
     */
    public function insert($Arr){
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
        $this->setQuery(self::INSERT.$this->getStructureName().$cols.' values '.$vals, 'insert');
    }
    /**
     * Constructs a query that can be used to delete a row from a table using 
     * the ID column.
     * @param string $id The value of the ID on the row.
     * @since 1.0
     */
    public function delete($id,$idColName=self::ID_COL){
        $this->setQuery(self::DELETE.$this->getStructureName().' where '.$idColName.' = '.$id, 'delete');
    }
    /**
     * Constructs a query that can be used to update the values of a table row.
     * @param array $arr An associative array of keys and values. The keys will 
     * be acting as the columns names and the values will be acting as the new 
     * values for each field.
     * @param string $id The value of the ID column.
     * @since 1.0
     */
    public function update($arr,$id,$idColName=self::ID_COL){
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
        $this->setQuery('update '.$this->getStructureName().' set '.$cols.' where '.$idColName.' = '.$id, 'update');
    }
    /**
     * Updates a table columns that has a datatype of blob from source files.
     * @param array $arr An associative array of keys and values. The keys will 
     * be acting as the columns names and the values should be a path to a file 
     * on the host machine.
     * @param string $id  the ID of the record that will be updated.
     * @since 1.2
     */
    public function updateBlobFromFile($arr,$id,$idColName=self::ID_COL){
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
        $this->setQuery('update '.$this->getStructureName().' set '.$cols.' where '.$idColName.' = '.$id, 'update');
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
     * @param string $col The name of the column.
     * @param string $rename The new name of the column that contains max value (optional). 
     * the default is 'max({col_name}' where 'col_name' is the name of the column.
     * @since 1.3
     */
    public function selectMax($col,$rename='max'){
        if($rename !== 'max'){
            $this->setQuery('select max('.$col.') as '.$rename.' from '.$this->getStructureName(), 'select');
        }
        else {
            $this->setQuery('select max('.$col.') from '.$this->getStructureName(), 'select');
        }
    }
    /**
     * Constructs a query that can be used to select minimum value of a table column.
     * @param string $col The name of the column.
     * @param string $rename The new name of the column that contains min value (optional). 
     * the default is 'min({col_name}' where 'col_name' is the name of the column.
     * @since 1.3
     */
    public function selectMin($col,$rename='max'){
        if($rename !== 'max'){
            $this->setQuery('select min('.$col.') as '.$rename.' from '.$this->getStructureName(), 'select');
        }
        else {
            $this->setQuery('select min('.$col.') from '.$this->getStructureName(), 'select');
        }
    }
    /**
     * Constructs a query that can be used to get the maximum value of the ID column 
     * @since 1.3
     */
    public function selectMaxID(){
        $this->selectMax(self::ID_COL, self::ID_COL); 
    }
    /**
     * Constructs a query that can be used to get the minimum value of the ID column 
     * in a table. The value will be contained in a column with the name 'id'.
     * @since 1.3
     */
    public function selectMinID(){
        $this->selectMin(self::ID_COL, self::ID_COL); 
    }
    /**
     * Constructs a query that can be used to create the table.
     * @param boolean $inclComments If set to <b>TRUE</b>, the generated MySQL 
     * query will have basic comments explaining the structure.
     * @since 1.5
     */
    public function createStructure($inclComments=false){
        $this->createTable($this->getStructure($inclComments));
    }
    /**
     * Returns the name of the column from the table given its key.
     * @param string $colKey The name of the column key.
     * @return string The name of the column in the table. If no column was 
     * found, the function will return the string 'NO_SUCH_COL'. If there is 
     * no table linked with the query object, the function will return the 
     * string 'NO_STRUCTURE'.
     * @since 1.5
     */
    public function getColName($colKey){
        $col = $this->getCol($colKey);
        if($col instanceof Column){
            return $col->getName();
        }
        return $col;
    }
    /**
     * Returns a column from the table given its key.
     * @param string $colKey The name of the column key.
     * @return string|Column The the column in the table. If no column was 
     * found, the function will return the string 'NO_SUCH_COL'. If there is 
     * no table linked with the query object, the function will return the 
     * string 'NO_STRUCTURE'.
     * @since 1.6
     */
    public function getCol($colKey){
        $structure = $this->getStructure();
        if($structure instanceof Table){
            $col = $structure->getCol($colKey);
            if($col instanceof Column){
                return $col;
            }
            return 'NO_SUCH_COL';
        }
        return $structure;
    }
    /**
     * Returns the table that is used for constructing queries.
     * @return Table The table that is used for constructing queries.
     * @since 1.5
     */
    public abstract function getStructure();
    /**
     * Returns the name of the table that is used to construct queries.
     * @return string The name of the table that is used to construct queries. 
     * if no table is given, the function will return the string 'NO_STRUCTURE'.
     * @since 1.5
     */
    public function getStructureName(){
        $s = $this->getStructure();
        if($s instanceof Table){
            return $s->getName();
        }
        return 'NO_STRUCTURE';
    }
    
    public function __toString() {
        return 'Query: '.$this->getQuery().'<br/>'.'Query Type: '.$this->getType().'<br/>';
    }
}
