<?php

/**
 * RRule.php
 *
 * A formmodel that handles a single rrule
 *
 * Follows the iCalendar rules
 * @link http://www.kanzaki.com/docs/ical/rrule.html
 *
 * PHP version 5.3+
 *
 * @author Joe Blocher <yii@myticket.at>
 * @copyright 2012 myticket it-solutions gmbh
 * @license New BSD License
 * @category Date and Time
 * @package recur
 * @version 0.9
 */

class RRule extends CFormModel
{
    public $EXRULE; //boolean, is exclude rule

    public $DSTART;
    public $TSTART;
    public $DEND;
    public $TEND;

    public $FREQ;
    public $INTERVAL;
    public $BYDAY;
    public $prefixBYDAY;
    public $BYMONTH;
    public $BYMONTHDAY;
    public $COUNT;
    public $UNTIL;


    private $_rulestr;
    private $_dtDelimiter = '#';

    private $_eventTitle;
    private $_eventId;
    private $_eventUrl;

    /**
     * Const for generating the RRule
     * Don't change
     */
    const rrEXRULE = 'EXRULE';
    const rrRULE = 'RRULE';
    const rrBYMONTH = 'BYMONTH';
    const rrBYMONTHDAY = 'BYMONTHDAY';
    const rrCOUNT = 'COUNT';
    const rrINTERVAL = 'INTERVAL';
    const rrDTSTART = 'DTSTART';
    const rrDSTART = 'DSTART';
    const rrTSTART = 'TSTART';
    const rrDTEND = 'DTEND';
    const rrDEND = 'DEND';
    const rrTEND = 'TEND';
    const rrUNTIL = 'UNTIL';
    const rrBYDAY = 'BYDAY';
    const rrPrefixBYDAY = 'prefixBYDAY';
    const rrFREQ = 'FREQ';
    const rrDAILY = 'DAILY';
    const rrWEEKLY = 'WEEKLY';
    const rrMONTHLY = 'MONTHLY';
    const rrYEARLY = 'YEARLY';
    const rrMO = 'MO';
    const rrTU = 'TU';
    const rrWE = 'WE';
    const rrTH = 'TH';
    const rrFR = 'FR';
    const rrSA = 'SA';
    const rrSU = 'SU';

    /**
     * Default date/time formats
     */
    const rrDateFormat = 'Y-m-d';
    const rrTimeFormat = 'H:i';
    const rrDateTimeFormat = 'Y-m-d H:i';


    /**
     * Set the ExRule to false
     */
    public function init()
    {
        $this->EXRULE = 0;
    }

    /*
    * Returns array of php DateTime object
    */
    public static function getDateTimesFromRRuleStr($rrulestr,$dtStart)
    {
        $dateTimes = array();

        if(empty($rrulestr) || empty($dtStart))
            return $dateTimes;

        Yii::import('ext.recur.vendors.tplaner-When.When');

        $r = new When();
        $r->recur($dtStart)->rrule($rrulestr);

        while($result = $r->next())
        {
            $dateTimes[] = $result;
        }

        return $dateTimes;
    }


    /**
     * Split a datetime string into date and time
     *
     * @static
     * @param $dateTime
     * @param string $date
     * @param string $time
     * @param string $dFormat
     * @param string $tFormat
     */
    public static function splitDateTime($dateTime,&$date='',&$time='',$dFormat=self::rrDateFormat,$tFormat=self::rrTimeFormat)
    {
        $parts = date_parse($dateTime);

        $date = date($dFormat,mktime(null,null,null,$parts['month'],$parts['day'],$parts['year']));

        if(!empty($parts['hour']))
           $time = date($tFormat,mktime($parts['hour'],$parts['minute']));
    }


    /**
     * Check if it is a valid RRule attribute
     *
     * @static
     * @param $attribute
     * @return bool
     */
    public static function isRRuleAttribute($attribute)
    {
       $attributes = array('FREQ','INTERVAL','BYDAY','BYMONTH','BYMONTHDAY','COUNT','UNTIL','WKST','BYSETPOS','BYYEARDAY','BYWEEKNO');
       return in_array($attribute,$attributes);
    }

    /**
     * Convert a datetime string to the iCal format
     *
     * @static
     * @param $datetime
     * @param bool $incl_time
     * @return string
     */
    public static function getIcalDate($datetime, $incl_time = true)
    {
        return $incl_time ? date('Ymd\THi', strtotime($datetime)) : date('Ymd', strtotime($datetime));
    }

    /**
     * Initializes this model.
     * This method is invoked in the constructor right after {@link scenario} is set.
     * You may override this method to provide code that is needed to initialize the model (e.g. setting
     * initial property values.)
     */
    public function initAttributesFromForm($widgetId='rrf',$method='POST')
    {
        $this->unsetAttributes();
        $this->_rulestr = null;

        $data = $method=='POST' ? $_POST : $_GET;
        if(isset($data) && isset($data[$widgetId]))
        {
            //print_r($_POST);
            $attributes = $data[$widgetId];

            $this->setAttributes($data[$widgetId]['RULE']);

            return $this->validate();
        }
        else
            return null; //no POST/GET rrule data
    }


    /**
     * The full rule (including dtstart,dtend, ruletype)
     *
     * @return string
     */

    public function getRuleStr()
    {
        if(isset($this->_rulestr))
            return $this->_rulestr;

        $rulestr = '';

        if(isset($this->FREQ)) //required for building the rrule
        {
            $rulestr = self::rrFREQ.'='.$this->FREQ.';';

            if(isset($this->BYDAY) && is_array($this->BYDAY) && count($this->BYDAY))
            {
                if(isset($this->prefixBYDAY) && is_array($this->prefixBYDAY) && count($this->prefixBYDAY))
                {
                    $days = '';
                    foreach($this->BYDAY as $day)
                        foreach($this->prefixBYDAY as $prefix)
                        {
                            $days .= $prefix . $day .',';
                        }

                    $days = substr($days,0,-1);

                    $rulestr .= self::rrBYDAY . '='.$days .';';
                }
                else
                {
                    $rule = implode(',',$this->BYDAY) .';';
                    $rulestr .=  self::rrBYDAY . '='.$rule;
                }
            }

            if(isset($this->BYMONTH) && is_array($this->BYMONTH) && count($this->BYMONTH))
            {
                $rulestr .= self::rrBYMONTH . '=' . implode(',',$this->BYMONTH) .';';
            }

            if(isset($this->BYMONTHDAY) && is_array($this->BYMONTHDAY) && count($this->BYMONTHDAY))
            {
                $rulestr .= self::rrBYMONTHDAY . '=' . implode(',',$this->BYMONTHDAY) .';';
            }

            //count or until
            if(!empty($this->COUNT))
                $rulestr .= self::rrCOUNT . '=' . $this->COUNT .';';
            else
             if(!empty($this->UNTIL))
                $rulestr .= self::rrUNTIL . '=' . $this->UNTIL .';';

            if(!empty($this->INTERVAL))
                $rulestr .= self::rrINTERVAL . '=' . $this->INTERVAL .';';

        }

        if(!empty($rulestr))
        {
            $rulestr = substr($rulestr,0,-1);
            $rulestr = $this->getType()  .':'. $rulestr;

            $dtStart = $this->getIcalDate($this->getDTStart(),!empty($this->TSTART));
            $dtEnd = !empty($this->TEND) ? $this->getIcalDate($this->getDTEnd(),!empty($this->TEND)) : '';

            $rulestr = self::rrDTSTART  .'='. $dtStart . $this->_dtDelimiter .
                     self::rrDTEND  .'='. $dtEnd . $this->_dtDelimiter .$rulestr;
        }

        $this->_rulestr =  $rulestr;

        return $this->_rulestr;
    }

    /**
     * The rrule string (without dtstart,dtend) for the WHEN component
     *
     * @return string
     */
    public function getRRuleStr()
    {
        $rrule = '';

        if(!isset($this->_rulestr))
            $this->getRuleStr();

        if(empty($this->_rulestr))
            return null;

        list($prefix,$rrule) = explode(':',$this->_rulestr,2);
        return $rrule;
    }


    /**
     * Initialize the attribute values from the $_POST vars
     *
     * @param string $widgetId
     * @param string $method
     * @return bool|null|string
     */
    public function initFromForm($widgetId='rrf',$method='POST')
    {
        if(!($valid = $this->initAttributesFromForm($widgetId,$method)))
            return $valid; //null if no POST/GET data, false if not valid

        return $this->getRuleStr(); //empty string if FREQ is not set, rule-string otherwise
    }


    /**
     * Initialize the model from a rule string
     *
     * @return bool|null
     */
    protected function initFromRuleStr()
    {
        $this->unsetAttributes();

        if(empty($this->_rulestr))
            return null; //null if rule is empty

        //parse the

        list($dtStart,$dtEnd,$type_rule) = explode($this->_dtDelimiter,$this->_rulestr,3);
        list($type,$rrule) = explode(':',$type_rule,2);

        $this->EXRULE = (integer)($type == self::rrEXRULE);

        list($name,$dtVal) = explode('=',$dtStart,2);
        self::splitDateTime($dtVal,$dVal,$tVal);
        $this->DSTART = $dVal;
        $this->TSTART = $tVal;

        list($name,$dtVal) = explode('=',$dtEnd,2);
        self::splitDateTime($dtVal,$dVal,$tVal);
        $this->DEND = $dVal;
        $this->TEND = $tVal;

        $parts = explode(";", $rrule);

        foreach($parts as $part)
        if(!empty($part))
        {
            list($attribute, $param) = explode("=", $part);

            $attribute = strtoupper($attribute);
            $param = strtoupper($param);

            switch($attribute)
            {
                case "BYDAY":
                case "BYMONTH":
                case "BYMONTHDAY":
                    $this->$attribute = explode(',',$param);
                    break;
                default:
                    if(self::isRRuleAttribute($attribute))
                        $this->$attribute=$param;
                    break;
            }
        }

        return $this->validate() ? $this->_rulestr : false;
    }


    /**
     * Set the rulestring and init the model
     *
     * @param $value
     * @return bool|null
     */
    public function setRulestr($value)
    {
        $this->_rulestr =  trim($value);
        return $this->initFromRuleStr();
    }

    /**
     * Get the datetime start string
     * @return string
     */
    public function getDTStart()
    {
        $dtStart = $this->DSTART;

        if (!empty($this->TSTART))
            $dtStart .= ' ' . $this->TSTART;

        return $dtStart;
    }

    /**
     * Get the datetime end string
     * @return string
     */
    public function getDTEnd()
    {
        $dtEnd = '';

        if (!empty($this->TEND))
        {
            $dtEnd = empty($this->DEND) ? $this->DSTART : $this->DEND;
            $dtEnd .= ' ' . $this->TEND;
        }

        return $dtEnd;
    }

    /**
     * Get the type of this rule model (RRule or EXRule)
     * @return string
     */
    public function getType()
    {
       return $this->isExRule() ? self::rrEXRULE : self::rrRULE;
    }


    /**
     * Check if this is an EXRule
     *
     * @return bool
     */
    public function isExRule()
    {
        return (boolean)$this->EXRULE;
    }

    /**
     * Check if an endtime is set
     *
     * @static
     * @param $dtItem
     * @return bool
     */
    public static function hasEndDateTime($dtItem)
    {
        return isset($dtItem['DTEND']) && $dtItem['DTSTART'] != $dtItem['DTEND'];
    }


    /**
     * Get the formatted datetimes for the list preview
     * Uses CDateFormatter, because php DateTime doesn't support locales
     *
     * @param string $dtformat
     * @param string $dformat
     * @param string $tformat
     * @return array
     */
    public function getPreviewListData($dtformat,$dformat,$tformat)
    {

        $result = array();

        $dateTimes = $this->getDateTimes(true,true);

        foreach($dateTimes as $dtStartEnd)
        {
            $dtStr = Yii::app()->dateFormatter->format($dtformat,$dtStartEnd['DTSTART']->getTimestamp());
            $dtStr = empty($this->TSTART) ? Yii::app()->dateFormatter->format($dformat,$dtStartEnd['DTSTART']->getTimestamp())
                                          : Yii::app()->dateFormatter->format($dtformat,$dtStartEnd['DTSTART']->getTimestamp());
            //$dtStr = empty($this->TSTART) ? $dtStartEnd['DTSTART']->format($dformat) : $dtStartEnd['DTSTART']->format($dtformat);

            if(!empty($this->TEND))
                $dtStr .= ' - '. Yii::app()->dateFormatter->format($tformat,$dtStartEnd['DTEND']->getTimestamp());
                //$dtStr .= ' - '.$dtStartEnd['DTEND']->format($tformat);

            $result[] = $dtStr;
        }

        return $result;
    }

    /**
     * Generate the calendar items for the fullcalendar
     *
     * @param null $eventModel
     * @param string $eventTitleAttr
     * @param string $defaultEventTitle
     * @param string $eventIdAttr
     * @param string $eventUrl
     * @return array
     */
    public function getFullCalendarEvents()
    {
        $result = array();
        $dateTimes = $this->getDateTimes(true,true);

        if(empty($dateTimes))
            return $result;

        $dtId = 0;
        foreach($dateTimes as $dateTime)
        {
            $hasDTEnd = $this->hasEndDateTime($dateTime);

            $dtId++;
            $format = $hasDTEnd ? 'Y-m-d H:i' : 'Y-m-d';

            $eventUrl = $this->getEventUrl();
            if(!empty($eventUrl))
                $eventUrl = Yii::app()->createUrl($eventUrl,array('dtId'=>$dtId));


            $dtStart = $dateTime['DTSTART']->format($format);
            $id = $this->getEventId() . '__' .$dtId;

            $event = array(
                'id' => $id,
                'title' => $this->getEventTitle(),
                'start' => $dtStart,
                'url' => $eventUrl,
            );

            if($hasDTEnd)
                $event['end'] = $dateTime['DTEND']->format($format);

            $result[] = $event;
        }

        return $result;
    }


    /**
     * Get the array of DateTime objects
     *
     * @param bool $dtStart
     * @param bool $dtEnd
     * @return array
     */
    public function getDateTimes($dtStart=true,$dtEnd=false)
    {
        if($dtStart && $dtEnd)
        {
           $result = array();
           $dateTimesStart =  $this->getDateTimesFromRRuleStr($this->getRRuleStr(), $this->getDTStart());
           if(!empty($dateTimesStart))
           {
               foreach($dateTimesStart as $dtStartObj)
               {
                   if(empty($this->TEND))
                   {
                      $result[] = array(
                         'DTSTART' => $dtStartObj,
                         'DTEND' => null,
                      );
                   }
                   else
                   {
                       $dtEndObj = clone $dtStartObj;

                       $endParts = date_parse($this->TEND);
                       $dtEndObj->setTime($endParts['hour'],$endParts['minute']);

                       $result[] = array(
                           'DTSTART' => $dtStartObj,
                           'DTEND' => $dtEndObj,
                       );
                   }
               }
           }

           return $result;
        }
        else
        if($dtStart)
            return $this->getDateTimesFromRRuleStr($this->getRRuleStr(), $this->getDTStart());
        elseif($dtEnd)
            return $this->getDateTimesFromRRuleStr($this->getRRuleStr(), $this->getDTEnd());

        return array();
    }


    /**
     * Get the dateTimes from all rules as array of formatted strings
     *
     * @param string $format
     * @return array
     */
    public function getFormattedDateTimes($dtStart=true,$dtEnd=false,$format='Y-m-d H:i')
    {
        $result = array();

        $dateTimes = $this->getDateTimes($dtStart,$dtEnd);

        if($dtStart && $dtEnd)
        {
            foreach($dateTimes as $dateTimes)
            $result[] = array(
                'DTSTART' => $dateTimes['DTSTART']->format($format),
                'DTEND' => $dateTimes['DTEND']->format($format),
            );
        }
        else
        foreach($dateTimes as $dtObj)
            $result[] = $dtObj->format($format);

        return $result;
    }


    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('FREQ,DSTART', 'required'),
            array('FREQ', 'in', 'range'=>array('DAILY','WEEKLY','MONTHLY','YEARLY')),
            //array('BYDAY', 'checkEmpty','values'=>array('MO','TU','WE','TH','FR','SA','SU')),
            array('BYMONTH', 'checkArrayIntValuesInRange','min'=>1,'max'=>12),
            array('BYMONTHDAY', 'checkArrayIntValuesInRange','min'=>-31,'max'=>31),
            array('COUNT', 'numerical', 'integerOnly'=>true,'min'=>0,'max'=>1000),
            array('COUNT', 'checkUntilOrCount'),
            array('TSTART', 'checkStartEnd'),
            array('prefixBYDAY', 'checkByDayNotEmpty'),
            array('INTERVAL', 'numerical', 'integerOnly'=>true,'min'=>1,'max'=>100),
            array('EXRULE', 'numerical', 'integerOnly'=>true,'min'=>0,'max'=>1),
            array('UNTIL,TSTART,DEND,TEND,BYDAY', 'safe'),
        );
    }


    /**
     * Validate if COUNT or UNTIL is set
     *
     * @param $attribute
     * @param $params
     */
    public function checkUntilOrCount($attribute,$params)
    {
        if(empty($this->COUNT) && empty($this->UNTIL))
            $this->addError('UNTIL', RRuleForm::t('UNTIL or COUNT must be set'));
    }

    /**
     * Validate if a weekday is set, if a prefixByDay is set
     * @param $attribute
     * @param $params
     */
    public function checkByDayNotEmpty($attribute,$params)
    {
        if(!empty($this->prefixBYDAY) && empty($this->BYDAY))
            $this->addError('prefixBYDAY', RRuleForm::t('Please select a least one weekday'));
    }

    /**
     * Validate if endtime > starttime
     *
     * @param $attribute
     * @param $params
     */
    public function checkStartEnd($attribute,$params)
    {
        if(!empty($this->TSTART) && !empty($this->TEND))
        {
            $tStart = DateTime::createFromFormat('H:i',$this->TSTART);
            $tEnd = DateTime::createFromFormat('H:i',$this->TEND);

            if($tStart>$tEnd)
               $this->addError('TEND', RRuleForm::t('The endtime must be greater than the starttime') .': ' . $this->getRuleStr());
        }
    }


    /**
     * Validate if is empty or the value is in a values array
     * @param $attribute
     * @param $params
     * @return mixed
     */
    public function checkEmptyOrInArray($attribute,$params)
    {
        $values = $this->$attribute;

        if(empty($values))
            return;

        if(!is_array($values))
            $this->addError($attribute, 'must be an array');
        else
        {
          foreach($values as $value)
            if(!in_array($value,$params['values']))
            {
                $this->addError($attribute, 'invalid value');
                return;
            }

        }
    }

    /**
     * Validate if the intvalue is in a range
     *
     * @param $attribute
     * @param $params
     * @return mixed
     */
    public function checkArrayIntValuesInRange($attribute,$params)
    {
        $values = $this->$attribute;

        if(empty($values))
            return;

        if(!is_array($values))
            $this->addError($attribute, 'must be an array');
        else
        {
            foreach($values as $value)
            {
                if((int)$value < $params['min'])
                {
                    $this->addError($attribute, 'Value cannot be lesser than '.$params['min']);
                    return;
                }

                if((int)$value > $params['max'])
                {
                    $this->addError($attribute, 'Value cannot be greater than '.$params['max']);
                    return;
                }
            }
        }
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'FREQ'=>RRuleForm::t('Frequence'),
            'DSTART'=>RRuleForm::t('Start date'),
            'TSTART'=>RRuleForm::t('Start time'),
            'DEND'=>RRuleForm::t('End date'),
            'TEND'=>RRuleForm::t('Ende time'),
            'BYMONTH'=>RRuleForm::t('By month'),
            'BYMONTHDAY'=>RRuleForm::t('By monthday'),
            'COUNT'=>RRuleForm::t('Count'),
            'INTERVAL'=>RRuleForm::t('Interval'),
            'EXRULE'=>RRuleForm::t('Except rule'),
        );
    }

    /**
     * Set the eventId
     * @param $eventId
     */
    public function setEventId($eventId)
    {
        $this->_eventId = $eventId;
    }

    /**
     * Set the eventTitle
     * @param $eventTitle
     */
    public function setEventTitle($eventTitle)
    {
        $this->_eventTitle = $eventTitle;
    }

    /**
     * Set the eventUrl
     * @param $eventUrl
     */
    public function setEventUrl($eventUrl)
    {
        $this->_eventUrl = $eventUrl;
    }

    /**
     * Get the eventUrl
     * @return string
     */
    public function getEventUrl()
    {
        return $this->_eventUrl;
    }

    /**
     * Get the eventId
     * @return string
     */
    public function getEventId()
    {
        return $this->_eventId;
    }

    /**
     * Get the eventTitle
     * @return string
     */
    public function getEventTitle()
    {
        if(!isset($this->_eventTitle))
            $this->_eventTitle = 'Event';

        return $this->_eventTitle;
    }
}