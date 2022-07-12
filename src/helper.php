<?php

use chenvle\cron\command\Run;
use chenvle\cron\command\Schedule;

//自定义命令行失败则注释
\think\Console::addDefaultCommands([
    Run::class,
    Schedule::class,
]);
