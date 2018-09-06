<?php
/**
 * Description of CronJob
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 */
class CronJob {
    const RANGE_VAL = 'r';
    const STEP_VAL = 's';
    const ANY_VAL = '*';
    const SPECIFIC_VAL = 'spe';
    private $name;
    private $minute;
    private $hour;
    private $dayOfMonth;
    private $month;
    private $dayOfWeek;
    
    
    public function __construct($when='* * * * *') {
        $this->minute = array(
            'ever-minute'=>FALSE,
            'every-x-minutes'=>-1,
            'at-ever-x-minute'=>-1,
            'at-range'=>array(-1,-1)
        );
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
    private function _getSubExprType($expr){
        Logger::logFuncCall(__METHOD__);
        $retVal = self::ANY_VAL;
        Logger::log('Checking if given expression is any value...');
        if($expr != '*'){
            Logger::log('Checking if given expression is step value...');
            $split = explode('/', $expr);
            $count = count($split);
            if($count == 2){
                Logger::log('It is a step value.');
                $retVal = self::STEP_VAL;
            }
            else{
                Logger::log('Checking if given expression is range...');
                $split = explode('-', $expr);
                $count = count($split);
                if($count == 2){
                    Logger::log('It is a range.');
                    $retVal = self::RANGE_VAL;
                }
                else{
                    //it can be invalid value
                    Logger::log('It is specific value.');
                    $retVal = self::SPECIFIC_VAL;
                }
            }
        }
        else{
            Logger::log('It is any value.');
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
        $split = explode(',', $minutesField);
        $minuteAttrs = array(
            'every-minute'=>FALSE,
            'every-x-minutes'=>array(),
            'at-every-x-minute'=>array(),
            'at-range'=>array()
        );
        foreach ($split as $subExpr){
            $exprType = $this->_getSubExprType($subExpr);
            if($exprType == self::ANY_VAL){
                $minuteAttrs['every-minute'] = TRUE;
            }
            else if($exprType == self::RANGE_VAL){
                $range = explode('-', $subExpr);
                $start = intval($range[0]);
                $end = intval($range[2]);
                if($start < $end){
                    if($start >= 0 && $start < 59){
                        if($end >= 0 && $end < 59){
                            $minuteAttrs['at-range'][] = array($start,$end);
                        }
                        else{
                            $retVal = FALSE;
                            break;
                        }
                    }
                    else{
                        $retVal = FALSE;
                        break;
                    }
                }
                else{
                    $retVal = FALSE;
                    break;
                }
            }
            else if($exprType == self::STEP_VAL){
                $stepVal = intval(explode('/', $subExpr)[2]);
                if($stepVal >= 0 && $stepVal < 59){
                    $minuteAttrs['every-x-minutes'][] = $stepVal;
                }
                else{
                    $retVal = FALSE;
                    break;
                }
            }
            else if($exprType == self::SPECIFIC_VAL){
                $value = intval($subExpr);
                if($value >= 0 && $value <= 59){
                    $minuteAttrs['at-every-x-minute'][] = $value;
                }
            }
        }
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
