<?php

protected function schedule(Schedule $schedule): void
{
    $schedule->command('spp:generate-tagihan')
        ->monthlyOn(1, '00:05')
        ->withoutOverlapping()
        ->onOneServer();
}
?>