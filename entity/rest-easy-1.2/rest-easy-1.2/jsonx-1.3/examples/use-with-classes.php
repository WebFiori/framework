<?php
//show errors
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//load required files
require_once '../JsonX.php';
require_once '../JsonI.php';


//defining a class user
//in order for the class to be added in JsonX object,
//it must implement the interface JsonI
class User implements JsonI{
    private $username;
    private $email;
    
    public function getUserName(){
        return $this->username;
    }
    public function setUsername($username){
        $this->username = $username;
    }

    public function setEmail($email){
        $this->email = $email;
    }

    //this function is from the interface JsonI
    // it must return JsonX object
    public function toJSON(){
        $retVal = new JsonX();
        $retVal->addString('username', $this->username);
        $retVal->addString('email', $this->email);
        return $retVal;
    }
}
$user = new User();
$user->setEmail('example@example.com');
$user->setUsername('Warrior Vx');

$json = new JsonX();
$json->addBoolean('my-boolean');
$json->addObject('user', $user);

//adding arrays
$json->addArray('my-arr', array(1,2,"hello",array("nested array")));

header('content-type:application/json');
//display json object in the browser.
echo $json;
