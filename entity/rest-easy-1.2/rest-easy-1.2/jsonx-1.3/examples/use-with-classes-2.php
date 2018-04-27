<?php
//show errors
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//load required files
require_once '../JsonX.php';
require_once '../JsonI.php';


//defining a class user
//In this example, the class does not 
//implement the interface 'JsonI'.
//In this case, the generated JSON object will contain
//the information returmed by the public methods.
class User{
    private $username;
    private $email;
    
    public function getEmail(){
        return $this->email;
    }
    public function getUserName(){
        return $this->username;
    }
    public function setUsername($username){
        $this->username = $username;
    }

    public function setEmail($email){
        $this->email = $email;
    }
}

$user = new User();
$user->setEmail('example@example.com');
$user->setUsername('Warrior Vx');
$json = new JsonX();
$json->addObject('user', $user);
header('content-type:application/json');
echo $json;
