<?php

namespace chenvle\cron\command;

use chenvle\cron\Task;
use Jenssegers\Date\Date;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Cache;
use think\facade\Config;

class Run extends Command
{
    /** @var Date */
    protected $startedAt;

    protected function configure()
    {
        $this->startedAt = Date::now();
        $this->setName('cron:run');
    }

    public function execute(Input $input, Output $output)
    {
        $tasks = Config::get('cron.tasks');

        foreach ($tasks as $taskClass) {
            if (is_subclass_of($taskClass, Task::class)) {
                /** @var Task $task */
                $task = new $taskClass();
                if ($task->isDue()) {
                    if (!$task->filtersPass()) {
                        continue;
                    }

                    if ($task->onOneServer) {
                        $this->runSingleServerTask($task);
                    } else {
                        $this->runTask($task);
                    }

                    $output->writeln("Task {$taskClass} run at ".Date::now());
                }
            }
        }
    }

    /**
     * @param $task Task
     *
     * @return bool
     */
    protected function serverShouldRun($task)
    {
        $key = $task->mutexName().$this->startedAt->format('Hi');
        if (Cache::has($key)) {
            return false;
        }
        Cache::set($key, true, 60);

        return true;
    }

    protected function runSingleServerTask($task)
    {
        if ($this->serverShouldRun($task)) {
            $this->runTask($task);
        } else {
            $this->output->writeln('<info>Skipping task (has already run on another server):</info> '.get_class($task));
        }
    }

    /**
     * @param $task Task
     */
    protected function runTask($task)
    {
        $task->run();
    }
}
