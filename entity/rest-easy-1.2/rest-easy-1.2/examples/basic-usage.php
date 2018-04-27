<?php
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
require_once '../jsonx-1.3/JsonI.php';
require_once '../jsonx-1.3/JsonX.php';
require_once '../API.php';
require_once '../APIAction.php';
require_once '../APIFilter.php';
require_once '../RequestParameter.php';
/*
 * Steps for creating new API:
 * 1- Create a class that extends the class 'API'.
 * 2- Implement 'isAuthorized()' function.
 * 3- Implement 'processRequest()' function.
 * 4- Create an instance of the class.
 * 5- Call the function 'process()'.
 */
class MyAPI extends API{
    
    public function __construct() {
        parent::__construct();
        //customize the API as you need here.
        //add actions, parameters for 'GET' or 'POST'
        
        //create new action
        $action = new APIAction();
        $action->setName('my-action');
        $action->addRequestMethod('get',TRUE);
        
        //add parameters for the action
        $action->addParameter(new RequestParameter('my-param', 'string', TRUE));
        
        //add the action to the API
        $this->addAction($action);
        
        //calling process in the constructor
        $this->process();
    }
    
    public function isAuthorized(){
        return TRUE;
    }
    
    public function processRequest(){
        header('content-type:text/plain');
        $inputs = $this->getInputs();
        if(isset($inputs['my-param'])){
            echo '"my-param" = '.$inputs['my-param'];
        }
        else{
            echo '"my-param" is not set';
        }
    }
}
//create an instance once the file is called. 
$a = new MyAPI();
