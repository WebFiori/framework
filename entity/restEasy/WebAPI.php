<?php
/*
 * The MIT License
 *
 * Copyright 2018 Ibrahim BinAlshikh, rest-easy (v1.4.2).
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
namespace restEasy;
use jsonx\JsonI;
/**
 * A class that represents a REST API.
 * @version 1.4.2
 */
abstract class WebAPI implements JsonI{
    /**
     * An array that contains the supported 'POST' request content types.
     * This array has the following values:
     * <ul>
     * <li>application/x-www-form-urlencoded</li>
     * <li>multipart/form-data</li>
     * </ul>
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
     * An array which contains the missing required body parameters.
     * @var array
     * @since 1.4.1 
     */
    private $missingParamsArr;
    /**
     * An array which contains body parameters that have invalid values.
     * @var array
     * @since 1.4.1 
     */
    private $invParamsArr;
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
     * The version number of the API.
     * @var string
     * @since 1.0 
     */
    private $apiVersion;
    /**
     * A general description for the API.
     * @var string
     * @since 1.3 
     */
    private $apiDesc;
    /**
     * Creates new instance of the class.
     * By default, the API will have two actions added to it:
     * <ul>
     * <li>api-info</li>
     * <li>request-info</li>
     * </ul>
     * The first action is used to return a JSON string which contains 
     * all needed information by the front-end to implement the API. The user 
     * can supply an optional parameter with it which is called 'version' in 
     * order to get information about specific API version. The 
     * second action is used to get basic info about the request.
     * @param string $version initial API version. Default is '1.0.0'
     */
    public function __construct($version='1.0.0'){
        $this->setVersion($version);
        $this->setDescription('NO DESCRIPTION');
        $this->requestMethod = filter_var(getenv('REQUEST_METHOD'));
        $this->actions = array();
        $this->authActions = array();
        $this->filter = new APIFilter();
        $action = new APIAction();
        $action->setDescription('Gets all information about the API.');
        $action->setName('api-info');
        $action->addRequestMethod('get');
        $action->addParameter(new RequestParameter('version', 'string', TRUE));
        $action->getParameterByName('version')->setDescription('Optional parameter. '
                . 'If set, the information that will be returned will be specific '
                . 'to the given version number.');
        $this->addAction($action,TRUE);
        $action2 = new APIAction();
        $action2->setDescription('Gets basic information about the request.');
        $action2->setName('request-info');
        $action2->addRequestMethod('get');
        $action2->addRequestMethod('post');
        $action2->addRequestMethod('delete');
        $action2->addRequestMethod('put');
        $action2->addRequestMethod('head');
        $action2->addRequestMethod('options');
        $this->addAction($action2,TRUE);
        $this->invParamsArr = array();
        $this->missingParamsArr = array();
    }
    /**
     * Sets the description of the API.
     * @param sting $desc Action description. Used to help front-end to identify 
     * the use of the API.
     * @since 1.3
     */
    public function setDescription($desc) {
        $this->apiDesc = $desc;
    }
    /**
     * Returns the description of the API.
     * @return string|NULL The description of the API. If the description is 
     * not set, the function will return NULL.
     * @since 1.3
     */
    public function getDescription() {
        return $this->apiDesc;
    }
    /**
     * Sends a response message to indicate that a database error has occur.
     * This function will send back a JSON string in the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"message":"Database Error",<br/>
     * &nbsp;&nbsp;"type":"error",<br/>
     * &nbsp;&nbsp;"err-info":OTHER_DATA<br/>
     * }
     * </p>
     * In here, 'OTHER_DATA' can be a basic string or JSON string.
     * Also, The response will sent HTTP code 404 - Not Found.
     * @param JsonI|JsonX|string $info An object of type JsonI or 
     * JsonX that describe the error in more details. Also it can be a simple string 
     * or JSON string.
     * @since 1.0
     */
    public function databaseErr($info=''){
        if($info instanceof JsonI){
            $this->sendResponse('Database Error', TRUE, 404, '"err-info":'.$info->toJSON());
        }
        if($info instanceof JsonX){
            $this->sendResponse('Database Error', TRUE, 404, '"err-info":'.$info);
        }
        else{
            $this->sendResponse('Database Error', TRUE, 404, '"err-info":"'.$info.'"');
        }
    }
    /**
     * Sends a response message to indicate that a user is not authorized to 
     * do an API call.
     * This function will send back a JSON string in the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"message":"Not authorized",<br/>
     * &nbsp;&nbsp;"type":"error"<br/>
     * }
     * </p>
     * In addition to the message, The response will sent HTTP code 401 - Not Authorized.
     * @since 1.0
     */
    public function notAuth(){
        $this->sendResponse('Not authorized', TRUE, 401);
    }
    /**
     * Sends a response message to indicate that an action is not supported by the API.
     * This function will send back a JSON string in the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"message":"Action not supported",<br/>
     * &nbsp;&nbsp;"type":"error"<br/>
     * }
     * </p>
     * In addition to the message, The response will sent HTTP code 404 - Not Found.
     * @since 1.0
     */
    public function actionNotSupported(){
        $this->sendResponse('Action not supported', TRUE, 404);
    }
    /**
     * Sends a response message to indicate that request content type is 
     * not supported by the API.
     * This function will send back a JSON string in the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"message":"Content type not supported.",<br/>
     * &nbsp;&nbsp;"type":"error",<br/>
     * &nbsp;&nbsp;"request-content-type":"content_type"<br/>
     * }
     * </p>
     * In addition to the message, The response will sent HTTP code 404 - Not Found.
     * @since 1.1
     */
    public function contentTypeNotSupported($cType=''){
        $this->sendResponse('Content type not supported.', TRUE, 404,'"request-content-type":"'.$cType.'"');
    }
    /**
     * Sends a response message to indicate that request method is not supported.
     * This function will send back a JSON string in the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"message":"Method Not Allowed.",<br/>
     * &nbsp;&nbsp;"type":"error",<br/>
     * }
     * </p>
     * In addition to the message, The response will sent HTTP code 405 - Method Not Allowed.
     * @since 1.0
     */
    public function requestMethodNotAllowed(){
        $this->sendResponse('Method Not Allowed.', TRUE, 405);
    }
    /**
     * Sends a response message to indicate that an action is not implemented.
     * This function will send back a JSON string in the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"message":"Action not implemented.",<br/>
     * &nbsp;&nbsp;"type":"error",<br/>
     * }
     * </p>
     * In addition to the message, The response will sent HTTP code 404 - Not Found.
     * @since 1.0
     */
    public function actionNotImpl(){
        $this->sendResponse('Action not implemented.', TRUE, 404);
    }
    /**
     * Sends a response message to indicate that a request parameter is missing.
     * This function will send back a JSON string in the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"message":"The parameter 'param_name' is missing.",<br/>
     * &nbsp;&nbsp;"type":"error",<br/>
     * }
     * </p>
     * In addition to the message, The response will sent HTTP code 404 - Not Found.
     * @param string $paramName The name of the parameter.
     * @since 1.0
     */
    public function missingParam($paramName){
        $this->sendResponse('The parameter \''.$paramName.'\' is missing.', TRUE, 404);
    }
    /**
     * Returns an array that contains the names of missing required API 
     * parameters.
     * @return array An array that contains the names of missing required API 
     * parameters.
     * @since 1.4.1
     */
    public function getMissingParameters() {
        return $this->missingParamsArr;
    }
    /**
     * Returns an array that contains the names of API 
     * parameters that has invalid values.
     * @return array An array that contains the names of API 
     * parameters that has invalid values 
     * parameters.
     * @since 1.4.1
     */
    public function getInvalidParameters() {
        return $this->invParamsArr;
    }
    /**
     * Sends a response message to indicate that a request parameter or parameters are missing.
     * This function will send back a JSON string in the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"message":"The following required parameter(s) where missing from the request body: 'param_1', 'param_2', 'param_n'",<br/>
     * &nbsp;&nbsp;"type":"error",<br/>
     * }
     * </p>
     * In addition to the message, The response will sent HTTP code 404 - Not Found.
     * @since 1.3
     */
    public function missingParams(){
        $val = '';
        $paramsNamesArr = $this->getMissingParameters();
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
        $this->sendResponse('The following required parameter(s) where missing from the request body: '.$val.'.', TRUE, 404);
    }
    /**
     * Sends a response message to indicate that a request parameter(s) have invalid values.
     * This function will send back a JSON string in the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"message":"The following parameter(s) has invalid values: 'param_1', 'param_2', 'param_n'",<br/>
     * &nbsp;&nbsp;"type":"error"<br/>
     * }
     * </p>
     * In addition to the message, The response will sent HTTP code 404 - Not Found.
     * @since 1.3
     */
    public function invParams(){
        $val = '';
        $i = 0;
        $paramsNamesArr = $this->getInvalidParameters();
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
        $this->sendResponse('The following parameter(s) has invalid values: '.$val.'.', TRUE, 404);
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
     * @param string $val Version number (such as 1.0.0). Version number 
     * must be provided in the form 'x.x.x'.
     * @return boolean TRUE if set. FALSE otherwise.
     * @since 1.0
     */
    public final function setVersion($val){
        $nums = explode('.', $val);
        if(count($nums) == 3){
            foreach ($nums as $v) {
                $len = strlen($v);
                for($x = 0 ; $x < $len ; $x++){
                    if($v[$x] < '0' || $v[$x] > '9'){
                        return FALSE;
                    }
                }
            }
            $this->apiVersion = $val;
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Returns an API action given its name.
     * @param string $actionName The name of the action.
     * @return APIAction|NULL An object of type 'APIAction' 
     * if the action is found. If no action was found, The function will return 
     * NULL.
     * @since 1.3
     */
    public function &getActionByName($actionName) {
        $actionName .= '';
        if(strlen($actionName) != 0){
            foreach ($this->getActions() as $action){
                if($action->getName() == $actionName){
                    return $action;
                }
            }
            foreach ($this->getAuthActions() as $action){
                if($action->getName() == $actionName){
                    return $action;
                }
            }
        }
        $null = NULL;
        return $null;
    }
    /**
     * Returns an array of supported API actions.
     * @return array An array that contains an objects of type APIAction. 
     * The actions on the returned array does not require authentication.
     * @since 1.0
     */
    public final function getActions(){
        return $this->actions;
    }
    /**
     * Returns an array of supported API actions.
     * @return array An array that contains an objects of type APIAction. 
     * The array will contains the actions 
     * that require authentication.
     * @since 1.0
     */
    public final function getAuthActions(){
        return $this->authActions;
    }
    /**
     * Adds new action to the set of API actions.
     * @param APIAction $action The action that will be added.
     * @param boolean $reqPermissions Set to TRUE if the action require user login or 
     * any additional permissions.
     * @return boolean TRUE if the action is added. FAlSE otherwise.
     * @since 1.0
     */
    public function addAction(&$action,$reqPermissions=false){
        if($action instanceof APIAction){
            if(!in_array($action, $this->getActions()) && !in_array($action, $this->getAuthActions())){
                $action->setSince($this->getVersion());
                if($reqPermissions == TRUE){
                    array_push($this->authActions, $action);
                }
                else{
                    array_push($this->actions, $action);
                }
                return TRUE;
            }
        }
        return FALSE;
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
     * @return JsonX An object of type JsonX.
     * @since 1.0
     */
    public function toJSON() {
        $json = new JsonX();
        $json->add('api-version', $this->getVersion());
        $json->add('method', $this->getRequestMethod());
        $json->add('description', $this->getDescription());
        $reqMeth = $this->getRequestMethod();
        if($reqMeth == 'GET' || $reqMeth == 'DELETE' || $reqMeth == 'PUT'){
            $vNum = filter_input(INPUT_GET, 'version');
        }
        else if($reqMeth == 'POST'){
            $vNum = filter_input(INPUT_POST, 'version');
        }
        if($vNum == NULL || $vNum == FALSE){
            $json->add('actions', $this->getActions());
            $json->add('auth-actions', $this->getAuthActions());
        }
        else{
            $actions = array();
            foreach ($this->getActions() as $a){
                if($a->getSince() == $vNum){
                    array_push($actions, $a);
                }
            }
            $authActions = array();
            foreach ($this->getAuthActions() as $a){
                if($a->getSince() == $vNum){
                    array_push($authActions, $a);
                }
            }
            $json->add('actions', $actions);
            $json->add('auth-actions', $authActions);
        }
        
        
        return $json;
    }
    /**
     * Checks if the action that is used to fetch the API is supported or not.
     * @return boolean TRUE if the API supports the action. FALSE 
     * if not or it is not set. The action name must be provided with the request 
     * as a parameter with the name 'action'.
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
     * @return boolean Returns TRUE in case the 'content-type' header is not 
     * set or the request method is not 'POST'. Also the function will return 
     * TRUE if the content type is supported. Other than that, the function 
     * will return FALSE
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
     * Checks if a client is authorized to call the API using the given 
     * action in request body.
     * @return boolean The function will return TRUE if the client is allowed 
     * to call the API using the action in request body.
     * @since 1.3.1
     */
    private function _isAuthorizedAction(){
        $action = $this->getAction();
        foreach ($this->getAuthActions() as $val){
            if($val->getName() == $action){
                return $this->isAuthorized();
            }
        }
        return TRUE;
    }
    /**
     * Checks the status of the API action.
     * This function checks if the following conditions are met:
     * <ul>
     * <li>The parameter "action" is set in request body.</li>
     * <li>The action is supported by the API.</li>
     * <li>Request method of the action is correct.</li>
     * </ul>
     * If one of the conditions is not met, the function will return FALSE and 
     * send back a response to indicate the issue.
     * @return boolean TRUE if API action is valid.
     * @since 1.0
     */
    private final function _checkAction(){
        $action = $this->getAction();
        //first, check if action is set and not null
        if($action != NULL){
            //after that, check if action is supported by the API.
            if($this->isActionSupported()){
                $isValidRequestMethod = FALSE;
                foreach ($this->getAuthActions() as $val){
                    if($val->getName() == $action){
                        $reqMethods = $val->getActionMethods();
                        foreach ($reqMethods as $method){
                            if($method == $this->getRequestMethod()){
                                $isValidRequestMethod = TRUE;
                            }
                        }
                        if(!$isValidRequestMethod){
                            $this->requestMethodNotAllowed();
                        }
                        return $isValidRequestMethod;
                    }
                }
                foreach ($this->getActions() as $val){
                    if($val->getName() == $action){
                        $reqMethods = $val->getActionMethods();
                        foreach ($reqMethods as $method){
                            if($method == $this->getRequestMethod()){
                                $isValidRequestMethod = TRUE;
                            }
                        }
                        if(!$isValidRequestMethod){
                            $this->requestMethodNotAllowed();
                        }
                        return $isValidRequestMethod;
                    }
                }
                return TRUE;
            }
            else{
                $this->actionNotSupported();
            }
        }
        else{
            $this->missingAPIAction();
        }
        return FALSE;
    }
    /**
     * Sends a response message to tell the front-end that the parameter 
     * 'action' is missing from request body.
     * This function will send back a JSON string in the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"message":"Action is not set.",<br/>
     * &nbsp;&nbsp;"type":"error"<br/>
     * }
     * </p>
     * In addition to the message, The response will sent HTTP code 404 - Not Found.
     * @since 1.3.1
     */
    public function missingAPIAction() {
        $this->sendResponse('Action is not set.', TRUE, 404);
    }
    /**
     * Checks if a user is authorized to perform an action that require authorization.
     * @return boolean The function must be implemented by the sub-class in a way 
     * that makes it return TRUE in case the user is allowed to perform the 
     * action. If the user is not permitted, the function must return FALSE.
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
     * This function must be called after creating any 
     * new instance of the API in order to process user request.
     * @since 1.0
     */
    public final function process(){
        $this->invParamsArr = array();
        $this->missingParamsArr = array();
        if($this->isContentTypeSupported()){
            if($this->_checkAction()){
                $actionObj = $this->getActionByName($this->getAction());
                $params = $actionObj->getParameters();
                $this->filter->clear();
                foreach ($params as $param) {
                    $this->filter->addRequestPaameter($param);
                }
                $reqMeth = $this->getRequestMethod();
                if($reqMeth == 'GET' || $reqMeth == 'DELETE' || $reqMeth == 'PUT'){
                    $this->filter->filterGET();
                }
                else if($reqMeth == 'POST'){
                    $this->filter->filterPOST();
                }
                $i = $this->getInputs();
                $processReq = TRUE;
                foreach ($params as $param) {
                    if(!$param->isOptional()){
                        if(!isset($i[$param->getName()])){
                            array_push($this->missingParamsArr, $param->getName());
                            $processReq = FALSE;
                        }
                    }
                    if(isset($i[$param->getName()]) && $i[$param->getName()] === 'INV'){
                        array_push($this->invParamsArr, $param->getName());
                        $processReq = FALSE;
                    }
                }
                if($processReq){
                    if($this->_isAuthorizedAction()){
                        if($this->getAction() == 'api-info'){
                            $this->send(self::MIME_TYPES['json'], $this->toJSON());
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
                    else{
                        $this->notAuth();
                    }
                }
                else{
                    if(count($this->missingParamsArr) != 0){
                        $this->missingParams();
                    }
                    else if(count($this->invParamsArr) != 0){
                        $this->invParams();
                    }
                }
            }
        }
        else{
            $this->contentTypeNotSupported($this->getContentType());
        }
    }
    /**
     * Sends a JSON response to the client.
     * The basic format of the message will be as follows:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"message":"Action is not set.",<br/>
     * &nbsp;&nbsp;"type":"error"<br/>
     * }
     * If other JSON string is given, It will be included in the response.
     * @param string $message The message to send back.
     * @param boolean $isErr TRUE if the message represents an error state.
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
     * Sends Back a data using specific content type using HTTP code 200 - Ok.
     * @param string $conentType Response content type (such as 'application/json')
     * @param type $data Any data to send back (it can be a file, a string ...).
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
     * is not set, the function will return NULL.
     * @since 1.0
     */
    public function getAction(){
        $reqMeth = $this->getRequestMethod();
        if($reqMeth == 'GET' || $reqMeth == 'DELETE' || $reqMeth == 'PUT'){
            if(isset($_GET['action'])){
                return filter_var($_GET['action']);
            }
        }
        else if($reqMeth == 'POST'){
            if(isset($_POST['action'])){
                return filter_var($_POST['action']);
            }
        }
        return NULL;
    }
}