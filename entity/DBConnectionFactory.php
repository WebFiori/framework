<?php

/*
 * The MIT License
 *
 * Copyright 2018 ibrah.
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
 * A factory class that is used to create connections to different types of 
 * DBMSs.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class DBConnectionFactory {
    /**
     * A constant that indicates the name of database host is missing.
     * @var string Constant that indicates the name of database host is missing.
     * @since 1.0
     */
    const MISSING_DB_HOST = 'missing_db_host';
    /**
     * A constant that indicates the name of the database is missing.
     * @var string Constant that indicates the name of the database is missing.
     * @since 1.0
     */
    const MISSING_DB_NAME = 'missing_db_name';
    /**
     * A constant that indicates username of the database is missing.
     * @var string Constant that indicates username of the database is missing.
     * @since 1.0
     * @see 
     */
    const MISSING_DB_USER = 'missing_db_user';
    /**
     * A constant that indicates the user password of the database is missing.
     * @var string Constant that indicates the user password of the database is missing.
     * @since 1.0
     * @see 
     */
    const MISSING_DB_PASS = 'missing_db_password';
    /**
     * A constant that indicates a database connection error has occur.
     * @var string Constant that indicates a database connection error has occur.
     * @since 1.0
     * @see 
     */
    const DB_CONNECTION_ERR = 'unable_to_connect_to_db';
    /**
     * Create a link to MySQL database.
     * @param array $connectionParams An associative array that contains 
     * database connection parameters. The indices are: 
     * <ul>
     * <li><b>'host'</b>: Database host address.</li>
     * <li><b>'user'</b>: Database username.</li>
     * <li><b>'pass'</b>: Database user's password.</li>
     * <li><b>'db-name'</b>: The name of the database (Schema name).</li>
     * </ul>
     * @return array|DatabaseLink If the connection to the database was 
     * established, the function will return an instance of 'DatabaseLink'. 
     * If something went wrong while attempting to connect, an associative 
     * array is returned which contains error details. The array has two 
     * indices: 
     * <ul>
     * <li><b>error-code</b>: Error code. It can be MySQL error code.</li>
     * <li><b>error-code</b>: A message that tels more information about 
     * the error.</li>
     * </ul>
     * @since 1.0
     */
    public static function mysqlLink($connectionParams=array()){
        Logger::logFuncCall(__METHOD__);
        $retVal = array(
            'error-code'=>'',
            'error-message'=>''
        );
        Logger::log('Checking if session is active...');
        if(isset($connectionParams['host'])){
            Logger::log('Database host = \''.$connectionParams['host'].'\'', 'debug');
            if(isset($connectionParams['user'])){
                Logger::log('Database username = \''.$connectionParams['user'].'\'', 'debug');
                if(isset($connectionParams['pass'])){
                    Logger::log('Database password = \''.$connectionParams['pass'].'\'', 'debug');
                    if(isset($connectionParams['db-name'])){
                        Logger::log('Database name = \''.$connectionParams['db-name'].'\'', 'debug');
                        $link = new DatabaseLink($connectionParams['host'],$connectionParams['user'],$connectionParams['pass']);
                        if($link->isConnected()){
                            if($link->setDB($connectionParams['db-name'])){
                                $retVal = $link;
                            }
                            else{
                                Logger::log('Unable to select database.', 'warning');
                                $retVal['error-code'] = $link->getErrorCode();
                                $retVal['error-message'] = $link->getErrorMessage();
                            }
                        }
                        else{
                            Logger::log('Unable to connect to the database using given info.', 'warning');
                            $retVal['error-code'] = $link->getErrorCode();
                            $retVal['error-message'] = $link->getErrorMessage();
                        }
                    }
                    else{
                        Logger::log('The attribute \'db-name\' is missing from the array.', 'warning');
                        $retVal['error-code'] = self::MISSING_DB_NAME;
                        $retVal['error-message'] = 'The attribute \'db-name\' is missing from the array.';
                    }
                }
                else{
                    Logger::log('The attribute \'pass\' is missing from the array.', 'warning');
                    $retVal['error-code'] = self::MISSING_DB_PASS;
                    $retVal['error-message'] = 'The attribute \'pass\' is missing from the array.';
                }
            }
            else{
                Logger::log('The attribute \'user\' is missing from the array.', 'warning');
                $retVal['error-code'] = self::MISSING_DB_USER;
                $retVal['error-message'] = 'The attribute \'user\' is missing from the array.';
            }
        }
        else{
            Logger::log('The attribute \'host\' is missing from the array.', 'warning');
            $retVal['error-code'] = self::MISSING_DB_HOST;
            $retVal['error-message'] = 'The attribute \'host\' is missing from the array.';
        }
        Logger::log($retVal, 'debug');
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
}
