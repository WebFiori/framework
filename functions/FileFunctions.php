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
 * Description of FileFunctions
 *
 * @author Ibrahim
 * @version 1.0
 */
class FileFunctions extends Functions{
    const NO_TYPES = 'no_types_added';
    const NO_SUCH_DIR = 'no_such_dir';
    const NOT_EXIST = 'not_exist';
    const NOT_ALLOWED = 'not_allowed_type';
    const NO_SUCH_FILE = 'no_such_file';
    /**
     *
     * @var type 
     * @since 1.0
     */
    private $uploadTypes;
    /**
     *
     * @var type 
     * @since 1.0
     */
    private $fileQuery;
    private $files;
    /**
     * @since 1.0
     */
    public function __construct() {
        parent::__construct();
        $this->fileQuery = new FileQuery('files', 'longblob');
        $this->uploadTypes = array();
    }
    /**
     * 
     * @param type $type
     * @return boolean
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
     * 
     * @param type $type
     * @return boolean
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
     *
     * @var FileFunctions
     * @since 1.0 
     */
    private static $instance;
    /**
     * 
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
    public function getFileInfo($fileId) {
        
    }
    /**
     * 
     * @param type $fileId
     * @return File
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
                return $file;
            }
            return self::NO_SUCH_FILE;
        }
        return MySQLQuery::QUERY_ERR;
    }
    /**
     * 
     * @param type $options
     * @return type
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
    public function getLastAddedFileId() {
        $this->fileQuery->getLastFileID();
        if($this->excQ($this->fileQuery)){
            return intval($this->getRow()['f_id']);
        }
        return MySQLQuery::QUERY_ERR;
    }
}
