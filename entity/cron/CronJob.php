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
namespace webfiori\entity\cron;
use webfiori\entity\Logger;
use Exception;
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 403 Forbidden");
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
        . 'Direct access not allowed.'
        . '</p>'
        . '</body>'
        . '</html>');
}
/**
 * A class thar represents a cron job.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0.2
 */
class CronJob {
    /**
     * A constant that indicates a sub cron expression is of type 'range'.
     * @since 1.0
     */
    const RANGE_VAL = 'r';
    /**
     * A constant that indicates a sub cron expression is of type 'step value'.
     * @since 1.0
     */
    const STEP_VAL = 's';
    /**
     * A constant that indicates a sub cron expression is of type 'multi-value'.
     * @since 1.0
     */
    const ANY_VAL = '*';
    /**
     * A constant that indicates a sub cron expression is of type 'specific value'.
     * @since 1.0
     */
    const SPECIFIC_VAL = 'spe';
    /**
     * A constant that indicates a sub cron expression is invalid.
     * @since 1.0
     */
    const INV_VAL = 'inv';
    /**
     * An associative array which holds the names and the numbers of year 
     * months.
     * @since 1.0
     */
    const MONTHS_NAMES = array(
        'JAN'=>1,'FEB'=>2,'MAR'=>3,'APR'=>4,'MAY'=>5,'JUN'=>6,
        'JUL'=>7,'AUG'=>8,'SEP'=>9,'OCT'=>10,'NOV'=>11,'DEC'=>12
    );
    /**
     * An associative array which holds the names and the numbers of week 
     * days.
     * @since 1.0
     */
    const WEEK_DAYS = array(
        'SAT'=>6,'SUN'=>0,'MON'=>1,'TUE'=>2,'WED'=>3,'THU'=>4,'FRI'=>5
    );
    /**
     * An array which contains all job details after parsing cron expression.
     * @var array 
     * @since 1.0
     */
    private $jobDetails;
    /**
     * An array which contains the events that will be executed if it is the time 
     * to execute the job.
     * @var array
     * @since 1.0 
     */
    private $events;
    /**
     * A name for the cron job.
     * @var string
     * @since 1.0 
     */
    private $jobName;
    /**
     * The full cron expression.
     * @var type 
     * @since 1.0.1
     */
    private $cronExpr;
    /**
     * Creates new instance of the class.
     * @param string $when [Optional] A cron expression. An exception will be thrown if 
     * the given expression is invalid. Default is '* * * * *' which means run 
     * the job every minute.
     * @throws Exception
     * @since 1.0
     */
    public function __construct($when='* * * * *') {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Initializing cron job...');
        $this->jobName = 'CRON-JOB';
        $this->jobDetails = array(
            'minutes'=>array(),
            'hours'=>array(),
            'days-of-month'=>array(),
            'months'=>array(),
            'days-of-week'=>array()
        );
        $this->events = array(
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
            Logger::log('No expression is given. Using default.');
            $this->cron();
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Sets an optional name for the job.
     * The name is used to make different jobs unique. Each job must 
     * have its own name. Also, the name of the job is used to force job 
     * execution. It can be supplied as a part of cron URL. 
     * @param string $name The name of the job.
     * @since 1.0
     */
    public function setJobName($name){
        if(strlen($name) > 0){
            $this->jobName = $name;
        }
    }
    /**
     * Returns the cron expression which is associated with the job.
     * @return string The cron expression which is associated with the job.
     * @since 1.0.1
     */
    public function getExpression() {
        return $this->cronExpr;
    }
    /**
     * Returns the name of the job.
     * The name is used to make different jobs unique. Each job must 
     * have its own name. Also, the name of the job is used to force job 
     * execution. It can be supplied as a part of cron URL. 
     * @return string The name of the job. If no name is set, the function will return 
     * 'CRON-JOB'.
     * @since 1.0
     */
    public function getJobName(){
        return $this->jobName;
    }
    /**
     * Schedules a cron job to run daily at specific hour and minute.
     * The job will be executed every day at the given hour and minute. The 
     * function uses 24 hours mode. If no parameters are given, 
     * The default time is 00:00 which means that the job will be executed 
     * daily at midnight.
     * @param int $hour [Optional] A number between 0 and 23 inclusive. 0 Means daily at 
     * 12:00 AM and 23 means at 11:00 PM. Default is 0.
     * @param int $minute [Optional] A number between 0 and 59 inclusive. Represents the 
     * minute part of an hour. Default is 0.
     * @return boolean If job time is set, the method will return TRUE. If 
     * not set, the method will return FALSE. It will not set only if the 
     * given time is not correct.
     * @since 1.0
     */
    public function dailyAt($hour=0,$minute=0){
        if($hour >= 0 && $hour <= 23){
            if($minute >= 0 && $minute <= 59){
                return $this->cron($minute.' '.$hour.' * * *');
            }
        }
        return FALSE;
    }
    /**
     * Schedules a cron job to run every hour.
     * @since 1.0.2
     */
    public function everyHour(){
        $this->cron('0 * * * *');
    }
    /**
     * Schedules a job to run weekly at specific week day and time.
     * @param int $dayNameOrNum [Optional] A 3 letter day name (such as 'sun' 
     * or 'tue') or a day number from 0 to 6. 0 for sunday. Default is 0.
     * @param string $time [Optional] A time in the form 'hh:mm'. hh can have any value 
     * between 0 and 23 inclusive. mm can have any value between 0 and 59 inclusive. 
     * default is '00:00'.
     * @return boolean If the time for the cron job is set, the method will 
     * return TRUE. If not, it will return FALSE.
     * @since 1.0
     */
    public function weeklyOn($dayNameOrNum=0,$time='00:00'){
        $uDayName = strtoupper($dayNameOrNum);
        if(in_array($uDayName, array_keys(self::WEEK_DAYS))){
            return $this->_weeklyOn(self::WEEK_DAYS[$uDayName], $time);
        }
        else{
            if($dayNameOrNum >= 0 && $dayNameOrNum <= 6){
                return $this->_weeklyOn($dayNameOrNum, $time);
            }
        }
        return FALSE;
    }
    /**
     * Schedules a job to run at specific day and time in a specific month.
     * @param int|string $monthNameOrNum [Optional] Month number from 1 to 12 inclusive 
     * or 3 letters month name. Default is 'jan'.
     * @param int $dayNum [Optional] The number of day in the month starting from 1 up to 
     * 31 inclusive. Default is 1.
     * @param string $time [Optional] A time in the form 'hh:mm'. hh can have any value 
     * between 0 and 23 inclusive. mm can have any value btween 0 and 59 inclusive. 
     * default is '00:00'.
     * @return boolean If the time for the cron job is set, the method will 
     * return TRUE. If not, it will return FALSE.
     * @since 1.0
     */
    public function onMonth($monthNameOrNum='jan',$dayNum=1,$time='00:00'){
        if($dayNum >= 1 && $dayNum <= 31){
            $timeSplit = explode(':', $time);
            if(count($timeSplit) == 2){
                $hour = intval($timeSplit[0]);
                $minute = intval($timeSplit[1]);
                if($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59){
                    $uMonth = strtoupper($monthNameOrNum);
                    if(in_array($uMonth, array_keys(self::MONTHS_NAMES))){
                        $monthNum = self::MONTHS_NAMES[$uMonth];
                        return $this->cron($minute.' '.$hour.' '.$dayNum.' '.$monthNum.' *');
                    }
                    else{
                        if($monthNameOrNum >= 1 && $monthNameOrNum <= 12){
                            $this->cron($minute.' '.$hour.' '.$dayNum.' '.$monthNameOrNum.' *');
                        }
                    }
                }
            }
        }
        return FALSE;
    }
    /**
     * Schedules a cron job to run every month on specific day and time.
     * @param int $dayNum The number of the day. It can be any value between 
     * 1 and 31 inclusive.
     * @param string $time A day time string in the form 'hh:mm' in 24 hours mode.
     * @return boolean If the time for the cron job is set, the method will 
     * return TRUE. If not, it will return FALSE.
     * @since 1.0.1
     */
    public function everyMonthOn($dayNum=1,$time='00:00'){
        if($dayNum >= 1 && $dayNum <= 31){
            $timeSplit = explode(':', $time);
            if(count($timeSplit) == 2){
                $hour = intval($timeSplit[0]);
                $minute = intval($timeSplit[1]);
                if($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59){
                    return $this->cron($minute.' '.$hour.' '.$dayNum.' * *');
                }
            }
        }
        return FALSE;
    }
    private function _weeklyOn($day,$time){
        $timeSplit = explode(':', $time);
        if(count($timeSplit) == 2){
            $hour = intval($timeSplit[0]);
            $minute = intval($timeSplit[1]);
            if($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59){
                return $this->cron($minute.' '.$hour.' * * '.$day);
            }
        }
        return FALSE;
    }
    /**
     * Schedules a job using specific cron expression.
     * For more information on cron expressions, go to 
     * https://en.wikipedia.org/wiki/Cron#CRON_expression. Note that 
     * the method does not support year field. This means 
     * the expression will have only 5 fields.
     * @param string $when A cron expression (such as '8 15 * * 1'). Default 
     * is '* * * * *' which means run the job every minute.
     * @return boolean If the given cron expression is valid, the method will 
     * set the time of cron job as specified by the expression and return 
     * TRUE. If the expression is invalid, the method will return FALSE.
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
                $this->cronExpr = $when;
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
    private function _checkMonth($monthField){
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
    /**
     * Checks if current day of month in time is a day at which the job must be 
     * executed.
     * @return boolean The method will return TRUE if the current day of month in 
     * time is a day at which the job must be executed.
     * @since 1.0
     */
    public function isDayOfMonth() {
        Logger::logFuncCall(__METHOD__);
        $monthDaysArr = $this->jobDetails['days-of-month'];
        Logger::log('Checking if the job will be executed every day of month...');
        Logger::log('every-day = \''.$monthDaysArr['every-day'].'\'.', 'debug');
        if($monthDaysArr['every-day'] === TRUE){
            Logger::log('It will be executed every day of month.');
            $retVal = TRUE;
        }
        else{
            Logger::log('Checking if the current day is in range of days...');
            $retVal = FALSE;
            $current = intval(date('d'));
            Logger::log('Current day of month = \''.$current.'\'.', 'debug');
            $ranges = $monthDaysArr['at-range'];
            foreach ($ranges as $range){
                Logger::log('Min Range Value = \''.$range[0].'\', Max Range Value = \''.$range[1].'\'.', 'debug');
                if($current >= $range[0] && $current <= $range[1]){
                    Logger::log('It is in given range.');
                    $retVal = TRUE;
                    break;
                }
            }
            if($retVal === FALSE){
                Logger::log('Checking if day is in a set of specific days...');
                $days = $monthDaysArr['at-every-x-day'];
                $retVal = in_array($current, $days);
                if($retVal === TRUE){
                    Logger::log('It is a specific day.');
                }
            }
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Checks if current day of week in time is a day at which the job must be 
     * executed.
     * @return boolean The method will return TRUE if the current day of week in 
     * time is a day at which the job must be executed.
     * @since 1.0
     */
    public function isDayOfWeek() {
        Logger::logFuncCall(__METHOD__);
        $daysArr = $this->jobDetails['days-of-week'];
        Logger::log('Checking if the job will be executed every day of week...');
        Logger::log('every-day = \''.$daysArr['every-day'].'\'.', 'debug');
        if($daysArr['every-day'] === TRUE){
            Logger::log('It will be executed every day of week.');
            $retVal = TRUE;
        }
        else{
            $retVal = FALSE;
            Logger::log('Checking if current week day is in range of days...');
            $current = intval(date('w'));
            Logger::log('Current day of week = \''.$current.'\'.', 'debug');
            $ranges = $daysArr['at-range'];
            foreach ($ranges as $range){
                Logger::log('Min Range Value = \''.$range[0].'\', Max Range Value = \''.$range[1].'\'.', 'debug');
                if($current >= $range[0] && $current <= $range[1]){
                    Logger::log('It is in given range.');
                    $retVal = TRUE;
                    break;
                }
            }
            if($retVal === FALSE){
                Logger::log('Checking if current day is in the set of specific days...');
                $days = $daysArr['at-x-day'];
                $retVal = in_array($current, $days);
                if($retVal === TRUE){
                    Logger::log('Current day is in the set of specific days.');
                }
            }
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Checks if current month in time is a month at which the job must be 
     * executed.
     * @return boolean The method will return TRUE if the current month in 
     * time is a month at which the job must be executed.
     * @since 1.0
     */
    public function isMonth(){
        Logger::logFuncCall(__METHOD__);
        $monthsArr = $this->jobDetails['months'];
        Logger::log('Checking if the job will be executed every month...');
        Logger::log('every-month = \''.$monthsArr['every-month'].'\'.', 'debug');
        if($monthsArr['every-month'] === TRUE){
            Logger::log('It will be executed every month.');
            $retVal = TRUE;
        }
        else{
            $retVal = FALSE;
            $current = intval(date('m'));
            Logger::log('Checking if current month is in range of months...');
            Logger::log('Current month = \''.$current.'\'.', 'debug');
            $ranges = $monthsArr['at-range'];
            foreach ($ranges as $range){
                Logger::log('Min Range Value = \''.$range[0].'\', Max Range Value = \''.$range[1].'\'.', 'debug');
                if($current >= $range[0] && $current <= $range[1]){
                    Logger::log('It is in given range.');
                    $retVal = TRUE;
                    break;
                }
            }
            if($retVal === FALSE){
                $months = $monthsArr['at-x-month'];
                Logger::log('Checking if month is in specific months array.');
                $retVal = in_array($current, $months);
                if($retVal === TRUE){
                    Logger::log('It is in specific months array.');
                }
            }
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Checks if current hour in time is an hour at which the job must be 
     * executed.
     * @return boolean The method will return TRUE if the current hour in 
     * time is an hour at which the job must be executed.
     * @since 1.0
     */
    public function isHour(){
        Logger::logFuncCall(__METHOD__);
        $hoursArr = $this->jobDetails['hours'];
        Logger::log('Checking if the job will be executed every hour...');
        Logger::log('every-hour = \''.$hoursArr['every-hour'].'\'.', 'debug');
        if($hoursArr['every-hour'] === TRUE){
            Logger::log('It will be executed every hour.');
            $retVal = TRUE;
        }
        else{
            $retVal = FALSE;
            $current = intval(date('H'));
            Logger::log('Current hour = \''.$current.'\'.', 'debug');
            $ranges = $hoursArr['at-range'];
            foreach ($ranges as $range){
                Logger::log('Min Range Value = \''.$range[0].'\', Max Range Value = \''.$range[1].'\'.', 'debug');
                if($current >= $range[0] && $current <= $range[1]){
                    Logger::log('It is in given range.');
                    $retVal = TRUE;
                    break;
                }
            }
            if($retVal === FALSE){
                Logger::log('Checking if job will be executed at the specific hour...');
                $hours = $hoursArr['at-every-x-hour'];
                $retVal = in_array($current, $hours);
                if($retVal === FALSE){
                    $hours = $hoursArr['every-x-hours'];
                    foreach ($hours as $hour){
                        if($current % $hour == 0){
                            Logger::log('Job will be executed at the specific hour.');
                            $retVal = TRUE;
                            break;
                        }
                    }
                }
                else{
                    Logger::log('Job will be executed at the specific hour.');
                }
            }
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Checks if current minute in time is a minute at which the job must be 
     * executed.
     * @return boolean The method will return TRUE if the current minute in 
     * time is a minute at which the job must be executed.
     * @since 1.0
     */
    public function isMinute(){
        Logger::logFuncCall(__METHOD__);
        $minuteArr = $this->jobDetails['minutes'];
        Logger::log('Checking if the job will be executed every minute...');
        Logger::log('every-minute = \''.$minuteArr['every-minute'].'\'.', 'debug');
        if($minuteArr['every-minute'] === TRUE){
            Logger::log('It will be executed every minute.');
            $retVal = TRUE;
        }
        else{
            Logger::log('Checking if current minute is in ranges of minutes...');
            $retVal = FALSE;
            $current = intval(date('i'));
            Logger::log('Current minute = \''.$current.'\'.', 'debug');
            $ranges = $minuteArr['at-range'];
            foreach ($ranges as $range){
                Logger::log('Min Range Value = \''.$range[0].'\', Max Range Value = \''.$range[1].'\'.', 'debug');
                if($current >= $range[0] && $current <= $range[1]){
                    Logger::log('It is in given range.');
                    $retVal = TRUE;
                    break;
                }
            }
            if($retVal === FALSE){
                Logger::log('Checking if job will be executed at the specific minute...');
                $minutes = $minuteArr['at-every-x-minute'];
                $retVal = in_array($current, $minutes);
                if($retVal === FALSE){
                    $minutes = $minuteArr['every-x-minutes'];
                    foreach ($minutes as $min){
                        if($current % $min == 0){
                            Logger::log('Job will be executed at specific minute.');
                            $retVal = TRUE;
                            break;
                        }
                    }
                }
                else{
                    Logger::log('Job will be executed at specific minute.');
                }
            }
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * 
     * @param type $dayOfWeekField
     * @return boolean
     * @since 1.0
     */
    private function _checkDayOfWeek($dayOfWeekField){
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
     * Returns an associative array which contains details about the timings 
     * at which the job will be executed.
     * @return array The array will have the following indices: 
     * <ul>
     * <li><b>minutes</b>: Contains sub arrays which has info about the minutes 
     * at which the job will be executed.</li>
     * <li><b>hours</b>: Contains sub arrays which has info about the hours 
     * at which the job will be executed.</li>
     * <li><b>days-of-month</b>: Contains sub arrays which has info about the days of month 
     * at which the job will be executed.</li>
     * <li><b>months</b>: Contains sub arrays which has info about the months 
     * at which the job will be executed.</li>
     * <li><b>days-of-week</b>: Contains sub arrays which has info about the days of week 
     * at which the job will be executed.</li>
     * </ul>
     * @since 1.0
     */
    public function getJobDetails() {
        return $this->jobDetails;
    }
    /**
     * Sets the event that will be fired in case it is time to execute the job.
     * @param callable $func The function that will be executed if it is the 
     * time to execute the job.
     * @param array $funcParams An array which can hold some parameters that 
     * can be passed to the function.
     * @since 1.0
     */
    public function setOnExecution($func,$funcParams=array()){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if first parameter is callable...');
        if(is_callable($func)){
            Logger::log('It is callable. Setting the on execute event.');
            $this->events['on']['func'] = $func;
            Logger::log('Checking if the second parameter is an array.');
            if(gettype($funcParams) == 'array'){
                Logger::log('It is an array. Setting the array as callable parameters.');
                $this->events['on']['params'] = $funcParams;
            }
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Execute the event which should run when it is time to execute the job. 
     * This method will be called automatically when cron URL is accessed. The 
     * method will check if it is time to execute the associated event or 
     * not. If it is the time, The event will be executed. If 
     * the job is forced to execute, the event that is associated with the 
     * job will be executed even if it is not the time to execute the job.
     * @param boolean $force [Optional] If set to TRUE, the job will be forced to execute 
     * even if it is not job time. Default is FALSE.
     * @return boolean If the event that is associated with the job is executed, 
     * the method will return TRUE. If it is not executed, the method 
     * will return FALSE.
     * @since 1.0
     */
    public function execute($force=false){
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        Logger::log('Checking if it is time to run cron job...');
        if($force === TRUE || ($this->isMinute() && $this->isHour() && $this->isDayOfMonth() && 
        $this->isMonth() && $this->isDayOfWeek())){
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
