<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('ksmct:backup')
    ->dailyAt('02:00')
    ->timezone(config('app.timezone', 'Africa/Lagos'))
    ->withoutOverlapping();
