<?php
/**
 * RRules.php
 *
 * A model that handles an array of RRule
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

class RRules extends CModel
{
    private $_eventTitle;
    private $_eventId;
    private $_eventUrl;

    private $_rules; //array of string
    private $_models;

    /**
     * No public attributes
     * @return array
     */
    public function attributeNames()
    {
      return array();
    }

    /**
     * Create the RRules model and initialize the rules/models from the $_POST vars
     *
     * @static
     * @param string $widgetId
     * @return RRules
     */
    public static function createFromPOST($widgetId = 'rrf')
    {
        $model = new RRules();
        $model->initFromPOST($widgetId);
        return $model;
    }


    /**
     * Add a single rule
     *
     * @param $rulestr
     */
    public function addRule($rulestr)
    {
        if(!is_array($this->_rules))
            $this->_rules = array();

        if(!is_array($this->_models))
            $this->_models = array();

        if (($model = $this->createModel($rulestr)) !== null)
        {
            $this->_models[] = $model;
            if(!in_array($rulestr,$this->_rules))
                $this->_rules[]=$rulestr;
        }
    }


    /**
     * Initializes this model.
     * This method is invoked in the constructor right after {@link scenario} is set.
     * You may override this method to provide code that is needed to initialize the model (e.g. setting
     * initial property values.)
     */
    protected function initFromPOST($widgetId = 'rrf')
    {
        $this->_rules = array();
        $this->_models = array();

        if (isset($_POST) && !empty($_POST[$widgetId . 'RRULES']))
            $this->_rules = $_POST[$widgetId . 'RRULES'];

        $this->createModels();
    }

    /**
     * Get the rules as array of strings
     *
     * @return array of string
     */
    public function getRules()
    {
        return empty($this->_rules) ? array() : $this->_rules;
    }

    /**
     * Get the array of rule models
     *
     * @return array of RRule
     */
    public function getModels()
    {
        return empty($this->_models) ? array() : $this->_models;
    }


    /**
     * Create a RRule model from a rulestring
     *
     * @param $rulestr
     * @return null|RRule
     */
    protected function createModel($rulestr)
    {
        $model = new RRule();

        if (!$model->setRulestr($rulestr))
        {
            if ($model->hasErrors())
                $this->addErrors($model->errors);
            else
                $this->addError($rulestr, 'Invalid rule');

            return null;
        }
        else
            return $model;
    }

    /**
     * Create all models from the rulestrings
     */
    public function createModels()
    {
        $this->_models = array();

        foreach ($this->getRules() as $rulestr)
        {
          if (($model = $this->createModel($rulestr)) !== null)
            $this->_models[] = $model;
        }
    }

    /**
     * Compare function for sorting the result of getDateTimes()
     *
     * @param $a DateTime
     * @param $b DateTime
     * @return int
     */
    protected function sortDateTime($a,$b)
    {
        if ($a == $b)
            return 0;

        return ($a > $b) ? +1 : -1;
    }

    /**
     * Compare function for sorting the result of getDateTimes(true,true) with Start and End
     *
     * @param $a DateTime
     * @param $b DateTime
     * @return int
     */
    protected function sortStartDateTime($a,$b)
    {
        if ($a['DTSTART'] == $b['DTSTART'])
            return 0;

        return ($a['DTSTART'] > $b['DTSTART']) ? +1 : -1;
    }


    /**
     * Get the array of datetime objects for all rules
     *
     * @param string $format
     * @return array of string
     */
    public function getDateTimes($dtStart=true,$dtEnd=false)
    {
        $result = array();

        $models = $this->getModels();

        if (empty($models))
            return $result;

        $exFormat = 'Ymd'; //check exclude date
        $incFormat = 'c'; //check already registered datetimes

        $exDates = array();
        $incDateTimes = array();

        //collect the exclude dates in $exFormat
        foreach($models as $model)
        {
               if($model->isExRule())
               {
                   $dtObjects =  $model->getDateTimes(true);
                   if(!empty($dtObjects))
                   {
                       foreach($dtObjects as $dtObj)
                           $exDates[] =  $dtObj->format($exFormat);
                   }

               }
        }

        //collect the datetimes without the exlude dates
        foreach($models as $model)
        {
            if(!$model->isExRule())
            {
                $dtObjects =  $model->getDateTimes($dtStart,$dtEnd);
                if(!empty($dtObjects))
                {
                   if($dtStart && $dtEnd)
                   {
                       foreach($dtObjects as $dtStartEnd)
                       {
                           $startObj = $dtStartEnd['DTSTART'];
                           $endObj = $dtStartEnd['DTEND']; //can be null

                           if(empty($endObj))
                               $startEnd = $startObj->format($incFormat);
                           else
                               $startEnd = $startObj->format($incFormat) . $endObj->format($incFormat);

                           if(!in_array($startObj->format($exFormat),$exDates) && //don't add startdatetimes excluded dates
                               !in_array($startEnd,$incDateTimes)) //don't add the same start-end-datetime twice
                           {
                               $result[] =  array(
                                    'DTSTART' => $startObj,
                                    'DTEND' => $endObj
                               );
                               $incDateTimes[] =  $startEnd;
                           }
                       }
                   }
                   else
                   {
                       foreach($dtObjects as $dtObj)
                           if(!in_array($dtObj->format($exFormat),$exDates) && //don't add excluded dates
                               !in_array($dtObj->format($incFormat),$incDateTimes)) //don't add datetimes twice
                           {
                               $result[] =  $dtObj;
                               $incDateTimes[] =  $dtObj->format($incFormat);
                           }
                   }
                }

            }
        }

        if($dtStart && $dtEnd)
          usort ($result, array($this, "sortStartDateTime"));
        else
          usort ($result, array($this, "sortDateTime"));

        return $result;
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
     * Get the formatted datetimes for the list preview
     * Uses CDateFormatter, because php DateTime doesn't support locales
     *
     * @param bool $timeEnabled
     * @param string $dtformat
     * @param string $dformat
     * @param string $tformat
     * @return array
     */
    public function getPreviewListData($timeEnabled, $dtformat,$dformat,$tformat)
    {
        $result = array();

        if($timeEnabled)
        {
            $dateTimes = $this->getDateTimes(true,true);

            foreach($dateTimes as $dtStartEnd)
            {
                //$dtStr = $dtStartEnd['DTSTART']->format($dtformat); no locale support
                $dtStr = Yii::app()->dateFormatter->format($dtformat,$dtStartEnd['DTSTART']->getTimestamp());


                if(RRule::hasEndDateTime($dtStartEnd))
                    //$dtStr .= ' - '.$dtStartEnd['DTEND']->format($tformat);
                    $dtStr .= ' - '.Yii::app()->dateFormatter->format($tformat,$dtStartEnd['DTEND']->getTimestamp());

                $result[] = $dtStr;
            }
        }
        else
        {
            $dateTimes = $this->getDateTimes(true,false);

            foreach($dateTimes as $dtObj)
            {
                //$dtStr = $dtObj->format($dformat);
                $dtStr = $dtObj->format($dformat);
                $result[] = Yii::app()->dateFormatter->format($dformat,$dtObj->getTimestamp());
            }
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
            $hasDTEnd = RRule::hasEndDateTime($dateTime);

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
