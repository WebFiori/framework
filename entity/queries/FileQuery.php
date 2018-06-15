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
 * A base class for file based operations. Files are stored as blobs.
 *
 * @author Ibrahim
 * @version 1.1
 */
class FileQuery extends MySQLQuery{
    /**
     *
     * @var Table The structure of the files table.
     * @since 1.0 
     */
    private $structure;
    /**
     * Creates new instance of <b>FileQuery</b>.
     * @param string $tableName The name of database table
     * @param string $storeType The type of the file in the database. It can be one of 3 
     * values: <b>'tinyblob'</b>, <b>'mediumblob'</b> or <b>'longblob'</b>. 
     * Default is <b>'mediumblob'</b>.
     * @throws Exception The function will throw an exception if the given file 
     * type is not one of the given 3 values. 
     * @since 1.0
     */
    public function __construct($tableName='files',$storeType='mediumblob') {
        parent::__construct();
        $this->structure = new Table($tableName);
        $this->structure->addColumn('file-id', new Column('f_id', 'int',11));
        $this->getCol('file-id')->setIsPrimary(TRUE);
        $this->getCol('file-id')->setIsAutoInc(TRUE);
        
        $xStoreType = strtolower($storeType);
        if($xStoreType == 'tinyblob' || $xStoreType == 'mediumblob' || $xStoreType == 'longblob'){
            $this->structure->addColumn('file', new Column('file', $xStoreType));
        }
        else{
            throw new Exception('Invalid file type: '.$storeType);
        }
        
        $this->structure->addColumn('file-name', new Column('mime', 'varchar', 255));
        $this->getCol('file-name')->setDefault('File');
        $this->structure->addColumn('mime-type', new Column('mime', 'varchar', 250));
        $this->structure->addColumn('date-added', new Column('added_on', 'timestamp'));
        $this->getCol('date-added')->setDefault('');
        $this->structure->addColumn('last-updated', new Column('last_updated', 'datetime'));
        $this->getCol('last-updated')->setDefault('');
        $this->getCol('last-updated')->autoUpdate();
    }
    /**
     * Returns the table that is used for constructing queries.
     * @return Table The table that is used for constructing queries.
     * @since 1.0
     */
    public function getStructure() {
        return $this->structure;
    }
    /**
     * Constructs a query that can be used to get a file given its ID.
     * @param string $fId The ID of the file.
     * @since 1.0
     */
    public function getFile($fId) {
        $this->selectByColVal($this->getColName('file-id'), $fId);
    }
    /**
     * Constructs a query that can be used to add new file to the database.  
     * Note that the generated query will only add file info to the database 
     * in addition to creating file ID. The information include: 
     * <ul>
     * <li>File Name</li>
     * <li>MIME Type</li>
     * <li>Date Added</li>
     * </ul>
     * In order to add the file it self, first get the ID of the file after 
     * adding it by using the function <b>FileQuery::getLastFileID()</b> 
     * and call the function <b>FileQuery::addOrUpdateFile()</b> 
     * to add the file as blob in the database.
     * @param File $file An object of type <b>File</b>.
     */
    public function addFileInfo($file) {
        $arr = array(
            $this->getColName('file-name')=>'\''.$file->getName().'\'',
            $this->getColName('mime-type')=>'\''.$file->getMIMEType().'\''
        );
        $this->insert($arr);
    }
    /**
     * Constructs a query that can be used to get the ID of the last added file.
     * @since 1.0
     */
    public function getLastFileID(){
        $this->selectMaxID();
    }
    /**
     * Adds new file or updates an already added file.
     * @param File $file An object of type <b>File</b>. This function can be only 
     * apply to an already exist file.
     * @since 1.0
     */
    public function addOrUpdateFile($file){
        $array = array(
            $this->getColName('file')=>$file->getPath(),
            $this->getColName('file-name')=>$file->getName(),
            $this->getColName('mime-type')=>$file->getMIMEType()
        );
        $this->updateBlobFromFile($array, $file->getID());
    }
    /**
     * Constructs a query that can be used to removes a file given its ID.
     * @param string $fid The ID of the file.
     * @since 1.0
     */
    public function removeFile($fid){
        $this->delete($fid, $this->getColName('file-id'));
    }
}
