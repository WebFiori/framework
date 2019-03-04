<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim BinAlshikh, restEasy library.
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
use jsonx\JsonX;
/**
 * A class that represents a REST API.
 * This class is used to create web services.
 * In order to create a simple web service, the developer must 
 * follow the following steps:
 * <ul>
 * <li>Extend this class.</li>
 * <li>Create API actions using the class APIAction.</li>
 * <li>Implement the abstract method <a href="#isAuthorized">WebAPI::isAuthorized?()</a> 
 * and the method <a href="#processRequest">WebAPI::processRequest()</a></li>
 * </li>
 * @version 1.4.3
 */
abstract class WebAPI implements JsonI{
    /**
     * An array that contains the supported 'POST' request content types.
     * This array has the following values:
     * <ul>
     * <li>application/x-www-form-urlencoded</li>
     * <li>multipart/form-data</li>
     * </ul>
     * @var array An array that contains the supported 'POST' and 'PUT' request content types.
     * @since 1.1
     */
    const POST_CONTENT_TYPES = array(
        'application/x-www-form-urlencoded',
        'multipart/form-data'
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
     * not set, the method will return NULL.
     * @since 1.3
     */
    public function getDescription() {
        return $this->apiDesc;
    }
    /**
     * Sends a response message to indicate that a database error has occur.
     * This method will send back a JSON string in the following format:
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
        $this->sendResponse('Database Error', 'error', 500, $info);
    }
    /**
     * Sends a response message to indicate that a user is not authorized to 
     * do an API call.
     * This method will send back a JSON string in the following format:
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
        $this->sendResponse('Not authorized', 'error', 401);
    }
    /**
     * Sends a response message to indicate that an action is not supported by the API.
     * This method will send back a JSON string in the following format:
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
        $this->sendResponse('Action not supported', 'error', 404);
    }
    /**
     * Sends a response message to indicate that request content type is 
     * not supported by the API.
     * This method will send back a JSON string in the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"message":"Content type not supported.",<br/>
     * &nbsp;&nbsp;"type":"error",<br/>
     * &nbsp;&nbsp;"request-content-type":"content_type"<br/>
     * }
     * </p>
     * In addition to the message, The response will sent HTTP code 404 - Not Found.
     * @param string $cType The value of the header 'content-type' taken from 
     * request header.
     * @since 1.1
     */
    public function contentTypeNotSupported($cType=''){
        $this->sendResponse('Content type not supported.', 'error', 404,'"request-content-type":"'.$cType.'"');
    }
    /**
     * Sends a response message to indicate that request method is not supported.
     * This method will send back a JSON string in the following format:
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
        $this->sendResponse('Method Not Allowed.', 'error', 405);
    }
    /**
     * Sends a response message to indicate that an action is not implemented.
     * This method will send back a JSON string in the following format:
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
        $this->sendResponse('Action not implemented.', 'error', 404);
    }
    /**
     * Sends a response message to indicate that a request parameter is missing.
     * This method will send back a JSON string in the following format:
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
        $this->sendResponse('The parameter \''.$paramName.'\' is missing.', 'error', 404);
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
     * This method will send back a JSON string in the following format:
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
        $this->sendResponse('The following required parameter(s) where missing from the request body: '.$val.'.', 'error', 404);
    }
    /**
     * Sends a response message to indicate that a request parameter(s) have invalid values.
     * This method will send back a JSON string in the following format:
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
        $this->sendResponse('The following parameter(s) has invalid values: '.$val.'.', 'error', 404);
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
     * if the action is found. If no action was found, The method will return 
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
     * any additional permissions. Default is FALSE.
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
     * Returns request content type.
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
     * and PUT requests only).
     * @return boolean Returns TRUE in case the 'content-type' header is not 
     * set or the request method is not 'POST'. Also the method will return 
     * TRUE if the content type is supported. Other than that, the method 
     * will return FALSE
     * @since 1.1
     */
    public final function isContentTypeSupported(){
        $c = $this->getContentType();
        $rm = $this->getRequestMethod();
        if($c != NULL && $rm == 'POST' || $rm == 'PUT'){
            return in_array($c, self::POST_CONTENT_TYPES);
        }
        return TRUE;
    }
    /**
     * Checks if a client is authorized to call the API using the given 
     * action in request body.
     * @return boolean The method will return TRUE if the client is allowed 
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
     * This method checks if the following conditions are met:
     * <ul>
     * <li>The parameter "action" is set in request body.</li>
     * <li>The action is supported by the API.</li>
     * <li>Request method of the action is correct.</li>
     * </ul>
     * If one of the conditions is not met, the method will return FALSE and 
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
     * This method will send back a JSON string in the following format:
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
        $this->sendResponse('Action is not set.', 'error', 404);
    }
    /**
     * Checks if a user is authorized to perform an action that require authorization.
     * @return boolean The method must be implemented by the sub-class in a way 
     * that makes it return TRUE in case the user is allowed to perform the 
     * action. If the user is not permitted, the method must return FALSE.
     * @since 1.1
     */
    public abstract function isAuthorized();
    /**
     * A method that is used to process the requested action.
     * @since 1.1
     */
    public abstract function processRequest();
    /**
     * Process user request. 
     * This method must be called after creating any 
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
                            $this->send('application/json', $this->toJSON());
                        }
                        else if($this->getAction() == 'request-info'){
                            $j = new JsonX();
                            $j->add('action', $this->getAction());
                            $j->add('content-type', $this->getContentType());
                            $j->add('method', $this->getRequestMethod());
                            $j->add('parameters', $this->getInputs());
                            $this->send('application/json', $j);
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
     * &nbsp;&nbsp;"more-info":EXTRA_INFO<br/>
     * }
     * </p>
     * Where EXTRA_INFO can be a simple string or any JSON data.
     * @param string $message The message to send back.
     * @param string $type A string that tells the client what is the type of 
     * the message. The developer can specify his own message types such as 
     * 'debug', 'info' or any string. If it is empty string, it will be not 
     * included in response payload.
     * @param int $code Response code (such as 404 or 200). Default is 200.
     * @param string|JsonX|JsonI $otherInfo Any other data to send back it can be a simple 
     * string, an object of type JsonX or JsonI. Default is empty string.
     * @since 1.0
     */
    public function sendResponse($message,$type='',$code=200,$otherInfo=''){
        header('content-type:application/json');
        http_response_code($code);
        $value =  '{"message":"'.$message.'"';
        if(strlen($type) !== 0){
            $value .= ',"type":"'.JsonX::escapeJSONSpecialChars($type).'"';
        }
        if($otherInfo instanceof JsonX){
            echo $value . ',"more-info":'.$otherInfo.'}';
        }
        else if($otherInfo instanceof JsonI){
            echo $value . ',"more-info":'.$otherInfo->toJSON().'}';
        }
        else{
            if(strlen($otherInfo) != 0){
                echo $value . ',"more-info":"'.JsonX::escapeJSONSpecialChars($otherInfo).'"}';
            }
            else{
                echo $value .'}';
            }
        }
        die();
    }
    /**
     * Sends Back a data using specific content type using HTTP code 200 - Ok.
     * @param string $conentType Response content type (such as 'application/json')
     * @param mixed $data Any data to send back. Mostly, it will be a string.
     */
    public function send($conentType,$data){
        header('content-type:'.$conentType);
        echo $data;
        die();
    }
    /**
     * Sends back multiple HTTP headers to the client.
     * @param array $headersArr An associative array. The keys will act as 
     * the headers names and the value of each key will represents the value 
     * of the header.
     * @since 1.4.3
     */
    public function sendHeaders($headersArr) {
        if(gettype($headersArr) == 'array'){
            foreach ($headersArr as $header => $val){
                header($header.':'.$val);
            }
        }
    }
    /**
     * Returns an associative array of filtered request inputs.
     * The indices of the array will represent request parameters and the 
     * values of each index will represent the value which was set in 
     * request body. The values will be filtered and might not be exactly the same as 
     * the values passed in request body.
     * @return array An array of filtered request inputs.
     * @since 1.0
     */
    public function getInputs(){
        return $this->filter->getInputs();
    }
    /**
     * Returns an associative array of non-filtered request inputs.
     * The indices of the array will represent request parameters and the 
     * values of each index will represent the value which was set in 
     * request body. The values will be exactly the same as 
     * the values passed in request body.
     * @return array An array of request parameters.
     * @since 1.4.3
     */
    public function getNonFiltered(){
        return $this->filter->getNonFiltered();
    }
    /**
     * Returns the action that was requested to perform.
     * @return string|NULL The action that was requested to perform. If the action 
     * is not set, the method will return NULL.
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