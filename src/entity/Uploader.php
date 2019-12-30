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
use jsonx\JsonI;
use jsonx\JsonX;
use webfiori\entity\File;
use webfiori\entity\Util;
/**
 * A helper class that is used to upload most types of files to the server's file system.
 * The main aim of this class is to allow the developer to upload files 
 * without having to deal directly with the array $_FILES. It can be used to 
 * perform the following tasks:
 * <ul>
 * <li>Upload one or multiple files.</li>
 * <li>Restrict the types of files which can be uploaded.</li>
 * <li>Store the uploaded file(s) to a specific location on the server.</li>
 * <li>View upload status of each file.</li>
 * <li>The ability to get MIME type of most file types using file extension only.<li>
 * </ul>
 * A basic example on how to use this class:
<pre>
    $uploader = new Uploader();
    //allow png only
    $uploader->addExt('png');
    $uploader->setUploadDir('\home\my-site\uploads');
    //the value of the attribute 'name' of file input
    $uploader->setAssociatedFileName('user-files');
    //upload files
    $files = $uploader->upload();
    //now we can check upload status of each file.
    foreach($files as $fileArr){
        //...
    }
</pre>
 * @author Ibrahim
 * @version 1.2.2
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
        $this->uploadStatusMessage = 'NO ACTION';
        $this->files = array();
        $this->setAssociatedFileName('files');
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
    private $extentions = [];
    /**
     * Sets the directory at which the file will be uploaded to.
     * This method does not check whether the directory is exist or not. It 
     * just validate that the structure of the path is valid by replacing 
     * forward slashes with backward slashes. The directory will never update 
     * if the given string is empty.
     * @param string $dir Upload Directory (such as '/files/uploads' or 
     * 'C:/Server/uploads'). 
     * @return boolean If upload directory was updated, the method will 
     * return true. If not updated, the method will return false.
     * @since 1.0
     */
    public function setUploadDir($dir){
        $retVal = false;
        $len = strlen($dir);
        if($len > 0){
            while($dir[$len - 1] == '/' || $dir[$len - 1] == '\\'){
                $tmpDir = trim($dir,'/');
                $dir = trim($tmpDir,'\\');
                $len = strlen($dir);
            }
            while($dir[0] == '/' || $dir[0] == '\\'){
                $tmpDir = trim($dir,'/');
                $dir = trim($tmpDir,'\\');
            }
            if(strlen($dir) > 0){
                $dir = str_replace('/', '\\', $dir);
                $this->uploadDir = !Util::isDirectory($dir) ? '\\'.$dir : $dir;
                $retVal = true;
            }
        }
        return $retVal;
    }
    /**
     * Returns an array which contains all information about the uploaded files.
     * The returned array will be indexed. At each index, a sub associative array 
     * that holds uploaded file information. Each array will have the following 
     * indices:
     * <ul>
     * <li><b>file-name</b>: The name of the uploaded file.</li>
     * <li><b>size</b>: The size of the uploaded file in bytes.</li>
     * <li><b>upload-path</b>: The location at which the file was uploaded to in the server.</li>
     * <li><b>upload-error</b>: Any error which has happend during upload.</li>
     * <li><b>is-exist</b>: A boolean. Set to true if the file does exist in the server.</li>
     * <li><b>is-replace</b>: A boolean. Set to true if the file was already uploaded and replaced.</li>
     * <li><b>mime</b>: MIME type of the file.</li>
     * <li><b>uploaded</b>: A boolean. Set to true if the file was uploaded.</li>
     * </ul>
     * @return array
     * @since 1.0
     * 
     */
    public function getFiles() {
        return $this->files;
    }
    /**
     * Adds multiple extensions at once to the set of allowed files types.
     * @param array $arr An array of strings. Each string represents a file type.
     * @return array The method will return an associative array of booleans. 
     * The key value will be the extension name and the value represents the status 
     * of the addition. If added, it well be set to true.
     * @since 1.2.2
     */
    public function addExts($arr) {
        $retVal = [];
        foreach ($arr as $ext){
            $retVal[] = $this->addExt($ext);
        }
        return $retVal;
    }
    /**
     * Adds new extension to the array of allowed files types.
     * @param string $ext File extension (e.g. jpg, png, pdf).
     * @return boolean If the extension is added, the method will return true.
     * @since 1.0
     */
    public function addExt($ext){
        $ext = str_replace('.', '', $ext);
        $len = strlen($ext);
        $retVal = true;
        if($len != 0){
            for($x = 0 ; $x < $len ; $x++){
                $ch = $ext[$x];
                if($ch == '_' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || ($ch >= '0' && $ch <= '9')){
                    
                }
                else{
                    $retVal = false;
                    break;
                }
            }
            if($retVal === true){
                $this->extentions[] = $ext;
            }
        }
        else{
            $retVal = false;
        }
        return $retVal;
    }
    /**
     * Removes an extension from the array of allowed files types.
     * @param string $ext File extension= (e.g. jpg, png, pdf,...).
     * @return boolean If the extension was removed, the method will return true.
     * @since 1.0
     */
    public function removeExt($ext){
        $count = count($this->extentions);
        $retVal = false;
        for($x = 0 ; $x < $count ; $x++){
            if($this->extentions[$x] == $ext){
                unset($this->extentions[$x]);
                $retVal = true;
            }
        }
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
     * This value is the value of the attribute 'name' in case of HTML file
     * input element.
     * @since 1.0
     */
    public function setAssociatedFileName($name){
        $this->asscociatedName = $name;
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
     * @return string|null If the extension MIME type is found, it will be 
     * returned. If not, the method will return null.
     * @since 1.0
     * @deprecated since 1.2.1
     */
    public static function getMIMEType($ext){
        $retVal = null;
        $x = self::ALLOWED_FILE_TYPES[strtolower($ext)];
        if($x !== null){
            $retVal = $x['mime'];
        }
        return $retVal;
    }
    /**
     * Checks if uploaded file is allowed or not.
     * @param string $fileName The name of the file (such as 'image.png')
     * @return boolean If file extension is in the array of allowed types, 
     * the method will return true.
     * @since 1.0
     */
    private function isValidExt($fileName){
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $retVal = in_array($ext, $this->getExts(),true) || in_array(strtolower($ext), $this->getExts(),true);
        return $retVal;
    }
    /**
     * Checks if PHP upload code is error or not.
     * @param int $code PHP upload code.
     * @return boolean If the given code does not equal to UPLOAD_ERR_OK, the 
     * method will return true.
     * @since 1.0
     */
    private function isError($code){
        switch($code){
            case UPLOAD_ERR_OK:{
                $this->uploadStatusMessage = 'File Uploaded';
                return false;
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
        return true;
    }
    /**
     * Upload the file to the server.
     * @param bolean $replaceIfExist If a file with the given name found 
     * and this parameter is set to true, the file will be replaced.
     * @return array An array which contains uploaded files info. Each index 
     * will contain an associative array which has the following info:
     * <ul>
     * <li><b>file-name</b>: The name of uploaded file.</li>
     * <li><b>size</b>: The size of uploaded file in bytes.</li>
     * <li><b>upload-path</b>: The location at which the file was uploaded to in the server.</li>
     * <li><b>upload-error</b>: A string that represents upload error.</li>
     * <li><b>is-exist</b>: A boolean. Set to true if the file was found in the 
     * server.</li>
     * <li><b>is-replace</b>: A boolean. Set to true if the file was exist and replaced.</li>
     * <li><b>mime</b>: MIME type of the file.</li>
     * <li><b>uploaded</b>: A boolean. Set to true if the file was uploaded.</li>
     * </ul>
     */
    public function upload($replaceIfExist = false){
        $this->files = [];
        $reqMeth = filter_var($_SERVER['REQUEST_METHOD'],FILTER_SANITIZE_STRING);
        if($reqMeth == 'POST'){
            $fileOrFiles = null;
            if(isset($_FILES[$this->asscociatedName])){
                $fileOrFiles = $_FILES[$this->asscociatedName];
            }
            if($fileOrFiles !== null){
                if(gettype($fileOrFiles['name']) == 'array'){
                    //multi-upload
                    $filesCount = count($fileOrFiles['name']);
                    for($x = 0 ; $x < $filesCount ; $x++){
                        $fileInfoArr = [];
                        $fileInfoArr['name'] = $fileOrFiles['name'][$x];
                        $fileInfoArr['size'] = $fileOrFiles['size'][$x];
                        $fileInfoArr['upload-path'] = $this->getUploadDir();
                        $fileInfoArr['upload-error'] = 0;
                        //$fileInfoArr['url'] = 'N/A';
                        if(!$this->isError($fileOrFiles['error'][$x])){
                            if($this->isValidExt($fileInfoArr['name'])){
                                if(Util::isDirectory($this->getUploadDir()) == true){
                                    $targetDir = $this->getUploadDir().'\\'.$fileInfoArr['name'];
                                    $targetDir = str_replace('\\', '/', $targetDir);
                                    if(!file_exists($targetDir)){
                                        $fileInfoArr['is-exist'] = false;
                                        $fileInfoArr['is-replace'] = false;
                                        if(move_uploaded_file($fileOrFiles["tmp_name"][$x], $targetDir)){
                                            if(function_exists('mime_content_type')){
                                                $fPath = str_replace('\\','/',$fileInfoArr['upload-path'].'/'.$fileInfoArr['name']);
                                                $fileInfoArr['mime'] = mime_content_type($fPath);
                                            }
                                            else{
                                                $ext = pathinfo($fileInfoArr['name'], PATHINFO_EXTENSION);
                                                $fileInfoArr['mime'] = File::getMIMEType($ext);
                                            }
                                            $fileInfoArr['uploaded'] = true;
                                        }
                                        else{
                                            $fileInfoArr['uploaded'] = false;
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
                                        $fileInfoArr['is-exist'] = true;
                                        if($replaceIfExist){
                                            $fileInfoArr['is-replace'] = true;
                                            
                                            unlink($targetDir);
                                            if(move_uploaded_file($fileOrFiles["tmp_name"][$x], $targetDir)){
                                                $fileInfoArr['uploaded'] = true;
                                            }
                                            else{
                                                $fileInfoArr['uploaded'] = false;
                                            }
                                        }
                                        else{
                                            $fileInfoArr['is-replace'] = false;
                                        }
                                    }
                                }
                                else{
                                    $fileInfoArr['upload-error'] = self::NO_SUCH_DIR;
                                    $fileInfoArr['uploaded'] = false;
                                }
                            }
                            else{
                                $fileInfoArr['uploaded'] = false;
                                $fileInfoArr['upload-error'] = self::NOT_ALLOWED;
                            }
                        }
                        else{
                            $fileInfoArr['uploaded'] = false;
                            $fileInfoArr['upload-error'] = $fileOrFiles['error'][$x];
                        }
                        array_push($this->files, $fileInfoArr);
                    }
                }
                else{
                    //single file upload
                    $fileInfoArr = [];
                    $fileInfoArr['name'] = $fileOrFiles['name'];
                    $fileInfoArr['size'] = $fileOrFiles['size'];
                    $fileInfoArr['upload-path'] = $this->getUploadDir();
                    $fileInfoArr['upload-error'] = 0;
                    //$fileInfoArr['url'] = 'N/A';
                    $fileInfoArr['mime'] = 'N/A';
                    if(!$this->isError($fileOrFiles['error'])){
                        if($this->isValidExt($fileInfoArr['name'])){
                            if(Util::isDirectory($this->getUploadDir()) == true){
                                $targetDir = $this->getUploadDir().'\\'.$fileInfoArr['name'];
                                $targetDir = str_replace('\\', '/', $targetDir);
                                if(!file_exists($targetDir)){
                                    $fileInfoArr['is-exist'] = true;
                                    $fileInfoArr['is-replace'] = true;
                                    if(move_uploaded_file($fileOrFiles["tmp_name"], $targetDir)){
                                        $fileInfoArr['uploaded'] = true;
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
                                        $fileInfoArr['uploaded'] = false;
                                    }
                                }
                                else{
                                    $fileInfoArr['is-exist'] = true;
                                    if(function_exists('mime_content_type')){
                                        $fPath = str_replace('\\','/',$fileInfoArr['upload-path'].'/'.$fileInfoArr['name']);
                                        $fileInfoArr['mime'] = mime_content_type($fPath);
                                    }
                                    else{
                                        $ext = pathinfo($fileInfoArr['name'], PATHINFO_EXTENSION);
                                        $fileInfoArr['mime'] = File::getMIMEType($ext);
                                    }
                                    if($replaceIfExist){
                                        $fileInfoArr['is-replace'] = true;
                                        unlink($targetDir);
                                        if(move_uploaded_file($fileOrFiles["tmp_name"], $targetDir)){
                                            $fileInfoArr['uploaded'] = true;
                                        }
                                        else{
                                            $fileInfoArr['uploaded'] = false;
                                        }
                                    }
                                    else{
                                        $fileInfoArr['is-replace'] = false;
                                    }
                                }
                            }
                            else{
                                $fileInfoArr['upload-error'] = self::NO_SUCH_DIR;
                                $fileInfoArr['uploaded'] = false;
                            }
                        }
                        else{
                            $fileInfoArr['uploaded'] = false;
                            $fileInfoArr['upload-error'] = self::NOT_ALLOWED;
                        }
                    }
                    else{
                        $fileInfoArr['uploaded'] = false;
                        $fileInfoArr['upload-error'] = $fileOrFiles['error'];
                    }
                    array_push($this->files, $fileInfoArr);
                }
            }
        }
        return $this->files;
    }
    /**
     * Returns the name of the index at which the uploaded files will exist on in the array $_FILES.
     * This value represents the value of the attribute 'name' of the files input 
     * in case of HTML forms.
     * @return string the name of the index at which the uploaded files will exist on in the array $_FILES.
     * Default value is 'files'.
     */
    public function getAssociatedFileName(){
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
        $j->add('associated-file-name', $this->getAssociatedFileName());
        $j->add('allowed-types', $this->getExts());
        $fsArr = [];
        foreach ($this->getFiles() as $fArr){
            $fileJson = new JsonX();
            $fileJson->add('file-name', $fArr['name']);
            $fileJson->add('size', $fArr['size']);
            $fileJson->add('upload-path', $fArr['upload-path']);
            $fileJson->add('upload-error', $fArr['upload-error']);
            $mime = isset($fArr['mime']) ? $fArr['mime'] : null;
            $fileJson->add('mime', $mime);
            $isExist = isset($fArr['is-exist']) ? $fArr['is-exist'] === true : false;
            $fileJson->add('is-exist', $isExist);
            $isReplace = isset($fArr['is-replace']) ? $fArr['is-replace'] === true : false;
            $fileJson->add('is-replace',$isReplace );
            $fsArr[] = $fileJson;
        }
        $j->add('files', $fsArr);
        return $j;
    }
    /**
     * Returns a JSON string that represents the object.
     * The string will be something the the following:
<pre>
{
&nbsp&nbsp"upload-directory":"",
&nbsp&nbsp"allowed-types":[],
&nbsp&nbsp"files":[],
&nbsp&nbsp"associated-file-name":""
}
</pre>
     * @return string A JSON string.
     */
    public function __toString() {
        return $this->toJSON().'';
    }
}

