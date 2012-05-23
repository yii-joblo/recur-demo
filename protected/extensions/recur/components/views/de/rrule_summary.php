<?php
/**
 * Display the rule string in the rulesContainer as a userfriendly output
 * You can copy this viewfile and set the property RRuleForm::ruleSummaryView to your customized view.
 *
 * Don't use $this in this view, because it can be the RRuleForm widget or the action controller
 *
 * $model is an instance of the RRule model
 */

if(isset($model->FREQ)) //required for building the rrule
{
    $count = !empty($model->COUNT) ? $model->COUNT .' mal ' : '';

    if($model->isExRule())
        echo CHtml::tag('span',array('class'=>'exrule'),'Schließe aus') .' ';
    else
        echo CHtml::tag('span',array('class'=>'rrule'),'Wiederhole') .' ';

    echo $count .' ab dem ' . $model->getDTStart() .' ';

    //$dtEnd = $model->getDTEnd();
    if(!empty($model->UNTIL))
        echo 'bis zum ' .$model->UNTIL .' ';


    $freq = '';
    switch($model->FREQ)
    {
        case RRule::rrDAILY:
            echo 'jeden ';
            $freq = 'Tag';
            break;
        case RRule::rrWEEKLY:
            echo 'jede ';
            $freq = 'Woche';
            break;
        case RRule::rrMONTHLY:
            echo 'jeden ';
            $freq = 'Monat';
            break;
        case RRule::rrYEARLY:
            echo 'jedes ';
            $freq = 'Jahr';
            break;
    }


    $interval = $model->INTERVAL>1 ? $model->INTERVAL.'. ' : '';
    echo $interval.$freq;


    if(isset($model->BYMONTHDAY) && is_array($model->BYMONTHDAY) && count($model->BYMONTHDAY))
    {
        $days = '';
        foreach($model->BYMONTHDAY as $day)
            $days .= $day .'., ';

        $days = substr($days,0,-2);

        echo '; jeweils am ' . $days .' des Monats; ';
    }
    else
        echo'; ';

    if(is_array($model->BYMONTH) && count($model->BYMONTH))
    {
        echo 'aber nur in den Monaten: ' . implode(', ',$model->BYMONTH) .'; ';
    }

    if(is_array($model->BYDAY) && count($model->BYDAY))
    {
        $days = '';
        $prefix = '';

        if(isset($model->prefixBYDAY) && is_array($model->prefixBYDAY) && count($model->prefixBYDAY))
        {

            foreach($model->prefixBYDAY as $key)
            {
                switch($key)
                {
                    case '1':
                        $prefix .= 'ersten, ';
                        break;
                    case '2':
                        $prefix .= 'zweiten, ';
                        break;
                    case '3':
                        $prefix .= 'dritten, ';
                        break;
                    case '4':
                        $prefix .= 'vierten, ';
                        break;
                    case '5':
                        $prefix .= 'fünften, ';
                        break;
                    case '-1':
                        $prefix .= 'letzten, ';
                        break;
                    case '-2':
                        $prefix .= 'vorletzten, ';
                        break;
                    case '-3':
                        $prefix .= 'drittletzten, ';
                        break;
                    case '-4':
                        $prefix .= 'viertletzten, ';
                        break;
                    case '-5':
                        $prefix .= 'fünftletzten, ';
                        break;
                }
            }

            $prefix = substr($prefix,0,-2) .' ';
        }


        foreach($model->BYDAY as $day)
            $days .= RRuleForm::t($day) .', ';

        $days = substr($days,0,-2);
        echo 'jeweils am ' . $prefix . $days .'; ';
    }

}


