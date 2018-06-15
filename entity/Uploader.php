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
    /**
     * The size of the uploaded file in bytes.
     * @var int 
     */
    private $fSize;
    /**
     * The name of the uploaded file.
     * @var string 
     */
    private $fName;
    /**
     * A code that indicates the status of the upload.
     * @var int 
     */
    private $err;
    /**
     * Set to true if the file is already uploaded and want to replace.
     * @var boolean  
     * @since 1.0
     * 
     */
    private $isReplace;
    public function __construct() {
        $this->isReplace = false;
        $this->err = 0;
        $this->fSize = 0;
        $this->fName = '';
        $this->uploadStatusMessage = 'NO ACTION';
    }
    /**
     * The directory at which the file will be uploaded to.
     * @var string A directory. 
     * @since 1.0
     */
    private $uploadDir;
    /**
     * The maximum file size allowed in Bytes.
     * Remember that 1KB = 1000B
     * @var int the size of file in Bytes. 
     * @since 1.0
     */
    private $fileSize;
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
    /**
     * Sets the maximum size of file in bytes.
     * Remember that one KB = 1000 Bytes.
     * @param type $limit The maximum file size.
     * @since 1.0
     */
    public function setLimit($limit){
        $size = filter_input(INPUT_REQUEST, 'MAX_FILE_SIZE');
        if($size){
            $this->fileSize = (int)$size;
        }
        else{
            if($limit != null){
                $this->fileSize = $limit;
            }
        }
    }
    public function getFiles() {
        return $this->files;
    }
    /**
     * Checks if the file is replaceable if it is exist.
     * @return boolean <b>TRUE</b> if the file is replaceable.
     * @since 1.0
     */
    public function isReplace(){
        return $this->isReplace;
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
     * Returns the maximum allowed size for a file to upload.
     * @return int Size of file.
     * @since 1.0
     */
    public function getLimit(){
        return $this->fileSize;
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
    /**
     * Returns the message that was generated after calling the function <b>upload()</b>.
     * @return string The message that was generated after calling the function <b>upload()</b>.
     * @since 1.0
     */
    public function getUploadMessage(){
        return $this->uploadStatusMessage;
    }
    /**
     * Returns the error code that was generated after calling the function <b>upload()</b>.
     * @return string The error code that was generated after calling the function <b>upload()</b>.
     * @since 1.0
     */
    public function getErrCode(){
        return $this->err;
    }
    public function getFileName(){
        return $this->fName;
    }
    public function getFileSize(){
        return $this->fSize;
    }
    public function getMIMEType(){
        return self::ALLOWED_FILE_TYPES[$this->getFileExt()]['mime'];
    }
    public function getFileExt(){
        return strtolower(pathinfo($this->getFileName(), PATHINFO_EXTENSION));
    }
    private function isValidExt($fileName){
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        return in_array($ext, $this->getExts()) || in_array(strtolower($ext), $this->getExts());
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
     * Returns the location at which the file was uploaded to including file name.
     * @return string The full path to the file.
     * @since 1.1
     */
    public function getFilePath(){
        $path = $this->getUploadDir().'\\'.$this->getFileName();      
        return str_replace('\\', '/', $path);
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
                }
                else{
                    //single file upload
                    $fileInfoArr = array();
                    $fileInfoArr['name'] = $fileOrFiles['name'];
                    $fileInfoArr['size'] = $fileOrFiles['size'];
                    $fileInfoArr['upload-path'] = $this->getUploadDir();
                    $fileInfoArr['upload-error'] = 0;
                    if(!$this->isError($fileOrFiles['error'])){
                        if($this->isValidExt($fileOrFiles['name'])){
                            if(Util::isDirectory($this->getUploadDir()) == TRUE){
                                $targetDir = $this->getUploadDir().'\\'.$this->getFileName();
                                $targetDir = str_replace('\\', '/', $targetDir);
                                if(!file_exists($targetDir)){
                                    $fileInfoArr['is-exist'] = 'NO';
                                    $fileInfoArr['is-replace'] = 'NO';
                                    if(move_uploaded_file($fileOrFiles["tmp_name"], $targetDir)){
                                        $fileInfoArr['uploaded'] = 'YES';
                                    }
                                    else{
                                        $fileInfoArr['uploaded'] = 'NO';
                                    }
                                }
                                else{
                                    $fileInfoArr['is-exist'] = 'YES';
                                    if($replaceIfExist){
                                        $fileInfoArr['is-replace'] = 'YES';
                                        unlink($targetDir);
                                        if(move_uploaded_file($fileOrFiles["tmp_name"], $targetDir)){
                                            $fileInfoArr['uploaded'] = 'YES';
                                        }
                                        else{
                                            $fileInfoArr['uploaded'] = 'NO';
                                        }
                                    }
                                    else{
                                        $fileInfoArr['is-replace'] = 'NO';
                                    }
                                }
                            }
                            else{
                                $fileInfoArr['upload-error'] = FileFunctions::NO_SUCH_DIR;
                                $fileInfoArr['uploaded'] = 'NO';
                            }
                        }
                        else{
                            $fileInfoArr['uploaded'] = 'NO';
                            $fileInfoArr['upload-error'] = FileFunctions::NOT_ALLOWED;
                        }
                    }
                    else{
                        $fileInfoArr['uploaded'] = 'NO';
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
        $j->add('limit', ($this->getLimit()/1000).' KB"');
        $j->add('file-size', ($this->getFileSize()/1000).' KB"');
        $j->add('upload-directory', $this->getUploadDir());
        $j->add('file-name', $this->getFileName());
        $j->add('associated-name', $this->getAssociatedName());
        $j->add('file-path', $this->getFilePath());
        $j->add('error-code', $this->getErrCode());
        $j->add('upload-message', $this->getUploadMessage());
        $j->add('allowed-types', $this->getExts());
        return $j;
    }
    public function __toString() {
        return $this->toJSON().'';
    }
}

