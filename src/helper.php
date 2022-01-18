<?php

use chenvle\cron\command\Run;
use chenvle\cron\command\Schedule;

\think\Console::addDefaultCommands([
    Run::class,
    Schedule::class,
]);
