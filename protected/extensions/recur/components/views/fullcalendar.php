<?php
/**
 * Display the fullcalendar as ajax response
 */

$this->widget('ext.recur.components.Fullcalendar',
    array(
        'isAjaxRequest' => true, //important!
        'events'=>isset($events) ? $events : array(),
        'options'=>array(
            'theme' => true,
            'header'=>array(
                'left'=>'title',
                //'center'=>'month,agendaWeek', //agendaDay,agendaWeek,basicWeek,basicDay, TODO: datetime/end format
                'right'=>'today prev,next'),
        )
    )
);