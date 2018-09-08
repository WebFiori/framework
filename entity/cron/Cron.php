<?php
/**
 * Description of CronExecuter
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class Cron {
    /**
     * The password that is used to access and execute jobs.
     * @var string
     * @since 1.0 
     */
    private $accessPass;
    /**
     * A queue which contains all cron jobs.
     * @var Queue 
     * @since 1.0
     */
    private $cronJobsQueue;
    /**
     * An instance of 'CronExecuter'
     * @var Cron 
     * @since 1.0
     */
    private static $executer;
    /**
     * Returns a singleton of the class CronExecuter.
     * @return Cron
     * @since 1.0
     */
    private static function &_get(){
        if(self::$executer === NULL){
            self::$executer = new Cron();
        }
        return self::$executer;
    }
    /**
     * Creates new instance of the class.
     * @since 1.0
     */
    public function __construct() {
        $this->cronJobsQueue = new Queue();
        $this->_setPassword('');
        Router::closure('/cron-jobs/execute', function(){
            Logger::logFuncCall('CLOSURE_ROUTE');
            Logger::log('Validating source IP address...');
            $clientIp = Util::getClientIP();
            $serverIp = Util::getClientIP();
            Logger::log('Client IP = \''.$clientIp.'\'.', 'debug');
            Logger::log('Server IP = \''.$serverIp.'\'.', 'debug');
            if($clientIp == $serverIp){
                Logger::log('Checking if password is required to execute cron jobs...');
                if(Cron::password() != 'NO_PASSWORD'){
                    Logger::log('Password required. Checking if password is provided...');
                    $password = isset($_GET['password']) ? filter_var($_GET['password']) : '';
                    Logger::log('Password = \''.$password.'\'.', 'debug');
                    if($password != ''){
                        Logger::log('Checking if password is valid...');
                        if($password == Cron::password()){
                            Logger::log('Valid password.');
                            Logger::log('Starting the execution of tasks.');
                            $totalJobs = Cron::jobsQueue()->size();
                            $executedJobsCount = 0;
                            while ($job = Cron::jobsQueue()->dequeue()){
                                if($job->execute()){
                                    $executedJobsCount++;
                                }
                            }
                            Logger::log('Jobs execution finished.');
                            Logger::requestCompleted();
                            http_response_code(200);
                            die(''
                            . '<!DOCTYPE html>'
                            . '<html>'
                            . '<head>'
                            . '<title>OK</title>'
                            . '</head>'
                            . '<body>'
                            . '<h1>200 - OK</h1>'
                            . '<hr>'
                            . '<p>'
                            . 'Total number of jobs: '.$totalJobs
                            . '</p>'
                            . '<p>'
                            . 'Number of jobs executed: '.$executedJobsCount
                            . '</p>'
                            . '</body>'
                            . '</html>');
                        }
                        else{
                            Logger::log('Invalid password.', 'error');
                            Logger::requestCompleted();
                            die(''
                            . '<!DOCTYPE html>'
                            . '<html>'
                            . '<head>'
                            . '<title>Not Authorized</title>'
                            . '</head>'
                            . '<body>'
                            . '<h1>401 - Not Authorized</h1>'
                            . '<hr>'
                            . '<p>'
                            . 'Invalid cron password.'
                            . '</p>'
                            . '</body>'
                            . '</html>');
                        }
                    }
                    else{
                        Logger::log('No password is provided.', 'error');
                        Logger::requestCompleted();
                        die(''
                        . '<!DOCTYPE html>'
                        . '<html>'
                        . '<head>'
                        . '<title>Not Authorized</title>'
                        . '</head>'
                        . '<body>'
                        . '<h1>401 - Not Authorized</h1>'
                        . '<hr>'
                        . '<p>'
                        . 'Password is missing.'
                        . '</p>'
                        . '</body>'
                        . '</html>');
                    }
                }
                else{
                    Logger::log('No password required. Executing jobs...');
                    $totalJobs = Cron::jobsQueue()->size();
                    $executedJobsCount = 0;
                    while ($job = Cron::jobsQueue()->dequeue()){
                        if($job->execute()){
                            $executedJobsCount++;
                        }
                    }
                    Logger::log('Jobs execution finished.');
                    Logger::requestCompleted();
                    http_response_code(200);
                    die(''
                    . '<!DOCTYPE html>'
                    . '<html>'
                    . '<head>'
                    . '<title>OK</title>'
                    . '</head>'
                    . '<body>'
                    . '<h1>200 - OK</h1>'
                    . '<hr>'
                    . '<p>'
                    . 'Total number of jobs: '.$totalJobs
                    . '</p>'
                    . '<p>'
                    . 'Number of jobs executed: '.$executedJobsCount
                    . '</p>'
                    . '</body>'
                    . '</html>');
                }
            }
            else{
                Logger::log('Client IP address is not the same as server IP. No jobs executed.', 'error');
                Logger::requestCompleted();
                http_response_code(403);
                die(''
                . '<!DOCTYPE html>'
                . '<html>'
                . '<head>'
                . '<title>Forbidden</title>'
                . '</head>'
                . '<body>'
                . '<h1>403 - Forbidden</h1>'
                . '<hr>'
                . '<p>'
                . 'Cron jobs can be executed only withen the server environment.'
                . '</p>'
                . '</body>'
                . '</html>');
            }
        });
    }
    
    public static function createJob($when='*/5 * * * *',$function='',$funcParams=array()){
        
    }
    /**
     * 
     * @param type $pass
     * @return type
     * @since 1.0
     */
    public static function password($pass=null) {
        if($pass !== NULL){
            self::_get()->_setPassword($pass);
        }
        return self::_get()->_getPassword();
    }
    /**
     * 
     * @return Queue
     * @since 1.0
     */
    public static function jobsQueue(){
        return self::_get()->_getQueue();
    }

    /**
     * 
     * @param type $job
     * @return type
     * @since 1.0
     */
    public static function scheduleJob($job){
        return self::_get()->_addJob($job);
    }
    /**
     * 
     * @param CronJob $job
     * @return type
     * @since 1.0
     */
    private function _addJob($job){
        $retVal = FALSE;
        if($job instanceof CronJob){
            $retVal = $this->cronJobsQueue->enqueue($job);
        }
        return $retVal;
    }
    /**
     * 
     * @return Queue
     * @since 1.0
     */
    public function _getQueue() {
        return $this->cronJobsQueue;
    }
    /**
     * 
     * @return string
     * @since 1.0
     */
    private function _getPassword(){
        if($this->accessPass == ''){
            return 'NO_PASSWORD';
        }
    }
    /**
     * 
     * @param type $pass
     * @since 1.0
     */
    private function _setPassword($pass){
        if(gettype($pass) == 'string'){
            $this->accessPass = $pass;
        }
    }
}
