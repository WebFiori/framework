<?php
/**
 * MIT License
 *
 * Copyright (c) 2019 Ibrahim BinAlshikh, phMysql library.
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
namespace phMysql;
use Exception;
use phMysql\MySQLTable;
/**
 * A base class that is used to construct MySQL queries. It can be used as a base 
 * class for constructing other MySQL queries.
 * @author Ibrahim
 * @version 1.9.0
 */
class MySQLQuery{
    /**
     * The name of the entity class at which a select 
     * statement result will be mapped to.
     * @var string|null
     * @since 1.9.0
     */
    private $resultMap;
    /**
     * The linked database table.
     * @var MySQLTable 
     */
    private $table;
    /**
     * The name of database schema that the query will be executed on.
     * @var string 
     * @since 1.8.7
     */
    private $schemaName;
    /**
     * An attribute that is set to true if the query is an update or insert of 
     * blob datatype.
     * @var boolean 
     */
    private $isFileInsert;
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
     * A constant that indicates a table structure is not linked with the instance.
     * @since 1.8.3
     */
    const NO_STRUCTURE = 'no_struture';
    /**
     * An array that contains the supported MySQL query types.
     * @since 1.1
     */
    const Q_TYPES = array(
        'select','update','delete','insert','show','create','alter','drop'
    );
    /**
     * A constant for the query 'select * from '.
     * @since 1.0
     */
    const SELECT = 'select * from ';
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
    private $origColsNames = [];
    /**
     * A string that represents the type of the query such as 'select' or 'update'.
     * @var string 
     * @since 1.0
     */
    private $queryType;
    /**
     * Constructs a query that can be used to get the number of tables in a 
     * schema given its name.
     * The result of executing 
     * the query is a table with one row and one column. The column name will be 
     * 'tables_count' which will contain an integer value that indicates the 
     * number of tables in the schema. If the schema does not exist or has no tables, 
     * the result in the given column will be 0.
     * @param string $schemaName The name of the schema.
     * @since 1.8
     */
    public function schemaTablesCount($schemaName){
        $this->query = 'select count(*) as tables_count from information_schema.tables where TABLE_TYPE = \'BASE TABLE\' and TABLE_SCHEMA = \''.$schemaName.'\';';
        $this->queryType = 'select';
    }
    /**
     * Returns the name of the entity class at which a select result will 
     * be mapped to.
     * @return string|null If the entity name is set, the method will return 
     * it as string. If not set, the method will return null.
     * @since 1.9.0
     */
    public function getMappedEntity() {
        return $this->resultMap;
    }
    /**
     * Constructs a query which can be used to update the server's global 
     * variable 'max_allowed_packet'.
     * The value of the attribute is in bytes. The developer might want to 
     * update the value of this variable if he wants to send large data to 
     * database using one query. The maximum value this attribute can have is 
     * 1073741824 bytes.
     * @param int $size The new size.
     * @param string $unit One of 4 values: 'B' for byte, 'KB' for kilobyte, 
     * 'MB' for megabyte and 'GB' for gigabyte. If the given value is none of the 
     * 4, the type will be set to 'MP'.
     */
    public function setMaxPackete($size,$unit='MB'){
        $max = 1073741824;
        $updatedSize = 0;
        $uUnit = strtoupper($unit);
        if($uUnit != 'MB' && $uUnit != 'B' && $uUnit != 'KB' && $uUnit != 'GB'){
            $uUnit = 'MB';
        }
        switch ($uUnit){
            case 'B':{
                $updatedSize = $size < $max && $size > 0 ? $size : $max;
                break;
            }
            case 'KB':{
                $new = $size*1024;
                $updatedSize = $new < $max && $new > 0 ? $new : $max;
                break;
            }
            case 'MB':{
                $new = $size*1024*1024;
                $updatedSize = $new < $max && $new > 0 ? $new : $max;
                break;
            }
            case 'GB':{
                $new = $size*1024*1024*1024;
                $updatedSize = $new < $max && $new > 0 ? $new : $max;
                break;
            }
        }
        $this->query = 'set global max_allowed_packet = '.$updatedSize.';';
    }
    /**
     * Constructs a query that can be used to get all tables in a schema given its name.
     * The result of executing the query is a table with one colum. The name 
     * of the column is 'TABLE_NAME'. The column will simply contain all the 
     * names of the tables in the schema. If the given schema does not exist 
     * or has no tables, The result will be an empty table.
     * @param string $schemaName The name of the schema.
     * @since 1.8 
     */
    public function getSchemaTables($schemaName) {
        $this->query = 'select TABLE_NAME from information_schema.tables where TABLE_TYPE = \'BASE TABLE\' and TABLE_SCHEMA = \''.$schemaName.'\'';
        $this->queryType = 'select';
    }
    /**
     * Constructs a query that can be used to get the number of views in a 
     * schema given its name.
     * The result of executing the query is a table with one row and one column.
     *  The column name will be 'views_count' which will contain an integer 
     * value that indicates the number of views in the schema. If the schema 
     * does not exist or has no views, the result in the given column will be 0.
     * @param string $schemaName The name of the schema.
     * @since 1.8
     */
    public function schemaViewsCount($schemaName){
        $this->query = 'select count(*) as views_count from information_schema.tables where TABLE_TYPE = \'VIEW\' and TABLE_SCHEMA = \''.$schemaName.'\';';
        $this->queryType = 'select';
    }
    /**
     * Sets the name of database (Schema) that the query will be executed on.
     * A schema is a collection of tables. On the other hand, a database 
     * is a collection of schema. In MySQL, the two terms usually refer to the 
     * same thing.
     * @param string $name Database schema name. A valid name 
     * must have the following conditions:
     * <ul>
     * <li>Must not be an empty string.</li>
     * <li>Cannot start with a number.</li>
     * <li>Can have numbers in the middle.</li>
     * <li>Consist of the following characters: [A-Z][a-z] and underscore only.</li>
     * </ul>
     * @return boolean If the name of the schema is set, the method will return 
     * true. Other than that, the method will return false.
     * @since 1.8.7
     */
    public function setSchemaName($name) {
        $nameT = trim($name);
        if(strlen($nameT) != 0){
            $len = strlen($nameT);
            if($len > 0){
                for ($x = 0 ; $x < $len ; $x++){
                    $ch = $nameT[$x];
                    if($x == 0 && ($ch >= '0' && $ch <= '9')){
                        return false;
                    }
                    if($ch == '_' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || ($ch >= '0' && $ch <= '9')){

                    }
                    else{
                        return false;
                    }
                }
                $this->schemaName = $nameT;
                $this->getTable()->setSchemaName($nameT);
                return true;
            }
        }
        return false;
    }
    /**
     * Returns database schema name that the query will be executed on.
     * A schema is a collection of tables. On the other hand, a database 
     * is a collection of schema. In MySQL, the two terms usually refer to the 
     * same thing.
     * @return string Database schema name. If not set, the method will 
     * return null.
     * @since 1.8.7
     */
    public function getSchemaName() {
        return $this->schemaName;
    }
    /**
     * Constructs a query that can be used to get the names of all views in a 
     * schema given its name.
     * The result of executing the query is a table with one column. The name 
     * of the column is 'TABLE_NAME'. The column will simply contain all the 
     * names of the views in the schema. If the given schema does not exist 
     * or has no views, The result will be an empty table.
     * @param string $schemaName The name of the schema.
     * @since 1.8 
     */
    public function getSchemaViews($schemaName) {
        $this->query = 'select TABLE_NAME from information_schema.tables where TABLE_TYPE = \'VIEW\' and TABLE_SCHEMA = \''.$schemaName.'\'';
        $this->queryType = 'select';
    }
    /**
     * Creates new instance of the class.
     * @param string $tableName The name of the table that will be associated 
     * with the queries that will be created.
     */
    public function __construct($tableName=null) {
        $this->table = new MySQLTable($tableName);
        $this->query = self::SELECT.$this->getTableName();
        $this->queryType = 'select';
        $this->setIsBlobInsertOrUpdate(false);
        $this->origColsNames = [];
    }
    /**
     * Links a table to the query.
     * @param MySQLTable $tableObj The table that will be linked.
     * @since 1.9.0
     */
    public function setTable($tableObj) {
        if($tableObj instanceof MySQLTable){
            $this->table = $tableObj;
            $this->table->setOwnerQuery($this);
        }
    }
    /**
     * Constructs a query that can be used to alter the properties of a table
     * given its name.
     * @param array $alterOps An array that contains the alter operations.
     * @since 1.4
     */
    public function alter($alterOps){
        $schema = $this->getSchemaName() === null ? '' : $this->getSchemaName().'.';
        $q = 'alter table '.$schema.''.$this->getStructureName().self::NL;
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
     * Constructs a query that can be used to add a primary key to a table.
     * @param MySQLTable $table The table that will have the primary key.
     * @since 1.8.8
     */
    public function addPrimaryKey($table) {
        if($table instanceof MySQLTable){
            $primaryCount = $table->primaryKeyColsCount();
            if($primaryCount != 0){
                $stm = 'alter table '.$table->getName().' add constraint '.$table->getPrimaryKeyName().' primary key (';
                $index = 0;
                $alterStm = '';
                foreach ($table->getColumns() as $col){
                    if($col->isPrimary()){
                        if($index + 1 == $primaryCount){
                            $stm .= $col->getName().')';
                        }
                        else{
                            $stm .= $col->getName().',';
                        }
                        if($col->isAutoInc()){
                            $alterStm .= 'alter table '.$table->getName().' modify '.$col.' auto_increment;'.self::NL;
                        }
                        $index++;
                    }
                }
                if(strlen($stm) !== 0){
                    $stm .= ';'.MySQLQuery::NL.$alterStm;
                    $this->setQuery($stm, 'alter');
                    return;
                }
                $this->setQuery('', 'alter');
            }
        }
    }
    /**
     * Constructs a query that can be used to alter a table and add a 
     * foreign key to it.
     * @param ForeignKey $key An object of type <b>ForeignKey</b>.
     * @since 1.4
     */
    public function addForeignKey($key){
        $ownerTable = $key->getOwner();
        $sourceTable = $key->getSource();
        if($sourceTable !== null && $ownerTable !== null){
            $query = 'alter table '.$ownerTable->getName()
                    . ' add constraint '.$key->getKeyName().' foreign key (';
            $ownerCols = $key->getOwnerCols();
            $ownerCount = count($ownerCols);
            $i0 = 0;
            foreach ($ownerCols as $col){
                if($i0 + 1 == $ownerCount){
                    $query .= $col->getName().') ';
                }
                else{
                    $query .= $col->getName().', ';
                }
                $i0++;
            }
            $query .= 'references '.$key->getSourceName().'(';
            $sourceCols = $key->getSourceCols();
            $refCount = count($sourceCols);
            $i1 = 0;
            foreach ($sourceCols as $col){
                if($i1 + 1 == $refCount){
                    $query .= $col->getName().') ';
                }
                else{
                    $query .= $col->getName().', ';
                }
                $i1++;
            }
            $onDelete = $key->getOnDelete();
            if($onDelete !== null){
                $query .= 'on delete '.$onDelete.' ';
            }
            $onUpdate = $key->getOnUpdate();
            if($onUpdate !== null){
                $query .= 'on update '.$onUpdate;
            }
        }
        $this->setQuery($query, 'alter');
    }
    /**
     * Constructs a query that can be used to create a new table.
     * @param MySQLTable $table an instance of <b>MySQLTable</b>.
     * @param boolean $inclSqlComments If set to true, a set of comment will appear 
     * in the generated SQL which description what is happening in every SQL Statement.
     * @since 1.4
     */
    private function _createTable($table,$inclSqlComments=false){
        if($table instanceof MySQLTable){
            $query = '';
            if($inclSqlComments === true){
                $query .= '-- Structure of the table \''.$this->getStructureName().'\''.self::NL;
                $query .= '-- Number of columns: \''.count($this->getStructure()->columns()).'\''.self::NL;
                $query .= '-- Number of forign keys count: \''.count($this->getStructure()->forignKeys()).'\''.self::NL;
                $query .= '-- Number of primary key columns count: \''.$this->getStructure()->primaryKeyColsCount().'\''.self::NL;
            }
            $query .= 'create table if not exists '.$table->getName().'('.self::NL;
            $keys = $table->colsKeys();
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
            $comment = $table->getComment();
            if($comment !== null){
                $query .= 'comment \''.$comment.'\''.self::NL;
            }
            $query .= 'ENGINE = '.$table->getEngine().self::NL;
            $query .= 'DEFAULT CHARSET = '.$table->getCharSet().self::NL;
            $query .= 'collate = '.$table->getCollation().';'.self::NL;
            $coutPk = $this->getStructure()->primaryKeyColsCount();
            if($coutPk >= 1){
                if($inclSqlComments === true){
                    $query .= '-- Add Primary key to the table.'.self::NL;
                }
                $this->addPrimaryKey($table);
                $q = $this->getQuery();
                if(strlen($q) != 0){
                    //no need to append ';\n' as it was added before.
                    $query .= $q;
                }
            }
            //add forign keys
            $count2 = count($table->forignKeys());
            if($inclSqlComments === true && $count2 != 0){
                $query .= '-- Add Forign keys to the table.'.self::NL;
            }
            for($x = 0 ; $x < $count2 ; $x++){
                $this->addForeignKey($table->forignKeys()[$x]);
                $query .= $this->getQuery().';'.self::NL;
            }
            if($inclSqlComments === true){
                $query .= '-- End of the Structure of the table \''.$this->getStructureName().'\''.self::NL;
            }
            $this->setQuery($query, 'create');
        }
    }
    /**
     * Escape any MySQL special characters from a string.
     * @param string $query The string that the characters will be escaped from.
     * @return string A string with escaped MySQL characters.
     * @deprecated since version 1.8.9
     * @since 1.4
     */
    public static function escapeMySQLSpeciarChars($query){
        $escapedQuery = '';
        $query = ''.$query;
        if($query){
            $mysqlSpecial = array(
                "\\","'","\0","\b","\n"
            );
            $mysqlSpecialEsc = array(
                "\\\\","\'","\\0","\\b","\\n"
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
     * Constructs a query that can be used to show database table engines.
     * The result of executing the query will be a table with the following 
     * columns:
     * <ul>
     * <li>Engine</li>
     * <li>Support</li>
     * <li>Comment</li>
     * <li>Transactions</li>
     * <li>Savepoints</li>
     * </ul>
     * @since 1.8.7
     */
    public function showEngines() {
        $this->show('engines');
    }
    /**
     * Constructs a query that can be used to show some information about something.
     * @param string $toShow The thing that will be shown.
     * @since 1.4
     */
    public function show($toShow){
        $this->setQuery('show '.$toShow.';', 'show');
    }
    /**
     * Constructs a query that can be used to delete the table from the 
     * database.
     * @since 1.9.0
     */
    public function dropTable() {
        $this->setQuery('drop table '.$this->getTableName().';', 'delete');
    }
    /**
     * Returns the value of the property $query.
     * It is simply the query that was constructed by calling any method 
     * of the class.
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
     * Sets the value of the property $query. 
     * The type of the query must be taken from the array MySQLQuery::Q_TYPES.
     * @param string $query a MySQL query.
     * @param string $type The type of the query (such as 'select', 'update').
     * @since 1.0
     * @throws Exception If the given query type is not supported. 
     */
    public function setQuery($query,$type){
        $ltype = strtolower($type.'');
        if(in_array($ltype, self::Q_TYPES)){
            $this->query = $query;
            $this->queryType = $ltype;
        }
        else{
            throw new Exception('Unsupported query type: \''.$type.'\'');
        }
    }
    /**
     * Constructs a query that can be used to select all columns from a table.
     * @param int $limit The value of the attribute 'limit' of the select statement. 
     * If zero or a negative value is given, it will not be ignored. 
     * Default is -1.
     * @param int $offset The value of the attribute 'offset' of the select statement. 
     * If zero or a negative value is given, it will not be ignored. 
     * Default is -1.
     * @since 1.0
     */
    public function selectAll($limit=-1,$offset=-1){
        $this->select(array(
            'limit'=>$limit,
            'offset'=>$offset
        ));
    }
    /**
     * Constructs a select query which is used to count number of rows on a 
     * table.
     * @param array $options An associative array of options to customize the 
     * select query. Available options are:
     * <ul>
     * <li><b>as</b>: A name for the column that will contain 
     * count result. If not provided, the value 'count' is used as 
     * default value.</li>
     * <li><b>where</b>: An associative array. The indices can 
     * be values and the value at each index is an objects of type 'Column'. 
     * Or the indices can be column indices or columns names taken from MySQLTable object and 
     * the values are set for each index. The second way is recommended as one 
     * table might have two columns with the same values.</li>
     * <li><b>conditions</b>: An array that can contains conditions (=, !=, &lt;, 
     * &lt;=, &gt; or &gt;=). If anything else is given at specific index, '=' will be used. In 
     * addition, If not provided or has invalid value, an array of '=' conditions 
     * is used.</li>
     * <li><b>join-operators</b>: An array that contains a set of MySQL join operators 
     * like 'and' and 'or'. If not provided or has invalid value, 
     * an array of 'and's will be used.</li>
     * </ul>
     * @since 1.8.9
     */
    public function selectCount($options=[]) {
        $asPart = ' as count';
        $where = '';
        if(gettype($options) == 'array'){
            if(isset($options['as'])){
                $trimmedAs = trim($options['as']);
                if(strlen($trimmedAs) != 0){
                    $asPart = ' as '. str_replace(' ', '_', $trimmedAs);
                }
            }
            $options['join-operators'] = isset($options['join-operators']) && 
                    gettype($options['join-operators']) == 'array' ? $options['join-operators'] : [];
            $options['conditions'] = isset($options['conditions']) && 
                    gettype($options['conditions']) == 'array' ? $options['conditions'] : [];
            if(isset($options['where']) && isset($options['conditions'])){
                $cols = [];
                $vals = [];
                foreach($options['where'] as $valOrColIndex => $colOrVal){
                    if($colOrVal instanceof MySQLColumn){
                        $cols[] = $colOrVal;
                        $vals[] = $valOrColIndex;
                    }
                    else{
                        if(gettype($valOrColIndex) == 'integer'){
                            $testCol = $this->getStructure()->getColByIndex($valOrColIndex);
                        }
                        else{
                            $testCol = $this->getStructure()->getCol($valOrColIndex);
                        }
                        $cols[] = $testCol;
                        $vals[] = $colOrVal;
                    }
                }
                $where = $this->createWhereConditions($cols, $vals, $options['conditions'], $options['join-operators']);
            }
            else{
                $where = '';
            }
            if(trim($where) == 'where'){
                $where = '';
            }
        }
        $this->setQuery('select count(*)'.$asPart.' from '.$this->getStructureName().$where.';', 'select');
    }
    /**
     * Creates an object of the class which represents a join between two tables.
     * For every join, there is a left table, a right table and a join 
     * condition. The table which 
     * will be on the left side of the join will be the table which is 
     * linked with current instance and the right table is the one which is 
     * supplied as a parameter to the method.
     * @param $options array An associative array that contains join information. 
     * The available options are:
     * <ul>
     * <li><b>right-table</b>: This index must be set. It represents the right 
     * table of the join. It can be an object of type 'MySQLQuery' or an object 
     * of type 'MySQLTable'.</li>
     * <li><b>join-cols</b>: An associative array of columns. The indices should be 
     * the names of columns keys taken from left table and the values should be 
     * columns keys taken from right table.</li>
     * <li><b>join-conditions</b>: An optional array of join conditions. It can have 
     * values like '=' or '!='.</li>
     * <li><b>join-type</b>: A string that represents the type of the join. 
     * It can have a value such as 'left', 'right' or 'cross'. Default is 'join'.</li>
     * <li><b>alias</b>: An optional name for the table that will be created 
     * by the join. Default is null which means a name will be generated 
     * automatically.</li>
     * <li><b>keys-map</b>: An optional array that can have two associative 
     * arrays. One with key 'left' and the other is with key 'right'. Each one 
     * of the two arrays can have new names for table columns keys. The indices 
     * in each array are the original keys names taken from joined tables and 
     * the values are the new keys which will exist in the joined table. It is 
     * simply used to map joined keys with new keys which will exist in the new 
     * joined table.</li>
     * </ul>

     * @return MySQLQuery|null If the join is a success, the method will return 
     * an object of type 'MySQLQuery' that can be used to get info from joined 
     * tables. If no join is formed, the method will return null.
     */
    public function join($options) {
        $right = isset($options['right-table']) ? $options['right-table'] : null;
        $joinCols = isset($options['join-cols']) && 
                gettype($options['join-cols']) == 'array' ? $options['join-cols'] : [];
        $joinType = isset($options['join-type']) ? $options['join-type'] : 'join';
        $conds = isset($options['join-conditions']) && 
                gettype($options['join-conditions']) == 'array' ? $options['join-conditions'] : [];
        $joinOps = [];
        $alias = isset($options['alias']) ? $options['alias'] : null;
        $keysAliases = isset($options['keys-map']) && 
                gettype($options['keys-map']) == 'array' ? $options['keys-map'] : [];
        if($right instanceof MySQLQuery || $right instanceof MySQLTable){
            $joinQuery = new MySQLQuery();
            $joinTable = new JoinTable($this, $right, $alias, $keysAliases);
            $joinTable->setJoinType($joinType);
            $joinTable->setJoinCondition($joinCols, $conds, $joinOps);
            $joinQuery->setTable($joinTable);
            return $joinQuery;
        }
    }
    /**
     * Constructs a 'select' query.
     * @param array $selectOptions An associative array which contains 
     * options to construct different select queries. The available options are: 
     * <ul>
     * <li><b>map-result-to</b>: A string that represents the name 
     * of the entity class at which query result will be mapped to. If the 
     * entity class is in a namespace, then this value must have the name of the 
     * namespace.</li>
     * <li><b>columns</b>: An optional array which can have the keys of columns that 
     * will be select. Also, this array can be an associative array. The indices are columns 
     * keys and the values are aliases for the columns. In case of joins, the array can have 
     * two sub arrays for selecting columns from left or right table. the first 
     * can exist in the index 'left' and the second one in the index 'right'.</li>
     * <li><b>limit</b>: The 'limit' attribute of the query.</li>
     * <li><b>offset</b>: The 'offset' attribute of the query. Ignored if the 
     * option 'limit' is not set.</li>
     * <li><b>condition-cols-and-vals</b>: An associative array. The indices can 
     * be values and the value at each index is an objects of type 'Column'. 
     * Or the indices can be column indices or columns names taken from MySQLTable object and 
     * the values are set for each index. The second way is recommended as one 
     * table might have two columns with the same values. For multiple values select, 
     * the value of the indices must be a sub array that can have the following indices: 
     * 
     * <ul>
     * <li><b>values</b>: The values that the column can have.</li>
     * <li><b>conditions</b>: An array of conditions such as '=' or a string. The 
     * string can only have one of two values: 'in' or 'not in'</li>
     * <li><b>join-conditions</b>: An array of conditions which are used to join the 
     * values. The array can have one of two values: 'and' or 'or'.<li>
     * </ul></li>
     * <li><b>where</b>: Similar to 'condition-cols-and-vals'.</li>
     * <li><b>conditions</b>: An array that can contains conditions (=, !=, &lt;, 
     * &lt;=, &gt; or &gt;=). If anything else is given at specific index, '=' will be used. In 
     * addition, If not provided or has invalid value, an array of '=' conditions 
     * is used.</li>
     * <li><b>join-operators</b>: An array that contains a set of MySQL join operators 
     * like 'and' and 'or'. If not provided or has invalid value, 
     * an array of 'and's will be used.</li>
     * <li><b>select-max</b>: A boolean value. Set to true if you want to select maximum 
     * value of a column. Ignored in case the option 'columns' is set.</li>
     * <li><b>select-min</b>: A boolean value. Set to true if you want to select minimum 
     * value of a column. Ignored in case the option 'columns' or 'select-max' is set.</li>
     * <li><b>column</b>: The column which contains maximum or minimum value.</li>
     * <li><b>rename-to</b>: Rename the max or min column to the given name.</li>
     * <li><b>group-by</b>: An indexed array that contains 
     * sub associative arrays which has 'group by' columns info. The sub associative 
     * arrays can have the following indices:
     * <ul>
     * <li>col: The name of the column.</li>
     * </ul></li>
     * <li><b>order-by</b>: An indexed array that contains 
     * sub associative arrays which has columns 'order by' info. The sub associative 
     * arrays can have the following indices:
     * <ul>
     * <li><b>col</b>: The name of the column.</li>
     * <li>order-type: An optional string to represent the order. It can 
     * be 'A' for ascending or 'D' for descending</li>
     * </ul></li>
     * </ul>
     * @since 1.8.3
     */
    public function select($selectOptions=array(
        'columns'=>[],
        'condition-cols-and-vals'=>[],
        'conditions'=>[],
        'join-operators'=>[],
        'limit'=>-1,
        'offset'=>-1,
        'select-min'=>false,
        'select-max'=>false,
        'column'=>'',
        'rename-to'=>'',
        'order-by'=>null,
        'group-by'=>null,
        'without-select'=>false
        )) {
        $table = $this->getTable();
        if($table instanceof MySQLTable){
            $vNum = $table->getMySQLVersion();
            $vSplit = explode('.', $vNum);
            if(intval($vSplit[0]) <= 5 && intval($vSplit[1]) < 6){
                
            }
            $selectQuery = 'select ';
            $limit = isset($selectOptions['limit']) ? $selectOptions['limit'] : -1;
            $offset = isset($selectOptions['offset']) ? $selectOptions['offset'] : -1;
            if($limit > 0 && $offset > 0){
                $limitPart = ' limit '.$limit.' offset '.$offset;
            }
            else if($limit > 0 && $offset <= 0){
                $limitPart = ' limit '.$limit;
            }
            else{
                $limitPart = '';
            }
            $groupByPart = '';
            if(isset($selectOptions['group-by']) && gettype($selectOptions['group-by']) == 'array'){
                $groupByPart = $this->_buildGroupByCondition($selectOptions['group-by']);
            }
            $orderByPart = '';
            if(isset($selectOptions['order-by']) && gettype($selectOptions['order-by']) == 'array'){
                $orderByPart = $this->_buildOrderByCondition($selectOptions['order-by']);
            }
            if(isset($selectOptions['columns']) && gettype($selectOptions['columns']) == 'array'){
                $withTablePrefix = isset($selectOptions['table-prefix']) ? $selectOptions['table-prefix'] === true : false;
                if($table instanceof JoinTable){
                    if($table->getJoinType() == 'join'){
                        $joinStm = 'join';
                    }
                    else{
                        $joinStm = $table->getJoinType().' join';
                    }
                    $completeJoin = $this->_getJoinStm($table, $joinStm);
                    $columnsStr = $this->createColsToSelect($selectOptions['columns'], $withTablePrefix);
                    $selectQuery .= trim($columnsStr,' ').'from '.$completeJoin;
                }
                else{
                    $columnsStr = $this->createColsToSelect($selectOptions['columns'], $withTablePrefix);
                    $selectQuery .= trim($columnsStr).' from '.$this->getTableName();
                }
            }
            else if(isset ($selectOptions['select-max']) && $selectOptions['select-max'] === true){
                $renameTo = isset($selectOptions['rename-to']) ? $selectOptions['rename-to'] : '';
                if(strlen($renameTo) != 0){
                    $renameTo = 'as '.$renameTo;
                }
                else{
                    $renameTo = '';
                }
                if(isset($selectOptions['column']) && $table->hasColumn($selectOptions['column'])){
                    $selectQuery .= 'max('.$this->getColName($selectOptions['column']).') '.$renameTo.' from '.$table->getName();
                    $limitPart = '';
                }
                else{
                    return false;
                }
            }
            else if(isset ($selectOptions['select-min']) && $selectOptions['select-min'] === true){
                $renameTo = isset($selectOptions['rename-to']) ? $selectOptions['rename-to'] : '';
                if(strlen($renameTo) != 0){
                    $renameTo = 'as '.$renameTo;
                }
                else{
                    $renameTo = '';
                }
                if(isset($selectOptions['column']) && $table->hasColumn($selectOptions['column'])){
                    $selectQuery .= 'min('.$this->getColName($selectOptions['column']).') '.$renameTo.' from '.$table->getName();
                    $limitPart = '';
                }
                else{
                    return false;
                }
            }
            else{
                if($table instanceof JoinTable){
                    $colsToSelect = $this->createColsToSelect([], true);
                    if($table->getJoinType() == 'join'){
                        $joinStm = 'join';
                    }
                    else{
                        $joinStm = $table->getJoinType().' join';
                    }
                    $selectQuery .= trim($colsToSelect,' ')."from ".$this->_getJoinStm($table, $joinStm);
                }
                else{
                    $selectQuery .= '* from '.$this->getTableName();
                }
            }
            if(!isset($selectOptions['condition-cols-and-vals'])){
                $selectOptions['condition-cols-and-vals'] = isset($selectOptions['where']) ? $selectOptions['where'] : [];
            }
            $selectOptions['join-operators'] = isset($selectOptions['join-operators']) && 
                    gettype($selectOptions['join-operators']) == 'array' ? $selectOptions['join-operators'] : array();
            $selectOptions['conditions'] = isset($selectOptions['conditions']) && 
                    gettype($selectOptions['conditions']) == 'array' ? $selectOptions['conditions'] : array();
            if(isset($selectOptions['condition-cols-and-vals']) && isset($selectOptions['conditions'])){
                $cols = [];
                $vals = [];
                foreach($selectOptions['condition-cols-and-vals'] as $valOrColIndex => $colOrVal){
                    if($colOrVal instanceof MySQLColumn){
                        $cols[] = $colOrVal;
                        $vals[] = $valOrColIndex;
                    }
                    else{
                        if(gettype($valOrColIndex) == 'integer'){
                            $testCol = $table->getColByIndex($valOrColIndex);
                        }
                        else{
//                            if($table instanceof JoinTable){
//                                $testCol = $table->getJoinCol($valOrColIndex);
//                            }
//                            else{
                                $testCol = $table->getCol($valOrColIndex);
                            //}
                        }
                        $cols[] = $testCol;
                        $vals[] = $colOrVal;
                    }
                }
                $where = $table instanceof JoinTable ? 
                        $this->createWhereConditions($cols, $vals, $selectOptions['conditions'], $selectOptions['join-operators'],$table->getName()) :
                        $this->createWhereConditions($cols, $vals, $selectOptions['conditions'], $selectOptions['join-operators']);
            }
            else{
                $where = '';
            }
            if(trim($where) == 'where'){
                $where = '';
            }
            if($table instanceof JoinTable){
                if(isset($selectOptions['without-select']) && $selectOptions['without-select'] === true){
                    $this->setQuery($selectQuery.$where.$groupByPart.$orderByPart.$limitPart, 'select');
                }
                else{
                    $this->setQuery('select * from ('.$selectQuery.")\nas ".$table->getName().$where.$groupByPart.$orderByPart.$limitPart.';', 'select');
                }
            }
            else{
                $this->setQuery($selectQuery.$where.$groupByPart.$orderByPart.$limitPart.';', 'select');
            }
            $asView = isset($selectOptions['as-view']) ? $selectOptions['as-view'] === true : false;
            if($asView === true){
                $viewName = $this->getTableName();
                if(isset($selectOptions['view-name'])){
                    $trimmed = trim($selectOptions['view-name']);
                    if(strlen($trimmed) != 0){
                        $viewName = $trimmed;
                    }
                }
                $this->setQuery('create view '.$viewName.' as ('.trim($this->getQuery(),';').');', 'create');
            }
            if(isset($selectOptions['map-result-to'])){
                if(class_exists($selectOptions['map-result-to'])){
                    $this->resultMap = $selectOptions['map-result-to'];
                }
                else{
                    $this->resultMap = null;
                }
            }
            else{
                $this->resultMap = null;
            }
            foreach ($this->origColsNames as $key => $origName){
                $this->getCol($key)->setName($origName);
            }
            return true;
        }
        return false;
    }
    /**
     * Constructs a string which contains columns names that will be selected.
     * @param array $colsArr It can be an indexed array which contains columns 
     * names as specified while creating the linked table. Or it can be an 
     * associative array. The key should be the name of the column and the value 
     * is an alias to the column. For example, If the following array is given:
     * <p>
     * <code>[
     * 'name','id'=>'user_id','email'
     * ]</code>
     * </p>
     * And assuming that the column names are the same as given values, 
     * Then the output will be the following string:
     * <p>
     * <code>name, id as user_id, email</code>
     * </p>
     * @param boolean $withTablePrefix If set to true, then column name will be 
     * prefixed with table name.
     * @return string
     * @since 1.9.0
     */
    public function createColsToSelect($colsArr,$withTablePrefix){
        $retVal = '';
        $table = $this->getTable();
        if($table instanceof JoinTable && $table->hasCommon()){
            if(count($colsArr) == 0){
                $comma = " \n";
                foreach ($table->getLeftTable()->getColumns() as $colObj){
                    if($table->isCommon($colObj->getName())){
                        $alias = 'left_'.$colObj->getName();
                        $colObj->setAlias($alias);
                        $asPart = $comma.$colObj->getName(true).' as '.$alias;
                    }
                    else{
                        $asPart = $comma.$colObj->getName(true);
                    }
                    $retVal .= $asPart;
                    $comma = ",\n";
                }
                foreach ($table->getRightTable()->getColumns() as $colObj){
                    if($table->isCommon($colObj->getName())){
                        $alias = 'right_'.$colObj->getName();
                        $colObj->setAlias($alias);
                        $asPart = $comma.$colObj->getName(true).' as '.$alias;
                    }
                    else{
                        $asPart = $comma.$colObj->getName(true);
                    }
                    $retVal .= $asPart;
                }
            }
            else{
                $comma = " \n";
                if(isset($colsArr['left']) && gettype($colsArr['left']) == 'array'){
                    $retVal .= $this->_createColToSelechH1($colsArr['left'], $comma, 'left');
                }
                if(isset($colsArr['right']) && gettype($colsArr['right']) == 'array'){
                    $retVal .= $this->_createColToSelechH1($colsArr['right'], $comma, 'right');
                }
                $retVal .= $this->_createColToSelechH1($colsArr, $comma);
            }
        }
        else{
            if(count($colsArr) == 0){
                $retVal = '*';
            }
            else{
                $comma = " \n";
                foreach ($colsArr as $index => $colName){
                    if(gettype($index) == 'string'){
                        $colObj = $this->getCol($index);
                        $colObj->setAlias($colName);
                        $asPart = ' as '.$colName;
                    }
                    else{
                        $colObj = $this->getCol($colName);
                        $asPart = '';
                    }
                    if($colObj instanceof MySQLColumn){
                        $retVal .= $comma.$colObj->getName($withTablePrefix).$asPart;
                        $comma = ",\n";
                    }
                }
            }
        }
        return $retVal."\n";
    }
    private function _createColToSelechH1($colsArr,&$comma,$leftOrRightOrBoth='both') {
        $retVal = '';
        foreach ($colsArr as $index => $colName){
            $colPart = null;
            if(gettype($index) == 'string'){
                $colPart = $this->_createColToSelectH2($index, $colName, $leftOrRightOrBoth);
            }
            else{
                $colPart = $this->_createColToSelectH2($colName, null, $leftOrRightOrBoth);
            }
            if($colPart !== null){
                $retVal .= $comma.$colPart;
                $comma = ",\n";
            }
        }
        return $retVal;
    }
    /**
     * 
     * @param type $colKey
     * @param type $alias
     * @param type $leftOrRight
     * @return type
     */
    private function _createColToSelectH2($colKey,$alias=null,$leftOrRight='both') {
        $table = $this->getTable();
        $left = true;
        $asPart = null;
        $updateName = false;
        $leftTable = $table->getLeftTable();
        $rightTable = $table->getRightTable();
        if($leftOrRight == 'left'){
            $colObj = $leftTable->getCol($colKey);
            if(!($colObj instanceof MySQLColumn)){
                $colObj = $table->getCol($colKey);
            }
        }
        else if($leftOrRight == 'right'){
            $left = false;
            $colObj = $rightTable->getCol($colKey);
            if(!($colObj instanceof MySQLColumn)){
                $colObj = $table->getCol($colKey);
            }
        }
        else{
            $colObj = $leftTable->getCol($colKey);
            if(!($colObj instanceof MySQLColumn)){
                $left = false;
                $colObj = $rightTable->getCol($colKey);
                if(!($colObj instanceof MySQLColumn) /*&& $alias !== null*/){
                    $colObj = $table->getCol($colKey);
                    if($colObj instanceof MySQLColumn && $colObj->getOwner()->getName() == $leftTable->getName()){
                        $left = true;
                    }
                    $updateName = true;
                }
            }
        }
        if($colObj instanceof MySQLColumn){
            if($alias !== null){
//                if($colObj->getAlias() !== null){
//                    $asPart = $colObj->getAlias(true).' as '.$alias;
//                    $colObj->setName($colObj->getAlias());
//                    $colObj->setAlias($alias);
//                }
//                else{
                    $asPart = $colObj->getName(true).' as '.$alias;
                    $colObj->setAlias($alias);
                //}
                if($updateName){
                    $this->origColsNames[$colKey] = $colObj->getName();
                    $colObj->setName($alias);
                }
            }
            else{
                if($this->getTable()->isCommon($colObj->getName())){
                    if($left === true){
                        $alias = 'left_'.$colObj->getName();
                        $colObj->setAlias($alias);
                        $asPart = $colObj->getName(true).' as '.$alias;
                    }
                    else{
                        $alias = 'right_'.$colObj->getName();
                        $colObj->setAlias($alias);
                        $asPart = $colObj->getName(true).' as '.$alias;
                    }
                }
                else{
                    $asPart = $colObj->getName(true);
                }
            }
        }
        else{
            $asPart = null;
        }
        return $asPart;
    }
    /**
     * 
     * @param JoinTable $table
     * @param string $joinStm
     * @return string
     * @since 1.9.0
     */
    private function _getJoinStm($table,$joinStm){
        $selectQuery = '';
        $lt = $table->getLeftTable();
        $rt = $table->getRightTable();
        $joinCond = $table->getJoinCondition();
        if($lt instanceof JoinTable){
            if($rt instanceof JoinTable){
                
            }
            else{
                $tempQuery = new MySQLQuery();
                $tempQuery->setTable($lt);
                $tempQuery->select([
                    'without-select'=>true
                ]);
                $selectQuery = '('.$tempQuery->getQuery().') as '.$lt->getName().' '.$joinStm.' '.$rt->getName()."\n".$joinCond;
            }
        }
        else if($rt instanceof JoinTable){
        
        }
        else{
            $selectQuery = $lt->getName().' '.$joinStm.' '.$rt->getName()."\n".$joinCond;
        }
        return $selectQuery;
    }
    /**
     * Constructs the 'order by' part of a query.
     * @param array $orderByArr An indexed array that contains 
     * sub associative arrays which has columns 'order by' info. The sub associative 
     * arrays can have the following indices:
     * <ul>
     * <li>col: The name of the column.</li>
     * <li>order-type: An optional string to represent the order. It can 
     * be 'A' for ascending or 'D' for descending</li>
     * </ul>
     * @return string The string that represents order by part.
     */
    private function _buildOrderByCondition($orderByArr){
        $colsCount = count($orderByArr);
        $orderByStr = 'order by ';
        $actualColsArr = [];
        for($x = 0 ; $x < $colsCount ; $x++){
            $colName = isset($orderByArr[$x]['col']) ? $orderByArr[$x]['col'] : null;
            $colObj = $this->getCol($colName);
            if($colObj instanceof MySQLColumn){
                $orderType = isset($orderByArr[$x]['order-type']) ? strtoupper($orderByArr[$x]['order-type']) : null;
                $actualColsArr[] = [
                    'object'=>$colObj,
                    'order-type'=>$orderType
                ];
            }
        }
        $actualCount = count($actualColsArr);
        for($x = 0 ; $x < $actualCount ; $x++){
            $colObj = $actualColsArr[$x]['object'];
            $orderByStr .= $colObj->getName();
            $orderType = $actualColsArr[$x]['order-type'];
            if($orderType == 'A'){
                $orderByStr .= ' asc';
            }
            else if($orderType == 'D'){
                $orderByStr .= ' desc';
            }
            if($x + 1 != $actualCount){
                $orderByStr .= ', ';
            }
        }
        if($orderByStr == 'order by '){
            return '';
        }
        return ' '.trim($orderByStr);
    }
    /**
     * Constructs the 'group by' part of a query.
     * @param array $groupByArr An indexed array that contains 
     * sub associative arrays which has 'group by' columns info. The sub associative 
     * arrays can have the following indices:
     * <ul>
     * <li>col: The name of the column.</li>
     * </ul>
     * @return string The string that represents order by part.
     */
    private function _buildGroupByCondition($groupByArr){
        $colsCount = count($groupByArr);
        $groupByStr = 'group by ';
        $actualColsArr = [];
        for($x = 0 ; $x < $colsCount ; $x++){
            $colName = isset($groupByArr[$x]['col']) ? $groupByArr[$x]['col'] : null;
            $colObj = $this->getCol($colName);
            if($colObj !== null){
                $actualColsArr[] = [
                    'object'=>$colObj
                ];
            }
        }
        $actualCount = count($actualColsArr);
        for($x = 0 ; $x < $actualCount ; $x++){
            $colObj = $actualColsArr[$x]['object'];
            $groupByStr .= $colObj->getName();
            if($x + 1 != $actualCount){
                $groupByStr .= ', ';
            }
        }
        if($groupByStr == 'group by '){
            return '';
        }
        return ' '.trim($groupByStr);
    }

    /**
     * Constructs a 'where' condition given a date.
     * @param string $date A date or timestamp.
     * @param string $colName The name of the column that will contain 
     * the date value.
     * @param string $format The format of the date. The supported formats 
     * are:
     * <ul>
     * <li>YYYY-MM-DD HH:MM:SS</li>
     * <li>YYYY-MM-DD</li>
     * <li>YYYY</li>
     * <li>MM</li>
     * <li>DD</li>
     * <li>HH:MM:SS</li>
     * <li>HH</li>
     * <li>MM</li>
     * <li>SS</li>
     * </ul>
     */
    public static function createDateCondition($date,$colName,$format='YYYY-MM-DD HH:MM:SS') {
        $formatInUpperCase = strtoupper(trim($format));
        $condition = '';
        if($formatInUpperCase == 'YYYY-MM-DD HH:MM:SS'){
            $dateTimeSplit = explode(' ', $date);
            if(count($date) == 2){
                $datePart = explode('-', $dateTimeSplit[0]);
                $timePart = explode(':', $dateTimeSplit[0]);
                if(count($datePart) == 3 && count($timePart) == 3){
                    $condition = 'year('.$colName.') = '.$datePart[0].' and '
                            .'month('.$colName.') = '.$datePart[1].' and '
                            .'day('.$colName.') = '.$datePart[2].' and '
                            .'hour('.$colName.') = '.$datePart[2].' and '
                            .'minute('.$colName.') = '.$datePart[2].' and '
                            .'second('.$colName.') = '.$datePart[2].' and ';
                }
            }
        }
        else if($formatInUpperCase == 'YYYY-MM-DD'){
            $datePart = explode('-', $date);
            if(count($datePart) == 3){
                $condition = 'year('.$colName.') = '.$datePart[0].' and '
                            .'month('.$colName.') = '.$datePart[1].' and '
                            .'day('.$colName.') = '.$datePart[2];
            }
        }
        else if($formatInUpperCase == 'YYYY'){
            $asInt = intval($date);
            if($asInt > 1900 && $asInt < 10000){
                $condition = 'year('.$colName.') = '.$date;
            }
        }
        else if($formatInUpperCase == 'MM'){
            $asInt = intval($date);
            if($asInt > 0 && $asInt < 13){
                $condition = 'month('.$colName.') = '.$date;
            }
        }
        else if($formatInUpperCase == 'DD'){
            $asInt = intval($date);
            if($asInt > 0 && $asInt < 32){
                $condition = 'day('.$colName.') = '.$date;
            }
        }
        else if($formatInUpperCase == 'HH:MM:SS'){
            $datePart = explode(':', $date);
            if(count($datePart) == 3){
                $condition = 'hour('.$colName.') = '.$datePart[0].' and '
                            .'minute('.$colName.') = '.$datePart[1].' and '
                            .'second('.$colName.') = '.$datePart[2];
            }
        }
        else if($formatInUpperCase == 'HH'){
            $asInt = intval($date);
            if($asInt > 0 && $asInt < 24){
                $condition = 'hour('.$colName.') = '.$date;
            }
        }
        else if($formatInUpperCase == 'SS'){
            $asInt = intval($date);
            if($asInt > 0 && $asInt < 60){
                $condition = 'second('.$colName.') = '.$date;
            }
        }
        else if($formatInUpperCase == 'MM'){
            $asInt = intval($date);
            if($asInt > 0 && $asInt < 59){
                $condition = 'minute('.$colName.') = '.$date;
            }
        }
        return $condition;
    }
    private function _selectIn($optionsArr){
        
    }
    /**
     * Constructs a query that can be used to insert a new record.
     * @param array $colsAndVals An associative array. The indices must be 
     * columns names taken from the linked table. For example, if we have 
     * a table which has two columns with names 'student-id' and 'registered-course', 
     * then the array would look like the following:
     * <p>
     * <code>[<br/>
     * &nbsp;&nbsp;'student-id'=>55<br/>
     * &nbsp;&nbsp;'registered-course'=>542<br/>]</code>
     * </p>
     * Note that it is possible for the index to be a numeric value such as 0 
     * or 1. The numeric value will represents column position in the table.
     * Another thing to note is that if a column does not have a value, either  
     * the default value of the column will be used or 'null' will be used.
     * @since 1.8.2
     */
    public function insertRecord($colsAndVals) {
        $cols = '';
        $vals = '';
        $count = count($colsAndVals);
        $index = 0;
        $comma = '';
        $columnsWithVals = [];
        $defaultCols = $this->getTable()->getDefaultColsKeys();
        $createdOnKey = $defaultCols['created-on'];
        if($createdOnKey !== null){
            $createdOnColObj = $this->getCol($createdOnKey);
        }
        else{
            $createdOnColObj = null;
        }
        foreach($colsAndVals as $colIndex=>$val){
            if($index + 1 == $count){
                $comma = '';
            }
            else{
                $comma = ',';
            }
            if(gettype($colIndex) == 'integer'){
                $column = $this->getTable()->getColByIndex($colIndex);
            }
            else{
                $column = $this->getTable()->getCol($colIndex);
            }
            if($column instanceof MySQLColumn){
                $columnsWithVals[] = $colIndex;
                $cols .= $column->getName().$comma;
                $type = $column->getType();
                if($val !== 'null'){
                    $cleanedVal = $column->cleanValue($val);
                    if($cleanedVal === null){
                        if($createdOnColObj !== null && $createdOnColObj->getIndex() == $column->getIndex()){
                            $vals .= $column->cleanValue($column->getDefault()).$comma;
                            $createdOnColObj = null;
                        }
                        else{
                            $vals .= 'null'.$comma;
                        }
                    }
                    else{
                        if($type == 'tinyblob' || $type == 'mediumblob' || $type == 'longblob'){
                            $fixedPath = str_replace('\\', '/', $val);
                            if(file_exists($fixedPath)){
                                $file = fopen($fixedPath, 'r');
                                $data = '';
                                if($file !== false){
                                    $fileContent = fread($file, filesize($fixedPath));
                                    if($fileContent !== false){
                                        $data = '\''. addslashes($fileContent).'\'';
                                        $vals .= $data.$comma;
                                        $this->setIsBlobInsertOrUpdate(true);
                                    }
                                    else{
                                        $vals .= 'null'.$comma;
                                    }
                                    fclose($file);
                                }
                                else{
                                    $vals .= 'null'.$comma;
                                }
                            }
                            else{
                                $vals .= 'null'.$comma;
                            }
                        }
                        else{
                            if($createdOnColObj !== null && $createdOnColObj->getIndex() == $column->getIndex()){
                                $vals .= $cleanedVal.$comma;
                                $createdOnColObj = null;
                            }
                            else{
                                $vals .= $cleanedVal.$comma;
                            }
                        }
                    }
                }
                else{
                    $vals .= 'null'.$comma;
                }
            }
            $index++;
        }
        if($createdOnColObj !== null){
            $cols .= ','.$createdOnColObj->getName();
            if($createdOnColObj->getDefault() == 'now()' || $createdOnColObj->getDefault() == 'current_timestamp'){
                $vals .= ",'".date('Y-m-d H:i:s')."'";
            }
            else{
                $vals .= ','.$createdOnColObj->cleanValue($createdOnColObj->getDefault());
            }
        }
        
        $cols = ' ('.$cols.')';
        $vals = ' ('.$vals.')';
        $this->setQuery(self::INSERT.$this->getTableName().$cols.' values'.$vals.';', 'insert');
    }
    /**
     * Removes a record from the table.
     * @param array $columnsAndVals An associative array. The indices of the array 
     * should be the values of the columns and the value at each index is 
     * an object of type 'Column'.
     * @param array $valsConds An array that can have one of the following 
     * values: '=','!=','&lt;','&lt;=','&gt;' and '&gt;='. The number of elements 
     * in this array must match number of 
     * elements in the array $cols. If not provided, '=' is used. Default is empty array.
     * @param array $jointOps An array which contains conditional operators 
     * to join conditions. The operators can be logical or bitwise. Possible 
     * values include: &amp;&amp;, ||, and, or, |, &amp;, xor. If not provided, 
     * 'and' is used for all values.
     * @since 1.8.2
     */
    public function deleteRecord($columnsAndVals,$valsConds=[],$jointOps=[]) {
        $colsCount = count($columnsAndVals);
        $condsCount = count($valsConds);
        $joinOpsCount = count($jointOps);
        while ($colsCount > $condsCount){
            $valsConds[] = '=';
            $condsCount = count($valsConds);
        }
        while (($colsCount - 1) > $joinOpsCount){
            $jointOps[] = 'and';
            $joinOpsCount = count($jointOps);
        }
        $cols = [];
        $vals = [];
        foreach ($columnsAndVals as $valOrIndex => $colObjOrVal){
            if($colObjOrVal instanceof MySQLColumn){
                $cols[] = $colObjOrVal;
                $vals[] = $valOrIndex;
            }
            else{
                if(gettype($valOrIndex) == 'integer'){
                    $testCol = $this->getTable()->getColByIndex($valOrIndex);
                }
                else{
                    $testCol = $this->getTable()->getCol($valOrIndex);
                }
                $cols[] = $testCol;
                $vals[] = $colObjOrVal;
            }
        }
        $query = 'delete from '.$this->getTableName();
        $this->setQuery($query.$this->createWhereConditions($cols, $vals, $valsConds, $jointOps).';', 'delete');
    }
    /**
     * A method that is used to create the 'where' part of any query in case 
     * of multiple columns.
     * @param array $cols An array that holds an objects of type 'Column'.
     * @param array $vals An array that contains columns values. The number of 
     * elements in this array must match number of elements in the array $cols.
     * @param array $valsConds An array that can have only two possible values, 
     * '=' and '!='. The number of elements in this array must match number of 
     * elements in the array $cols.
     * @param array $jointOps An array which contains conditional operators 
     * to join conditions. The operators can be logical or bitwise. Possible 
     * values include: &&, ||, and, or, |, &, xor.
     * @param string $tablePrefix An optional string that represents table prefix.
     * @return string A string that represents the 'where' part of the query.
     * @since 1.8.2
     */
    private function createWhereConditions($cols,$vals,$valsConds,$jointOps,$tablePrefix=null){
        if($tablePrefix !== null){
            $tablePrefix = trim($tablePrefix).'.';
        }
        $colsCount = count($cols);
        $valsCount = count($vals);
        $condsCount = count($valsConds);
        $joinOpsCount = count($jointOps);
        if($colsCount == 0 || $valsCount == 0){
            return '';
        }
        while ($colsCount > $condsCount){
            $valsConds[] = '=';
            $condsCount = count($valsConds);
        }
        while (($colsCount - 1) > $joinOpsCount){
            $jointOps[] = 'and';
            $joinOpsCount = count($jointOps);
        }
        if($colsCount != $valsCount || $colsCount != $condsCount || ($colsCount - 1) != $joinOpsCount){
            return '';
        }
        $index = 0;
        $where = ' where ';
        $supportedConds = ['=','!=','<','<=','>','>='];
        foreach ($cols as $col){
            //first, check given condition
            $equalityCond = trim($valsConds[$index]);
            if(!in_array($equalityCond, $supportedConds)){
                $equalityCond = '=';
            }
            //then check if column object is given
            if($col instanceof MySQLColumn){
                //then check value
                if(gettype($vals[$index]) != 'array'){
                    $cleanVal = $col->cleanValue($vals[$index]);
                    $valLower = strtolower(trim($vals[$index]));
                }
                else{
                    $val = isset($vals[$index]['values']) ? $vals[$index]['values'] : null;
                    if(gettype($val) == 'array'){
                        $cleanVal = $col->cleanValue($val);
                        $valLower = gettype($vals[$index]) != 'array' ? strtolower(trim($vals[$index])) : '';
                    }
                    else{
                        continue;
                    }
                }
                if($col->getAlias() != null){
                    $colName = $tablePrefix.$col->getAlias();
                }
                else{
                    $colName = $tablePrefix.$col->getName();
                }
                if($valLower == 'is null' || $valLower == 'is not null'){
                    $where .= $colName.' '.$valLower.' ';
                }
                else if($cleanVal === null){
                    if($equalityCond == '='){
                        $where .= $colName.' is null ';
                    }
                    else{
                        $where .= $colName.' is not null ';
                    }
                }
                else{
                    if($col->getType() == 'datetime' || $col->getType() == 'timestamp'){
                        if($equalityCond == '='){
                            $where .= $colName.' >= '.$cleanVal.' ';
                            $cleanVal = $col->cleanValue($vals[$index],true);
                            $where .= 'and '.$colName.' <= '.$cleanVal.' ';
                        }
                        else if($equalityCond == '!='){
                            $where .= $colName.' < '.$cleanVal.' ';
                            $cleanVal = $col->cleanValue($vals[$index],true);
                            $where .= 'and '.$colName.' > '.$cleanVal.' ';
                        }
                        else if($equalityCond == '>='){
                            $where .= $colName.' >= '.$cleanVal.' ';
                            $cleanVal = $col->cleanValue($vals[$index],true);
                        }
                        else if($equalityCond == '<='){
                            $cleanVal = $col->cleanValue($vals[$index],true);
                            $where .= $colName.' <= '.$cleanVal.' ';
                        }
                        else if($equalityCond == '>'){
                            $cleanVal = $col->cleanValue($vals[$index],true);
                            $where .= $colName.' > '.$cleanVal.' ';
                        }
                        else if($equalityCond == '<'){
                            $where .= $colName.' < '.$cleanVal.' ';
                        }
                    }
                    else if(gettype($vals[$index]) == 'array'){
                        $conditions = isset($vals[$index]['conditions']) ? $vals[$index]['conditions'] : [];
                        $joinConditions = isset($vals[$index]['join-operators']) ? $vals[$index]['join-operators'] : [];
                        $where .= '(';
                        if(gettype($conditions) == 'array'){
                            $condIndex = 0;
                            while(count($conditions) < count($cleanVal)){
                                $conditions[] = '=';
                            }
                            while(count($joinConditions) < count($cleanVal)){
                                $joinConditions[] = 'and';
                            }
                            foreach ($cleanVal as $singleVal){
                                $cond = $conditions[$condIndex];
                                if(!in_array($cond, $supportedConds)){
                                    $cond = '=';
                                }
                                if($condIndex > 0){
                                    $joinCond = $joinConditions[$condIndex - 1];
                                    if($joinCond == 'and' || $joinCond == 'or'){
                                        
                                    }
                                    else{
                                        $joinCond = 'and';
                                    }
                                    $where .= $joinCond.' '.$colName.' '.$cond.' '.$singleVal.' ';
                                }
                                else{
                                    $where .= $colName.' '.$cond.' '.$singleVal.' ';
                                }
                                $condIndex++;
                            }
                            
                        }
                        else{
                            $lCond = strtolower(trim($conditions));
                            if($lCond == 'in' || $lCond == 'not in'){
                                $inCond = $lCond.'(';
                                for($x = 0 ; $x < count($cleanVal) ; $x++){
                                    if($x + 1 == count($cleanVal)){
                                        $inCond .= $cleanVal[$x];
                                    }
                                    else{
                                        $inCond .= $cleanVal[$x].',';
                                    }
                                }
                                $where .= $colName.' '.$inCond.')';
                            }
                            else{
                                
                            }
                        }
                        $where = trim($where).')';
                    }
                    else{
                        $where .= $colName.' '.$equalityCond.' '.$cleanVal.' ';
                    }
                }
                if($index + 1 != $colsCount){
                    $where .= $jointOps[$index].' ';
                }
            }
            $index++;
        }
        return ' '.trim($where);
    }
    
    /**
     * Constructs a query that can be used to update a record.
     * @param array $colsAndNewVals An associative array. The key must be the 
     * new value and the value of the index is an object of type 'Column'. Also, the key 
     * can be column name or its index in the table that it belongs to and 
     * the value of the index is the condition value. 
     * @param array $conditionColsAndVals An associative array that contains columns and 
     * values for the 'where' clause. The indices can be the values and the 
     * value at each index can be an object of type 'Column'. Also, the key 
     * can be column name or its index in the table that it belongs to and 
     * the value of the index is the condition value. 
     * The number of elements in this array must match number of elements 
     * in the array $colsAndNewVals.
     * @param array $valsConds An array that can have only the following 
     * values: '=','!=','&gt;','&gt;=','&lt;' and '&lt;='. The number of elements in this array must match number of 
     * elements in the array $conditionColsAndVals. If not provided, '=' is used by 
     * default. Default is empty array.
     * @param array $jointOps An array which contains conditional operators 
     * to join conditions. The operators can be logical or bitwise. Possible 
     * values include: &&, ||, and, or, |, &, xor. It is optional in case there 
     * is only one condition. If not provided, 'and' is used. Default is empty array.
     * @since 1.8.2
     */
    public function updateRecord($colsAndNewVals,$conditionColsAndVals,$valsConds=[],$jointOps=[]) {
        $condColsCount = count($conditionColsAndVals);
        $condsCount = count($valsConds);
        $joinOpsCount = count($jointOps);
        while ($condColsCount > $condsCount){
            $valsConds[] = '=';
            $condsCount = count($valsConds);
        }
        while (($condColsCount - 1) > $joinOpsCount){
            $jointOps[] = 'and';
            $joinOpsCount = count($jointOps);
        }
        $defaultCols = $this->getTable()->getDefaultColsKeys();
        $lastUpdatedKey = $defaultCols['last-updated'];
        if($lastUpdatedKey !== null){
            $lastUpdatedColObj = $this->getCol($lastUpdatedKey);
        }
        else{
            $lastUpdatedColObj = null;
        }
        $colsStr = '';
        $comma = '';
        $index = 0;
        $count = count($colsAndNewVals);
        foreach($colsAndNewVals as $colIndex=>$val){
            if($index + 1 == $count){
                $comma = '';
            }
            else{
                $comma = ',';
            }
            if(gettype($colIndex) == 'integer'){
                $column = $this->getTable()->getColByIndex($colIndex);
            }
            else{
                $column = $this->getTable()->getCol($colIndex);
            }
            if($column instanceof MySQLColumn){
                $colsStr .= $column->getName().' = ';
                $type = $column->getType();
                if($val !== 'null'){
                    $cleanedVal = $column->cleanValue($val);
                    if($cleanedVal === null){
                        if($lastUpdatedColObj !== null && $lastUpdatedColObj->getIndex() == $column->getIndex()){
                            $colsStr .= $column->cleanValue($column->getDefault()).$comma;
                            $lastUpdatedColObj = null;
                        }
                        else{
                            $colsStr .= 'null'.$comma;
                        }
                    }
                    else{
                        if($type == 'tinyblob' || $type == 'mediumblob' || $type == 'longblob'){
                            $fixedPath = str_replace('\\', '/', $colIndex);
                            if(file_exists($fixedPath)){
                                $file = fopen($fixedPath, 'r');
                                $data = '';
                                if($file !== false){
                                    $fileContent = fread($file, filesize($fixedPath));
                                    if($fileContent !== false){
                                        $data = '\''. addslashes($fileContent).'\'';
                                        $colsStr .= $data.$comma;
                                        $this->setIsBlobInsertOrUpdate(true);
                                    }
                                    else{
                                        $colsStr .= 'null'.$comma;
                                    }
                                    fclose($file);
                                }
                                else{
                                    $colsStr .= 'null'.$comma;
                                }
                            }
                            else{
                                $colsStr .= 'null'.$comma;
                            }
                        }
                        else{
                            if($lastUpdatedColObj !== null && $lastUpdatedColObj->getIndex() == $column->getIndex()){
                                $colsStr .= $cleanedVal.$comma;
                                $lastUpdatedColObj = null;
                            }
                            else{
                                $colsStr .= $cleanedVal.$comma;
                            }
                        }
                    }
                }
                else{
                    $colsStr .= 'null'.$comma;
                }
            }
            $index++;
        }
        if($lastUpdatedColObj !== null){
            $colsStr .= ','.$lastUpdatedColObj->getName().' = '
                    .$lastUpdatedColObj->cleanValue(date('Y-m-d H:i:s'));
        }
        $colsArr = [];
        $valsArr = [];
        foreach ($conditionColsAndVals as $valueOrIndex=>$colObjOrVal){
            if($colObjOrVal instanceof MySQLColumn){
                $colsArr[] = $colObjOrVal;
                $valsArr[] = $valueOrIndex;
            }
            else{
                if(gettype($valueOrIndex) == 'integer'){
                    $testCol = $this->getTable()->getColByIndex($valueOrIndex);
                }
                else{
                    $testCol = $this->getTable()->getCol($valueOrIndex);
                }
                $colsArr[] = $testCol;
                $valsArr[] = $colObjOrVal;
            }
        }
        $this->setQuery('update '.$this->getTableName().' set '.$colsStr.$this->createWhereConditions($colsArr, $valsArr, $valsConds, $jointOps).';', 'update');
    }
    /**
     * Checks if the query represents a blob insert or update.
     * The aim of this method is to fix an issue with setting the collation 
     * of the connection while executing a query.
     * @return boolean The Function will return true if the query represents an 
     * insert or un update of blob datatype. false if not.
     * @since 1.8.5
     */
    public function isBlobInsertOrUpdate(){
        return $this->isFileInsert;
    }
    /**
     * Sets the property that is used to check if the query represents an insert 
     * or an update of a blob datatype.
     * The attribute is used to fix an issue with setting the collation 
     * of the connection while executing a query.
     * @param boolean $boolean true if the query represents an insert or an update 
     * of a blob datatype. false if not.
     * @since 1.8.5
     */
    public function setIsBlobInsertOrUpdate($boolean) {
        $this->isFileInsert = $boolean === true ? true : false;
    }
    /**
     * Updates a table columns that has a datatype of blob from source files.
     * @param array $arr An associative array of keys and values. The keys will 
     * be acting as the columns names and the values should be a path to a file 
     * on the host machine.
     * @param string $id  the ID of the record that will be updated.
     * @since 1.2
     */
    public function updateBlobFromFile($arr,$id,$idColName){
        $cols = '';
        $count = count($arr);
        $index = 0;
        foreach($arr as $col => $val){
            $fixedPath = str_replace('\\', '/', $val);
            $file = fopen($fixedPath, 'r');
            $data = '\'\'';
            if($file !== false){
                $fileContent = fread($file, filesize($fixedPath));
                if($fileContent !== false){
                    $data = '\''. addslashes($fileContent).'\'';
                    $this->setIsBlobInsertOrUpdate(true);
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
        $this->setQuery('update '.$this->getTableName().' set '.$cols.' where '.$idColName.' = '.$id, 'update');
    }
    /**
     * Constructs a query that can be used to select maximum value of a table column.
     * @param string $col The name of the column as specified while initializing 
     * linked table. This value should return an object of type Column 
     * when passed to the method MySQLQuery::getCol().
     * @param string $rename The new name of the column that contains max value. 
     * The default value is 'max'.
     * @since 1.3
     */
    public function selectMax($col,$rename='max'){
        return $this->select(array(
            'column'=> $col,
            'select-max'=>true,
            'rename-to'=>$rename
        ));
    }
    /**
     * Constructs a query that can be used to select minimum value of a table column.
     * @param string $col The name of the column as specified while initializing 
     * linked table. This value should return an object of type Column 
     * when passed to the method MySQLQuery::getCol().
     * @param string $rename The new name of the column that contains min value. 
     * The default value is 'min'.
     * @since 1.3
     */
    public function selectMin($col,$rename='min'){
        return $this->select(array(
            'column'=>$col,
            'select-min'=>true,
            'rename-to'=>$rename
        ));
    }
    /**
     * Constructs a query that can be used to create the table which is linked 
     * with the query class.
     * @param boolean $inclComments If set to true, the generated MySQL 
     * query will have basic comments explaining the structure.
     * @return boolean Once the query is structured, the method will return 
     * true. If the query is not created, the method will return false.
     * @deprecated since version 1.9.0
     * @since 1.5
     */
    public function createStructure($inclComments=false){
        $t = $this->getTable();
        if($t instanceof MySQLTable){
            $this->_createTable($t,$inclComments);
            return true;
        }
        return false;
    }
    /**
     * Constructs a query that can be used to create the table which is linked 
     * with the query class.
     * @param boolean $withComments If set to true, the generated MySQL 
     * query will have basic comments explaining the structure.
     * @since 1.9.0
     */
    public function createTable($withComments=false) {
        $this->createStructure($withComments);
    }
    /**
     * Returns the name of the column from the table given its key.
     * @param string $colKey The name of the column key.
     * @return string The name of the column in the table. If no column was 
     * found, the method will return the string MySQLTable::NO_SUCH_COL. If there is 
     * no table linked with the query object, the method will return the 
     * string MySQLQuery::NO_STRUCTURE.
     * @since 1.5
     */
    public function getColName($colKey){
        $col = $this->getCol($colKey);
        if($col instanceof MySQLColumn){
            return $col->getName();
        }
        return $col;
    }
    /**
     * Returns a column from the table given its key.
     * @param string $colKey The name of the column key.
     * @return string|MySQLColumn The the column in the table. If no column was 
     * found, the method will return the string 'MySQLTable::NO_SUCH_COL'. If there is 
     * no table linked with the query object, the method will return the 
     * string MySQLQuery::NO_STRUCTURE.
     * @since 1.6
     */
    public function getCol($colKey){
        $structure = $this->getStructure();
        $retVal = self::NO_STRUCTURE;
        if($structure instanceof MySQLTable){
            $col = $structure->getCol($colKey);
            if($col instanceof MySQLColumn){
                return $col;
            }
            $retVal = MySQLTable::NO_SUCH_COL;
        }
        return $retVal;
    }
    /**
     * Returns the index of a column given its key.
     * @param string $colKey The name of the column key.
     * @return int  The index of the column if found starting from 0. 
     * If the column was not found, the method will return -1.
     * @since 1.8.4
     */
    public function getColIndex($colKey){
        $col = $this->getCol($colKey);
        $index = $col instanceof MySQLColumn ? $col->getIndex() : -1;
        return $index;
    }
    /**
     * Returns the table that is used for constructing queries.
     * @return MySQLTable The table that is used for constructing queries.
     * @since 1.5
     * @deprecated since version 1.9.0 Use MySQLQuery::getTable() instead.
     */
    public function getStructure(){
        return $this->table;
    }
    /**
     * Returns the name of the table that is used to construct queries.
     * @return string The name of the table that is used to construct queries. 
     * if no table is linked, the method will return the string MySQLQuery::NO_STRUCTURE.
     * @since 1.5
     * @deprecated since version 1.9.0 Use MySQLQuery::getTableName() instead.
     */
    public function getStructureName(){
        $s = $this->getStructure();
        if($s instanceof MySQLTable){
            return $s->getName();
        }
        return self::NO_STRUCTURE;
    }
    /**
     * Returns the name of the table which is used to constructs the queries.
     * @param boolean $dbPrefix If database prefix is set and this parameter is 
     * set to true, the name of the table will include database prefix.
     * @return string The name of the table which is used to constructs the queries.
     * @since 1.9.0
     */
    public function getTableName($dbPrefix=true) {
        return $this->getTable()->getName($dbPrefix);
    }
    /**
     * Returns the table which is associated with the query.
     * @return MySQLTable The table which is used to constructs queries for.
     * @since 1.9.0
     */
    public function getTable() {
        return $this->table;
    }
    public function __toString() {
        return $this->getQuery();
    }
}
