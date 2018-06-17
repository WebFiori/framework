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
                $statusArr['file-'.$index] = $file;
                $index++;
            }
        }
        return $statusArr;
    }
}
