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
 * A class that represents a file.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.1.1
 */
class File implements JsonI{
    /**
     * An associative array that contains MIME types of common files.
     * @since 1.1.1
     */
    const MIME_TYPES = array(
        //audio and video
        'avi'=>'video/avi',
        'mp3'=>'audio/mpeg',
        '3gp'=>'video/3gpp',
        'mp4'=>'video/mp4',
        'mov'=>'video/quicktime',
        'wmv'=>'video/x-ms-wmv',
        'mov'=>'video/quicktime',
        'flv'=>'video/x-flv',
        'midi'=>'audio/midi',
        //images 
        'jpeg'=>'image/jpeg',
        'jpg'=>'image/jpeg',
        'png'=>'image/png',
        'bmp'=>'image/bmp',
        'ico'=>'image/x-icon',
        //pdf 
        'pdf'=>'application/pdf',
        //MS office documents
        'doc'=>'application/msword',
        'docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'=>'application/vnd.ms-excel',
        'ppt'=>'application/vnd.ms-powerpoint',
        'pptx'=>'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        //other text based files
        'txt'=>'text/plain',
        'php'=>'text/plain',
        'css'=>'text/css',
        'js'=>'text/javascribt',
        'asm'=>'text/x-asm',
        'java'=>'text/x-java-source',
        'log'=>'text/plain',
        'ini'=>'text/plain',
        'htaccess'=>'application/x-extension-htaccess',
        'asp'=>'text/asp',
        //other files
        'zip'=>'application/zip',
        'exe'=>'application/vnd.microsoft.portable-executable',
        'psd'=>'application/octet-stream',
        'ai'=>'application/postscript'
    );
    /**
     * The size of the file in bytes.
     * @var int
     * @since 1.1 
     */
    private $fSize;
    /**
     * The full path to the file.
     * @var string 
     */
    private $path;
    /**
     * The name of the attachment.
     * @var string 
     * @since 1.0
     */
    private $fileName;
    /**
     * MIME type of the attachment (such as 'image/png')
     * @var string 
     * @since 1.0
     */
    private $mimeType;
    /**
     * A unique ID for the file.
     * @var string
     * @since 1.0 
     */
    private $id;
    /**
     * Raw data of the file in binary.
     * @var type 
     * @since 1.0
     */
    private $rawData;
    /**
     * Sets the value of the property <b>$path</b>.
     * @param string $path
     * @since 1.0
     */
    public function setPath($path){
        $this->path = $path;
    }
    /**
     * Returns the full path to the file.
     * @return string Full path to the file (e.g. 'root/images/hello.png'). 
     * The returned value will depend on the name of the file and its 
     * path. If one of the two is not set, the function will return 
     * empty string.
     * @since 1.1.1
     */
    public function getAbsolutePath() {
        $path = $this->getPath();
        $name = $this->getName();
        if(strlen($path) != 0 && strlen($name)){
            return $path.'\\'.$name;
        }
        return '';
    }
    /**
     * Returns MIME type of a file type.
     * @param string $ext File extension without the suffix (such as 'jpg').
     * @return string|NULL If the extension MIME type is found, it will be 
     * returned. If not, the function will return NULL.
     * @since 1.1.1
     */
    public static function getMIMEType($ext){
        $lowerCase = strtolower($ext);
        $retVal = NULL;
        $x = self::MIME_TYPES[$lowerCase];
        if($x !== NULL){
            $retVal = $x;
        }
        return $retVal;
    }
    /**
     * Gets the value of the property <b>$path</b>.
     * @return string
     * @since 1.0
     */
    public function getPath(){
        return $this->path;
    }
    /**
     * Reads the file in binary.
     * @return boolean If the file was opened and its content was fetched, 
     * the function will return TRUE. Also, the function will try to set MIME 
     * type of the file. If MIME type was not detected, it will set to 
     * 'application/octet-stream'. If the function did not read the 
     * content of the file, it will throw an exception.
     * @throws Exception
     * @since 1.1.1
     */
    public function read() {
        $path = $this->getAbsolutePath();
        if($path != ''){
            if(file_exists($path)){
                $this->_setSize(filesize($path));
                set_error_handler(function(){});
                $h = fopen($path, 'rb');
                if(is_resource($h)){
                    $this->rawData = fread($h, $this->getSize());
                    fclose($h);
                    $ext = pathinfo($this->getName(), PATHINFO_EXTENSION);
                    $mime = self::getMIMEType($ext);
                    $mimeSet = $mime === NULL ? 'application/octet-stream' : $mime;
                    $this->setMIMEType($mimeSet);
                    restore_error_handler();
                    return TRUE;
                }
                restore_error_handler();
                throw new Exception('Unable to open the file \''.$path.'\'.');
            }
            throw new Exception('File not found: \''.$path.'\'.');
        }
        throw new Exception('File absolute path is invalid.');
    }
    /**
     * Write raw binary data into a file.
     * @param string $path [Optional] An optional file path. If not provided, 
     * the path that is returned by File::getPath() will be used.
     * @return boolean If the file is stored the function will return TRUE. 
     * Other than that, the function will throw an exception in the following cases: 
     * <ul>
     * <li>If the function is unable to open the file for writing.</li>
     * <li>If given file path is invalid.</li>
     * <li>If file absolute path is invalid.</li>
     * <li>If file name is invalid.</li>
     * </ul>
     * @throws Exception
     */
    public function write($path=null) {
        if($path === NULL){
            $path = $this->getAbsolutePath();
            if($path != ''){
                return $this->_writeHelper($path);
            }
            throw new Exception('File absolute path is invalid.');
        }
        else{
            $fName = $this->getName();
            if(strlen($fName) > 0){
                if(strlen($path) > 0){
                    return $this->_writeHelper($path.'\\'.$fName);
                }
                throw new Exception('Path cannot be empty string.');
            }
            throw new Exception('File name cannot be empty string.');
        }
    }
    private function _writeHelper($path){
        if($this->getRawData() === NULL){
            $this->read();
        }
        $h = fopen($path, 'wb');
        if(is_resource($h)){
            fwrite($h, $this->getRawData());
            fclose($h);
            restore_error_handler();
            return TRUE;
        }
        restore_error_handler();
        throw new Exception('Unable to open the file at \''.$path.'\'.');
    }
    /**
     * Display the file. If the raw data of the file is NULL, the function will 
     * try to read the file that was specified by the name and its path. If 
     * the function is unable to read the file, an exception is thrown. Also, 
     * an exception will be thrown in case of unknown file MIME type.
     * @param boolean $asAttachment [Optional] If this parameter is set to 
     * TRUE, the header 'content-disposition' will have the attribute 'attachment' 
     * set instead of 'inline'. This will trigger 'save as' dialog to appear.
     * @throws Exception
     */
    public function view($asAttachment=false){
        $raw = $this->getRawData();
        if($raw !== NULL){
            $this->_viewFileHelper($asAttachment);
        }
        else{
            $this->read();
            $this->_viewFileHelper($asAttachment);
        }
    }
    private function _viewFileHelper($asAttachment){
        $contentType = $this->getFileMIMEType();
        if($contentType != NULL){
            header('Content-Type:'.$contentType);
            if($asAttachment === TRUE){
                header('Content-Disposition: attachment; filename="'.$this->getName().'"');
            }
            else{
                header('Content-Disposition: inline; filename="'.$this->getName().'"');
            }
            echo $this->getRawData();
        }
        else{
            throw new Exception('MIME type of raw data is not set.');
        }
    }
    private function _setSize($size){
        if($size >= 0){
            $this->fSize = $size;
        }
    }
    /**
     * Returns the size of the file in bytes.
     * @return int Size of the file in bytes.
     */
    public function getSize() {
        return $this->fSize;
    }
    /**
     * Sets the name of the file (such as 'my-image.png')
     * @param string $name The name of the file.
     * @since 1.0
     */
    public function setName($name){
        $this->fileName = $name;
    }
    /**
     * Returns the name of the file.
     * @return string The name of the file.
     * @since 1.0
     */
    public function getName(){
        return $this->fileName;
    }
    /**
     * Sets the MIME type of the file.
     * @param string $type MIME type (such as 'application/pdf')
     * @since 1.0
     */
    public function setMIMEType($type){
        if(strlen($type) != 0){
            $this->mimeType = $type;
        }
    }
    /**
     * Returns MIME type of the file.
     * @return string MIME type of the file. If MIME type of the file is not set 
     * or not detected, the function will return 'application/octet-stream'.
     * @since 1.0
     */
    public function getFileMIMEType(){
        return $this->mimeType;
    }
    /**
     * Sets the ID of the file.
     * @param string $id The unique ID of the file.
     * @since 1.0
     */
    public function setID($id){
        $this->id = $id;
    }
    /**
     * Returns the ID of the file.
     * @return string The ID of the file. If the ID is not set, the function 
     * will return -1.
     * @since 1.0
     */
    public function getID(){
        return $this->id;
    }
    /**
     * Sets the binary representation of the file.
     * @param string $raw Binary raw data of the file.
     * @since 1.0
     */
    public function setRawData($raw){
        if(strlen($raw) > 0){
            $this->rawData = $raw;
            $this->_setSize(strlen($raw));
        }
    }
    /**
     * Returns the raw data of the file.
     * @return type Raw data of the file.
     * @since 1.0
     */
    public function getRawData(){
        return $this->rawData;
    }
    /**
     * Returns a JSON string that represents the file.
     * @return string A JSON string on the following format:<br/>
     * <b>{<br/>&nbsp;&nbsp;"id":"",<br/>&nbsp;&nbsp;"mime":"",<br/>&nbsp;&nbsp;"name":""<br/>}</b>
     * @since 1.0
     */
    public function toJSON(){
        $jsonX = new JsonX();
        $jsonX->add('id', $this->getID());
        $jsonX->add('mime', $this->getFileMIMEType());
        $jsonX->add('name', $this->getName());
        $jsonX->add('path', $this->getPath());
        $jsonX->add('size-in-bytes', $this->getSize());
        $jsonX->add('size-in-kbytes', $this->getSize()/1024);
        $jsonX->add('size-in-mbytes', ($this->getSize()/1024)/1024);
        return $jsonX;
    }
    /**
     * Returns JSON string that represents basic file info.
     * @return string
     */
    public function __toString() {
        return $this->toJSON().'';
    }
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        $this->mimeType = 'application/octet-stream';
        $this->path = '';
        $this->id = -1;
        $this->fileName = '';
        $this->fSize = 0;
    }
}

