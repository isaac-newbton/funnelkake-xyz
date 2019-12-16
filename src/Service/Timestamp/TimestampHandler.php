<?php
namespace App\Service\Timestamp;

use DateTime;
use DateTimeZone;

class TimestampHandler {
    public function createTimestamp(){
        $datetime = new DateTime();
        $timezone = new DateTimeZone('America/New_York');
        $datetime->setTimezone($timezone);
        return $datetime;
    }
}