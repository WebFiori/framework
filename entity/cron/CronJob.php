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
    const INV_VAL = 'inv';
    const MONTHS_NAMES = array(
        'JAN'=>1,'FEB'=>2,'MAR'=>3,'APR'=>4,'MAY'=>5,'JUN'=>6,
        'JUL'=>7,'AUG'=>8,'SEP'=>9,'OCT'=>10,'NOV'=>11,'DEC'=>12
    );
    const WEEK_DAYS = array(
        'SAT'=>6,'SUN'=>0,'MON'=>1,'TUE'=>2,'WED'=>3,'THU'=>4,'FRI'=>5
    );
    private $jobDetails;
    private $events;
    /**
     * 
     * @param string $when [Optional] A cron expression. An exception will be thrown if 
     * the given expression is invalid. Default is '* * * * *' which means run 
     * the job every minute.
     * @throws Exception
     */
    public function __construct($when='* * * * *') {
        $this->jobDetails = array(
            'minutes'=>array(),
            'hours'=>array(),
            'days-of-month'=>array(),
            'months'=>array(),
            'days-of-week'=>array()
        );
        $this->events = array(
            'before'=>array(
                'func'=>function(){},
                'params'=>array()
            ),
            'after'=>array(
                'func'=>function(){},
                'params'=>array()
            ),
            'on'=>array(
                'func'=>function(){},
                'params'=>array()
            )
        );
        Logger::log('Checking if a cron expression is given...');
        if($when !== NULL){
            Logger::log('Checking if cron expression is valid...');
            if($this->cron($when) === FALSE){
                Logger::log('The given cron expression is invalid. An exception is thrown.', 'error');
                Logger::requestCompleted();
                throw new Exception('Invalid cron expression.');
            }
            else{
                Logger::log('Valid expression.');
            }
        }
        else{
            $this->cron();
        }
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
        if(count($split) == 5){
            $minutesValidity = $this->_checkMinutes($split[0]);
            $hoursValidity = $this->_checkHours($split[1]);
            $daysOfMonthValidity = $this->_dayOfMonth($split[2]);
            $monthValidity = $this->_checkMonth($split[3]);
            $daysOfWeekValidity = $this->_checkDayOfWeek($split[4]);
            if($minutesValidity === FALSE || 
               $hoursValidity === FALSE || 
               $daysOfMonthValidity === FALSE ||
               $monthValidity === FALSE || 
               $daysOfWeekValidity === FALSE){
                Logger::log('Once of thefields has invalid expression.', 'warning');
            }
            else{
                $this->jobDetails = array(
                    'minutes'=>$minutesValidity,
                    'hours'=>$hoursValidity,
                    'days-of-month'=>$daysOfMonthValidity,
                    'months'=>$monthValidity,
                    'days-of-week'=>$daysOfWeekValidity
                );
                $retVal = TRUE;
                Logger::log('Cron job time is set.');
            }
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
     * @param type $expr
     * @return string
     * @since 1.0
     */
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
                if(strlen($split[0]) != 0 && strlen($split[1]) != 0){
                    $retVal = self::STEP_VAL;
                }
                else{
                    Logger::log('Empty string in step.', 'warning');
                    $retVal = self::INV_VAL;
                }
            }
            else{
                Logger::log('Checking if given expression is range...');
                $split = explode('-', $expr);
                $count = count($split);
                if($count == 2){
                    Logger::log('It is a range.');
                    if(strlen($split[0]) != 0 && strlen($split[1]) != 0){
                        $retVal = self::RANGE_VAL;
                    }
                    else{
                        Logger::log('Empty string in range.', 'warning');
                        $retVal = self::INV_VAL;
                    }
                }
                else{
                    //it can be invalid value
                    Logger::log('It is specific value.');
                    if(strlen($expr) != 0){
                        $retVal = self::SPECIFIC_VAL;
                    }
                    else{
                        Logger::log('Empty string is given as parameter.', 'warning');
                        $retVal = self::INV_VAL;
                    }
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
     * @return boolean|array
     * @since 1.0
     */
    private function _checkMinutes($minutesField){
        Logger::logFuncCall(__METHOD__);
        $isValidExpr = TRUE;
        $split = explode(',', $minutesField);
        $minuteAttrs = array(
            'every-minute'=>FALSE,
            'every-x-minutes'=>array(),
            'at-every-x-minute'=>array(),
            'at-range'=>array()
        );
        Logger::log('Validating sub expressions...');
        foreach ($split as $subExpr){
            Logger::log('Sub Expression = \''.$subExpr.'\'.', 'debug');
            Logger::log('Getting excepression type...');
            $exprType = $this->_getSubExprType($subExpr);
            Logger::log('Expression type = \''.$exprType.'\'.', 'debug');
            if($exprType == self::ANY_VAL){
                Logger::log('The expression means that the job will be executed every minute.');
                $minuteAttrs['every-minute'] = TRUE;
            }
            else if($exprType == self::INV_VAL){
                Logger::log('Invalid value in expression.', 'warning');
                $isValidExpr = FALSE;
                break;
            }
            else if($exprType == self::RANGE_VAL){
                Logger::log('The expression means that thejob will be executed between specific range of menutes.');
                Logger::log('Checking range validity...');
                $range = explode('-', $subExpr);
                $start = intval($range[0]);
                $end = intval($range[1]);
                Logger::log('$start = \''.$start.'\'.', 'debug');
                Logger::log('$end = \''.$end.'\'.', 'debug');
                if($start < $end){
                    if($start >= 0 && $start <= 59){
                        if($end >= 0 && $end <= 59){
                            Logger::log('Valid range. New minute attribute added.');
                            $minuteAttrs['at-range'][] = array($start,$end);
                        }
                        else{
                            Logger::log('Invalid range.', 'warning');
                            $isValidExpr = FALSE;
                            break;
                        }
                    }
                    else{
                        Logger::log('Invalid range.', 'warning');
                        $isValidExpr = FALSE;
                        break;
                    }
                }
                else{
                    Logger::log('Invalid range.', 'warning');
                    $isValidExpr = FALSE;
                    break;
                }
            }
            else if($exprType == self::STEP_VAL){
                Logger::log('The expression means that the job will be executed every x minute(s).');
                Logger::log('Checking step validity...');
                $stepVal = intval(explode('/', $subExpr)[1]);
                Logger::log('$stepVal = \''.$stepVal.'\'.', 'debug');
                if($stepVal >= 0 && $stepVal <= 59){
                    Logger::log('Valid step value. New minute attribute added.');
                    $minuteAttrs['every-x-minutes'][] = $stepVal;
                }
                else{
                    Logger::log('Invalid value.', 'warning');
                    $isValidExpr = FALSE;
                    break;
                }
            }
            else if($exprType == self::SPECIFIC_VAL){
                Logger::log('The expression means that the job will be executed at specific minute.');
                $value = intval($subExpr);
                Logger::log('Checking minute validity...');
                Logger::log('$value = \''.$value.'\'.', 'debug');
                if($value >= 0 && $value <= 59){
                    $minuteAttrs['at-every-x-minute'][] = $value;
                    Logger::log('Valid value. New minute attribute added.');
                }
                else{
                    Logger::log('Invalid value.', 'warning');
                    $isValidExpr = FALSE;
                    break;
                }
            }
        }
        if($isValidExpr !== TRUE){
            Logger::log('Invalid minutes expression.', 'warning');
            $minuteAttrs = FALSE;
        }
        Logger::logFuncReturn(__METHOD__);
        return $minuteAttrs;
    }
    /**
     * 
     * @param type $hoursField
     * @return boolean
     * @since 1.0
     */
    private function _checkHours($hoursField){
        Logger::logFuncCall(__METHOD__);
        $isValidExpr = TRUE;
        $split = explode(',', $hoursField);
        $hoursAttrs = array(
            'every-hour'=>FALSE,
            'every-x-hours'=>array(),
            'at-every-x-hour'=>array(),
            'at-range'=>array()
        );
        Logger::log('Validating sub expressions...');
        foreach ($split as $subExpr){
            Logger::log('Sub Expression = \''.$subExpr.'\'.', 'debug');
            Logger::log('Getting excepression type...');
            $exprType = $this->_getSubExprType($subExpr);
            Logger::log('Expression type = \''.$exprType.'\'.', 'debug');
            if($exprType == self::ANY_VAL){
                Logger::log('The expression means that the job will be executed every hour.');
                $hoursAttrs['every-hour'] = TRUE;
            }
            else if($exprType == self::INV_VAL){
                Logger::log('Invalid value in expression.', 'warning');
                $isValidExpr = FALSE;
                break;
            }
            else if($exprType == self::RANGE_VAL){
                Logger::log('The expression means that the job will be executed between specific range of hours.');
                Logger::log('Checking range validity...');
                $range = explode('-', $subExpr);
                $start = intval($range[0]);
                $end = intval($range[1]);
                Logger::log('$start = \''.$start.'\'.', 'debug');
                Logger::log('$end = \''.$end.'\'.', 'debug');
                if($start < $end){
                    if($start >= 0 && $start < 24){
                        if($end >= 0 && $end < 24){
                            Logger::log('Valid range. New hours attribute added.');
                            $hoursAttrs['at-range'][] = array($start,$end);
                        }
                        else{
                            Logger::log('Invalid range.', 'warning');
                            $isValidExpr = FALSE;
                            break;
                        }
                    }
                    else{
                        Logger::log('Invalid range.', 'warning');
                        $isValidExpr = FALSE;
                        break;
                    }
                }
                else{
                    Logger::log('Invalid range.', 'warning');
                    $isValidExpr = FALSE;
                    break;
                }
            }
            else if($exprType == self::STEP_VAL){
                Logger::log('The expression means that the job will be executed every x hours(s).');
                Logger::log('Checking step validity...');
                $stepVal = intval(explode('/', $subExpr)[1]);
                Logger::log('$stepVal = \''.$stepVal.'\'.', 'debug');
                if($stepVal >= 0 && $stepVal < 24){
                    Logger::log('Valid step value. New hours attribute added.');
                    $hoursAttrs['every-x-hours'][] = $stepVal;
                }
                else{
                    Logger::log('Invalid value.', 'warning');
                    $isValidExpr = FALSE;
                    break;
                }
            }
            else if($exprType == self::SPECIFIC_VAL){
                Logger::log('The expression means that the job will be executed at specific hour.');
                $value = intval($subExpr);
                Logger::log('Checking hour validity...');
                Logger::log('$value = \''.$value.'\'.', 'debug');
                if($value >= 0 && $value <= 23){
                    $hoursAttrs['at-every-x-hour'][] = $value;
                    Logger::log('Valid value. New hours attribute added.');
                }
                else{
                    Logger::log('Invalid value.', 'warning');
                    $isValidExpr = FALSE;
                    break;
                }
            }
        }
        if($isValidExpr !== TRUE){
            Logger::log('Invalid hours expression.', 'warning');
            $hoursAttrs = FALSE;
        }
        Logger::logFuncReturn(__METHOD__);
        return $hoursAttrs;
    }
    /**
     * 
     * @param type $dayOfMonthField
     * @return boolean
     * @since 1.0
     */
    private function _dayOfMonth($dayOfMonthField){
        Logger::logFuncCall(__METHOD__);
        $isValidExpr = TRUE;
        $split = explode(',', $dayOfMonthField);
        $monthDaysAttrs = array(
            'every-day'=>FALSE,
            'at-every-x-day'=>array(),
            'at-range'=>array()
        );
        Logger::log('Validating sub expressions...');
        foreach ($split as $subExpr){
            Logger::log('Sub Expression = \''.$subExpr.'\'.', 'debug');
            Logger::log('Getting excepression type...');
            $exprType = $this->_getSubExprType($subExpr);
            Logger::log('Expression type = \''.$exprType.'\'.', 'debug');
            if($exprType == self::ANY_VAL){
                Logger::log('The expression means that the job will be executed every day.');
                $monthDaysAttrs['every-day'] = TRUE;
            }
            else if($exprType == self::INV_VAL){
                Logger::log('Invalid value in expression.', 'warning');
                $isValidExpr = FALSE;
                break;
            }
            else if($exprType == self::RANGE_VAL){
                Logger::log('The expression means that the job will be executed between specific range of days.');
                Logger::log('Checking range validity...');
                $range = explode('-', $subExpr);
                $start = intval($range[0]);
                $end = intval($range[1]);
                Logger::log('$start = \''.$start.'\'.', 'debug');
                Logger::log('$end = \''.$end.'\'.', 'debug');
                if($start < $end){
                    if($start >= 1 && $start < 32){
                        if($end >= 1 && $end < 32){
                            Logger::log('Valid range. New month days attribute added.');
                            $monthDaysAttrs['at-range'][] = array($start,$end);
                        }
                        else{
                            Logger::log('Invalid range.', 'warning');
                            $isValidExpr = FALSE;
                            break;
                        }
                    }
                    else{
                        Logger::log('Invalid range.', 'warning');
                        $isValidExpr = FALSE;
                        break;
                    }
                }
                else{
                    Logger::log('Invalid range.', 'warning');
                    $isValidExpr = FALSE;
                    break;
                }
            }
            else if($exprType == self::STEP_VAL){
                Logger::log('Step values in month days expression are not allowed.');
                $isValidExpr = FALSE;
                break;
            }
            else if($exprType == self::SPECIFIC_VAL){
                Logger::log('The expression means that the job will be executed at specific month day.');
                $value = intval($subExpr);
                Logger::log('Checking hour validity...');
                Logger::log('$value = \''.$value.'\'.', 'debug');
                if($value >= 1 && $value <= 31){
                    $monthDaysAttrs['at-every-x-day'][] = $value;
                    Logger::log('Valid value. New month days attribute added.');
                }
                else{
                    Logger::log('Invalid value.', 'warning');
                    $isValidExpr = FALSE;
                    break;
                }
            }
        }
        if($isValidExpr !== TRUE){
            Logger::log('Invalid month days expression.', 'warning');
            $monthDaysAttrs = FALSE;
        }
        Logger::logFuncReturn(__METHOD__);
        return $monthDaysAttrs;
    }
    /**
     * 
     * @param type $monthField
     * @return boolean
     * @since 1.0
     */
    public function _checkMonth($monthField){
        Logger::logFuncCall(__METHOD__);
        $isValidExpr = TRUE;
        $split = explode(',', $monthField);
        $monthAttrs = array(
            'every-month'=>FALSE,
            'at-x-month'=>array(),
            'at-range'=>array()
        );
        Logger::log('Validating sub expressions...');
        foreach ($split as $subExpr){
            Logger::log('Sub Expression = \''.$subExpr.'\'.', 'debug');
            Logger::log('Getting excepression type...');
            $exprType = $this->_getSubExprType($subExpr);
            Logger::log('Expression type = \''.$exprType.'\'.', 'debug');
            if($exprType == self::ANY_VAL){
                Logger::log('The expression means that the job will be executed every month.');
                $monthAttrs['every-month'] = TRUE;
            }
            else if($exprType == self::INV_VAL){
                Logger::log('Invalid value in expression.', 'warning');
                $isValidExpr = FALSE;
                break;
            }
            else if($exprType == self::RANGE_VAL){
                Logger::log('The expression means that the job will be executed between specific range of months.');
                Logger::log('Checking range validity...');
                $range = explode('-', $subExpr);
                $start = in_array(strtoupper($range[0]), array_keys(self::MONTHS_NAMES)) ? self::MONTHS_NAMES[strtoupper($range[0])] : intval($range[0]);
                $end = in_array(strtoupper($range[1]), array_keys(self::MONTHS_NAMES)) ? self::MONTHS_NAMES[strtoupper($range[1])] : intval($range[1]);
                Logger::log('$start = \''.$start.'\'.', 'debug');
                Logger::log('$end = \''.$end.'\'.', 'debug');
                if($start < $end){
                    if($start >= 1 && $start < 13){
                        if($end >= 1 && $end < 13){
                            Logger::log('Valid range. New month attribute added.');
                            $monthAttrs['at-range'][] = array($start,$end);
                        }
                        else{
                            Logger::log('Invalid range.', 'warning');
                            $isValidExpr = FALSE;
                            break;
                        }
                    }
                    else{
                        Logger::log('Invalid range.', 'warning');
                        $isValidExpr = FALSE;
                        break;
                    }
                }
                else{
                    Logger::log('Invalid range.', 'warning');
                    $isValidExpr = FALSE;
                    break;
                }
            }
            else if($exprType == self::STEP_VAL){
                Logger::log('Step values not allowed in month fileld.','warning');
                $isValidExpr = FALSE;
            }
            else if($exprType == self::SPECIFIC_VAL){
                Logger::log('The expression means that the job will be executed at specific month.');
                $subExpr = strtoupper($subExpr);
                $value = in_array($subExpr, array_keys(self::MONTHS_NAMES)) ? self::MONTHS_NAMES[$subExpr] : intval($subExpr);
                Logger::log('Checking month validity...');
                Logger::log('$value = \''.$value.'\'.', 'debug');
                if($value >= 1 && $value <= 12){
                    $monthAttrs['at-x-month'][] = $value;
                    Logger::log('Valid value. New month attribute added.');
                }
                else{
                    Logger::log('Invalid value.', 'warning');
                    $isValidExpr = FALSE;
                    break;
                }
            }
        }
        if($isValidExpr !== TRUE){
            Logger::log('Invalid month expression.', 'warning');
            $monthAttrs = FALSE;
        }
        Logger::logFuncReturn(__METHOD__);
        return $monthAttrs;
    }
    public function isDayOfMonth() {
        $monthDaysArr = $this->jobDetails['days-of-month'];
        if($monthDaysArr['every-day'] === TRUE){
            return TRUE;
        }
        else{
            $retVal = FALSE;
            $current = intval(date('d'));
            $ranges = $monthDaysArr['at-range'];
            foreach ($ranges as $range){
                if($current >= $range[0] && $current <= $range[1]){
                    $retVal = TRUE;
                    break;
                }
            }
            if($retVal === FALSE){
                $days = $monthDaysArr['at-every-x-day'];
                $retVal = in_array($current, $days);
            }
        }
        return $retVal;
    }
    public function isDayOfWeek() {
        $daysArr = $this->jobDetails['days-of-week'];
        if($daysArr['every-day'] === TRUE){
            return TRUE;
        }
        else{
            $retVal = FALSE;
            $current = intval(date('w'));
            $ranges = $daysArr['at-range'];
            foreach ($ranges as $range){
                if($current >= $range[0] && $current <= $range[1]){
                    $retVal = TRUE;
                    break;
                }
            }
            if($retVal === FALSE){
                $days = $daysArr['at-x-day'];
                $retVal = in_array($current, $days);
            }
        }
        return $retVal;
    }
    public function isMonth(){
        $monthsArr = $this->jobDetails['months'];
        if($monthsArr['every-month'] === TRUE){
            return TRUE;
        }
        else{
            $retVal = FALSE;
            $current = intval(date('m'));
            $ranges = $monthsArr['at-range'];
            foreach ($ranges as $range){
                if($current >= $range[0] && $current <= $range[1]){
                    $retVal = TRUE;
                    break;
                }
            }
            if($retVal === FALSE){
                $months = $monthsArr['at-x-month'];
                $retVal = in_array($current, $months);
            }
        }
        return $retVal;
    }
    public function isHour(){
        $hoursArr = $this->jobDetails['hours'];
        if($hoursArr['every-hour'] === TRUE){
            return TRUE;
        }
        else{
            $retVal = FALSE;
            $current = intval(date('H'));
            $ranges = $hoursArr['at-range'];
            foreach ($ranges as $range){
                if($current >= $range[0] && $current <= $range[1]){
                    $retVal = TRUE;
                    break;
                }
            }
            if($retVal === FALSE){
                $hours = $hoursArr['at-every-x-hour'];
                $retVal = in_array($current, $hours);
                if($retVal === FALSE){
                    $hours = $hoursArr['every-x-hours'];
                    foreach ($hours as $hour){
                        if($current % $hour == 0){
                            $retVal = TRUE;
                            break;
                        }
                    }
                }
            }
        }
        return $retVal;
    }
    public function isMinute(){
        $minuteArr = $this->jobDetails['minutes'];
        if($minuteArr['every-minute'] === TRUE){
            return TRUE;
        }
        else{
            $retVal = FALSE;
            $current = intval(date('i'));
            $ranges = $minuteArr['at-range'];
            foreach ($ranges as $range){
                if($current >= $range[0] && $current <= $range[1]){
                    $retVal = TRUE;
                    break;
                }
            }
            if($retVal === FALSE){
                $minutes = $minuteArr['at-every-x-minute'];
                $retVal = in_array($current, $minutes);
                if($retVal === FALSE){
                    $minutes = $minuteArr['every-x-minutes'];
                    foreach ($minutes as $min){
                        if($current % $min == 0){
                            $retVal = TRUE;
                            break;
                        }
                    }
                }
            }
        }
        return $retVal;
    }
    /**
     * 
     * @param type $minutesField
     * @return boolean
     * @since 1.0
     */
    public function _checkDayOfWeek($dayOfWeekField){
        Logger::logFuncCall(__METHOD__);
        $isValidExpr = TRUE;
        $split = explode(',', $dayOfWeekField);
        $dayAttrs = array(
            'every-day'=>FALSE,
            'at-x-day'=>array(),
            'at-range'=>array()
        );
        Logger::log('Validating sub expressions...');
        foreach ($split as $subExpr){
            Logger::log('Sub Expression = \''.$subExpr.'\'.', 'debug');
            Logger::log('Getting excepression type...');
            $exprType = $this->_getSubExprType($subExpr);
            Logger::log('Expression type = \''.$exprType.'\'.', 'debug');
            if($exprType == self::ANY_VAL){
                Logger::log('The expression means that the job will be executed every day.');
                $dayAttrs['every-day'] = TRUE;
            }
            else if($exprType == self::INV_VAL){
                Logger::log('Invalid value in expression.', 'warning');
                $isValidExpr = FALSE;
                break;
            }
            else if($exprType == self::RANGE_VAL){
                Logger::log('The expression means that the job will be executed between specific range of days.');
                Logger::log('Checking range validity...');
                $range = explode('-', $subExpr);
                $start = in_array(strtoupper($range[0]), array_keys(self::WEEK_DAYS)) ? self::WEEK_DAYS[strtoupper($range[0])] : intval($range[0]);
                $end = in_array(strtoupper($range[1]), array_keys(self::WEEK_DAYS)) ? self::WEEK_DAYS[strtoupper($range[1])] : intval($range[1]);
                Logger::log('$start = \''.$start.'\'.', 'debug');
                Logger::log('$end = \''.$end.'\'.', 'debug');
                if($start < $end){
                    if($start >= 0 && $start < 6){
                        if($end >= 0 && $end < 6){
                            Logger::log('Valid range. New day attribute added.');
                            $dayAttrs['at-range'][] = array($start,$end);
                        }
                        else{
                            Logger::log('Invalid range.', 'warning');
                            $isValidExpr = FALSE;
                            break;
                        }
                    }
                    else{
                        Logger::log('Invalid range.', 'warning');
                        $isValidExpr = FALSE;
                        break;
                    }
                }
                else{
                    Logger::log('Invalid range.', 'warning');
                    $isValidExpr = FALSE;
                    break;
                }
            }
            else if($exprType == self::STEP_VAL){
                Logger::log('Step values not allowed in day fileld.','warning');
                $isValidExpr = FALSE;
            }
            else if($exprType == self::SPECIFIC_VAL){
                Logger::log('The expression means that the job will be executed at specific day.');
                $subExpr = strtoupper($subExpr);
                $value = in_array($subExpr, array_keys(self::WEEK_DAYS)) ? self::WEEK_DAYS[$subExpr] : intval($subExpr);
                Logger::log('Checking day validity...');
                Logger::log('$value = \''.$value.'\'.', 'debug');
                if($value >= 0 && $value <= 6){
                    $dayAttrs['at-x-day'][] = $value;
                    Logger::log('Valid value. New day attribute added.');
                }
                else{
                    Logger::log('Invalid value.', 'warning');
                    $isValidExpr = FALSE;
                    break;
                }
            }
        }
        if($isValidExpr !== TRUE){
            Logger::log('Invalid day expression.', 'warning');
            $dayAttrs = FALSE;
        }
        Logger::logFuncReturn(__METHOD__);
        return $dayAttrs;
    }
    /**
     * 
     * @return array
     * @since 1.0
     */
    public function getJobDetails() {
        return $this->jobDetails;
    }
    /**
     * 
     * @param type $func
     * @param type $funcParams
     * @since 1.0
     */
    public function setOnExecution($func,$funcParams=array()){
        if(is_callable($func)){
            $this->events['on']['func'] = $func;
            if(gettype($funcParams) == 'array'){
                $this->events['on']['params'] = $funcParams;
            }
        }
    }
    /**
     * @since 1.0
     */
    public function execute(){
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        Logger::log('Checking if it is time to run cron job...');
        if($this->isMinute() && $this->isHour() && $this->isDayOfMonth() && 
        $this->isMonth() && $this->isDayOfWeek()){
            Logger::log('It is time.');
            Logger::log('Executing the \'on\' event.');
            call_user_func($this->events['on']['func'], $this->events['on']['params']);
            $retVal = TRUE;
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
}
