<?php
/*
 * The MIT License
 *
 * Copyright 2018 ibrah.
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
 * An extension for the class 'API' that adds support for multi-language 
 * response messages.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0.1
 */
abstract class ExtendedAPI extends API{
    private $translation;
    /**
     * Creates new instance of 'API'.
     * @param string $version [Optional] initial API version. Default is '1.0.0'.
     * @since 1.0
     */
    public function __construct($version = '1.0.0') {
        parent::__construct($version);
        $langCode = LisksCode::getWebsiteFunctions()->getSession()->getLang(TRUE);
        $this->translation = &Language::loadTranslation($langCode);
        $this->createLangDir('general');
        if($langCode == 'AR'){
            $this->setLangVars('general', array(
                'action-not-supported'=>'العملية غير مدعومة.',
                'content-not-supported'=>'نوع المحتوى غير مدعوم.',
                'action-not-impl'=>'لم يتم تنفيذ العملية.',
                'missing-params'=>'المعاملات التالية مفقودة من جسم الطلب: ',
                'inv-params'=>'المُعاملات التالية لديها قيم غير صالحة: ',
                'db-error'=>'خطأ في قاعدة البيانات.'
            ));
        }
        else{
            $this->setLangVars('general', array(
                'action-not-supported'=>'Action is not supported by the API.',
                'content-not-supported'=>'Content type not supported.',
                'action-not-impl'=>'API action is not implemented yet.',
                'missing-params'=>'The following required parameter(s) where missing from the request body: ',
                'inv-params'=>'The following parameter(s) has invalid values: ',
                'db-error'=>'Database Error.'
            ));
        }
    }
    /**
     * Returns an associative array that contains HTTP authorization header 
     * content.
     * @return array An associative array that has two indices: 
     * <ul>
     * <li><b>type</b>: Type of authorization (e.g. basic, bearer )</li>
     * <li><b>credentials</b>: Depending on authorization type, 
     * this field will have different values.</li>
     * </ul>
     * If no authorization header is sent, The two indices will be empty.
     * @since 1.0.1
     */
    public function getAuthorizationHeader(){
        $retVal = array(
            'type'=>'',
            'credentials'=>''
        );
        $headers = Util::getRequestHeaders();
        if(isset($headers['authorization'])){
            $split = explode(' ', $headers['authorization']);
            $retVal['type'] = strtolower($split[0]);
            $retVal['credentials'] = $split[1];
        }
        return $retVal;
    }
    /**
     * Adds new action to the set of API actions.
     * @param APIAction $action The action that will be added.
     * @param boolean $reqPermission Set to 'TRUE' if the action require user login or 
     * any additional permissions.
     * @return boolean 'TRUE' if the action is added. 'FAlSE' otherwise.
     * @since 1.0
     */
    public function addAction($action,$reqPermission=false) {
        if($action instanceof APIAction){
            $sid = new RequestParameter('session-id', 'string', TRUE);
            $sid->setDefault('');
            $sid->setDescription('The ID of the session that is currently active or '
                    . 'the session that will be used by request.');
            $action->addParameter($sid);
            parent::addAction($action, $reqPermission);
        }
        return FALSE;
    }
    /**
     * Returns the language instance which is linked with the instance.
     * @return Language an instance of the class 'Language'.
     * @since 1.0
     */
    public function &getTranslation() {
        return $this->translation;
    }
    /**
     * Returns the value of a language variable.
     * @param string $dir A directory to the language variable (such as 'pages/login/login-label').
     * @return string|array If the given directory represents a label, the 
     * function will return its value. If it represents an array, the array will 
     * be returned. If nothing was found, the returned value will be the passed 
     * value to the function. 
     * @since 1.0
     */
    public function get($dir) {
        return $this->getTranslation()->get('api-messages/'.$dir);
    }
    /**
     * Creates a sub array to define language variables.
     * @param string $dir A string that looks like a 
     * directory. For example, if the given string is 'general', 
     * an array with key name 'general' will be created. Another example is 
     * if the given string is 'pages/login', two arrays will be created. The 
     * top one will have the key value 'pages' and another one inside 
     * the pages array with key value 'login'.
     * @since 1.0
     */
    public function createLangDir($dir) {
        $this->getTranslation()->createDirectory('api-messages/'.$dir);
    }
    /**
     * Sets multiple language variables.
     * @param string $dir A string that looks like a 
     * directory. 
     * @param array $arr An associative array. The key will act as the variable 
     * and the value of the key will act as the variable value.
     * @since 1.0
     */
    public function setLangVars($dir,$arr=array()) {
        $this->getTranslation()->setMultiple('api-messages/'.$dir, $arr);
    }
    /**
     * Sends a response message to indicate that a database error has occur.
     * @param JsonI|string $info An object of type 'JsonI' that 
     * describe the error in more details. Also it can be a simple string.
     * @since 1.0
     */
    public function databaseErr($info=''){
        $message = $this->get('general/db-error');
        if($info instanceof JsonI){
            $this->sendResponse($message, TRUE, 404, '"err-info":'.$info->toJSON());
        }
        else{
            $this->sendResponse($message, TRUE, 404, '"err-info":"'.$info.'"');
        }
    }
    /**
     * Sends a response message to indicate that a user is not authorized to do an API call.
     * @since 1.0
     */
    public function notAuth(){
        $message = $this->get('general/http-codes/401/message');
        $this->sendResponse($message, TRUE, 401);
    }
    /**
     * Sends a response message to indicate that an action is not supported by the API.
     * @since 1.0
     */
    public function actionNotSupported(){
        $message = $this->get('general/action-not-supported');
        $this->sendResponse($message, TRUE, 404);
    }
    /**
     * Sends a response message to indicate that request content type is not supported by the API.
     * @since 1.1
     */
    public function contentTypeNotSupported($cType=''){
        $message = $this->get('general/content-not-supported');
        $this->sendResponse($message, TRUE, 404,'"request-content-type":"'.$cType.'"');
    }
    /**
     * Sends a response message to indicate that request method is not supported.
     * @since 1.0
     */
    public function requMethNotAllowed(){
        $message = $this->get('general/http-codes/405/message');
        $this->sendResponse($message, TRUE, 405);
    }
    /**
     * Sends a response message to indicate that an action is not implemented.
     * @since 1.0
     */
    public function actionNotImpl(){
        $message = $this->get('general/action-not-impl');
        $this->sendResponse($message, TRUE, 404);
    }
    /**
     * Sends a response message to indicate that a request parameter or parameters are missing.
     * @param array $paramsNamesArr An array that contains the name(s) of the parameter(s).
     * @since 1.3
     */
    public function missingParams($paramsNamesArr){
        $val = '';
        if(gettype($paramsNamesArr) == 'array'){
            $i = 0;
            $count = count($paramsNamesArr);
            foreach ($paramsNamesArr as $paramName){
                if($i + 1 == $count){
                    $val .= '\''.$paramName.'\'';
                }
                else{
                    $val .= '\''.$paramName.'\', ';
                }
                $i++;
            }
        }
        $message = $this->get('general/missing-params');
        $this->sendResponse($message.$val.'.', TRUE, 404);
    }
    /**
     * Sends a response message to indicate that a request parameter(s) have invalid values.
     * @param array $paramsNamesArr An array that contains the name(s) of the parameter(s).
     * @since 1.3
     */
    public function invParams($paramsNamesArr){
        $val = '';
        if(gettype($paramsNamesArr) == 'array'){
            $i = 0;
            $count = count($paramsNamesArr);
            foreach ($paramsNamesArr as $paramName){
                if($i + 1 == $count){
                    $val .= '\''.$paramName.'\'';
                }
                else{
                    $val .= '\''.$paramName.'\', ';
                }
                $i++;
            }
        }
        $message = $this->get('general/inv-params');
        $this->sendResponse($message.$val.'.', TRUE, 404);
    }
}
