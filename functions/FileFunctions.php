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
                array_push($this->uploadTypes, Uploader::ALLOWED_FILE_TYPES[$k]);
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
    public function extractFilesData($arrName){
        $this->files = array();
        $fileOrFiles = $_FILES[$arrName];
        Util::print_r($fileOrFiles);
        if(gettype($fileOrFiles['name']) == 'array'){
            //multi-upload
            echo 'multi-up';
        }
        else{
            //single file upload
            echo 'single-up';
        }
    }
    /**
     * 
     * @param type $options
     * @return type
     */
    public function upload($namesArr,$options=array(
        'store-in-database'=>false,
        'store-path'=>'',
        'replace-if-exists'=>false,
        'store-and-remove'=>false,
        'create-path-if-not-exist'=>false
    )){
        $statusArr = array();
        $statusArr['upload-options'] = $options;
        if(count($this->uploadTypes) == 0){
            return self::NO_TYPES;
        }
        else{
            $statusArr['upload-types'] = $this->uploadTypes;
        }
        foreach ($namesArr as $val){
            if(isset($options['store-path']) && strlen($options['store-path']) != 0){
                $path = ROOT_DIR.'\\'.$options['store-path'];
            }
            else{
                $path = ROOT_DIR.'\\'.'uploads';
            }
            if($path == ROOT_DIR.'\\'.'uploads'){
                $isDir = Util::isDirectory($path, TRUE);
            }
            else{
                if(isset($options['create-path-if-not-exist'])){
                    $isDir = Util::isDirectory($path, $options['create-path-if-not-exist']);
                }
                else{
                    if($path == ROOT_DIR.'\\'.'uploads'){
                        $isDir = Util::isDirectory($path, TRUE);
                    }
                    else{
                        $isDir = Util::isDirectory($path);
                    }
                }
            }
            
            $replaceIfExists = isset($options['replace-if-exists']) ? $options['replace-if-exists'] === TRUE : FALSE;
            $uploader = new Uploader();
            $uploader->setAssociatedFileName($val);
            $uploader->setUploadDir($path);
            foreach ($this->uploadTypes as $type => $val2){
                $uploader->addExt($type);
            }
            $files = $uploader->upload($replaceIfExists);
            $index = 0;
            foreach ($files as $file){
                $statusArr['file-'.$index] = $file;
                $index++;
            }
        }
        return $statusArr;
    }
}
