<?php

use Illuminate\Support\Facades\Schedule;

// Password expiry reminders, 14 days before the 90-day expiry (D-082).
Schedule::command('identity:password-expiry-reminders')->dailyAt('06:00')->timezone('Africa/Mogadishu');
