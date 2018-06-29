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
defined('ROOT_DIR') or die('Direct Access Not Allowed.');
/**
 * A REST API that provides the basic file related operations. 
 * The user can extend this API to add his own File Related APIs.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class FileAPIs extends API{
    /**
     * An array of upload options
     * @var array An associative array that contains upload options. 
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
     * </ul> 
     */
    private $uploadOptions;
    /**
     * Creates new instance.
     */
    public function __construct() {
        parent::__construct('1.0.0');
        $this->setDescription('An API that is used to handel file upload operations.');
        $a1 = new APIAction('upload-files');
        $a1->setDescription('Upload one or multiple files to the server.');
        $a1->addRequestMethod('post');
        $a1->addParameter(new RequestParameter('upload-path', 'string',TRUE));
        $a1->getParameterByName('upload-path')->setDescription('Optional Parameter. The path where the file will be uploaded to. '
                . 'If no path is given, the default will be \'ROOT_DIR/uploads\'');
        $a1->getParameterByName('upload-path')->setDefault('uploads');
        $a1->addParameter(new RequestParameter('replace-in-path', 'boolean',TRUE));
        $a1->getParameterByName('replace-in-path')->setDescription('Optional parameter. Used only in '
                . 'case of the upload was to a path. If a file with the given name and type was found '
                . 'in the upload path, the file will be replaced. Default is false.');
        $a1->getParameterByName('replace-in-path')->setDefault('f');
        $a1->addParameter(new RequestParameter('database-upload', 'boolean',TRUE));
        $a1->getParameterByName('database-upload')->setDescription('Optional Parameter. If set to '
                . 'true, The file will be stored in the database as blob. Default is false.');
        $a1->getParameterByName('database-upload')->setDefault('f');
        $a1->addParameter(new RequestParameter('create-path', 'boolean',TRUE));
        $a1->getParameterByName('create-path')->setDefault('f');
        $a1->getParameterByName('create-path')->setDescription('Optional parameter. Used only in '
                . 'case of the upload was to a path. If the given path does not exists in system files, it '
                . 'will be created if this option is set to true. Default is false.');
        $a1->addParameter(new RequestParameter('upload-and-remove', 'boolean', TRUE));
        $a1->getParameterByName('upload-and-remove')->setDefault('f');
        $a1->getParameterByName('upload-and-remove')->setDescription('Optional parameter. If the '
                . 'set to true, the file will be removed from the path that it was uploaded to. Default is false.');
        $a1->addParameter(new RequestParameter('name', 'string'));
        $a1->getParameterByName('name')->setDescription('The name of the file (or array) that will contain uploaded file. '
                . 'The value of this parameter is usually the value of the attribute \'name\' for the HTML input element '
                . 'that is used to upload files. If multiple inputs of type file where present, '
                . 'the names can be seprated using comma (such as \'file-1,videos,images\'');
        $this->addAction($a1);
        
        $a2 = new APIAction('remove-file');
        $a2->setDescription('Removes a file given its path or ID.');
        $a2->addRequestMethod('delete');
        $a2->addParameter(new RequestParameter('file-path', 'string', TRUE));
        $a2->getParameterByName('file-path')->setDescription('Optional parameter. If '
                . 'the file was uploaded to server path, then the path of the file must be provided in '
                . 'order to remov it.');
        $a2->addParameter(new RequestParameter('file-id', 'integer', TRUE));
        $a2->getParameterByName('file-id')->setDescription('Optional parameter. If the file was uploaded to the database, '
                . 'then this parameter must be provided.');
        $this->addAction($a2);
        
        $a3 = new APIAction('get-file');
        $a3->addRequestMethod('get');
        $a3->setDescription('Returns a downloadable version of the file from the database given its ID.');
        $a3->addParameter(new RequestParameter('file-id', 'integer'));
        $a3->getParameterByName('file-id')->setDescription('The ID of the file taken from the database.');
        $this->addAction($a3);
        
        $a4 = new APIAction('get-file-info');
        $a4->addRequestMethod('get');
        $a4->setDescription('Returns file information from the database given its ID.');
        $a4->addParameter(new RequestParameter('file-id', 'integer'));
        $a4->getParameterByName('file-id')->setDescription('The ID of the file taken from the database.');
        $this->addAction($a4);
        
        $this->uploadOptions = $options=array(
            'database-upload'=>false,
            'upload-path'=>'uploads',
            'replace-in-path'=>false,
            'upload-and-remove'=>false,
            'create-path'=>false
        );
    }
    /**
     * Returns an associative array that contains upload options.
     * @return array An associative array that contains upload options. 
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
     * </ul>
     */
    public function getUploadOptions() {
        return $this->uploadOptions;
    }
    /**
     * Sets the value of an upload option.
     * @param string $optionName The name of the option. The available options are: 
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
     * @param mixed $val The value of the option.
     * @return boolean The function will return <b>TRUE</b> once the option 
     * is updated.
     * @since 1.0
     */
    public function setUploadOption($optionName,$val) {
        if(isset($this->uploadOptions[$optionName])){
            $this->uploadOptions[$optionName] = $val;
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Process upload request.
     * @since 1.0
     */
    public final function processUpload(){
        $i = $this->getInputs();
        $name = $i['name'];
        $namesArr = explode(',', trim($name, ','));
        FileFunctions::get()->addFileType('jpg');
        FileFunctions::get()->addFileType('mp4');
        $this->setUploadOption('replace-in-path', $i['replace-in-path']);
        $this->setUploadOption('upload-path', $i['upload-path']);
        $this->setUploadOption('create-path', $i['create-path']);
        $this->setUploadOption('database-upload', $i['database-upload']);
        $this->setUploadOption('upload-and-remove', $i['upload-and-remove']);
        $resultsArr = FileFunctions::get()->upload($namesArr, $this->getUploadOptions());
        if($resultsArr == FileFunctions::NO_TYPES){
            $this->sendResponse($resultsArr, TRUE, 404,'"details":"No file types where added to allowed upload types"');
        }
        else{
            $jsonx = new JsonX();
            $jsonx->add('response', $resultsArr);
            $this->send('application/json', $jsonx);
        }
    }
    /**
     * This function must be overridden by sub classes to check 
     * authorization.
     * @return boolean The function should return <b>TRUE</b> if the 
     * user is authorized to perform specific action. <b>FALSE</b> if not.
     * @since 1.0
     */
    public function isAuthorized() {
        return TRUE;
    }
    /**
     * Sends back file information as JSON if it is exists in the database given its ID. 
     * This function must be called only if 
     * the action is 'get-file-info'.
     * @since 1.0 
     */
    public final function getFileInfo() {
        $file = FileFunctions::get()->getFile($this->getInputs()['file-id']);
        if($file instanceof File){
            $this->send('application/json', $file);
        }
        else if($file == FileFunctions::NO_SUCH_FILE){
            $this->sendResponse('Not found', TRUE, 404);
        }
        else if($file == MySQLQuery::QUERY_ERR){
            $this->databaseErr();
        }
    }
    /**
     * Sends back the file if it is exists in the database given its ID. 
     * This function must be called only if 
     * the action is 'get-file'.
     * @since 1.0 
     */
    public final function getFile() {
        $file = FileFunctions::get()->getFile($this->getInputs()['file-id']);
        if($file instanceof File){
            header('Content-Disposition: inline; filename="'.$file->getName().'"');
            $this->send($file->getMIMEType(), $file->getRawData());
        }
        else if($file == FileFunctions::NO_SUCH_FILE){
            $this->sendResponse('Not found', TRUE, 404);
        }
        else if($file == MySQLQuery::QUERY_ERR){
            $this->databaseErr();
        }
    }
    /**
     * Removes a file given its ID. This function must be called only if 
     * the action is 'remove-file'.
     * @since 1.0
     */
    public final function removeFile() {
        $r = FileFunctions::get()->removeFile($this->getInputs()['file-id']);
        if($r instanceof File){
            $this->sendResponse('File Removed');
        }
        else if($r == FileFunctions::NO_SUCH_FILE){
            $this->sendResponse('Not found', TRUE, 404);
        }
        else if($r == MySQLQuery::QUERY_ERR){
            $this->databaseErr();
        }
    }
    /**
     * Process request. This function can only process the 
     * following actions: 
     * <ul>
     * <li>upload-files</li>
     * <li>get-file</li>
     * <li>get-file-info</li>
     * <li>remove-file</li>
     * </ul>
     * @since 1.0
     */
    public function processRequest() {
        $action = $this->getAction();
        if($action == 'upload-files'){
            $this->processUpload();
        }
        else if($action == 'get-file'){
            $this->getFile();
        }
        else if($action == 'remove-file'){
            $this->removeFile();
        }
        else if($action == 'get-file-info'){
            $this->getFileInfo();
        }
    }
}
$api = new FileAPIs();
$api->process();