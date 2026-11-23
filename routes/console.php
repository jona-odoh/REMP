<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('ksmct:backup')
    ->dailyAt('02:00')
    ->timezone(config('app.timezone', 'Africa/Lagos'))
    ->withoutOverlapping();

Schedule::command('ksmct:check-expiring-licenses')
    ->dailyAt('07:30')
    ->timezone(config('app.timezone', 'Africa/Lagos'))
    ->withoutOverlapping();

Schedule::command('ksmct:send-task-reminders')
    ->dailyAt('08:00')
    ->timezone(config('app.timezone', 'Africa/Lagos'))
    ->withoutOverlapping();
