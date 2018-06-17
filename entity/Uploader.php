<?php
/**
 * A helper class that is used to upload files to the server file system.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.2
 */
class Uploader implements JsonI{
    /**
     * An array of supported file types and their MIME types.
     * @var array 
     * @since 1.1
     */
    const ALLOWED_FILE_TYPES = array(
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
        'flv'=>array(
            'mime'=>'video/x-flv',
            'ext'=>'flv'
        ),
        'zip'=>array(
            'mime'=>'application/zip',
            'ext'=>'zip'
        ),
        'php'=>array(
            'mime'=>'text/plain',
            'ext'=>'php'
        ),
        'avi'=>array(
            'mime'=>'video/avi',
            'ext'=>'avi'
        ),
        'mp3'=>array(
            'mime'=>'audio/mpeg',
            'ext'=>'mp3'
        ),
        'xls'=>array(
            'mime'=>'application/vnd.ms-excel',
            'ext'=>'xls'
        ),
        'jpg'=>array(
            'mime'=>'image/jpeg',
            'ext'=>'jpg'
        ),
        'png'=>array(
            'mime'=>'image/png',
            'ext'=>'png'
        ),
        'pdf'=>array(
            'mime'=>'application/pdf',
            'ext'=>'pdf'
        ),
        'txt'=>array(
            'mime'=>'text/plain',
            'ext'=>'txt'
        ),
        'doc'=>array(
            'mime'=>'application/msword',
            'ext'=>'doc'
        ),
        'jpeg'=>array(
            'mime'=>'image/jpeg',
            'ext'=>'jpeg'
        )
    );
    private $baseUrl;
    private $files;
    /**
     * A constant to indicate that a file extension is not allowed to be uploaded.
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

    public function setBaseURL($url) {
        $this->baseUrl = $url;
    }
    public function __construct() {
        $this->uploadStatusMessage = 'NO ACTION';
    }
    /**
     * The directory at which the file will be uploaded to.
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
     * @param string $dir Upload Directory.
     * @since 1.0
     */
    public function setUploadDir($dir){
        $this->uploadDir = str_replace('/', '\\', $dir);
    }
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
        array_push($this->extentions, $ext);
    }
    /**
     * Removes an extention from the array of allowed files types.
     * @param string $ext File extention. The extention should be included 
     * without suffix.(e.g. jpg, png, pdf)
     * @since 1.0
     */
    public function removeExt($ext){
        array_pop($this->extentions,$ext);
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
    public static function getMIMEType($ext){
        $x = self::ALLOWED_FILE_TYPES[$ext];
        if($x != NULL){
            return $x['mime'];
        }
        return NULL;
    }
    private function isValidExt($fileName){
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        return in_array($ext, $this->getExts(),TRUE) || in_array(strtolower($ext), $this->getExts(),TRUE);
    }
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
     * @return boolean
     */
    public function upload($replaceIfExist = false){
        $this->files = array();
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if(isset($_FILES[$this->asscociatedName])){
                $fileOrFiles = $_FILES[$this->asscociatedName];
            }
            else{
                return FALSE;
            }
            if($fileOrFiles != null){
                if(gettype($fileOrFiles['name']) == 'array'){
                    //multi-upload
                    $filesCount = count($fileOrFiles['name']);
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
                                        $fileInfoArr['is-exist'] = 'NO';
                                        $fileInfoArr['is-replace'] = 'NO';
                                            if(move_uploaded_file($fileOrFiles["tmp_name"][$x], $targetDir)){
                                                if(function_exists('mime_content_type')){
                                                $fileInfoArr['mime'] = mime_content_type($fileInfoArr['upload-path']);
                                            }
                                            else{
                                                $ext = pathinfo($fileInfoArr['name'], PATHINFO_EXTENSION);
                                                $fileInfoArr['mime'] = self::getMIMEType($ext);
                                            }
                                            $fileInfoArr['uploaded'] = 'true';
                                        }
                                        else{
                                            $fileInfoArr['uploaded'] = 'NO';
                                        }
                                    }
                                    else{
                                        if(function_exists('mime_content_type')){
                                            $fileInfoArr['mime'] = mime_content_type($fileInfoArr['upload-path']);
                                        }
                                        else{
                                            $ext = pathinfo($fileInfoArr['name'], PATHINFO_EXTENSION);
                                            $fileInfoArr['mime'] = self::getMIMEType($ext);
                                        }
                                        $fileInfoArr['is-exist'] = 'true';
                                        if($replaceIfExist){
                                            $fileInfoArr['is-replace'] = 'true';
                                            
                                            unlink($targetDir);
                                            if(move_uploaded_file($fileOrFiles["tmp_name"][$x], $targetDir)){
                                                $fileInfoArr['uploaded'] = 'true';
                                            }
                                            else{
                                                $fileInfoArr['uploaded'] = 'false';
                                            }
                                        }
                                        else{
                                            $fileInfoArr['is-replace'] = 'false';
                                        }
                                    }
                                }
                                else{
                                    $fileInfoArr['upload-error'] = FileFunctions::NO_SUCH_DIR;
                                    $fileInfoArr['uploaded'] = 'false';
                                }
                            }
                            else{
                                $fileInfoArr['uploaded'] = 'false';
                                $fileInfoArr['upload-error'] = FileFunctions::NOT_ALLOWED;
                            }
                        }
                        else{
                            $fileInfoArr['uploaded'] = 'false';
                            $fileInfoArr['upload-error'] = $fileOrFiles['error'][$x];
                        }
                        array_push($this->files, $fileInfoArr);
                    }
                }
                else{
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
                                    $fileInfoArr['is-exist'] = 'false';
                                    $fileInfoArr['is-replace'] = 'false';
                                    if(move_uploaded_file($fileOrFiles["tmp_name"], $targetDir)){
                                        $fileInfoArr['uploaded'] = 'true';
                                        if(function_exists('mime_content_type')){
                                            $fileInfoArr['mime'] = mime_content_type($fileInfoArr['upload-path']);
                                        }
                                        else{
                                            $ext = pathinfo($fileInfoArr['name'], PATHINFO_EXTENSION);
                                            $fileInfoArr['mime'] = self::getMIMEType($ext);
                                        }
                                    }
                                    else{
                                        $fileInfoArr['uploaded'] = 'false';
                                    }
                                }
                                else{
                                    $fileInfoArr['is-exist'] = 'true';
                                    if(function_exists('mime_content_type')){
                                        $fileInfoArr['mime'] = mime_content_type($fileInfoArr['upload-path']);
                                    }
                                    else{
                                        $ext = pathinfo($fileInfoArr['name'], PATHINFO_EXTENSION);
                                        $fileInfoArr['mime'] = self::getMIMEType($ext);
                                    }
                                    if($replaceIfExist){
                                        $fileInfoArr['is-replace'] = 'true';
                                        unlink($targetDir);
                                        if(move_uploaded_file($fileOrFiles["tmp_name"], $targetDir)){
                                            $fileInfoArr['uploaded'] = 'true';
                                        }
                                        else{
                                            $fileInfoArr['uploaded'] = 'false';
                                        }
                                    }
                                    else{
                                        $fileInfoArr['is-replace'] = 'false';
                                    }
                                }
                            }
                            else{
                                $fileInfoArr['upload-error'] = FileFunctions::NO_SUCH_DIR;
                                $fileInfoArr['uploaded'] = 'false';
                            }
                        }
                        else{
                            $fileInfoArr['uploaded'] = 'false';
                            $fileInfoArr['upload-error'] = FileFunctions::NOT_ALLOWED;
                        }
                    }
                    else{
                        $fileInfoArr['uploaded'] = 'false';
                        $fileInfoArr['upload-error'] = $fileOrFiles['error'];
                    }
                    array_push($this->files, $fileInfoArr);
                }
            }
            else{
                $this->files[$this->getAssociatedName()] = FileFunctions::NOT_EXIST;
            }
        }
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

