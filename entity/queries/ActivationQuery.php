<?php
/**
 *
 * @author Ibrahim
 * @version 1.0
 */
class ActivationQuery extends MySQLQuery{
    /**
     *
     * @var Table 
     * @since 1.0
     */
    private $structure;
    /**
     * Initialization
     */
    private function init(){
        $this->structure = new Table('activation_codes');
        $this->structure->addColumn('code', new Column('code', 'varchar', 64));
        $this->structure->addColumn('user-id', new Column('user_id','int',11));
        $this->structure->getCol('user-id')->setIsUnique(TRUE);
        
        //add a foreign key
        $userQ = new UserQuery();
        $key = new ForeignKey();
        $key->setSourceCol($this->structure->getCol('user-id')->getName());
        $key->setReferenceCol(self::ID_COL);
        $key->setReferenceTable($userQ->getStructure()->getName());
        $key->setOnDelete('cascade');
        $key->setOnUpdate('cascade');
        $key->setKeyName('activation_fk');
        $this->structure->addForeignKey($key);
    }
    /**
     * Constructs a query that can be used to create the table.
     * @since 1.0
     */
    public function createStructure(){
        $this->createTable($this->getStructure());
    }
    /**
     * Returns the table that is linked with query operations.
     * @return Table an object of type <b>Table</b>.
     * @since 1.0
     */
    public function getStructure(){
        return $this->structure;
    }
    
    public function __construct() {
        parent::__construct();
        $this->init();
    }
    /**
     * Constructs a query that can be used to remove a user from the set of 
     * inactive users. 
     * @param string $userId The ID of the user.
     * @since 1.0
     */
    public function activate($userId) {
        $this->delete(
                $this->getStructure()->getName(), 
                $userId,
                $this->getStructure()->getCol('user-id')->getName());
    }
    /**
     * Constructs a query that can be used to get a user activation code.
     * @param string $userId The ID of the user.
     * @since 1.0
     */
    public function getActivationCode($userId) {
        $this->selectByColVal(
                $this->getStructure()->getName(), 
                $this->getStructure()->getCol('user-id')->getName(), 
                $userId);
    }
    /**
     * Constructs a query that can be used to add new record to the activation codes 
     * table.
     * @param string $id The ID of the user.
     * @since 1.0
     */
    public function addNew($id){
        $arr = array(
        $this->getStructure()->getCol('user-id')->getName()=>$id,
        $this->getStructure()->getCol('code')->getName()=>'\''.hash('sha256',date('Y-m-d h:i:s')).'\''
        );
        $this->insert($this->getStructure()->getName(), $arr);
    }
}
