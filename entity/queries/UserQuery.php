<?php
/**
 * A class that can be used to constructs different queries that is related to 
 * user.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.4
 */
class UserQuery extends MySQLQuery{
    /**
     * The structure of the users table.
     * @var Table
     * @since 1.1 
     */
    private $structure;
    /**
     * Initialize the object.
     * @since 1.1
     */
    private function init(){
        $this->structure = new Table();
        $this->structure->setName('users');
        $this->structure->setOrder(0);
        //id column
        $this->structure->addColumn(self::ID_COL, new Column(self::ID_COL, 'int', 11));
        $this->structure->getCol(self::ID_COL)->setIsPrimary(TRUE);
        $this->structure->getCol(self::ID_COL)->setIsAutoInc(TRUE);
        
        //username column
        $this->structure->addColumn('username', new Column('username', 'varchar', 30));
        $this->getCol('username')->setIsUnique(TRUE);
        
        //password column
        $this->structure->addColumn('password', new Column('pass', 'varchar', 64));
        
        //email column
        $this->structure->addColumn('email', new Column('email', 'varchar', 100));
        
        //display name column
        $this->structure->addColumn('disp-name', new Column('disp_name', 'varchar', 70));
        
        //status column
        $this->structure->addColumn('status', new Column('status', 'varchar', 1));
        
        //access level column
        $this->structure->addColumn('acc-level', new Column('acc_level', 'varchar', 1));
        
        //registration date column
        $this->structure->addColumn('reg-date', new Column('reg_date', 'timestamp'));
        $this->getCol('reg-date')->setDefault('');
        
        //last login column
        $this->structure->addColumn('last-login', new Column('last_login', 'timestamp'));
        $this->structure->getCol('last-login')->autoUpdate();
    }
    /**
     * Returns the table that is linked with query operations.
     * @return Table
     * @since 1.1
     */
    public function getStructure(){
        return $this->structure;
    }
    
    public function __construct() {
        parent::__construct();
        $this->init();
    }
    /**
     * Constructs a query that can be used to update 
     * the access level of a user given his ID.
     * @param string $new The new access level.
     * @param string $id The ID of the user.
     * @since 1.3
     */
    public function updateAccessLevel($new, $id){
        $arr = array($this->getColName('acc-level')=>$new);
        $this->update($arr, $id);
    }
    /**
     * Constructs a query that can be used to update 
     * the display name of a user given his ID.
     * @param string $newName The new display name.
     * @param string $id The ID of the user.
     * @since 1.3
     */
    public function updateDisplayName($newName, $id){
        $arr = array($this->getColName('disp-name')=>'\''.$newName.'\'');
        $this->update($arr, $id);
    }
    /**
     * Constructs a query that can be used to update the status of a user.
     * @param string $newStatus The new status.
     * @param string $id The ID of the user.
     * @since 1.3
     */
    public function updateStatus($newStatus,$id){
        $arr = array($this->getColName('status')=>'\''.$newStatus.'\'');
        $this->update($this->getStructure()->getName(), $arr, $id);
    }
    /**
     * Constructs a query that can be used to get a user given his ID.
     * @param string $id The ID of the user.
     * @since 1.0
     */
    public function getUserByID($id){
        $this->selectByID($id);
    }
    /**
     * Constructs a query that can be used to get user information given his email 
     * address.
     * @param string $email The email address of the user.
     * @since 1.0
     */
    public function getUserByEmail($email) {
        $this->selectByColVal($this->getColName('email'),'\''.$email.'\'');
    }
    /**
     * Constructs a query that can be used to get user information given his 
     * username.
     * @param string $username The username of the user.
     * @since 1.0
     */
    public function getUserByUsername($username){
        $this->selectByColVal($this->getColName('username'),'\''. $username.'\'');
    }
    /**
     * Constructs a query that can be used to update the value of the column 
     * 'last_login' of the users table given a user ID.
     * @param string $id The ID of the user.
     * @since 1.0
     */
    public function updateLastLogin($id){
        $date = date('Y-m-d h:i:s');
        $arr = array($this->getColName('last-login')=>'\''.$date.'\'');
        $this->update($arr, $id);
    }
    /**
     * Constructs a query that can be used to insert a new user.
     * @param User $user An object of type <b>User</b>
     * @since 1.2
     */
    public function addUser($user) {
        if($user instanceof User){
            $arr = array(
                $this->getColName('username')=>'\''.$user->getUserName().'\'',
                $this->getColName('password')=>'\''.$user->getPassword().'\'',
                $this->getColName('email')=>'\''.$user->getEmail().'\'',
                $this->getColName('acc-level')=>$user->getAccessLevel(),
                $this->getColName('disp-name')=>'\''.$user->getDisplayName().'\'',
                $this->getColName('last-login')=>'\''. date('Y-m-d h:i:s').'\'',
                $this->getColName('status')=>'\'N\''
            );
            $this->insert($arr);
        }
    }
    /**
     * Updates the value of the column 'email' given user ID.
     * @param string $newMail The new email address of the user.
     * @param string $id The ID of the user.
     * @since 1.0
     */
    public function updateEmail($newMail,$id){
        $arr = array($this->getColName('email')=>'\''.$newMail.'\'');
        $this->update($arr, $id);
    }
    /**
     * Constructs a query that can be used to update the password of a user given 
     * his ID.
     * @param string $newPassHashed The new password.
     * @param string $id The ID of the user.
     * @since 1.0
     */
    public function updatePassword($newPassHashed,$id) {
        $arr = array($this->getColName('password')=>'\''.$newPassHashed.'\'');
        $this->update($arr, $id);
    }
    /**
     * Constructs a query that can be used to get all system users.
     * @since 1.0
     */
    public function getUsers(){
        $this->selectAll();
    }
}
