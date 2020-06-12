<?php
use Crunz\Schedule;

$schedule = new Schedule();

$schedule->run('php bin/console app:increase-time')
    ->description('Passes time for pets, and fireplace. Deletes expired sessions.')
    ->everyMinute()
;

$schedule->run('php bin/console app:run-park-events')
    ->description('Runs park events.')
    ->everyMinute()
;

$schedule->run('php bin/console app:buzz-buzz')
    ->description('Updates beehives.')
    ->everyHour()
;

$schedule->run('php bin/console app:calculate-daily-market-item-averages')
    ->description('Calculates daily min, max, and average market prices for all items bought/sold.')
    ->daily()
;

return $schedule;
