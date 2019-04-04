<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
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
namespace webfiori\entity;
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
use jsonx\JsonI;
use jsonx\JsonX;
use webfiori\entity\File;
use webfiori\entity\Logger;
use webfiori\entity\Util;
/**
 * A helper class that is used to upload files to the server file system.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.2.1
 */
class Uploader implements JsonI{
    /**
     * A constant that is used to indicates upload directory does not exists.
     * It usually returned by some methods as error code.
     * @since 1.0
     */
    const NO_SUCH_DIR = 'no_such_dir';
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
     * An array of supported file types and their MIME types.
     * @var array 
     * @since 1.1
     * @deprecated since 1.2.1
     */
    const ALLOWED_FILE_TYPES = array(
        //audio and video
        'avi'=>array(
            'mime'=>'video/avi',
            'ext'=>'avi'
        ),
        'mp3'=>array(
            'mime'=>'audio/mpeg',
            'ext'=>'mp3'
        ),
        '3gp'=>array(
            'mime'=>'video/3gpp',
            'ext'=>'3gp'
        ),
        'mp4'=>array(
            'mime'=>'video/mp4',
            'ext'=>'mp4'
        ),
        'mov'=>array(
            'mime'=>'video/quicktime',
            'ext'=>'mov'
        ),
        'wmv'=>array(
            'mime'=>'video/x-ms-wmv',
            'ext'=>'wmv'
        ),
        'mov'=>array(
            'mime'=>'video/quicktime',
            'ext'=>'mov'
        ),
        'flv'=>array(
            'mime'=>'video/x-flv',
            'ext'=>'flv'
        ),
        'midi'=>array(
            'mime'=>'audio/midi',
            'ext'=>'midi'
        ),
        //images 
        'jpeg'=>array(
            'mime'=>'image/jpeg',
            'ext'=>'jpeg'
        ),
        'jpg'=>array(
            'mime'=>'image/jpeg',
            'ext'=>'jpg'
        ),
        'png'=>array(
            'mime'=>'image/png',
            'ext'=>'png'
        ),
        'bmp'=>array(
            'mime'=>'image/bmp',
            'ext'=>'bmp'
        ),
        'ico'=>array(
            'mime'=>'image/x-icon',
            'ext'=>'ico'
        ),
        //pdf 
        'pdf'=>array(
            'mime'=>'application/pdf',
            'ext'=>'pdf'
        ),
        //MS office documents
        'doc'=>array(
            'mime'=>'application/msword',
            'ext'=>'doc'
        ),
        'docx'=>array(
            'mime'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'ext'=>'docx'
        ),
        'xls'=>array(
            'mime'=>'application/vnd.ms-excel',
            'ext'=>'xls'
        ),
        'ppt'=>array(
            'mime'=>'application/vnd.ms-powerpoint',
            'ext'=>'ppt'
        ),
        'pptx'=>array(
            'mime'=>'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'ext'=>'pptx'
        ),
        'xlsx'=>array(
            'mime'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ext'=>'xlsx'
        ),
        //other text based files
        'txt'=>array(
            'mime'=>'text/plain',
            'ext'=>'txt'
        ),
        'php'=>array(
            'mime'=>'text/plain',
            'ext'=>'php'
        ),
        'css'=>array(
            'mime'=>'text/css',
            'ext'=>'css'
        ),
        'js'=>array(
            'mime'=>'text/javascribt',
            'ext'=>'js'
        ),
        'asm'=>array(
            'mime'=>'text/x-asm',
            'ext'=>'asm'
        ),
        'java'=>array(
            'mime'=>'text/x-java-source',
            'ext'=>'java'
        ),
        'log'=>array(
            'mime'=>'text/plain',
            'ext'=>'log'
        ),
        'asp'=>array(
            'mime'=>'text/asp',
            'ext'=>'asp'
        ),
        //other files
        'zip'=>array(
            'mime'=>'application/zip',
            'ext'=>'zip'
        ),
        'exe'=>array(
            'mime'=>'application/vnd.microsoft.portable-executable',
            'ext'=>'exe'
        ),
        'psd'=>array(
            'mime'=>'application/octet-stream',
            'ext'=>'psd'
        ),
        'ai'=>array(
            'mime'=>'application/postscript',
            'ext'=>'ai'
        )
    );
    /**
     * An array which contains uploaded files.
     * @var array
     * @since 1.0 
     */
    private $files;
    /**
     * A constant to indicate that a file type is not allowed to be uploaded.
     * @since 1.1
     */
    const UPLOAD_ERR_EXT_NOT_ALLOWED = -1;
    /**
     * A constant to indicate that a file already exist in upload directory.
     * @since 1.1
     */
    const UPLOAD_ERR_FILE_ALREADY_EXIST = -2;
    /**
     * The name of the index at which the file is stored in the array <b>$_FILES</b>.
     * @var string
     * @since 1.0
     */
    private $asscociatedName;
    /**
     * Upload status message.
     * @var string
     * @since 1.0 
     */
    private $uploadStatusMessage;
    /**
     * Creates new instance of the class.
     * @since 1.0
     */
    public function __construct() {
        Logger::logFuncCall(__METHOD__);
        $this->uploadStatusMessage = 'NO ACTION';
        $this->files = array();
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * The directory at which the file (or files) will be uploaded to.
     * @var string A directory. 
     * @since 1.0
     */
    private $uploadDir;
    /**
     * An array that contains all the allowed file types.
     * @var array An array of strings. 
     * @since 1.0
     */
    private $extentions = array();
    /**
     * Sets the directory at which the file will be uploaded to.
     * This method does not check whether the directory is exist or not. It 
     * just validate that the structure of the path is valid by replacing 
     * forward slashes with backward slashes. The directory will never update 
     * if the given string is empty.
     * @param string $dir Upload Directory (such as '/files/uploads' or 
     * 'C:/Server/uploads'). 
     * @return boolean If upload directory was updated, the method will 
     * return TRUE. If not updated, the method will return FALSE.
     * @since 1.0
     */
    public function setUploadDir($dir){
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        $len = strlen($dir);
        Logger::log('Checking length...');
        if($len > 0){
            Logger::log('Trimming forward and backward slashes...');
            while($dir[$len - 1] == '/' || $dir[$len - 1] == '\\'){
                $tmpDir = trim($dir,'/');
                $dir = trim($tmpDir,'\\');
                $len = strlen($dir);
            }
            while($dir[0] == '/' || $dir[0] == '\\'){
                $tmpDir = trim($dir,'/');
                $dir = trim($tmpDir,'\\');
            }
            Logger::log('Finished.');
            Logger::log('Validating trimming result...');
            if(strlen($dir) > 0){
                $dir = str_replace('/', '\\', $dir);
                $this->uploadDir = !Util::isDirectory($dir) ? '\\'.$dir : $dir;
                Logger::log('New upload directory = \''.$this->uploadDir.'\'', 'debug');
                $retVal = TRUE;
            }
            else{
                Logger::log('Empty string after trimming.','warning');
            }
        }
        else{
            Logger::log('Empty string is given.', 'warning');
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Returns an array which contains all information about the uploaded files.
     * The returned array will be indexed. At each index, a sub associative array 
     * that holds uploaded file information. Each array will have the following 
     * indices:
     * <ul>
     * <li><b>name</b>: The name of the file.</li>
     * <li><b>size</b>: Size of the file in bytes.</li>
     * <li><b>upload-path</b>: The name of the file.</li>
     * <li><b>name</b>: The name of the file.</li>
     * <li><b>name</b>: The name of the file.</li>
     * <li><b>name</b>: The name of the file.</li>
     * </ul>
     * @return array
     * @since 1.0
     * 
     */
    public function getFiles() {
        return $this->files;
    }

    /**
     * Adds new extention to the array of allowed files types.
     * @param string $ext File extention. The extention should be 
     * included without suffix.(e.g. jpg, png, pdf)
     * @since 1.0
     */
    public function addExt($ext){
        Logger::logFuncCall(__METHOD__);
        Logger::log('$ext = \''.$ext.'\'','debug');
        Logger::log('Removing the suffix if any.');
        $ext = str_replace('.', '', $ext);
        $len = strlen($ext);
        $retVal = TRUE;
        Logger::log('Checking length...');
        if($len != 0){
            Logger::log('Validating  characters...');
            for($x = 0 ; $x < $len ; $x++){
                $ch = $ext[$x];
                if($ch == '_' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || ($ch >= '0' && $ch <= '9')){
                    
                }
                else{
                    Logger::log('Invalid character found: \''.$ch.'\'.', 'warning');
                    $retVal = FALSE;
                    break;
                }
            }
            if($retVal === TRUE){
                $this->extentions[] = $ext;
                Logger::log('Extention added.');
            }
            else{
                Logger::log('Extention not added.','warning');
            }
        }
        else{
            Logger::log('Empty string given.', 'warning');
            $retVal = FALSE;
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Removes an extention from the array of allowed files types.
     * @param string $ext File extention. The extention should be included 
     * without suffix.(e.g. jpg, png, pdf)
     * @since 1.0
     */
    public function removeExt($ext){
        Logger::logFuncCall(__METHOD__);
        $count = count($this->extentions);
        $retVal = FALSE;
        for($x = 0 ; $x < $count ; $x++){
            if($this->extentions[$x] == $ext){
                unset($this->extentions[$x]);
                $retVal = TRUE;
            }
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Returns the directory at which the file will be uploaded to.
     * @return string upload directory.
     * @since 1.0
     * 
     */
    public function getUploadDir(){
        return $this->uploadDir;
    }
    /**
     * Sets The name of the index at which the file is stored in the array $_FILES.
     * @param string $name The name of the index at which the file is stored in the array $_FILES.
     * The value of this property is usually equals to the HTML element that is used in 
     * the upload form.
     * @since 1.0
     */
    public function setAssociatedFileName($name){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Passed value = \''.$name.'\'.', 'debug');
        $this->asscociatedName = $name;
        Logger::logFuncCall(__METHOD__);
    }
    /**
     * Returns the array that contains all allowed file types.
     * @return array
     * @since 1.0
     */
    public function getExts(){
        return $this->extentions;
    }
    /**
     * Returns MIME type of a file extension.
     * @param string $ext File extension without the suffix (such as 'jpg').
     * @return string|NULL If the extension MIME type is found, it will be 
     * returned. If not, the method will return NULL.
     * @since 1.0
     * @deprecated since 1.2.1
     */
    public static function getMIMEType($ext){
        Logger::logFuncCall(__METHOD__);
        Logger::log('$ext = \''.$ext.'\'', 'debug');
        $retVal = NULL;
        $x = self::ALLOWED_FILE_TYPES[strtolower($ext)];
        if($x !== NULL){
            Logger::log('MIME found.');
            $retVal = $x['mime'];
        }
        else{
            Logger::log('No MIME type was found for the given value.', 'warning');
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Checks if uploaded file is allowed or not.
     * @param string $fileName The name of the file (such as 'image.png')
     * @return boolean If file extension is in the array of allowed types, 
     * the method will return TRUE.
     * @since 1.0
     */
    private function isValidExt($fileName){
        Logger::logFuncCall(__METHOD__);
        Logger::log('File name = \''.$fileName.'\'.', 'debug');
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $retVal = in_array($ext, $this->getExts(),TRUE) || in_array(strtolower($ext), $this->getExts(),TRUE);
        Logger::logReturnValue($retVal);
        Logger::logFuncCall(__METHOD__);
        return $retVal;
    }
    /**
     * Checks if PHP upload code is error or not.
     * @param int $code PHP upload code.
     * @return boolean If the given code does not equal to UPLOAD_ERR_OK, the 
     * method will return TRUE.
     * @since 1.0
     */
    private function isError($code){
        switch($code){
            case UPLOAD_ERR_OK:{
                $this->uploadStatusMessage = 'File Uploaded';
                return FALSE;
            }
            case UPLOAD_ERR_INI_SIZE:{
                $this->uploadStatusMessage = 'File Size is Larger Than '. (ini_get('upload_max_filesize')/1000).'KB. Found in php.ini.';
                break;
            }
            case UPLOAD_ERR_FORM_SIZE:{
                $this->uploadStatusMessage = 'File Size is Larger Than '.($this->getLimit()/1000).'KB';
                break;
            }
            case UPLOAD_ERR_PARTIAL:{
                $this->uploadStatusMessage = 'File Uploaded Partially';
                break;
            }
            case UPLOAD_ERR_NO_FILE:{
                $this->uploadStatusMessage = 'No File was Uploaded';
                break;
            }
            case UPLOAD_ERR_NO_TMP_DIR:{
                $this->uploadStatusMessage = 'Temporary Folder is Missing';
                break;
            }
            case UPLOAD_ERR_CANT_WRITE:{
                $this->uploadStatusMessage = 'Faild to Write File to Disk';
                break;
            }
        }
        return TRUE;
    }
    /**
     * Upload the file to the server.
     * @param bolean $replaceIfExist If a file with the given name found 
     * and this attribute is set to true, the file will be replaced.
     * @return array An array which contains uploaded files info. Each index 
     * will contain an associative array which has the following info:
     * <ul>
     * <li><b>file-name</b>: </li>
     * <li><b>size</b>: </li>
     * <li><b>upload-path</b>: </li>
     * <li><b>upload-error</b>: </li>
     * <li><b>is-exist</b>: </li>
     * <li><b>is-replace</b>: </li>
     * <li><b>mime</b>: </li>
     * <li><b>uploaded</b>: </li>
     * </ul>
     */
    public function upload($replaceIfExist = false){
        Logger::logFuncCall(__METHOD__);
        $this->files = array();
        Logger::log('Checking if request method is \'POST\'.');
        $reqMeth = $_SERVER['REQUEST_METHOD'];
        Logger::log('Request method = \''.$reqMeth.'\'.', 'debug');
        if($reqMeth == 'POST'){
            Logger::log('Checking if $_FILES[\''.$this->asscociatedName.'\'] is set...');
            $fileOrFiles = NULL;
            if(isset($_FILES[$this->asscociatedName])){
                $fileOrFiles = $_FILES[$this->asscociatedName];
                Logger::log('It is set.');
            }
            if($fileOrFiles !== null){
                if(gettype($fileOrFiles['name']) == 'array'){
                    Logger::log('Multiple files where found.');
                    //multi-upload
                    $filesCount = count($fileOrFiles['name']);
                    Logger::log('Number of files: \''.$filesCount.'\'.', 'debug');
                    for($x = 0 ; $x < $filesCount ; $x++){
                        $fileInfoArr = array();
                        $fileInfoArr['name'] = $fileOrFiles['name'][$x];
                        $fileInfoArr['size'] = $fileOrFiles['size'][$x];
                        $fileInfoArr['upload-path'] = $this->getUploadDir();
                        $fileInfoArr['upload-error'] = 0;
                        $fileInfoArr['url'] = 'N/A';
                        if(!$this->isError($fileOrFiles['error'][$x])){
                            if($this->isValidExt($fileInfoArr['name'])){
                                if(Util::isDirectory($this->getUploadDir()) == TRUE){
                                    $targetDir = $this->getUploadDir().'\\'.$fileInfoArr['name'];
                                    $targetDir = str_replace('\\', '/', $targetDir);
                                    if(!file_exists($targetDir)){
                                        $fileInfoArr['is-exist'] = FALSE;
                                        $fileInfoArr['is-replace'] = FALSE;
                                        if(move_uploaded_file($fileOrFiles["tmp_name"][$x], $targetDir)){
                                            if(function_exists('mime_content_type')){
                                                $fPath = str_replace('\\','/',$fileInfoArr['upload-path'].'/'.$fileInfoArr['name']);
                                                $fileInfoArr['mime'] = mime_content_type($fPath);
                                            }
                                            else{
                                                $ext = pathinfo($fileInfoArr['name'], PATHINFO_EXTENSION);
                                                $fileInfoArr['mime'] = File::getMIMEType($ext);
                                            }
                                            $fileInfoArr['uploaded'] = TRUE;
                                        }
                                        else{
                                            $fileInfoArr['uploaded'] = FALSE;
                                        }
                                    }
                                    else{
                                        if(function_exists('mime_content_type')){
                                            $fPath = str_replace('\\','/',$fileInfoArr['upload-path'].'/'.$fileInfoArr['name']);
                                            $fileInfoArr['mime'] = mime_content_type($fPath);
                                        }
                                        else{
                                            $ext = pathinfo($fileInfoArr['name'], PATHINFO_EXTENSION);
                                            $fileInfoArr['mime'] = File::getMIMEType($ext);
                                        }
                                        $fileInfoArr['is-exist'] = TRUE;
                                        if($replaceIfExist){
                                            $fileInfoArr['is-replace'] = TRUE;
                                            
                                            unlink($targetDir);
                                            if(move_uploaded_file($fileOrFiles["tmp_name"][$x], $targetDir)){
                                                $fileInfoArr['uploaded'] = TRUE;
                                            }
                                            else{
                                                $fileInfoArr['uploaded'] = FALSE;
                                            }
                                        }
                                        else{
                                            $fileInfoArr['is-replace'] = FALSE;
                                        }
                                    }
                                }
                                else{
                                    $fileInfoArr['upload-error'] = self::NO_SUCH_DIR;
                                    $fileInfoArr['uploaded'] = FALSE;
                                }
                            }
                            else{
                                $fileInfoArr['uploaded'] = FALSE;
                                $fileInfoArr['upload-error'] = self::NOT_ALLOWED;
                            }
                        }
                        else{
                            $fileInfoArr['uploaded'] = FALSE;
                            $fileInfoArr['upload-error'] = $fileOrFiles['error'][$x];
                        }
                        array_push($this->files, $fileInfoArr);
                    }
                }
                else{
                    Logger::log('Single file upload.');
                    //single file upload
                    $fileInfoArr = array();
                    $fileInfoArr['name'] = $fileOrFiles['name'];
                    $fileInfoArr['size'] = $fileOrFiles['size'];
                    $fileInfoArr['upload-path'] = $this->getUploadDir();
                    $fileInfoArr['upload-error'] = 0;
                    $fileInfoArr['url'] = 'N/A';
                    $fileInfoArr['mime'] = 'N/A';
                    if(!$this->isError($fileOrFiles['error'])){
                        if($this->isValidExt($fileInfoArr['name'])){
                            if(Util::isDirectory($this->getUploadDir()) == TRUE){
                                $targetDir = $this->getUploadDir().'\\'.$fileInfoArr['name'];
                                $targetDir = str_replace('\\', '/', $targetDir);
                                if(!file_exists($targetDir)){
                                    $fileInfoArr['is-exist'] = TRUE;
                                    $fileInfoArr['is-replace'] = TRUE;
                                    if(move_uploaded_file($fileOrFiles["tmp_name"], $targetDir)){
                                        $fileInfoArr['uploaded'] = TRUE;
                                        if(function_exists('mime_content_type')){
                                            $fPath = str_replace('\\','/',$fileInfoArr['upload-path'].'/'.$fileInfoArr['name']);
                                            $fileInfoArr['mime'] = mime_content_type($fPath);
                                        }
                                        else{
                                            $ext = pathinfo($fileInfoArr['name'], PATHINFO_EXTENSION);
                                            $fileInfoArr['mime'] = File::getMIMEType($ext);
                                        }
                                    }
                                    else{
                                        $fileInfoArr['uploaded'] = FALSE;
                                    }
                                }
                                else{
                                    $fileInfoArr['is-exist'] = TRUE;
                                    if(function_exists('mime_content_type')){
                                        $fPath = str_replace('\\','/',$fileInfoArr['upload-path'].'/'.$fileInfoArr['name']);
                                        $fileInfoArr['mime'] = mime_content_type($fPath);
                                    }
                                    else{
                                        $ext = pathinfo($fileInfoArr['name'], PATHINFO_EXTENSION);
                                        $fileInfoArr['mime'] = File::getMIMEType($ext);
                                    }
                                    if($replaceIfExist){
                                        $fileInfoArr['is-replace'] = TRUE;
                                        unlink($targetDir);
                                        if(move_uploaded_file($fileOrFiles["tmp_name"], $targetDir)){
                                            $fileInfoArr['uploaded'] = TRUE;
                                        }
                                        else{
                                            $fileInfoArr['uploaded'] = FALSE;
                                        }
                                    }
                                    else{
                                        $fileInfoArr['is-replace'] = FALSE;
                                    }
                                }
                            }
                            else{
                                $fileInfoArr['upload-error'] = self::NO_SUCH_DIR;
                                $fileInfoArr['uploaded'] = FALSE;
                            }
                        }
                        else{
                            $fileInfoArr['uploaded'] = FALSE;
                            $fileInfoArr['upload-error'] = self::NOT_ALLOWED;
                        }
                    }
                    else{
                        $fileInfoArr['uploaded'] = FALSE;
                        $fileInfoArr['upload-error'] = $fileOrFiles['error'];
                    }
                    array_push($this->files, $fileInfoArr);
                }
            }
            else{
                Logger::log('The variable $_FILES[\''.$this->asscociatedName.'\'] is not set. No files uploaded.', 'warning');
            }
        }
        else{
            Logger::log('Invalid request method. No file(s) were uploaded', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
        return $this->files;
    }
    public function getAssociatedName(){
        return $this->asscociatedName;
    }
    /**
     * Returns a JSON representation of the object.
     * @return JsonX an object of type <b>JsonX</b>
     * @since 1.0
     */
    public function toJSON(){
        $j = new JsonX();
        $j->add('upload-directory', $this->getUploadDir());
        $j->add('allowed-types', $this->getExts());
        return $j;
    }
    public function __toString() {
        return $this->toJSON().'';
    }
}

