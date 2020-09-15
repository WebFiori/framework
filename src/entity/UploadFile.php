<?php
/*
 * The MIT License
 *
 * Copyright 2020 Ibrahim, WebFiori Framework.
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

/**
 * A class which is used by the class 'Uploader' to represents uploaded files.
 * 
 * The class is used by the method 'Uploader::uploadAsFileObj()' to upload files 
 * as objects of type 'File'.
 * 
 * @author Ibrahim
 * 
 * @version 1.0
 */
class UploadFile extends File {
    /**
     * A boolean which is set to true in case the file is uploaded and was replaced.
     * 
     * @var boolean
     * 
     * @since 1.0 
     */
    private $isReplace;
    /**
     * A boolean which is set to true in case the file is uploaded without issues.
     * 
     * @var boolean
     * 
     * @since 1.0 
     */
    private $isUploaded;
    /**
     * A string that contains a message which indicates what caused upload failure.
     * 
     * @var string 
     * 
     * @since 1.0
     */
    private $uploadError;
    /**
     * Creates new instance of the class.
     * 
     * This method will set the path and name to empty string. Also, it will 
     * set the size to 0 and ID to -1. Finally, it will set MIME type to 
     * "application/octet-stream"
     * 
     * @param string $fName The name of the file such as 'my-file.png'.
     * 
     * @param string $fPath The path of the file such as 'C:/Images/Test'.
     */
    public function __construct($fName = '', $fPath = '') {
        parent::__construct($fName, $fPath);
        $this->setIsReplace(false);
        $this->setIsUploaded(false);
        $this->setUploadErr('');
    }
    /**
     * Returns a string that represents upload error.
     * 
     * @return string A string that can be used to identify the cause of upload 
     * failure.
     * 
     * @since 1.0
     */
    public function getUploadError() {
        return $this->uploadError;
    }
    /**
     * Checks if the file was replaced by another uploaded file.
     * 
     * @return boolean If the file was already exist in the server and a one 
     * which has the same name was uploaded, the method will return true. Default 
     * return value is false.
     * 
     * @since 1.0
     */
    public function isReplace() {
        return $this->isReplace;
    }
    /**
     * Checks if the file was uploaded successfully or not.
     * 
     * @return boolean If the file was uploaded to the server without any errors, 
     * the method will return true. Default return value is false.
     * 
     * @since 1.0
     */
    public function isUploaded() {
        return $this->isUploaded;
    }
    /**
     * Sets the value of the property '$isReplace'.
     * The property is used to check if the file was already exist in the server and 
     * was replaced by another uploaded file. 
     * 
     * @param boolean $bool A boolen. If true is passed, it means the file was replaced 
     * by new one with the same name.
     * 
     * @since 1.0
     */
    public function setIsReplace($bool) {
        $this->isReplace = $bool === true;
    }
    /**
     * Sets the value of the property '$isUploaded'.
     * The property is used to check if the file was successfully uploaded to the server.
     * 
     * @param boolean $bool A boolen. If true is passed, it means the file was uploaded 
     * without any errors.
     * 
     * @since 1.0
     */
    public function setIsUploaded($bool) {
        $this->isUploaded = $bool === true;
    }
    /**
     * Sets an error message that indicates the cause of upload failure.
     * 
     * @param string $err Error message.
     * 
     * @since 1.0
     */
    public function setUploadErr($err) {
        $this->uploadError = $err;
    }
    /**
     * Returns a JSON string that represents the file.
     * 
     * @return Json An object of type 'Json' that contains file information. 
     * The object will have the following information:<br/>
     * <b>{<br/>&nbsp;&nbsp;"id":"",<br/>
     * &nbsp;&nbsp;"mime":"",<br/>
     * &nbsp;&nbsp;"name":"",<br/>
     * &nbsp;&nbsp;"path":"",<br/>
     * &nbsp;&nbsp;"sizeInBytes":"",<br/>
     * &nbsp;&nbsp;"sizeInKBytes":"",<br/>
     * &nbsp;&nbsp;"sizeInMBytes":"",<br/>&nbsp;&nbsp;"uploaded":"",<br/>
     * &nbsp;&nbsp;"isReplace":"",<br/>&nbsp;&nbsp;"uploadError":"",<br/>}</b>
     * 
     * @since 1.0
     */
    public function toJSON() {
        $json = parent::toJSON();
        $json->addMultiple([
            'uploaded' => $this->isUploaded(),
            'isReplace' => $this->isReplace(),
            'uploadError' => $this->getUploadError()
        ]);

        return $json;
    }
}
