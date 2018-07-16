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
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 403 Forbidden");
    die(''
        . '<!DOCTYPE html>'
        . '<html>'
        . '<head>'
        . '<title>Forbidden</title>'
        . '</head>'
        . '<body>'
        . '<h1>403 - Forbidden</h1>'
        . '<hr>'
        . '<p>'
        . 'Direct access not allowed.'
        . '</p>'
        . '</body>'
        . '</html>');
}
/**
 * A basic file related operations controller. Extend it to 
 * implement more complex file related operations.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class FileFunctions extends Functions{
    /**
     * A constant that is used to indicates no upload types where added.
     * @since 1.0
     */
    const NO_TYPES = 'no_types_added';
    /**
     * A constant that is used to indicates upload directory does not exists.
     * @since 1.0
     */
    const NO_SUCH_DIR = 'no_such_dir';
    
    const NOT_EXIST = 'not_exist';
    /**
     * A constant that is used to indicates uploaded file type is not allowed.
     * @since 1.0
     */
    const NOT_ALLOWED = 'not_allowed_type';
    /**
     * A constant that is used to indicates that a file does not exists.
     * @since 1.0
     */
    const NO_SUCH_FILE = 'no_such_file';
    /**
     * An associative array of allowed upload file types.
     * @var array 
     * @since 1.0
     */
    private $uploadTypes;
    /**
     * An object of type <b>FileQuery</b>.
     * @var fileQuery 
     * @since 1.0
     */
    private $fileQuery;
    /**
     * @since 1.0
     */
    public function __construct() {
        parent::__construct();
        $this->fileQuery = new FileQuery('files', 'longblob');
        $this->uploadTypes = array();
    }
    /**
     * Adds a file type from the allowed upload types.
     * @param string $type File extention (such as 'jpg' or 'mp4'). An array 
     * of supported types can be found at the constant array <b>Uploader::ALLOWED_FILE_TYPES</b>.
     * @return boolean The function will return <b>TRUE</b> once the type 
     * is added. <b>FALSE</b> if it is not added. The type won't be added 
     * if its not supported.
     * @since 1.0
     */
    public function addFileType($type) {
        foreach (Uploader::ALLOWED_FILE_TYPES as $k=>$v){
            if($k == $type){
                $this->uploadTypes[$type] = $v;
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * Removes a file type from the allowed upload types.
     * @param string $type File extention (such as 'jpg' or 'mp4').
     * @return boolean The function will return <b>TRUE</b> once the type 
     * is removed. <b>FALSE</b> if it is not exists on the added types.
     * @since 1.0
     */
    public function removeFileType($type) {
        if(isset($this->uploadTypes[$type])){
            unset($this->uploadTypes[$type]);
            return TRUE;
        }
        return FALSE;
    }
    /**
     * A single instance of the class.
     * @var FileFunctions
     * @since 1.0 
     */
    private static $instance;
    /**
     * Returns a single instance of the class.
     * @return FileFunctions
     * @since 1.0
     */
    public static function get(){
        if(self::$instance != NULL){
            return self::$instance;
        }
        self::$instance = new FileFunctions();
        return self::$instance;
    }
    /**
     * Removes a file from the database given its ID.
     * @param int $fId The ID of the file.
     * @return File|string If the file was removed, the function will 
     * return <b>TRUE</b>. If a database error has occurred while removing the file, 
     * the function will return <b>MySQLQuery::QUERY_ERR</b>. If the file does not exists, 
     * the function will return <b>FileFunctions::NO_SUCH_FILE</b>.
     * @since 1.0
     */
    public function removeFile($fId) {
        $this->useDatabase();
        $file = $this->getFile($fId);
        if($file instanceof File){
            $this->fileQuery->removeFile($fId);
            if($this->excQ($this->fileQuery)){
                return $file;
            }
            return MySQLQuery::QUERY_ERR;
        }
        return $file;
    }
    /**
     * Returns the size of a file in bytes given its ID.
     * @param int $id The ID of the file.
     * @return int|string The function will return the size of the file in bytes
     * if it was found. If a database error has occurred while getting the size, 
     * the function will return <b>MySQLQuery::QUERY_ERR</b>. If the file does not exists, 
     * the function will return <b>FileFunctions::NO_SUCH_FILE</b>.
     * @since 1.0
     */
    public function getFileSize($id){
        $this->useDatabase();
        $this->fileQuery->fileSize($id);
        if($this->excQ($this->fileQuery)){
            $row = $this->getRow();
            if($row != NULL){
                return intval($row['file_size']);
            }
            return self::NO_SUCH_FILE;
        }
        return MySQLQuery::QUERY_ERR;
    }
    /**
     * Sets the value of the object that is used to create file 
     * queries.
     * @param FileQuery $fQuery An object that is a sub class of the class 
     * <b>FileQuery</b>.
     * @since 1.0
     */
    public function setFileQuery($fQuery) {
        if($fQuery instanceof FileQuery){
            $this->fileQuery = $fQuery;
        }
    }
    /**
     * Returns a file from the database given its ID.
     * @param int $fileId The ID of the file.
     * @return File|string If the file was found the function will return 
     * an object of type <b>File</b> which contains all needed file data. If a database error has occurred while getting the file, 
     * the function will return <b>MySQLQuery::QUERY_ERR</b>. If the file does not exists, 
     * the function will return <b>FileFunctions::NO_SUCH_FILE</b>.
     * @since 1.0
     */
    public function getFile($fileId){
        $this->useDatabase();
        $this->fileQuery->getFile($fileId);
        if($this->excQ($this->fileQuery)){
            $row = $this->getRow();
            if($row != NULL){
                $file = new File();
                $file->setRawData($row[$this->fileQuery->getColName('file')]);
                $file->setMIMEType($row[$this->fileQuery->getColName('mime-type')]);
                $file->setName($row[$this->fileQuery->getColName('file-name')]);
                $file->setSize($this->getFileSize($fileId));
                return $file;
            }
            return self::NO_SUCH_FILE;
        }
        return MySQLQuery::QUERY_ERR;
    }
    /**
     * 
     * @param array $options An associative array that contains upload options. 
     * the array will have the following keys:
     * <ul>
     * <li><b>database-upload</b>: A boolean value. Set to <b>TRUE</b> if 
     * the file will be stored in the database.</li>
     * <li><b>upload-path</b>: A string. A directory that will contain file 
     * uploads.</li>
     * <li><b>replace-in-path</b>: A boolean value. If set to <b>TRUE</b> 
     * and there was a file which has the same type and name in upload directory, 
     * the file will be replaced.</li>
     * <li><b>upload-and-remove</b>: A boolean value. If set to <b>TRUE</b>, 
     * the file will be uploaded temporary to upload path and then removed. 
     * It is good practice to set it to <b>TRUE</b> if the uploads will 
     * be stored to database.</li>
     * <li><b>create-path</b>: A boolean value. If set to <b>TRUE</b> and <b>upload-path</b> 
     * does not exists, it will be created.</li>
     * @return array|string If files uploaded, the function will return an associative 
     * array which will contain upload process information. The information will 
     * include the following: 
     * <ul>
     * <li><b>upload-options</b>: An associative array that will contain the 
     * settings that where used in upload process.</li>
     * <li><b>names</b>: An array that will contain the indices at which the files are 
     * stored in the array <b>$_FILES</b>.</li>
     * <li><b>files</b>: All uploaded files info such as the name, the size, 
     * the upload status and so on.</li>
     * </ul>
     * If there was no types added, the function will return the constant 
     * <b>FileFunctions::NO_TYPES</b>.
     * @since 1.0
     */
    public function upload($namesArr,$options=array(
        'upload-to-database'=>false,
        'upload-path'=>'uploads',
        'replace-in-path'=>false,
        'upload-and-remove'=>false,
        'create-path'=>false
    )){
        $statusArr = array();
        $statusArr['upload-options'] = $options;
        if(count($this->uploadTypes) == 0){
            return self::NO_TYPES;
        }
        else{
            $statusArr['upload-types'] = $this->uploadTypes;
        }
        $statusArr['names'] = array();
        $statusArr['files'] = array();
        $replaceIfExists = isset($options['replace-in-path']) ? $options['replace-in-path'] === TRUE : FALSE;
        if(isset($options['upload-path']) && strlen($options['upload-path']) != 0){
            $path = ROOT_DIR.'\\'.$options['upload-path'];
        }
        else{
            $path = ROOT_DIR.'\\'.'uploads';
        }
        if(isset($options['create-path']) && $options['create-path'] === TRUE){
            Util::isDirectory($path, TRUE);
        }
        $index = 0;
        foreach ($namesArr as $val){
            array_push($statusArr['names'], $val);
            $uploader = new Uploader();
            $uploader->setAssociatedFileName($val);
            $uploader->setUploadDir($path);
            foreach ($this->uploadTypes as $type => $val2){
                $uploader->addExt($type);
            }
            $files = $uploader->upload($replaceIfExists);
            foreach ($files as $file){
                $statusArr['files']['file-'.$index] = $file;
                $index++;
            }
        }
        if(isset($options['database-upload']) && $options['database-upload'] === TRUE){
            $this->useDatabase();
            foreach ($statusArr['files'] as $k => $file){
                if($file['uploaded'] == 'true'){
                    $fileObj = new File();
                    $fileObj->setName($file['name']);
                    $fileObj->setMIMEType($file['mime']);
                    $path = $file['upload-path'].'\\'.$file['name'];
                    $fileObj->setPath($path);
                    $this->fileQuery->addFileInfo($fileObj);
                    if($this->excQ($this->fileQuery)){
                        $fileId = $this->getLastAddedFileId();
                        $statusArr['files'][$k]['id'] = $fileId;
                        $fileObj->setID($fileId);
                        if($fileId != MySQLQuery::QUERY_ERR){
                            $this->fileQuery->addOrUpdateFile($fileObj);
                            if($this->excQ($this->fileQuery)){
                                $statusArr['files'][$k]['database-uploaded'] = 'true';
                            }
                            else{
                                $statusArr['files'][$k]['database-uploaded'] = MySQLQuery::QUERY_ERR;
                            }
                        }
                        else{
                            $statusArr['files'][$k]['database-uploaded'] = 'N/A';
                        }
                    }
                    else{
                        $statusArr['files'][$k]['database-uploaded'] = MySQLQuery::QUERY_ERR;
                    }
                }
            }
        }
        if(isset($options['upload-and-remove']) && $options['upload-and-remove']){
            foreach ($statusArr['files'] as $k => $file){
                if($file['uploaded'] == 'true'){
                    $dir = $file['upload-path'].'\\'.$file['name'];
                    $result = unlink($dir);
                    if($result === TRUE){
                        $statusArr['files'][$k]['removed-from-path'] = 'true';
                    }
                    else{
                        $statusArr['files'][$k]['removed-from-path'] = 'false';
                    }
                }
            }
        }
        return $statusArr;
    }
    /**
     * Returns the ID of the last file that was added to the 
     * database.
     * @return string|int The ID of the last file that was added to the 
     * database. If a database error has occurred while getting the ID, 
     * the function will return <b>MySQLQuery::QUERY_ERR</b>.
     * @since 1.0
     */
    public function getLastAddedFileId() {
        $this->fileQuery->getLastFileID();
        if($this->excQ($this->fileQuery)){
            return intval($this->getRow()['f_id']);
        }
        return MySQLQuery::QUERY_ERR;
    }
}
