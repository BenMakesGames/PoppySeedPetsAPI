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

return $schedule;