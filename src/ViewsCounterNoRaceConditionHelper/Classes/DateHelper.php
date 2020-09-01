<?php

namespace ViewsCounterNoRaceConditionHelper\Classes;


use DateTime;

class DateHelper
{
    public  static function timeDiff($dateString)
    {
        $current = new DateTime(current_time('mysql'));
        $old = new DateTime($dateString);

        return $old->diff($current)->days;
    }

}
