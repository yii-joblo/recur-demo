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
    $count = !empty($model->COUNT) ? $model->COUNT .' times ' : '';

    if($model->isExRule())
        echo CHtml::tag('span',array('class'=>'exrule'),'Exclude') .' ';
    else
        echo CHtml::tag('span',array('class'=>'rrule'),'Repeat') .' ';

    echo $count .' from ' . $model->getDTStart() .' ';

    //$dtEnd = $model->getDTEnd();
    if(!empty($model->UNTIL))
        echo 'until ' .$model->UNTIL .' ';


    $freq = '';
    switch($model->FREQ)
    {
        case RRule::rrDAILY:
            echo 'every ';
            $freq = 'day';
            break;
        case RRule::rrWEEKLY:
            echo 'every ';
            $freq = 'week';
            break;
        case RRule::rrMONTHLY:
            echo 'every ';
            $freq = 'month';
            break;
        case RRule::rrYEARLY:
            echo 'every ';
            $freq = 'year';
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

        echo '; every ' . $days .' of the month; ';
    }
    else
        echo'; ';

    if(is_array($model->BYMONTH) && count($model->BYMONTH))
    {
        echo 'only in the month: ' . implode(', ',$model->BYMONTH) .'; ';
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
                        $prefix .= 'first, ';
                        break;
                    case '2':
                        $prefix .= 'second, ';
                        break;
                    case '3':
                        $prefix .= 'third, ';
                        break;
                    case '4':
                        $prefix .= 'forth, ';
                        break;
                    case '5':
                        $prefix .= 'fifth, ';
                        break;
                    case '-1':
                        $prefix .= 'last, ';
                        break;
                    case '-2':
                        $prefix .= 'second last, ';
                        break;
                    case '-3':
                        $prefix .= 'third last, ';
                        break;
                    case '-4':
                        $prefix .= 'forth last, ';
                        break;
                    case '-5':
                        $prefix .= 'fifth last, ';
                        break;
                }
            }

            $prefix = substr($prefix,0,-2) .' ';
        }


        foreach($model->BYDAY as $day)
            $days .= RRuleForm::t($day) .', ';

        $days = substr($days,0,-2);

        if(empty($prefix))
           echo 'on ' . $days .'; ';
        else
           echo 'on the ' . $prefix . $days .'; ';
    }

}
