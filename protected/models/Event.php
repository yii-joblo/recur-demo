<?php
/**
 * Created by JetBrains PhpStorm.
 * User: joe
 * Date: 05.05.12
 * Time: 08:10
 * To change this template use File | Settings | File Templates.
 */
class Event extends CFormModel
{
    public $title;
    public $subtitle;

    private $_rrules=array(); //array of RRule

    public function toArray()
    {
        return array(
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->start,
            'end' => $this->end,
            'url' => $this->url,
        );
    }
}
