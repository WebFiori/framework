<?php
/**
 * A base class that is used to construct MySQL queries. It can be used as a base 
 * class for constructing other MySQL queries.<br/>
 * @uses Table Used by the 'create table' Query.
 * @uses ForeignKey Used to alter a table and insert a foreign key in it.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.8.3
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
     * A constant that indicates a table structure is not linked with the instance.
     * @since 1.8.3
     */
    const NO_STRUCTURE = 'no_struture';
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
     * @param boolean $inclComments Description
     * @since 1.4
     */
    private function createTable($table,$inclComments=false){
        if($table instanceof Table){
            $query = '';
            if($inclComments === TRUE){
                $query .= '-- Structure of the table \''.$this->getStructureName().'\''.self::NL;
                $query .= '-- Number of columns: \''.count($this->getStructure()->columns()).'\''.self::NL;
                $query .= '-- Number of forign keys: \''.count($this->getStructure()->forignKeys()).'\''.self::NL;
                $query .= '-- Number of primary key columns: \''.$this->getStructure()->primaryKeyColsCount().'\''.self::NL;
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
            $query .= 'ENGINE = '.$table->getEngine().self::NL;
            $query .= 'DEFAULT CHARSET = '.$table->getCharSet().self::NL;
            $query .= 'collate = utf8mb4_unicode_520_ci;'.self::NL;
            
            $coutPk = $this->getStructure()->primaryKeyColsCount();
            if($coutPk > 1){
                if($inclComments === TRUE){
                    $query .= '-- Primary key of the table '.self::NL;
                }
                $query .= $table->getCreatePrimaryKeyStatement().';'.self::NL;
            }
            //add forign keys
            $count2 = count($table->forignKeys());
            if($inclComments === TRUE && $count2 != 0){
                $query .= '-- Forign keys of the table '.self::NL;
            }
            for($x = 0 ; $x < $count2 ; $x++){
                $query .= $table->forignKeys()[$x]->getAlterStatement().';'.self::NL;
            }
            if($inclComments === TRUE){
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
     * Returns the value of the property $query.
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
     * If zero or a negative value is given, it will not be ignored. 
     * Default is -1.
     * @param int $offset [Optional] The value of the attribute 'offset' of the select statement. 
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
     * Constructs a 'select' query.
     * @param array $selectOptions [Optional] An associative array which contains 
     * options to construct diffrent select quires. The available options are: 
     * <ul>
     * <li><b>colums</b>: An array which can have the keys of columns that 
     * will be select.</li>
     * <li><b>limit</b>: The 'limit' attribute of the query.</li>
     * <li><b>offset</b>: The 'offset' attribute of the query. Ignored if the 
     * option 'limit' is not set.</li>
     * <li><b>condition-cols-and-vals</b>: An associative array of values 
     * and objects of type 'Column'. The value is the condition at which the records 
     * will be selected based on.</li>
     * <li><b>conditions</b>: An array that can contains two possible values: 
     * '=' or '!='. If anything else is given at specific index, '=' will be used.</li>
     * <li><b>join-operators</b>: An array that contains a set of MySQL join operators 
     * like 'and' and 'or'.</li>
     * <li><b>select-max</b>: A boolean value. Set to TRUE if you want to select maximum 
     * value of a column. Ignored in case the option 'columns' is set.</li>
     * <li><b>select-min</b>: A boolean value. Set to TRUE if you want to select minimum 
     * value of a column. Ignored in case the option 'columns' or 'select-max' is set.</li>
     * <li><b>column</b>: The column which contains maximum or minimum value.</li>
     * <li><b>rename-to</b>: Rename the max or min column to the given name.</li>
     * </ul>
     * @since 1.8.3
     */
    public function select($selectOptions=array(
        'colums'=>array(),
        'condition-cols-and-vals'=>array(),
        'conditions'=>array(),
        'join-operators'=>array(),
        'limit'=>-1,
        'offset'=>-1,
        'select-min'=>false,
        'select-max'=>false,
        'column'=>'',
        'rename-to'=>''
        )) {
        $table = $this->getStructure();
        if($table instanceof Table){
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
            if(isset($selectOptions['columns']) && count($selectOptions['columns']) != 0){
                $count = count($selectOptions['columns']);
                $i = 0;
                $colsFound = 0;
                foreach ($selectOptions['columns'] as $column){
                    if($table->hasColumn($column)){
                        $colsFound++;
                        if($i + 1 == $count){
                            $selectQuery .= $this->getColName($column).' from '.$this->getStructureName();
                        }
                        else{
                            $selectQuery .= $this->getColName($column).',';
                        }
                    }
                    else{
                        if($i + 1 == $count && $colsFound != 0){
                            $selectQuery = trim($selectQuery, ',');
                            $selectQuery .= ' from '.$this->getStructureName();
                        }
                        else if($i + 1 == $count && $colsFound == 0){
                            $selectQuery .= '* from '.$this->getStructureName();
                        }
                    }
                    $i++;
                }
            }
            else if(isset ($selectOptions['select-max']) && $selectOptions['select-max'] === TRUE){
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
                    return FALSE;
                }
            }
            else if(isset ($selectOptions['select-min']) && $selectOptions['select-min'] === TRUE){
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
                    return FALSE;
                }
            }
            else{
                $selectQuery .= '* from '.$this->getStructureName();
            }
            if(isset($selectOptions['condition-cols-and-vals']) && isset($selectOptions['conditions']) && isset($selectOptions['join-operators'])){
                $cols = array();
                $vals = array();
                foreach($selectOptions['condition-cols-and-vals'] as $val => $col){
                    $cols[] = $col;
                    $vals[] = $val;
                }
                $where = $this->createWhereConditions($cols, $vals, $selectOptions['conditions'], $selectOptions['join-operators']);
            }
            else{
                $where = '';
            }
            $this->setQuery($selectQuery.$where.$limitPart.';', 'select');
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Constructs a query that can be used to get table data based on a specific 
     * column value.
     * @param string $col The name of the column in the table.
     * @param string $val The value that is used to filter data.
     * @param string $cond [Optional] The condition of select statement. It can be '=' or 
     * '!='. If anything else is given, '=' will be used. Note that if 
     * the parameter $val is equal to 'IS NULL' or 'IS NOT NULL', 
     * This parameter is ignored. Default is '='.
     * @param int $limit [Optional] The value of the attribute 'limit' of the select statement. 
     * If zero or a negative value is given, it will not be included in the generated 
     * MySQL query. Default is -1.
     * @param int $offset [Optional] The value of the attribute 'offset' of the select statement. 
     * If zero or a negative value is given, it will not be included in the generated 
     * MySQL query. Default is -1.
     * @since 1.0
     * @deprecated since version 1.8.3 Use MySQLQuery::select() instead.
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
     * @param array $cols An array that contains an objects of type 'Column'.
     * @param array $vals An array that contains values. 
     * @param array $valsConds An array that can contains two possible values: 
     * '=' or '!='. If anything else is given at specific index, '=' will be used. 
     * Note that if the value at '$vals[$index]' is equal to 'IS NULL' or 'IS NOT NULL', 
     * The value at '$valsConds[$index]' is ignored. 
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
     * @deprecated since version 1.8.3
     */
    public function selectByID($id){
        $this->setQuery(self::SELECT.$this->getStructureName().' where '.self::ID_COL.' = '.$id, 'select');
    }
    /**
     * Constructs a query that can be used to insert a new record.
     * @param array $colsAndVals An associative array. The indices should be 
     * the values of the columns and the value of the index should be an 
     * object of type 'Column'. If the column has a datatype of 'blob', 
     * the value should be the file path.
     * @since 1.8.2
     */
    public function insertRecord($colsAndVals) {
        $cols = '';
        $vals = '';
        $count = count($colsAndVals);
        $index = 0;
        $comma = '';
        foreach($colsAndVals as $val=>$colObj){
            if($index + 1 == $count){
                $comma = '';
            }
            else{
                $comma = ',';
            }
            $cols .= $colObj->getName().$comma;
            $colValLower = strtolower($val);
            if(trim($colValLower) != 'null'){
                $type = $colObj->getType();
                if($type == 'varchar' || $type == 'datetime' || $type == 'timestamp' || $type == 'text' || $type == 'mediumtext'){
                    $vals .= '\''.self::escapeMySQLSpeciarChars($val).'\''.$comma;
                }
                else if($type == 'tinyblob' || $type == 'mediumblob' || $type == 'longblob'){
                    $fixedPath = str_replace('\\', '/', $val);
                    if(file_exists($fixedPath)){
                        $file = fopen($fixedPath, 'r');
                        $data = '';
                        if($file !== FALSE){
                            $fileContent = fread($file, filesize($fixedPath));
                            if($fileContent !== FALSE){
                                $data = '\''. addslashes($fileContent).'\'';
                                $vals .= $data.$comma;
                            }
                            else{
                                $vals .= 'NULL'.$comma;
                            }
                        }
                        else{
                            $vals .= 'NULL'.$comma;
                        }
                    }
                    else{
                        $vals .= 'NULL'.$comma;
                    }
                }
                else{
                     $vals .= $val.$comma;
                }
            }
            else{
                $vals .= 'null'.$comma;
            }
            $index++;
        }
        
        $cols = ' ('.$cols.')';
        $vals = ' ('.$vals.')';
        $this->setQuery(self::INSERT.$this->getStructureName().$cols.' values '.$vals.';', 'insert');
    }
    /**
     * Constructs a query that can be used to insert data into a table.
     * @param array $Arr An associative array of keys and values. The keys will 
     * be acting as the columns names and the values will be acting as the values 
     * that will be inserted.
     * @since 1.0
     * @deprecated since version 1.8.2 Use MySQLQuery::insertRecord() instead.
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
     * Removes a record from the table.
     * @param array $columnsAndVals An associative array. The indices of the array 
     * should be the values of the columns and the value at each index is 
     * an object of type 'Column'.
     * @param array $valsConds An array that can have only two possible values, 
     * '=' and '!='. The number of elements in this array must match number of 
     * elements in the array $cols.
     * @param array $jointOps [Optional] An array which contains conditional operators 
     * to join conditions. The operators can be logical or bitwise. Possible 
     * values include: &&, ||, and, or, |, &, xor. It is optional in case there 
     * is only one condition.
     * @since 1.8.2
     */
    public function deleteRecord($columnsAndVals,$valsConds,$jointOps=array()) {
        $cols = array();
        $vals = array();
        foreach ($columnsAndVals as $val => $colObj){
            $cols[] = $colObj;
            $vals[] = $val;
        }
        $query = 'delete from '.$this->getStructureName();
        $this->setQuery($query.$this->createWhereConditions($cols, $vals, $valsConds, $jointOps).';', 'delete');
    }
    /**
     * A function that is used to create the 'where' part of any query in case 
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
     * @return string A string that represents the 'where' part of the query.
     * @since 1.8.2
     */
    private function createWhereConditions($cols,$vals,$valsConds,$jointOps){
        $colsCount = count($cols);
        $valsCount = count($vals);
        $condsCount = count($valsConds);
        $joinOpsCount = count($jointOps);
        if($colsCount != $valsCount || $colsCount != $condsCount || ($colsCount - 1) != $joinOpsCount){
            return '';
        }
        $index = 0;
        $count = count($cols);
        $where = ' where ';
        foreach ($cols as $col){
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
                            $where .= '\''.self::escapeMySQLSpeciarChars($vals[$index]).'\'' ;
                        }
                        else{
                            $where .= $vals[$index];
                        }
                    }
                    else{
                        $where .= $col->getName().' '.$equalityCond.' ';
                        if($col->getType() == 'varchar' || $col->getType() == 'datetime' || $col->getType() == 'timestamp' || $col->getType() == 'text' || $col->getType() == 'mediumtext'){
                            $where .= '\''.self::escapeMySQLSpeciarChars($vals[$index]).'\' '.$jointOps[$index].' ' ;
                        }
                        else{
                            $where .= $vals[$index].' '.$jointOps[$index].' ';
                        }
                    }
                }
            }
            $index++;
        }
        return $where;
    }
    /**
     * Constructs a query that can be used to update the values of a table row.
     * @param array $arr An associative array of keys and values. The keys will 
     * be acting as the columns names and the values will be acting as the new 
     * values for each field.
     * @param string $id The value of the ID column.
     * @since 1.0
     * @deprecated since version 1.8.2 Use MySQLQuery::updateRecord() instead.
     */
    public function update($arr,$id,$idColName=self::ID_COL){
        $colsStr = '';
        $count = count($arr);
        $index = 0;
        foreach($arr as $colName => $newVal){
            if($index + 1 == $count){
                $colsStr .= $colName.' = '.$newVal;
            }
            else{
                $colsStr .= $colName.' = '.$newVal.', ';
            }
            $index++;
        }
        $this->setQuery('update '.$this->getStructureName().' set '.$colsStr.' where '.$idColName.' = '.$id, 'update');
    }
    /**
     * Constructs a query that can be used to update a record.
     * @param array $colsAndNewVals An associative array. The key must be the 
     * new value and the value of the index is an object of type 'Column'.
     * @param array $colsAndVals An associative array that contains columns and 
     * values for the 'where' clause. The indices should be the values and the 
     * value at each index should be an object of type 'Column'. 
     * The number of elements in this array must match number of elements 
     * in the array $colsAndNewVals.
     * @param array $valsConds An array that can have only two possible values, 
     * '=' and '!='. The number of elements in this array must match number of 
     * elements in the array $colsAndNewVals.
     * @param array $jointOps [Optional] An array which contains conditional operators 
     * to join conditions. The operators can be logical or bitwise. Possible 
     * values include: &&, ||, and, or, |, &, xor. It is optional in case there 
     * is only one condition.
     * @since 1.8.2
     */
    public function updateRecord($colsAndNewVals,$colsAndVals,$valsConds,$jointOps=array()) {
        $colsStr = '';
        $comma = '';
        $index = 0;
        $count = count($colsAndNewVals);
        foreach($colsAndNewVals as $newVal => $colObj){
            if($colObj instanceof Column){
                if($index + 1 == $count){
                    $comma = '';
                }
                else{
                    $comma = ',';
                }
                $newValLower = strtolower($newVal);
                if(trim($newValLower) != 'null'){
                    $type = $colObj->getType();
                    if($type == 'varchar' || $type == 'datetime' || $type == 'timestamp' || $type == 'text' || $type == 'mediumtext'){
                        $colsStr .= ' '.$colObj->getName().' = \''.self::escapeMySQLSpeciarChars($newVal).'\''.$comma ;
                    }
                    else if($type == 'tinyblob' || $type == 'mediumblob' || $type == 'longblob'){
                        $fixedPath = str_replace('\\', '/', $newVal);
                        if(file_exists($fixedPath)){
                            $file = fopen($fixedPath, 'r');
                            $data = '';
                            if($file !== FALSE){
                                $fileContent = fread($file, filesize($fixedPath));
                                if($fileContent !== FALSE){
                                    $data = '\''. addslashes($fileContent).'\'';
                                    $colsStr .= $data.$comma;
                                }
                                else{
                                    $colsStr .= 'NULL'.$comma;
                                }
                            }
                            else{
                                $colsStr .= 'NULL'.$comma;
                            }
                        }
                        else{
                            $colsStr .= 'NULL'.$comma;
                        }
                    }
                    else{
                        $colsStr .= ' '.$colObj->getName().' = '.$newVal.$comma;
                    }
                }
                else{
                    $colsStr .= ' '.$colObj->getName().' = null'.$comma;
                }
            }
            $index++;
        }
        $colsArr = array();
        $valsArr = array();
        foreach ($colsAndVals as $vl=>$cl){
            $colsArr[] = $cl;
            $valsArr[] = $vl;
        }
        $this->setQuery('update '.$this->getStructureName().' set '.$colsStr.$this->createWhereConditions($colsArr, $valsArr, $valsConds, $jointOps).';', 'update');
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
     * @param string $rename [Optional] The new name of the column that contains max value. 
     * The default value is 'max'.
     * @since 1.3
     */
    public function selectMax($col,$rename='max'){
        return $this->select(array(
            'column'=>$col,
            'select-max'=>TRUE,
            'rename-to'=>$rename
        ));
    }
    /**
     * Constructs a query that can be used to select minimum value of a table column.
     * @param string $col The name of the column.
     * @param string $rename [Optional] The new name of the column that contains min value. 
     * The default value is 'min'.
     * @since 1.3
     */
    public function selectMin($col,$rename='min'){
        return $this->select(array(
            'column'=>$col,
            'select-min'=>TRUE,
            'rename-to'=>$rename
        ));
    }
    /**
     * Constructs a query that can be used to get the maximum value of the ID column 
     * @since 1.3
     * @deprecated since version 1.8.3
     */
    public function selectMaxID(){
        $this->selectMax(self::ID_COL, self::ID_COL); 
    }
    /**
     * Constructs a query that can be used to get the minimum value of the ID column 
     * in a table. The value will be contained in a column with the name 'id'.
     * @since 1.3
     * @deprecated since version 1.8.3
     */
    public function selectMinID(){
        $this->selectMin(self::ID_COL, self::ID_COL); 
    }
    /**
     * Constructs a query that can be used to create the table which is linked 
     * with the query class.
     * @param boolean $inclComments If set to TRUE, the generated MySQL 
     * query will have basic comments explaining the structure.
     * @return boolean Once the query is structured, the function will return 
     * TRUE. If the query is not created, the function will return FALSE. 
     * The query will not constructed if the function 'MySQLQuery::getStructure()' 
     * did not return an object of type 'Table'.
     * @since 1.5
     */
    public function createStructure($inclComments=false){
        $t = $this->getStructure();
        if($t instanceof Table){
            $this->createTable($t,$inclComments);
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Returns the name of the column from the table given its key.
     * @param string $colKey The name of the column key.
     * @return string The name of the column in the table. If no column was 
     * found, the function will return the string 'Table::NO_SUCH_COL'. If there is 
     * no table linked with the query object, the function will return the 
     * string 'MySQLQuery::NO_STRUCTURE'.
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
     * found, the function will return the string 'Table::NO_SUCH_COL'. If there is 
     * no table linked with the query object, the function will return the 
     * string 'MySQLQuery::NO_STRUCTURE'.
     * @since 1.6
     */
    public function &getCol($colKey){
        $structure = $this->getStructure();
        if($structure instanceof Table){
            $col = $structure->getCol($colKey);
            if($col instanceof Column){
                return $col;
            }
            return Table::NO_SUCH_COL;
        }
        return self::NO_STRUCTURE;
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
     * if no table is linked, the function will return the string 'MySQLQuery::NO_STRUCTURE'.
     * @since 1.5
     */
    public function getStructureName(){
        $s = $this->getStructure();
        if($s instanceof Table){
            return $s->getName();
        }
        return self::NO_STRUCTURE;
    }
    
    public function __toString() {
        return 'Query: '.$this->getQuery().'<br/>'.'Query Type: '.$this->getType().'<br/>';
    }
}
