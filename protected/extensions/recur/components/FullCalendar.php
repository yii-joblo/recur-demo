<?php
/**
 * FullCalendar.php
 *
 * A widget to display the fullcalendar by arshaw
 *
 * @see ext.recur.vendors.fullcalendar
 * @link http://arshaw.com/fullcalendar/
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
class FullCalendar extends CWidget
{
    public $events;

    public $isAjaxRequest = false;

    public $options = array(
        'timeFormat'=>'H(:mm)',
        // time formats
        'titleFormat' => array(
                        'month' => 'MMMM yyyy',
                        'week' => "MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}",
                        'day' => 'dddd, MMM d, yyyy'
                      ),
        'columnFormat' => array(
            'month' => 'ddd',
            'week' => 'ddd M/d',
            'day' => 'dddd M/d'
        ),

        'header'=>array(
            'left'=>'title',
            'center'=>'month,agendaWeek', //agendaDay,basicWeek,basicDay,
            'right'=>'today prev,next'),

        'buttonText'=>array(
            'today'=>'today',
            'month'=>'month',
            'week'=>'week',
            'day'=>'day',
        ),
        'monthNames'=>array('January', 'February', 'March', 'April', 'May', 'June', 'July',
            'August', 'September', 'October', 'November', 'December'),
        'monthNamesShort'=>array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'),
        'dayNames'=>array('Sunday', 'Monday', 'Tuesday', 'Wednesday',
            'Thursday', 'Friday', 'Saturday'),
        'dayNamesShort'=>array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'),
        'allDayText'=>'All day',
        'axisFormat'=>'HH(:mm)',
        'slotMinutes'=>30,
        'firstHour'=>8,     // first visible hour
        'minTime'=>'7:30',  // start day time
        'maxTime'=>'21:00', // end day time

    );


    public function run()
    {
        $containerId = $this->id.'_container';
        echo CHtml::tag('div',array('id'=>$containerId));

        if(!empty($this->events))
          $this->options['events'] = $this->events;

        $options=CJavaScript::encode($this->options);

        if(!$this->isAjaxRequest)
           self::publishScripts($options);


        $js = "jQuery('#{$containerId}').fullCalendar($options);";

        if($this->isAjaxRequest)
            echo CHtml::script($js);
        else
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#EventCal', $js);
    }

    /**
     * Publish the fullcalendar scripts
     * @static
     * @param null $theme
     */
    public static function publishScripts($theme=null)
    {
        $cs = Yii::app()->getClientScript();
        $scriptUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('ext.recur.vendors.fullcalendar.fullcalendar'));
        $cs->registerCssFile($cs->getCoreScriptUrl() . '/jui/css/base/jquery-ui.css');


        $cs->registerCssFile($scriptUrl . '/fullcalendar.css');
        $cs->registerCssFile($scriptUrl . '/fullcalendar.print.css');

        $cs->registerCoreScript('jquery');
        $cs->registerScriptFile($cs->getCoreScriptUrl() . '/jui/js/jquery-ui.min.js');
        $cs->registerScriptFile($scriptUrl . '/fullcalendar.min.js');
    }


}