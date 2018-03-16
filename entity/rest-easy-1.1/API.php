<?php
/*
 * The MIT License
 *
 * Copyright 2018 Ibrahim BinAlshikh.
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
 * A class that represents a REST API.
 * @version 1.1
 */
abstract class API implements JsonI{
    /**
     * An array that contains the supported 'POST' request content types.
     * @var array An array that contains the supported 'POST' request content types.
     * @since 1.1
     */
    const POST_CONTENT_TYPES = array(
        'application/x-www-form-urlencoded',
        'multipart/form-data'
    );
    /**
     * An array that contains most common MIME types with file extension as key.
     * @var array An array that contains most common MIME types with file extension as key.
     * @since 1.1
     */
    const MIME_TYPES = array(
        'js'=>'application/javascript',
        'json'=>'application/json',
        'xml'=>'application/xml',
        'zip'=>'application/zip',
        'pdf'=>'application/pdf',
        'sql'=>'application/sql',
        'doc'=>'application/msword',
        'docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'=>'application/vnd.ms-excel',
        'xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'=>'application/vnd.ms-powerpoint',
        'pptx'=>'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'mp3'=>'audio/mpeg',
        'css'=>'text/css',
        'html'=>'text/html',
        'csv'=>'text/csv',
        'txt'=>'text/plain',
        'png'=>'image/png',
        'jpeg'=>'image/jpeg',
        'gif'=>'image/gif',
        'mp4'=>'video/mp4'
    );
    /**
     * API request method.
     * @var string 
     * @since 1.0
     */
    private $requestMethod;
    /**
     * An array that contains the action that can be performed by the API.
     * @var array
     * @since 1.0 
     */
    private $actions;
    /**
     * Actions that requires authentication in order to perform.
     * @var array
     * @since 1.0 
     */
    private $authActions;
    /**
     * The filter used to sanitize request parameters.
     * @var APIFilter
     * @since 1.0 
     */
    private $filter;
    /**
     * Session manager.
     * @var SessionManager
     * @since 1.0 
     * @deprecated since version 1.1 Will be removed.
     */
    private $settionMngr;
    
    private $apiVersion;
    
    public function __construct(){
        $this->requestMethod = filter_var(getenv('REQUEST_METHOD'));
        $this->actions = array();
        $this->authActions = array();
        $this->filter = new APIFilter();
        $action = new APIAction();
        $action->setName('api-info');
        $action->addRequestMethod('get');
        $this->addAction($action);
        $action2 = new APIAction();
        $action2->setName('request-info');
        $action2->addRequestMethod('get');
        $this->addAction($action2);
        $this->filter->addParameter('action', 'string');
        $this->setVersion('0.0.0');
        if(class_exists('SessionManager')){
            $this->settionMngr = SessionManager::get();
        }
    }
    /**
     * 
     * @return SessionManager
     * @since 1.0
     * @deprecated since version 1.1 Will be removed.
     */
    public function getSession(){
        return $this->settionMngr;
    }
    /**
     * Sends a response message to indicate that a database error has occur.
     * @since 1.0
     */
    public function databaseErr(){
        $this->sendResponse('Database Error', TRUE, 404, '"db":'.$this->getSession()->getDBLink()->toJSON());
    }
    /**
     * Sends a response message to indicate that a user is not authorized to do an API call.
     * @since 1.0
     */
    public function notAuth(){
        $this->sendResponse('Not authorized', TRUE, 401);
    }
    /**
     * Sends a response message to indicate that an action is not supported by the API.
     * @since 1.0
     */
    public function actionNotSupported(){
        $this->sendResponse('Action not supported', TRUE, 404);
    }
    /**
     * Sends a response message to indicate that request content type is not supported by the API.
     * @since 1.1
     */
    public function contentTypeNotSupported($cType=''){
        $this->sendResponse('Content type not supported.', TRUE, 404,'"request-content-type":"'.$cType.'"');
    }
    /**
     * Sends a response message to indicate that request method is not supported.
     * @since 1.0
     */
    public function requMethNotSupported(){
        $this->sendResponse('Method Not Allowed', TRUE, 405);
    }
    /**
     * Sends a response message to indicate that an action is not implemented.
     * @since 1.0
     */
    public function actionNotImpl(){
        $this->sendResponse('Action not implemented', TRUE, 404);
    }
    /**
     * Sends a response message to indicate that a request parameter is missing.
     * @param string $paramName The name of the parameter.
     * @since 1.0
     */
    public function missingParam($paramName){
        $this->sendResponse('The parameter \''.$paramName.'\' is missing.', TRUE, 404);
    }
    /**
     * Returns the version number of the API.
     * @return string
     * @since 1.0
     */
    public final function getVersion(){
        return $this->apiVersion;
    }
    /**
     * Sets API version number.
     * @param string $val Version number.
     * @since 1.0
     */
    public final function setVersion($val){
        $this->apiVersion = $val;
    }

    /**
     * Returns an array of supported API actions.
     * @return array An array of supported API actions.
     * @since 1.0
     */
    public final function getActions(){
        return $this->actions;
    }
    /**
     * Returns an array of supported API actions. The array will contains the actions 
     * that require authentication.
     * @return array An array of supported API actions.
     * @since 1.0
     */
    public final function getAuthActions(){
        return $this->authActions;
    }
    /**
     * Adds new action to the set of API actions.
     * @param string $action The action that will be added.
     * @param boolean $reqPermissions <b>TRUE</b> if the action require user login.
     * @return boolean <b>TRUE</b> if the action is added. <b>FAlSE</b> otherwise.
     * @since 1.0
     */
    public final function addAction($action='',$reqPermissions=false){
        if($action instanceof APIAction){
            if(!in_array($action, $this->getActions()) && !in_array($action, $this->getAuthActions())){
                if($reqPermissions == TRUE){
                    array_push($this->authActions, $action);
                }
                else{
                    array_push($this->actions, $action);
                }
                $params = $action->getParameters();
                foreach ($params as $val){
                    $this->filter->addParameter($val->getName(), $val->getType());
                }
            }
        }
    }
    /**
     * Returns the request method used to fetch the API.
     * @return string Request method (POST, GET, etc...).
     * @since 1.0
     */
    public final function getRequestMethod(){
        return $this->requestMethod;
    }
    /**
     * Returns JsonX object that represents the API.
     * @return JsonX An object of type <b>JsonX</b>
     * @since 1.0
     */
    public function toJSON() {
        $json = new JsonX();
        $json->add('method', $this->getRequestMethod());
        $json->add('api-version', $this->getVersion());
        $json->add('actions', $this->getActions());
        $json->add('auth-actions', $this->getAuthActions());
        return $json;
    }
    /**
     * Checks if the action is supported by the API.
     * @return boolean <b>TRUE</b> if the API supports the action. <b>FALSE</b> 
     * if not or it is not set.
     * @since 1.0
     */
    public final function isActionSupported(){
        $action = $this->getAction();
        foreach ($this->getActions() as $val){
            if($val->getName() == $action){
                return TRUE;
            }
        }
        foreach ($this->getAuthActions() as $val){
            if($val->getName() == $action){
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * Returns request content type (For 'POST' requests).
     * @return string The value of the header 'content-type' in the request.
     * @since 1.1
     */
    public final function getContentType(){
        $c = filter_input(INPUT_SERVER, 'CONTENT_TYPE');
        if($c != NULL && $c != FALSE){
            return strtok($c, ';');
        }
        return NULL;
    }
    /**
     * Checks if request content type is supported by the API or not (For 'POST' 
     * requests).
     * @return boolean Returns <b>TRUE</b> in case the 'content-type' header is not 
     * set or the request method is not 'POST'. Also the function will return 
     * <b>TRUE</b> if the content type is supported. Other than that, the function 
     * will return <b>FALSE</b>
     * @since 1.1
     */
    public final function isContentTypeSupported(){
        $c = $this->getContentType();
        if($c != NULL && $this->getRequestMethod() == 'POST'){
            return in_array($c, self::POST_CONTENT_TYPES);
        }
        return TRUE;
    }

    /**
     * Checks the status of the API action. It checks if the action is supported by 
     * the API or not. After that, it checks if the user is permitted to perform the 
     * action or not. 
     * @return boolean <b>TRUE</b> if nothing wrong with the action.
     * @since 1.0
     */
    private final function checkAction(){
        $action = $this->getAction();
        if($action != NULL){
            if($this->isActionSupported()){
                foreach ($this->getAuthActions() as $val){
                    if($val->getName() == $action){
                        if($this->isAuthorized()){
                            $reqMethods = $val->getActionMethods();
                            foreach ($reqMethods as $method){
                                if($method == $this->getRequestMethod()){
                                    return TRUE;
                                }
                            }
                            return FALSE;
                        }
                        else if($this->getSession() != NULL){
                            if($this->getSession()->validateToken()){
                                $reqMethods = $val->getActionMethods();
                                foreach ($reqMethods as $method){
                                    if($method == $this->getRequestMethod()){
                                        return TRUE;
                                    }
                                }
                                return FALSE;
                            }
                            else{
                                $this->notAuth();
                                return FALSE;
                            }
                        }
                        else{
                            $this->notAuth();
                            return FALSE;
                        }
                    }
                }
                foreach ($this->getActions() as $val){
                    if($val->getName() == $action){
                        $reqMethods = $val->getActionMethods();
                        foreach ($reqMethods as $method){
                            if($method == $this->getRequestMethod()){
                                return TRUE;
                            }
                        }
                        return FALSE;
                    }
                }
                return TRUE;
            }
            else{
                $this->actionNotSupported();
            }
        }
        else{
            $this->sendResponse('Action is not set.', TRUE, 404);
        }
        return FALSE;
    }
    /**
     * Checks if a user is authorized to perform an action that require authorization.
     * @return boolean The function must be implemented by the sub-class in a way 
     * that makes it return <b>TRUE</b> in case the user is allowed to perform the 
     * action. If the user is not permitted, the function must return <b>FALSE</b>.
     * @since 1.1
     */
    public abstract function isAuthorized();
    /**
     * A function that is used to process the requested action.
     * @since 1.1
     */
    public abstract function processRequest();
    /**
     * Process user request.
     * @param string $callback A name of a callback function. It must be a function 
     * in the child class.
     * @since 1.0
     */
    public function process(){
        $reqMeth = $this->getRequestMethod();
        if($reqMeth == 'GET' || $reqMeth == 'DELETE' || $reqMeth = 'PUT'){
            $this->filter->filterGET();
        }
        else if($reqMeth == 'POST'){
            $this->filter->filterPOST();
        }
        if($this->isContentTypeSupported()){
            if($this->checkAction()){
                if($this->getAction() == 'api-info'){
                    echo $this->toJSON();
                }
                else if($this->getAction() == 'request-info'){
                    $j = new JsonX();
                    $j->add('action', $this->getAction());
                    $j->add('content-type', $this->getContentType());
                    $j->add('method', $this->getRequestMethod());
                    $j->add('parameters', $this->getInputs());
                    $this->send(self::MIME_TYPES['json'], $j);
                }
                else{
                    $this->processRequest();
                }
            }
        }
        else{
            $this->contentTypeNotSupported($this->getContentType());
        }
    }
    /**
     * Sends a JSON response to the client.
     * @param string $message The message to send back.
     * @param boolean $isErr <b>TRUE</b> if the message represents an error state.
     * @param int $code Response code (such as 404 or 200).
     * @param string $otherJsonStr Any other data to send back (it should be a 
     * JSON string).
     * @since 1.0
     */
    public function sendResponse($message,$isErr=false,$code=200,$otherJsonStr=''){
        header('content-type:application/json');
        http_response_code($code);
        if($isErr == TRUE){
            $e = 'error';
        }
        else{
            $e = 'info';
        }
        $value =  '{"message":"'.$message.'","type":"'.$e.'"';
        if(strlen($otherJsonStr) != 0){
            echo $value . ','.$otherJsonStr.'}';
        }
        else{
            echo $value .'}';
        }
    }
    /**
     * Sends Back a data using specific content type.
     * @param type $conentType
     * @param type $data
     */
    public function send($conentType,$data){
        header('content-type:'.$conentType);
        echo $data;
    }
    /**
     * Returns an array of filtered request inputs.
     * @return array An array of filtered request inputs.
     * @since 1.0
     */
    public function getInputs(){
        return $this->filter->getInputs();
    }

    /**
     * Returns the action that was requested to perform.
     * @return string|NULL The action that was requested to perform. If the action 
     * is not set, the function will return <b>NULL</b>.
     * @since 1.0
     */
    public function getAction(){
        $i = $this->getInputs();
        if(isset($i['action'])){
            return $i['action'];
        }
        return NULL;
    }
}