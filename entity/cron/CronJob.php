<?php
/**
 * Description of CronJob
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 */
class CronJob {
    private $name;
    private $minute;
    private $hour;
    private $dayOfMonth;
    private $month;
    private $dayOfWeek;
    
    
    public function __construct($when='* * * * *') {
        
    }
    /**
     * 
     * @param type $when
     * @return boolean
     * @since 1.0
     */
    public function cron($when='* * * * *'){
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        $trimmed = trim($when);
        Logger::log('Splitting cron exepression...');
        $split = explode(' ', $trimmed);
        Logger::log('Chcking excepression fields count...');
        $count = count($split);
        Logger::log('Fields count = \''.$count.'\'.', 'debug');
        if(count($split) == 6){
            
        }
        else{
            Logger::log('Number of filds is not 5. Fields count must be 5.', 'warning');
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * 
     * @param type $minutesField
     * @return boolean
     * @since 1.0
     */
    private function _checkMinutes($minutesField){
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        $len = strlen($minutesField);
        if($minutesField[0] == '*'){
            if($len == 1){
                $retVal = '*';
            }
            else{
                $split = explode('/', $minutesField);
                $splitCount = count($split);
                if($splitCount == 2){
                    $val = $split[1];
                    if($val >= 0 && $val <= 59){
                        $retVal = 'EVERY-'.$val;
                    }
                }
            }
        }
        else{
            
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * 
     * @param type $hoursField
     * @return boolean
     * @since 1.0
     */
    private function _checkHours($hoursField){
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * 
     * @param type $dayOfMonthField
     * @return boolean
     * @since 1.0
     */
    private function _dayOfMonth($dayOfMonthField){
        $Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * 
     * @param type $monthField
     * @return boolean
     * @since 1.0
     */
    private function _checkMonth($monthField){
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * 
     * @param type $minutesField
     * @return boolean
     * @since 1.0
     */
    private function _checkDayOfWeek($minutesField){
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * 
     * @param type $func
     * @param type $funcParams
     * @since 1.0
     */
    public function setOnBefore($func,$funcParams=array()){
        
    }
    /**
     * 
     * @param type $func
     * @param type $funcParams
     * @since 1.0
     */
    public function setOnExecution($func,$funcParams=array()){
        
    }
    /**
     * 
     * @param type $func
     * @param type $funcParams
     * @since 1.0
     */
    public function setOnAfter($func,$funcParams=array()){
        
    }
    /**
     * @since 1.0
     */
    public function execute(){
        
    }
}
