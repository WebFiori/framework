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
 * @version 1.0
 */
class API implements JsonI{
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
     */
    private $settionMngr;
    private $apiVersion;
    
    public function __construct(){
        $this->requestMethod = filter_var(getenv('REQUEST_METHOD'));
        $this->actions = array();
        $this->authActions = array();
        $this->settionMngr = SessionManager::get();
        $this->filter = new APIFilter();
        $action = new APIAction();
        $action->setName('api-info');
        $action->setActionMethod('GET or POST');
        $this->addAction($action);
        $this->filter->addParameter('action', 'string');
        $this->setVirsion('0.0.0');
    }
    /**
     * 
     * @return SessionManager
     * @since 1.0
     */
    public function getSession(){
        return $this->settionMngr;
    }
    /**
     * Sends a response message to indicate that a database error has occur.
     * @since 1.0
     */
    public function databaseErr(){
        $this->sendResponse('Database Error', TRUE, 404, $this->getSession()->getDBLink()->toJSON());
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
        $this->sendResponse('Not supported', TRUE, 404);
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
        $this->sendResponse('Not implemented', TRUE, 404);
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
    public function getVersion(){
        return $this->apiVersion;
    }
    /**
     * Sets API version number.
     * @param string $val Version number.
     * @since 1.0
     */
    public function setVirsion($val){
        $this->apiVersion = $val;
    }

    /**
     * Returns an array of supported API actions.
     * @return array An array of supported API actions.
     * @since 1.0
     */
    public function getActions(){
        return $this->actions;
    }
    /**
     * Returns an array of supported API actions. The array will contains the actions 
     * that require authentication.
     * @return array An array of supported API actions.
     * @since 1.0
     */
    public function getAuthActions(){
        return $this->authActions;
    }
    /**
     * Adds new action to the set of API actions.
     * @param string $action The action that will be added.
     * @param boolean $reqPermissions <b>TRUE</b> if the action require user login.
     * @return boolean <b>TRUE</b> if the action is added. <b>FAlSE</b> otherwise.
     * @since 1.0
     */
    public function addAction($action='',$reqPermissions=false){
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
    public function getRequestMethod(){
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
    public function isActionSupported(){
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
     * Checks the status of the API action. It checks if the action is supported by 
     * the API or not. After that, it checks if the user is permitted to perform the 
     * action or not. 
     * @return boolean <b>TRUE</b> if nothing wrong with the action.
     * @since 1.0
     */
    private function checkAction(){
        $action = $this->getAction();
        if($action != NULL){
            if($this->isActionSupported()){
                foreach ($this->getAuthActions() as $val){
                    if($val->getName() == $action){
                        if($this->getSession()->validateToken()){
                            if($val->getActionMethod() != $this->getRequestMethod()){
                                $this->requMethNotSupported();
                                return FALSE;
                            }
                            else{
                                return TRUE;
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
                        if($val->getActionMethod() != $this->getRequestMethod()){
                            $this->requMethNotSupported();
                            return FALSE;
                        }
                        else{
                            return TRUE;
                        }
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
     * Process user request.
     * @param string $callback A name of a callback function. It must be a function 
     * in the child class.
     * @since 1.0
     */
    public function process($callback='funcName'){
        $reqMeth = $this->getRequestMethod();
        if($reqMeth == 'GET' || $reqMeth == 'DELETE'){
            $this->filter->filterGET();
        }
        else if($reqMeth == 'POST'){
            $this->filter->filterPOST();
        }
        if($this->checkAction()){
            if($this->getAction() == 'api-info'){
                echo $this->toJSON();
            }
            else{
                $methodVariable = array($this, $callback);
                if(is_callable($methodVariable, FALSE,$callback)){
                    call_user_func(array($this, $callback), $this->filter->getInputs());
                }
                else{
                    $this->sendResponse('Nothing to process.');
                }
            }
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
     * Returns an array of filtered request inputs.
     * @return array An array of filtered request inputs.
     * @since 1.0
     */
    public function getInputs(){
        return $this->filter->getInputs();
    }

    /**
     * Returns the action that was requested to perform.
     * @return string The action that was requested to perform.
     * @since 1.0
     */
    public function getAction(){
        return $this->filter->getInputs()['action'];
    }
}