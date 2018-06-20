<?php

/*
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
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
 * A class that can be used to constructs different queries that is related to 
 * password resetting.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class PasswordResetQuery extends MySQLQuery{
    private $structure;
    /**
     * Creates new instance.
     * @since 1.0
     */
    public function __construct() {
        parent::__construct();
        $this->structure = new Table('pass_reset');
        $this->structure->addColumn('user-id', new Column('user_id', 'int', 11));
        $this->structure->getCol('user-id')->setIsPrimary(TRUE);
        $this->structure->addColumn('password-reset-token', new Column('pass_reset_tok', 'varchar', 64));
        $this->structure->getCol('password-reset-token')->setIsNull(TRUE);
        $this->structure->addColumn('request-time', new Column('date', 'timestamp'));
        $this->structure->getCol('request-time')->setDefault('');
    }
    /**
     * Constructs a query that is used to insert new record in the 
     * table 'pass_reset'.
     * @param User $user An object of type <b>User</b>.
     * @since 1.0
     */
    public function add($user) {
        $arr = array(
            $this->getColName('user-id')=>$user->getID(),
            $this->getColName('password-reset-token')=>'\''.$user->getResetToken().'\''
        );
        $this->insert($arr);
    }
    /**
     * Constructs a query that can be used to get user reset record.
     * @param int $userId The ID of the user.
     * @since 1.0
     */
    public function get($userId) {
        $this->selectByColVal($this->getColName('user-id'), $userId);
    }
    /**
     * Constructs a query that can be used to remove user reset record.
     * @param int $userId The ID of the user.
     * @since 1.0
     */
    public function remove($userId) {
        $this->delete($userId, $this->getColName('user-id'));
    }
    /**
     * Returns the table that is used for constructing queries.
     * @return Table The table that is used for constructing queries.
     * @since 1.0
     */
    public function getStructure() {
        return $this->structure;
    }

}
