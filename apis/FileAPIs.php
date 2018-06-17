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
require_once '../root.php';
/**
 * Description of FileAPIs
 *
 * @author Ibrahim
 */
class FileAPIs extends API{
    private $uploadOptions;
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
        $a3->setDescription('Returns a downloadable version of the file from the database given its ID.');
        $a3->addParameter(new RequestParameter('file-id', 'integer'));
        $a3->getParameterByName('file-id')->setDescription('The ID of the file taken from the database.');
        $this->addAction($a3);
        
        $this->uploadOptions = $options=array(
            'database-upload'=>false,
            'upload-path'=>'uploads',
            'replace-in-path'=>false,
            'upload-and-remove'=>false,
            'create-path'=>false
        );
    }
    
    public function getUploadOptions() {
        return $this->uploadOptions;
    }
    
    private function setUploadOption($optionName,$val) {
        if(isset($this->uploadOptions[$optionName])){
            $this->uploadOptions[$optionName] = $val;
            return TRUE;
        }
        return FALSE;
    }
    
    private function processUpload(){
        $i = $this->getInputs();
        $name = $i['name'];
        $namesArr = explode(',', trim($name, ','));
        FileFunctions::get()->addFileType('jpg');
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
            $this->sendResponse('Upload Info', FALSE, 200, '"json-response":'.$jsonx);
        }
    }
    
    public function isAuthorized() {
        return TRUE;
    }

    public function processRequest() {
        $action = $this->getAction();
        if($action == 'upload-files'){
            $this->processUpload();
        }
    }
    
    private function createJsonX($array) {
        
    }
}
$api = new FileAPIs();
$api->process();