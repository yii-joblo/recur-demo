<?php
/**
 * Display the fullcalendar as ajax response
 */

$this->widget('ext.recur.components.Fullcalendar',
    array(
        'isAjaxRequest' => true, //important!
        'events' => isset($events) ? $events : array(),
        'options' => array(
            'theme' => true,
            'titleFormat' => array(
                'month' => 'MMMM yyyy',
                'week' => "MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}",
                'day' => 'dddd, d. MMM, yyyy'
            ),
            'columnFormat' => array(
                'month' => 'ddd',
                'week' => 'ddd d. M.',
                'day' => 'dddd d. M.'
            ),
            'header'=>array(
                'left'=>'title',
                //'center'=>'month', //agendaDay,agendaWeek,basicWeek,basicDay, TODO: datetime/end format
                'right'=>'today prev,next'),

            'buttonText' => array(
                'today' => 'Heute',
                'month' => 'Monat',
                'week' => 'Woche',
                'day' => 'Tag',
            ),
            'monthNames' => array('Jänner', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli',
                'August', 'September', 'Oktober', 'November', 'Dezember'),
            'monthNamesShort' => array('Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun',
                'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'),
            'dayNames' => array('Sonntag', 'Montag', 'Dienstag', 'Mittwoch',
                'Donnerstag', 'Freitag', 'Samstag'),
            'dayNamesShort' => array('So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'),
            'allDayText' => 'Ganztägig',
        )
    )
);