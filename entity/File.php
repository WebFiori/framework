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
/**
 * A class that represents a file.
 * This class can be used to read and write files in binary. In addition to that, 
 * it can be used to view files in web browsers.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.1.2
 */
class File implements JsonI{
    /**
     * An associative array that contains MIME types of common files.
     * As of version 1.1.2 of the class, the array contains the 
     * following MIME types:
     * <ul>
     * <li><b>Audio and Video Formats:</b>
     * <ul>
     * <li>avi: video/avi</li>
     * <li>3gp: video/3gpp</li>
     * <li>ogv: video/ogg</li>
     * <li>mp4: video/mp4</li>
     * <li>mov: video/quicktime</li>
     * <li>wmv: video/x-ms-wmv</li>
     * <li>flv: video/x-flv</li>
     * <li>mpeg: video/mpeg</li>
     * <li>midi: audio/midi</li>
     * <li>oga: audio/ogg</li>
     * <li>mp3: audio/mpeg</li>
     * <li>mid: audio/midi</li>
     * <li>wav: audio/aac</li>
     * <li>acc: audio/aac</li>
     * </ul></li>
     * <li><b>Image Formats:</b>
     * <ul>
     * <li>jpeg: image/jpeg</li>
     * <li>jpg: image/jpeg</li>
     * <li>png: image/png</li>
     * <li>bmp: image/bmp</li>
     * <li>ico: image/x-icon</li>
     * <li>tiff: image/tiff</li>
     * <li>svg: image/svg+xml</li>
     * <li>psd: image/vnd.adobe.photoshop</li>
     * </ul></li>
     * <li><b>Documents Formats:</b>
     * <ul>
     * <li>pdf: application/pdf</li>
     * <li>rtf: application/rtf</li>
     * <li>doc: application/msword</li>
     * <li>docx: application/vnd.openxmlformats-officedocument.wordprocessingml.document</li>
     * <li>ppt: application/vnd.ms-powerpoint</li>
     * <li>pptx: application/vnd.openxmlformats-officedocument.presentationml.presentation</li>
     * <li>xls: application/vnd.ms-excel</li>
     * <li>xlsx: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet</li>
     * </ul></li>
     * <li><b>Text Based Formats:</b>
     * <ul>
     * <li>txt: text/plain</li>
     * <li>php: text/plain</li>
     * <li>log: text/plain</li>
     * <li>ini: text/plain</li>
     * <li>css: text/css</li>
     * <li>js: application/javascript</li>
     * <li>asm: text/x-asm</li>
     * <li>java: text/x-java-source</li>
     * <li>htaccess: application/x-extension-htaccess</li>
     * <li>asp: text/asp</li>
     * <li>c: text/x-c</li>
     * <li>cpp: text/x-c</li>
     * <li>csv: text/csv</li>
     * <li>htm: text/html</li>
     * <li>html: text/html</li>
     * </ul></li>
     * <li><b>Other Formats:</b>
     * <ul>
     * <li>jar: application/java-archive</li>
     * <li>zip: application/zip</li>
     * <li>rar: application/x-rar-compressed</li>
     * <li>tar: application/x-tar</li>
     * <li>7z: application/x-7z-compressed</li>
     * <li>exe: application/vnd.microsoft.portable-executable</li>
     * <li>bin: application/octet-stream</li>
     * <li>woff: font/woff</li>
     * <li>woff2: font/woff2</li>
     * <li>otf: font/otf</li>
     * <li>ttf: font/ttf</li>
     * <li>ai: application/postscript</li>
     * <li>swf: application/x-shockwave-flash</li>
     * <li>ogx: application/ogg</li>
     * </ul>
     * @since 1.1.1
     */
    const MIME_TYPES = array(
        //audio and video
        'avi'=>'video/avi',
        '3gp'=>'video/3gpp',
        'mp4'=>'video/mp4',
        'mov'=>'video/quicktime',
        'wmv'=>'video/x-ms-wmv',
        'flv'=>'video/x-flv',
        'ogv'=>'video/ogg',
        'mpeg'=>'video/mpeg',
        'midi'=>'audio/midi',
        'mid'=>'audio/midi',
        'acc'=>'audio/aac',
        'mp3'=>'audio/mpeg',
        'wav'=>'audio/wav',
        'oga'=>'audio/ogg',
        //images 
        'jpeg'=>'image/jpeg',
        'jpg'=>'image/jpeg',
        'png'=>'image/png',
        'bmp'=>'image/bmp',
        'ico'=>'image/x-icon',
        'tiff'=>'image/tiff',
        'svg'=>'image/svg+xml',
        'psd'=>'image/vnd.adobe.photoshop',
        //pdf 
        'pdf'=>'application/pdf',
        //rich text format
        'rtf'=>'application/rtf',
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
        'log'=>'text/plain',
        'ini'=>'text/plain',
        'css'=>'text/css',
        'js'=>'application/javascript',
        'asm'=>'text/x-asm',
        'java'=>'text/x-java-source',
        'htaccess'=>'application/x-extension-htaccess',
        'asp'=>'text/asp',
        'c'=>'text/x-c',
        'cpp'=>'text/x-c',
        'csv'=>'text/csv',
        'htm'=>'text/html',
        'html'=>'text/html',
        //other files
        'jar'=>'application/java-archive',
        'zip'=>'application/zip',
        'rar'=>'application/x-rar-compressed',
        'tar'=>'application/x-tar',
        '7z'=>'application/x-7z-compressed',
        'exe'=>'application/vnd.microsoft.portable-executable',
        'bin'=>'application/octet-stream',
        'woff'=>'font/woff',
        'woff2'=>'font/woff2',
        'otf'=>'font/otf',
        'ttf'=>'font/ttf',
        'ai'=>'application/postscript',
        'swf'=>'application/x-shockwave-flash',
        'ogx'=>'application/ogg'
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
     * Sets the path of the file.
     * The path is simply the folder that contains the file. For example, 
     * the path can be something like "C:/Users/Me/Documents". The path can 
     * use forward slashes or backward slashes.
     * @param string $path The folder which will contain the file. It must 
     * be non-empty string in order to set.
     * @return boolean The function will return TRUE if the path is set. Other 
     * than that, the function will return FALSE.
     * @since 1.0
     */
    public function setPath($path){
        $retVal = FALSE;
        $len = strlen($path);
        if($len > 0){
            while($path[$len - 1] == '/' || $path[$len - 1] == '\\'){
                $tmpDir = trim($path,'/');
                $path = trim($tmpDir,'\\');
                $len = strlen($path);
            }
            while($path[0] == '/' || $path[0] == '\\'){
                $tmpDir = trim($path,'/');
                $path = trim($tmpDir,'\\');
            }
            if(strlen($path) > 0){
                $path = str_replace('/', '\\', $path);
                $this->path = !Util::isDirectory($path) ? '\\'.$path : $path;
                $retVal = TRUE;
            }
        }
        return $retVal;
    }
    /**
     * Returns the full path to the file.
     * The full path of the file is a string that contains the path of the 
     * file alongside its name. Assuming that the path is set to "C:/Users/Me/Documents" 
     * and file name is set to "my-doc.docx", This function will return something like 
     * "C:\Users\Me\Documents\my-do.docx".
     * @return string Full path to the file (e.g. 'root\images\hello.png').
     * If the name of the file is not set or the path is not set, the function 
     * will return empty string.
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
     * The function will try to find MIME type based on its extension. If 
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
     * Returns the path of the file.
     * The path is simply the folder that contains the file. For example, 
     * the path can be something like "C:\Users\Me\Documents". Note that the 
     * returned path will be using backward slashes "\".
     * @return string The path to the file (such as "C:\Users\Me\Documents"). If 
     * the path is not set, the function will return empty string.
     * @since 1.0
     */
    public function getPath(){
        return $this->path;
    }
    /**
     * Reads the file in binary mode.
     * First of all, this function checks the existence of the file. If it 
     * is exist, it tries to open the file in binary mode 'rb'. If a resource 
     * is created, it is used to read the content of the file. Also, the function 
     * will try to set MIME type of the file. If MIME type was not detected, 
     * it will set to 'application/octet-stream'. If the function is unable to 
     * read the file, it will throw an exception.
     * @throws Exception The function will throw an exception with the message 
     * "File absolute path is invalid." if absolute path is empty string. Also, 
     * an exception with the message "Unable to open the file 'f_path'." if 
     * the function was unable to create the resource which is used to read 
     * the file. An exception with the message "File not found: 'f_path'." 
     * if no file was found which has the given path and name. "f_path" is the 
     * absolute path of the file.
     * @since 1.1.1
     */
    public function read() {
        $path = $this->getAbsolutePath();
        if($path != ''){
            if(!$this->_readHelper($path)){
                $path = str_replace('\\', '/', $this->getAbsolutePath());
                if(!$this->_readHelper($path)){
                    throw new Exception('File not found: \''.$path.'\'.');
                }
                else{
                    return;
                }
            }
            else{
                return;
            }
        }
        throw new Exception('File absolute path is invalid.');
    }
    private function _readHelper($path){
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
        return FALSE;
    }
    /**
     * Write raw binary data into a file.
     * The function will write the data using the binary write mode 'wb' mode. 
     * This means that if the file does not exist, the function will try to 
     * created it. If it fails, It will throw an exception. Note that if an 
     * optional path is provided, the name of the file must be set first.
     * @param string $path [Optional] An optional file path such as "C:\Users\Me\Documents". 
     * The path should not include the name of the file. If not provided, 
     * the path that is returned by File::getPath() will be used.
     * @throws Exception The function will throw an exception with the message 
     * "File absolute path is invalid." if file absolute path is invalid. Also, 
     * The function will throw an exception with the message "Path cannot be empty string." 
     * if provided file path is empty string. Also, an exception with the message 
     * "Path cannot be empty string." is thrown if the optional path is provided 
     * but file name is not set. Finally, an exception with the message 
     * "Unable to open the file at 'f_path'." is thrown in case the function was 
     * unable to create the resource which is used to write data.
     * @since 1.1.1
     */
    public function write($path=null) {
        if($path === NULL){
            $path = $this->getAbsolutePath();
            if($path != ''){
                $this->_writeHelper($path);
                return;
            }
            throw new Exception('File absolute path is invalid.');
        }
        else{
            $fName = $this->getName();
            if(strlen($fName) > 0){
                if(strlen($path) > 0){
                    $this->_writeHelper($path.'\\'.$fName);
                    return;
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
            return;
        }
        restore_error_handler();
        throw new Exception('Unable to open the file at \''.$path.'\'.');
    }
    /**
     * Display the file. 
     * If the raw data of the file is NULL, the function will 
     * try to read the file that was specified by the name and its path. If 
     * the function is unable to read the file, an exception is thrown.
     * @param boolean $asAttachment [Optional] If this parameter is set to 
     * TRUE, the header 'content-disposition' will have the attribute 'attachment' 
     * set instead of 'inline'. This will trigger 'save as' dialog to appear.
     * @throws Exception An exception with the message "MIME type of raw data is not set." 
     * If MIME type of the file is not set.
     * @since 1.1.1
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
     * Note that if the file is specified by its path and name, the function 
     * File::read() must be called before calling this function to update its 
     * size.
     * @return int Size of the file in bytes.
     */
    public function getSize() {
        return $this->fSize;
    }
    /**
     * Sets the name of the file (such as 'my-image.png')
     * The name is used to construct the absolute path of the file in addition 
     * to its path. The name of the file must include its extension (or suffix).
     * @param string $name The name of the file.
     * @since 1.0
     */
    public function setName($name){
        $this->fileName = $name;
    }
    /**
     * Returns the name of the file.
     * * The name is used to construct the absolute path of the file in addition 
     * to its path.
     * @return string The name of the file. If the name is not set, the function 
     * will return empty string.
     * @since 1.0
     */
    public function getName(){
        return $this->fileName;
    }
    /**
     * Sets the MIME type of the file.
     * It is not recommended to update MIME type of the file manually. Only 
     * use this function for custom file types. MIME type will be set only 
     * if its non-empty string.
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
     * Note that if the file is specified by its path and name, the function 
     * File::read() must be called before calling this function to update its 
     * MIME type.
     * @return string MIME type of the file. If MIME type of the file is not set 
     * or not detected, the function will return 'application/octet-stream'.
     * @since 1.0
     */
    public function getFileMIMEType(){
        return $this->mimeType;
    }
    /**
     * Sets the ID of the file.
     * This function is helpful in case the file is stored in database.
     * @param string $id The unique ID of the file.
     * @since 1.0
     */
    public function setID($id){
        $this->id = $id;
    }
    /**
     * Returns the ID of the file.
     * This function is helpful in case the file is stored in database.
     * @return string The ID of the file. If the ID is not set, the function 
     * will return -1.
     * @since 1.0
     */
    public function getID(){
        return $this->id;
    }
    /**
     * Sets the binary representation of the file.
     * The raw data is simply a string. It can be binary string or any basic 
     * string. Also, it can be a blob which was retrieved from a database.
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
     * The raw data is simply a string. It can be binary string or any basic 
     * string.
     * @return string Raw data of the file.
     * @since 1.0
     */
    public function getRawData(){
        return $this->rawData;
    }
    /**
     * Returns a JSON string that represents the file.
     * @return string A JSON string on the following format:<br/>
     * <b>{<br/>&nbsp;&nbsp;"id":"",<br/>&nbsp;&nbsp;"mime":"",<br/>&nbsp;&nbsp;"name":""<br/>
     * &nbsp;&nbsp;"path":""<br/>&nbsp;&nbsp;"size-in-bytes":""<br/>&nbsp;&nbsp;"size-in-kbytes":""<br/>
     * &nbsp;&nbsp;"size-in-mbytes":""<br/>}</b>
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
     * This function will set the path and name to empty string. Also, it will 
     * set the size to 0 and ID to -1. Finally, it will set MIME type to 
     * "application/octet-stream"
     */
    public function __construct() {
        $this->mimeType = 'application/octet-stream';
        $this->path = '';
        $this->id = -1;
        $this->fileName = '';
        $this->fSize = 0;
    }
    /**
     * Removes a file given its name and path.
     * Before calling this function, the name of the file and its path must 
     * be specified.
     * @return boolean If the file was removed, the function will return 
     * TRUE. Other than that, the function will return FALSE.
     * @since 1.1.2
     */
    public function remove() {
        if(file_exists($this->getAbsolutePath())){
            unlink($this->getAbsolutePath());
            return TRUE;
        }
        return FALSE;
    }
}

